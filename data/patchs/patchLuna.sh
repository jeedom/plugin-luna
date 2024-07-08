#!/bin/sh
INSTALL_FLAG=0
UPDATE_FLAG=0

while [ "$1" != "" ]; do
    case $1 in
        --install ) INSTALL_FLAG=1;;
        --update ) UPDATE_FLAG=1;;
    esac
    shift
done

if [ $INSTALL_FLAG -eq 1 ]; then
    #RC LOCAL
    php /var/www/html/core/php/jeecli.php message add "luna" "correction dmesg"
    sudo chmod +x /etc/rc.local
    sudo systemctl daemon-reload
    sudo systemctl start rc-local
    sudo udevadm control --reload-rules
    sudo udevadm trigger

    #battery_power_switch
    php /var/www/html/core/php/jeecli.php message add "luna" "Add new command for switch power"
    sudo chmod +x /etc/rc.button/battery_power_switch

    #batterie
    php /var/www/html/core/php/jeecli.php message add "luna" "Add battery switch"
    sudo chmod 755 /usr/bin/batterySwitch
    sudo chmod 644 /etc/systemd/system/batterySwitch.service
    sudo systemctl enable --now batterySwitch.service

    #LTE
    php /var/www/html/core/php/jeecli.php message add "luna" "correction lte"
    sudo systemctl stop lte.service
    sudo systemctl disable lte.service
    sudo systemctl stop bg96
    sudo systemctl disable bg96
    sudo rm /usr/bin/bg96
    sudo rm /etc/init.d/bg96
    sudo rm /etc/systemd/system/lte.service
    sudo rm /usr/bin/lteSearch

    #done
    php /var/www/html/core/php/jeecli.php message add "luna" "correction Up Start Led"
    sudo chmod 755 /etc/init.d/done
    sudo systemctl restart done

    #dnsmasq
    php /var/www/html/core/php/jeecli.php message add "luna" "correction dnsmasq sur la luna"
    sudo apt-get remove -y dnsmasq

    #wmsgd.service
    php /var/www/html/core/php/jeecli.php message add "luna" "correction double ip sur la luna"
    sudo systemctl stop wmsgd.service
    sudo systemctl disable wmsgd.service
    sudo rm /etc/init.d/wmsgd
    sudo rm /etc/systemd/system/wmsgd.service
fi

if [ $UPDATE_FLAG -eq 1 ]; then
    echo "UPDATE"
fi

if [ ! -f /boot/jeedomLTE ]; then
    sudo cp /var/www/html/plugins/luna/data/patchs/root/usr/bin/jeedomLTE /usr/bin/jeedomLTE
    sudo chmod +x /usr/bin/jeedomLTE

    sudo cp /var/www/html/plugins/luna/data/patchs/lte/jeedomLTE.service /etc/systemd/system/jeedomLTE.service
    sudo chmod 644 /etc/systemd/system/jeedomLTE.service
    sudo systemctl daemon-reload
    sudo systemctl enable jeedomLTE.service
    sudo systemctl start jeedomLTE.service

    if [ -f /etc/NetworkManager/system-connections/JeedomLTE.nmconnection ]; then
        sudo nmcli connection modify JeedomLTE connection.multi-connect 3
        sudo nmcli connection modify JeedomLTE connection.autoconnect-retries 0
        sudo nmcli connection modify JeedomLTE connection.auth-retries 0
    fi
fi



return 0
