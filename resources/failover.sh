#!/bin/bash

# Adresse IP à ping pour vérifier la connectivité Internet
TEST_IP="8.8.8.8" 
PING_COUNT=3
WAIT_TIME=60  # Temps d'attente entre chaque vérification (en secondes)

# Connexions NetworkManager (les noms des connexions)
ETH_CONN="Wired connection 1"
WIFI_CONN=""  # Le nom de la connexion WiFi sera détecté automatiquement
LTE_CONN="JeedomLTE"

# Metrics de base pour les connexions
ETH_METRIC=100
WIFI_METRIC=200
LTE_METRIC=300
PENALTY_METRIC=10000

# Variables pour suivre les statuts précédents
prev_eth_status=-1
prev_wifi_status=-1
prev_lte_status=-1
current_active_connection=""

attempts=0  # Initialisation du compteur
max_attempts=5  # Nombre maximal de tentatives

# Fonction pour obtenir le nom de la connexion WiFi active
function get_active_wifi() {
    WIFI_CONN=$(nmcli -t -f NAME,DEVICE connection show --active | grep wlan | cut -d: -f1)
}

# Fonction pour tester la connectivité Internet sur une connexion
function check_internet() {
    local iface=$1
    if ping -I "$iface" -c "$PING_COUNT" "$TEST_IP" &> /dev/null; then
        echo "Connectivité OK sur $iface"
        return 0  # Connexion OK
    else
        echo "Pas de connectivité sur $iface"
        return 1  # Pas de connexion
    fi
}

# Fonction pour mettre à jour les métriques de la connexion
function update_metrics() {
    local iface=$1
    local status=$2
    local prev_status=$3

    if [ "$status" -ne "$prev_status" ]; then
        if [ "$status" -eq 0 ]; then
            # Connexion bonne, remettre la métrique à sa valeur par défaut
            case "$iface" in
                eth0)
                    nmcli connection modify "$ETH_CONN" ipv4.route-metric "$ETH_METRIC"
                    nmcli connection up "$ETH_CONN"
                    ;;
                wlan0)
                    nmcli connection modify "$WIFI_CONN" ipv4.route-metric "$WIFI_METRIC"
                    nmcli connection up "$WIFI_CONN"
                    ;;
                ppp0)
                    nmcli connection modify "$LTE_CONN" ipv4.route-metric "$LTE_METRIC"
                    nmcli connection up "$LTE_CONN"
                    ;;
            esac
        elif [ "$status" -eq 1 ]; then
            # Connexion mauvaise, ajouter la pénalité
            case "$iface" in
                eth0)
                    nmcli connection modify "$ETH_CONN" ipv4.route-metric "$((ETH_METRIC + PENALTY_METRIC))"
                    nmcli connection up "$ETH_CONN"
                    ;;
                wlan0)
                    nmcli connection modify "$WIFI_CONN" ipv4.route-metric "$((WIFI_METRIC + PENALTY_METRIC))"
                    nmcli connection up "$WIFI_CONN"
                    ;;
                ppp0)
                    nmcli connection modify "$LTE_CONN" ipv4.route-metric "$((LTE_METRIC + PENALTY_METRIC))"
                    nmcli connection up "$LTE_CONN"
                    ;;
            esac
        fi
    fi
}

# Fonction pour basculer vers l'interface avec la meilleure priorité
function switch_interface() {
    local eth_status=$1
    local wifi_status=$2
    local lte_status=$3

    local new_active_connection=""

    # Vérifie l'ordre de priorité : Ethernet > Wi-Fi > LTE
    if [ "$eth_status" -eq 0 ]; then
        new_active_connection="$ETH_CONN"
    elif [ "$wifi_status" -eq 0 ]; then
        new_active_connection="$WIFI_CONN"
    elif [ "$lte_status" -eq 0 ]; then
        new_active_connection="$LTE_CONN"
    else
        echo "Aucune connexion Internet disponible" >&2
        new_active_connection=""
    fi

    # Si la connexion active change, activer la nouvelle connexion
    if [ "$new_active_connection" != "$current_active_connection" ]; then
        if [ -n "$new_active_connection" ]; then
            echo "Basculer vers $new_active_connection"
            nmcli connection up "$new_active_connection"
            nmcli connection up "$current_active_connection"
        fi
        current_active_connection="$new_active_connection"
    else
        echo "La connexion active reste inchangée ($current_active_connection)"
        echo "La nouvelle connexion ($new_active_connection)"
    fi
}

check_modem() {
    # Récupérer les informations du modem via mmcli
    modem=$(mmcli -L 2>/dev/null)
    echo "Checking modem: $modem"

    # Vérifie si mmcli détecte le modem
    if [[ "$modem" == *"ModemManager"* ]]; then
        echo "Modem exists in ModemManager."
    else
        # Vérifie si /dev/ttyUSB-LTE-Quectel existe
        if [[ -L "/dev/ttyUSB-LTE-Quectel" ]]; then
            echo "Device /dev/ttyUSB-LTE-Quectel is present."

            # reboot la box suite à un compteur
            echo "Checking modem (attempt $((attempts + 1))/$max_attempts): $modem"
            if (( attempts >= max_attempts - 1 )); then
                reboot
            fi
            ((attempts++))
            return 1  # Modem détecté et lien présent
        else
            echo "Device /dev/ttyUSB-LTE-Quectel is missing."
            # pas de 4G
            return 2  # Modem détecté mais lien manquant
        fi
        echo "Modem does not exist in ModemManager."
        return 0  # Modem non détecté
    fi
}


# Boucle principale pour surveiller les connexions
while true; do
    # Mise à jour du nom de la connexion WiFi active
    get_active_wifi
    
    # Vérification de la connectivité pour chaque connexion
    if check_internet "eth0"; then
        eth_status=0
    else
        eth_status=1
    fi
    
    if [ -n "$WIFI_CONN" ]; then
        if check_internet "wlan0"; then
            wifi_status=0
        else
            wifi_status=1
        fi
    else
        wifi_status=2
    fi
    
    if check_internet "ppp0"; then
        lte_status=0
    else
        lte_status=1
        # ajout option 2 
    fi
    
    # Mettre à jour les métriques uniquement si le statut a changé
    update_metrics "eth0" "$eth_status" "$prev_eth_status"
    update_metrics "wlan0" "$wifi_status" "$prev_wifi_status"
    update_metrics "ppp0" "$lte_status" "$prev_lte_status"
    
    # Basculer vers la meilleure interface disponible uniquement si nécessaire
    switch_interface "$eth_status" "$wifi_status" "$lte_status"

    # Mettre à jour les statuts précédents
    prev_eth_status=$eth_status
    prev_wifi_status=$wifi_status
    prev_lte_status=$lte_status

    # check_modem
    check_modem

    # Attendre avant de revérifier
    sleep "$WAIT_TIME"
done