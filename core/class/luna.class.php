<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class luna extends eqLogic {
  /*     * *************************Attributs****************************** */

  public static function dependancy_info() {
    $return = array();
    $return['progress_file'] = jeedom::getTmpFolder(__CLASS__) . '/dependance';
    $return['state'] = 'ok';
    log::add(__CLASS__, 'debug', 'sys > ' . system::getCmdSudo() . system::get('cmd_check') . '-E "rsync|cloud\-guest\-utils" | wc -l');
    if (exec(system::getCmdSudo() . system::get('cmd_check') . '-E "rsync|cloud\-guest\-utils" | wc -l') < 4) {
      $return['state'] = 'nok';
    }
    return $return;
  }

  public static function dependancy_install() {
    log::remove(__CLASS__ . '_update');
    return array('script' => __DIR__ . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder(__CLASS__) . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '_update'));
  }

  /*     * ***********************Methode static*************************** */

  /* ----- RECOVERY et MIGRATION ----- */
  public static function put_ini_file($file, $array, $i = 0) {
    $str = "[core]\n";
    foreach ($array as $k => $v) {
      if (is_array($v)) {
        $str .= str_repeat(" ", $i * 2) . "[$k]" . PHP_EOL;
        $str .= self::put_ini_file("", $v, $i + 1);
      } else
        $str .= str_repeat(" ", $i * 2) . "$k = $v" . PHP_EOL;
    }
    if ($file)
      return file_put_contents($file, $str);
    else
      return $str;
  }

  public static function startMigration() {
    log::clear('migrate');
    log::clear('downloadImage');
    config::save('migrationText', '{{prelancement}}');
    config::save('migration', 0);
    config::save('migrationTextfine', __('Détection en cours...', __FILE__));
    sleep(5);
    config::save('migrationText', 'emmc');
    config::save('migrationTextfine', __("Démarrage du test de l'image", __FILE__));
    if (luna::ddImg()) {
      config::save('migrationText', 'endMaj');
      return 'ok';
    }
  }

  public static function ddImg() {
    log::add(__CLASS__, 'debug', 'IN CREATE LOG');
    config::save('migrationText', 'verifdd');
    shell_exec('sudo mount /dev/mmcblk1p9 /userdata');
    if (luna::downloadImage()) {
      sleep(3);
      config::save('migrationText', 'dd');
      config::save('migrationTextfine', __('Image validée avec succes', __FILE__));
      sleep(3);
      return true;
    } else {
      log::add(__CLASS__, 'debug', 'ERREUR IMAGE MIGRATE');
      config::save('migrationText', 'errorDd');
      return false;
    }
  }

  public static function lancementRaZ() {
    shell_exec('sudo fsreset');
    jeedom::rebootSystem();
  }

  public static function lancementMajRestauration() {
    shell_exec('sudo mount /dev/mmcblk1p9 /userdata');
    shell_exec('sudo /usr/bin/updateEngine --image_url=/userdata/update.img --savepath=/userdata/update.img --misc=update --partition=0x3B00 --reboot');
  }

  public static function marketImg($text = true) {
    log::add(__CLASS__, 'debug', __('Demande d\'informations au Market.', __FILE__));
    if ($text == true) {
      config::save('migrationTextfine', __('Demande d\'informations au Market.', __FILE__));
      sleep(2);
    }
    $jsonrpc = repo_market::getJsonRpc();
    if (!$jsonrpc->sendRequest('box::luna_image_url')) {
      throw new Exception($jsonrpc->getErrorMessage());
    }
    $urlArray = $jsonrpc->getResult();
    if ($urlArray['url'] && $urlArray['SHA256'] && $urlArray['size']) {
      return $urlArray;
    }
    return false;
  }

  public static function downloadImage() {
    shell_exec('sudo mount /dev/mmcblk1p9 /userdata');
    $urlArray = luna::marketImg();
    if (!$urlArray) {
      log::add(__CLASS__, 'debug', __('Problème avec le Market.', __FILE__));
      return false;
    }
    $url = $urlArray['url'];
    $size = $urlArray['SHA256'];
    log::add(__CLASS__, 'debug', __('Téléchargement', __FILE__) . ' > ' . $size);
    exec('sudo pkill -9 wget');
    $path_imgOs = '/userdata';
    if (!file_exists($path_imgOs)) {
      mkdir($path_imgOs, 0644);
    }
    $find = false;
    $fichier = $path_imgOs . '/update.img';
    log::add(__CLASS__, 'debug', __('Fichier', __FILE__) . ' > ' . $fichier);
    if (file_exists($fichier)) {
      log::add(__CLASS__, 'debug', __('Test de l\'image (vérification SHA).', __FILE__));
      config::save('migrationTextfine', __('Test de l\'image (vérification SHA).', __FILE__));
      $sha_256 = hash_file('sha256', $fichier);
      log::add(__CLASS__, 'debug', __('Taille', __FILE__) . ' > ' . $size);
      log::add(__CLASS__, 'debug', __('SHA', __FILE__) . ' > ' . $sha_256);
      if ($size == $sha_256) {
        log::add(__CLASS__, 'debug', __('Image OK.', __FILE__));
        return true;
      } else {
        log::add(__CLASS__, 'debug', __('Image NOK.', __FILE__));
        config::save('migrationTextfine', __('Image NOK.', __FILE__));
        sleep(2);
        //RM fichier
        unlink($fichier);
      }
    }
    if ($find == false) {
      config::save('migrationText', 'upload');
      log::add(__CLASS__, 'debug', 'find a False');
      config::save('migrationTextfine', __('Téléchargement de l image depuis nos serveurs en cours...', __FILE__));
      log::add(__CLASS__, 'debug', 'URL > ' . $url);
      log::add(__CLASS__, 'debug', 'shell > sudo wget --progress=dot --dot=mega ' . $url . ' -a ' . log::getPathToLog('downloadImage') . ' -O ' . $path_imgOs . '/update.img >> ' . log::getPathToLog('downloadImage') . ' 2&>1');
      shell_exec('sudo wget --progress=dot --dot=mega ' . $url . ' -a ' . log::getPathToLog('downloadImage') . ' -O ' . $path_imgOs . '/update.img >> ' . log::getPathToLog('downloadImage'));
      sleep(10);
      $sha_256 = hash_file('sha256', $fichier);
      if ($size == $sha_256) {
        return true;
      } else {
        return false;
      }
    }
    return true;
  }

  public static function loopPercentage() {
    $urlArray = luna::marketImg(false);
    $size = $urlArray['size'];
    $GO = $size;
    $MO = $GO * 1024;
    $KO = $MO * 1024;
    $BytesGlobal = $KO * 1024;
    $level_percentage = 0;
    config::save('migration', $level_percentage);
    while (config::byKey('migration') < 100) {
      log::add(__CLASS__, 'debug', $level_percentage);
      sleep(1);
      $level_percentage = luna::percentageProgress($BytesGlobal);
      if (config::byKey('migration') < 101) {
        config::save('migration', $level_percentage);
      } else {
        log::add(__CLASS__, 'debug', 'NON save pour le 100%');
      }
    }
  }

  public static function percentageProgress($BytesGlobal) {
    $logMigrate = log::get('migrate', 0, 1);
    $logMigrateAll = log::get('migrate', 0, 10);

    $pos = self::posOut($logMigrateAll);
    $firstln = $logMigrate[0];
    log::add(__CLASS__, 'debug', __('AVANCEMENT', __FILE__) . ' : ' . $firstln);

    if ($pos == false) {
      $valueByte = stristr($firstln, 'bytes', true);
      $pourcentage = round((100 * $valueByte) / $BytesGlobal, 2);
      log::add(__CLASS__, 'debug', __('ETAT', __FILE__) . ' : ' . $pourcentage . '%');
      log::clear('migrate');
      if ($valueByte == '' || $valueByte == null) {
      } else {
        return $pourcentage;
      }
    } else {
      log::add(__CLASS__, 'debug', __('FIN', __FILE__) . ' 100%');
      return 100;
    }
  }


  public static function posOut($needles) {
    foreach ($needles as $needle) {
      $rep = strpos($needle, 'records');
      if ($rep != false) {
        log::add(__CLASS__, 'debug', __('Fin de migration.', __FILE__));
        return true;
      }
    }
    return false;
  }

  /* ------ FIN RECOVERY et MIGRATION ------ */


  public static function cron5() {
    $eqLogics = eqLogic::byType('luna');
    foreach ($eqLogics as $luna) {
      log::add(__CLASS__, 'debug', 'Pull Cron luna');
      if ($luna->getIsEnable() != 1) {
        continue;
      };
      $ssid = $luna->getConfiguration('wifi1Ssid', null);
      $ssid2 = $luna->getConfiguration('wifi2Ssid', null);
      $luna->checkAndUpdateCmd('battery', luna::batteryPourcentage());
      $luna->checkAndUpdateCmd('status', luna::batteryStatusLuna());
      $luna->checkAndUpdateCmd('tempBattery', luna::batteryTemp());
      $luna->checkAndUpdateCmd('ssid', $luna->getConfiguration('wifi1Ssid'));
      if ($ssid != null) {
        $luna->checkAndUpdateCmd('isconnected', luna::isWificonnected($ssid));
      } else {
        $luna->checkAndUpdateCmd('isconnected', false);
      }
      $luna->checkAndUpdateCmd('ssid2', $luna->getConfiguration('wifi2Ssid'));
      if ($ssid2 != null) {
        $luna->checkAndUpdateCmd('isconnected2', luna::isWificonnected($ssid2));
      } else {
        $luna->checkAndUpdateCmd('isconnected2', false);
      }
    }
    if (luna::detectedLte() === true) {
      $TTYLTE = exec('sudo find /sys/devices/platform/ -name "ttyUSB*" | grep "2-1\.1\/" | grep "2-1\.1:1\.2" | grep -v "tty\/"');
      if ($TTYLTE == "") {
        luna::scanLTEModule();
      }
    }
  }

  /* ----- START ----- */

  public static function start() {
    log::add(__CLASS__, 'debug', __('Jeedom est démarré, vérification des connexions.', __FILE__));
    $luna = eqLogic::byLogicalId('wifi', __CLASS__);
    if (is_object($luna)) {
      if (luna::detectedLte() === true) {
        $TTYLTE = exec('sudo find /sys/devices/platform/ -name "ttyUSB*" | grep "2-1\.1\/" | grep "2-1\.1:1\.2" | grep -v "tty\/"');
        if ($TTYLTE == "") {
          luna::scanLTEModule();
        }
      }
    }
  }

  /* ----- WIFI ----- */

  public static function isWificonnected($ssid) {
    $result = shell_exec("sudo nmcli d | grep '" . $ssid . "'");
    log::add(__CLASS__, 'debug', $result);
    if (strpos($result, 'connected') === false && strpos($result, 'connecté') === false) {
      return false;
    }
    return true;
  }

  public static function isWifiProfileexist($ssid, $type = 'wifi') {
    $result = shell_exec("nmcli --fields NAME con show");
    $countProfile = substr_count($result, $ssid);
    if ($countProfile > 1) {
      log::add(__CLASS__, 'debug', __('Suppression des profils.', __FILE__));
      shell_exec("nmcli --pretty --fields UUID,TYPE con show | grep " . $type . " | awk '{print $1}' | while read line; do nmcli con delete uuid  $line; done");
      return true;
    } else if ($countProfile == 1) {
      return true;
    } else {
      return false;
    }
  }

  public static function deleteProfile($ssid) {
    $result = shell_exec("nmcli --fields NAME con show");
    $countProfile = substr_count($result, $ssid);
    if ($countProfile > 0) {
      log::add(__CLASS__, 'debug', __('Suppression des profils.', __FILE__));
      shell_exec("nmcli --pretty --fields UUID,TYPE con show | grep wifi | awk '{print $1}' | while read line; do nmcli con delete uuid  $line; done");
      return true;
    } else {
      return false;
    }
  }

  public static function listWifi($forced = false, $interface = 1) {
    $interface = $interface - 1;
    log::add(__CLASS__, 'debug', 'Wifi enabled : ' . 'sudo nmcli -f SSID,SIGNAL,SECURITY,CHAN -t -m tabular dev wifi list ifname wlan' . $interface);
    $return = [];
    $scanresult = shell_exec('sudo nmcli -f SSID,SIGNAL,SECURITY,CHAN -t -m tabular dev wifi list ifname wlan' . $interface);
    $results = explode("\n", $scanresult);
    $return = array();
    foreach ($results as $result) {
      $result = str_replace('\:', '$%$%', $result);
      $wifiDetail = explode(':', $result);
      $chan = $wifiDetail[3];
      $security = $wifiDetail[2];
      if ($security == '') {
        $security = 'Aucune';
      }
      $signal =  $wifiDetail[1];
      $ssid = str_replace('$%$%', '\:', $wifiDetail[0]);
      if ($ssid != '') {
        if (isset($return[$ssid]) && $return[$ssid]['signal'] > $signal) {
          continue;
        }
        $return[$ssid] = array('ssid' => $ssid, 'signal' => $signal, 'security' => $security, 'channel' => $chan);
      }
    }
    return $return;
  }

  public static function saveWifi($data, $interface = 1) {
    $device = $interface - 1;
    log::add(__CLASS__, 'debug', 'save wifi >>' . json_encode($data));
    $return = [];
    $stateWifi = $data[0]['configuration']['wifi' . $interface . 'Enabled'];
    $wifiMode = $data[0]['configuration']['wifi' . $interface . 'Mode'];
    $typeAdressage = $data[0]['configuration']['wifi' . $interface . 'TypeAdressage'];
    $wifiSsid = $data[0]['configuration']['wifi' . $interface . 'Ssid'];
    $wifiPassword = $data[0]['configuration']['wifi' . $interface . 'Password'];
    $wifiIp = $data[0]['configuration']['wifi' . $interface . 'ip'];
    $wifiMask = $data[0]['configuration']['wifi' . $interface . 'mask'];
    $wifiRouter = $data[0]['configuration']['wifi' . $interface . 'router'];
    $wifiDns = $data[0]['configuration']['wifi' . $interface . 'dns'];
    $wifiDnsOpt = $data[0]['configuration']['wifi' . $interface . 'dnsOpt'];
    $wifiHotspotName = $data[0]['configuration']['wifi' . $interface . 'hotspotname'];
    $wifiHotspotPwd = $data[0]['configuration']['wifi' . $interface . 'hotspotpwd'];
    $wifiHotspotdhcp = $data[0]['configuration']['wifi' . $interface . 'hotspotdhcp'];
    $wifiHotspotip = $data[0]['configuration']['wifi' . $interface . 'hotspotip'];
    $wifiHotspotmask = $data[0]['configuration']['wifi' . $interface . 'hotspotmask'];
    $wifiHotspotrouter = $data[0]['configuration']['wifi' . $interface . 'hotspotrouter'];
    $wifiHotspotdns = $data[0]['configuration']['wifi' . $interface . 'hotspotdns'];
    //log::add(__CLASS__, 'debug', 'save wifi >>sudo nmcli dev wlan'.$device.' connect '.$wifiSsid.' password '.$wifiPassword.''. json_encode($data[0]['configuration']));
    if ($stateWifi == 0) {
      shell_exec('sudo nmcli dev disconnect wlan' . $device);
      return;
    }
    if ($wifiMode == "client") {
      log::add(__CLASS__, 'debug', 'save wifi >>bbbb' . luna::convertIP($wifiIp, $wifiMask));
      shell_exec('sudo nmcli dev wifi connect "' . $wifiSsid . '" password "' . $wifiPassword . '"');
      shell_exec('sudo nmcli con down "' . $wifiSsid . '"');
      shell_exec('sudo nmcli con modify "' . $wifiSsid . '"  ifname wlan' . $device);
      if ($typeAdressage == 'dhcp') {
        shell_exec('sudo nmcli con modify "' . $wifiSsid . '" ipv4.method auto');
        if ($wifiDnsOpt != "") {
          shell_exec('sudo nmcli con modify "' . $wifiSsid . '" ipv4.ignore-auto-dns yes');
          shell_exec('sudo nmcli con modify "' . $wifiSsid . '" ipv4.dns ' . $wifiDnsOpt);
        } else {
          shell_exec('sudo nmcli con modify "' . $wifiSsid . '" ipv4.ignore-auto-dns no');
        }
        shell_exec('sudo nmcli con up "' . $wifiSsid . '"');
      } else {
        shell_exec('sudo nmcli con modify "' . $wifiSsid . '"  ipv4.addresses ' . luna::convertIP($wifiIp, $wifiMask) . ' ipv4.gateway ' . $wifiRouter . ' ipv4.dns ' . $wifiDns . ' ipv4.method manual');
        shell_exec('sudo nmcli con modify "' . $wifiSsid . '"  ifname wlan' . $device);
        shell_exec('sudo nmcli con up "' . $wifiSsid . '"');
      }
      sleep(5);
    } else if ($wifiMode == "hotspot") {
      log::add(__CLASS__, 'debug', 'save wifi >>hotspot');
      self::cleanWifi($device);
      log::add(__CLASS__, 'debug', 'save wifi >>sudo nmcli device wifi hotspot ssid "'.$wifiHotspotName.'" password "'.$wifiHotspotPwd.'" ifname wlan' . $device . ' con-name Hotspot-wlan' . $device);
      shell_exec('sudo nmcli device wifi hotspot ssid "'.$wifiHotspotName.'" password "'.$wifiHotspotPwd.'" ifname wlan' . $device . ' con-name Hotspot-wlan' . $device);
      if($wifiHotspotdhcp == true){
        log::add(__CLASS__, 'debug', 'save wifi >> sudo nmcli con modify Hotspot-wlan' . $device . ' ipv4.addresses ' . luna::convertIP($wifiHotspotip, $wifiHotspotmask));
        shell_exec('sudo nmcli con modify Hotspot-wlan' . $device . ' ipv4.addresses ' . luna::convertIP($wifiHotspotip, $wifiHotspotmask));
      }
      shell_exec('sudo nmcli con modify Hotspot-wlan' . $device . ' connection.autoconnect yes');
      shell_exec('sudo nmcli con up Hotspot-wlan' . $device);
    }
    return $return;
  }

  public static function cleanWifi($device) {
    log::add(__CLASS__, 'debug', 'clean wifi >>' . $device);
    shell_exec('sudo nmcli dev disconnect wlan' . $device);
    shell_exec('sudo nmcli con delete $(nmcli --fields UUID,TYPE con show | grep wifi | awk \'{print $1}\')');
    return;
  }

  public static function disconnectWifi($interface = 1) {
    $device = $interface - 1;
    shell_exec('sudo nmcli dev disconnect wlan' . $device);
    return;
  }

  public static function connectWifi($interface = 1) {
    $device = $interface - 1;
    shell_exec('sudo nmcli dev connect wlan' . $device);
    return;
  }

  public static function saveEthernet($data) {
    log::add(__CLASS__, 'debug', 'save ethernet >>' . json_encode($data));
    $return = [];
    $typeAdressage = $data[0]['configuration']['ethernetTypeAdressage'];
    $Ip = $data[0]['configuration']['ethernetip'];
    $Mask = $data[0]['configuration']['ethernetmask'];
    $Router = $data[0]['configuration']['ethernetrouter'];
    $Dns = $data[0]['configuration']['ethernetdns'];
    $DnsOpt = $data[0]['configuration']['ethernetdnsOpt'];

    log::add(__CLASS__, 'debug', 'save ethernet >>bbbb' . luna::convertIP($Ip, $Mask));
    if ($typeAdressage == 'dhcp') {
      shell_exec('sudo nmcli con modify "Wired connection 1" ipv4.method auto');
      shell_exec('sudo nmcli con up "Wired connection 1"');
      if ($DnsOpt != "") {
        shell_exec('sudo nmcli con modify "Wired connection 1" ipv4.ignore-auto-dns yes');
        shell_exec('sudo nmcli con modify "Wired connection 1" ipv4.dns ' . $DnsOpt);
      } else {
        shell_exec('sudo nmcli con modify "Wired connection 1" ipv4.ignore-auto-dns no');
      }
    } else {
      log::add(__CLASS__, 'debug', 'sudo nmcli con modify "Wired connection 1" ipv4.addresses ' . luna::convertIP($Ip, $Mask) . ' ipv4.gateway ' . $Router . ' ipv4.dns ' . $Dns . ' ipv4.method manual');
      shell_exec('sudo nmcli con modify "Wired connection 1" ipv4.addresses ' . luna::convertIP($Ip, $Mask) . ' ipv4.gateway ' . $Router . ' ipv4.dns ' . $Dns . ' ipv4.method manual');
      shell_exec('sudo nmcli con up "Wired connection 1"');
    }
    return $return;
  }

  public static function savePriority($priorities) {
    log::add(__CLASS__, 'debug', 'save priority >>' . json_encode($priorities));
    $prio = 1;
    foreach ($priorities as $priority) {
      shell_exec('sudo nmcli con modify ' . $priority . ' ipv4.route-metric ' . ($prio * 100));
      shell_exec('sudo nmcli con up ' . $priority);
      $prio++;
    }
  }

  public static function convertIP($ip, $mask) {
    return $ip . "/" . strlen(str_replace("0", "", decbin(ip2long($mask))));
  }

  public static function listConnections($interface = 1) {
    $interface = $interface - 1;
    log::add(__CLASS__, 'debug', 'Wifi enabled : ' . 'sudo nmcli -f SSID,SIGNAL,SECURITY,CHAN -t -m tabular dev wifi list ifname wlan' . $interface);
    $return = [];
    $scanresult = shell_exec('sudo nmcli -f UUID,NAME,TYPE,ACTIVE -t -m tabular con');
    $results = explode("\n", $scanresult);
    $return = array();
    foreach ($results as $result) {
      $result = str_replace('\:', '$%$%', $result);
      $result = preg_replace("#(\r\n|\n\r|\n|\r)#", "", $result);
      $conDetail = explode(':', $result);
      $conUUID = $conDetail[0];
      $conName = $conDetail[1];
      $conType = $conDetail[2];
      $conActive = $conDetail[3];
      $conDevice = $conDetail[4];
      if ($conDevice == "") {
        $conDevice = shell_exec('sudo nmcli -f connection.interface-name -t -m tabular con show ' . $conUUID);
        $conDevice = preg_replace("#(\r\n|\n\r|\n|\r)#", "", $conDevice);
      }

      $return[] = array('UUID' => $conUUID, 'name' => $conName, 'type' => $conType, 'active' => $conActive, 'device' => $conDevice);
      log::add(__CLASS__, 'debug', json_encode($return));
    }

    return $return;
  }

  public static function removeConnection($UUID) {
    shell_exec('sudo nmcli con down ' . $UUID);
    shell_exec('sudo nmcli con del ' . $UUID);
    return True;
  }

  public static function getMac($_interface = 'eth0') {
    $interfaceIp = shell_exec("sudo ifconfig $_interface | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
    $interfaceMac = shell_exec("sudo ip addr show $_interface | grep -i 'link/ether' | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}' | sed -n 1p");
    return [$interfaceMac, $interfaceIp];
  }

  /* ----- FIN WIFI ----- */

  /* ----- DSLED ----- */

  public static function dsLed($demande = 'g on') {
    $dsledExe = __DIR__ . '/../../resources/dsled/dsled';
    exec('sudo ' . $dsledExe . ' g off');
    exec('sudo ' . $dsledExe . ' r off');
    exec('sudo ' . $dsledExe . ' b off');
    exec('sudo pkill -9 dsled');
    if ($demande !== 'off') {
      exec('sudo ' . $dsledExe . ' ' . $demande);
    }
  }

  /* ----- FIN DSLED ----- */

  /* ----- BATTERY ----- */

  public static function batteryPourcentage() {
    return exec('sudo cat /sys/class/power_supply/bq27546-0/capacity');
  }

  public static function batteryStatusLuna() {
    return exec('sudo cat /sys/class/power_supply/bq27546-0/status');
  }

  public static function batteryTemp() {
    $temp = exec('sudo cat /sys/class/power_supply/bq27546-0/temp');
    $temp = $temp / 10;
    return $temp;
  }

  public static function batteryPowerAvg() {
    return exec('sudo cat /sys/class/power_supply/bq27546-0/power_avg');
  }

  public static function batteryPresent() {
    return exec('sudo cat /sys/class/power_supply/bq27546-0/present');
  }

  /* ----- FIN BATTERY ----- */

  /* root etc Patch */

  public static function patchLuna() {
    message::add(__CLASS__, __('Patch Luna', __FILE__));
    exec('sudo cp -r ' . __DIR__ . '/../../data/patchs/root/* /');
    exec('sudo ' . __DIR__ . '/../../data/patchs/patchLuna.sh');
    message::add(__CLASS__, __('Patch Luna Fini', __FILE__));
  }

  /* fin patch */

  /* ----- SD ----- */

  public static function partitionSD() {
    $sdSector = "/dev/mmcblk2";
    exec('sudo unmount ' . $sdSector);
    message::add(__CLASS__, __('Patitionnage en cours', __FILE__));
    exec('sudo chmod +x ../../data/patchs/partitionSD.sh');
    exec('sudo ../../data/patchs/partitionSD.sh');
    message::add(__CLASS__, __('Carte SD bien partitionnée', __FILE__));
  }

  public static function checkPartitionSD() {
    exec('sudo lsblk -f -J 2>&1', $jsonVolumes);
    $response = false;
    foreach ($jsonVolumes as $volume) {
      $valueVolume = json_decode($volume, true);
      if ($valueVolume['name'] === 'mmcblk2' && $valueVolume['fstype'] === 'ext3') {
        log::add(__CLASS__, 'debug', 'JSON VOLUME > trouvé');
        $response = true;
      }
    }
    return $response;
  }

  public static function presentSD() {
    $sdSector = "/dev/mmcblk2";
    if (file_exists($sdSector)) {
      return true;
    }
    return false;
  }

  public static function BackupOkInSd() {
    if (config::byKey('backup::path') == "/media") {
      return true;
    } else {
      return false;
    }
  }

  public static function mountSD() {
    $sdSector = "/dev/mmcblk2";
    $montage = "/media";
    exec('sudo unmount ' . $sdSector);
    exec('sudo mount ' . $sdSector . ' ' . $montage);
    exec('sudo chmod 775 ' . $montage);
    exec('sudo chown www-data:www-data -R ' . $montage);
  }

  public static function changeBackupToSD() {
    $montage = "/media";
    config::save('backup::path', $montage);
    exec('sudo chmod 775 ' . $montage);
    exec('sudo chown www-data:www-data -R ' . $montage);
  }

  public static function changeBackupToEmmc() {
    if (luna::BackupOkInSd()) {
      $montage = "/var/www/html/backup/";
      config::save('backup::path', $montage);
    }
  }

  /* ----- FIN SD ----- */

  /* ------ DEBUT LORA ----- */

  public static function formatUid($UID) {
    $UID = substr($UID, -16);
    log::add(__CLASS__, 'debug', 'UID -18 > ' . $UID);
    $UID = str_replace('x', '', $UID);
    log::add(__CLASS__, 'debug', 'UID replace > ' . $UID);
    return $UID;
  }

  public static function detectedLora() {
    if (config::byKey('gatewayUID', 'luna', null) == null) {
      $UID = exec('cd /usr/bin/lora && sudo ./chip_id -d /dev/spidev32766.0 | grep -io "concentrator EUI: 0x*[0-9a-fA-F][0-9a-fA-F]*\+"');
      if ($UID != "") {
        config::save('gatewayUID', luna::formatUid($UID), 'luna');
        log::add(__CLASS__, 'debug', 'UID > ' . $UID);
        return true;
      } else {
        config::save('gatewayUID', false, 'luna');
        return false;
      }
    } elseif (config::byKey('gatewayUID', 'luna', null) == false) {
      return false;
    } else {
      return true;
    }
  }

  public static function loraServiceActif() {
    $loraService = exec('sudo systemctl is-active lora.service');

    if ($loraService == "activating") {
      return true;
    } else {
      return false;
    }
  }

  public static function loraSwitchMaj($actived = "active") {
    if ($actived == "active") {
      message::add(__CLASS__, __('Activation Lora', __FILE__));
      exec('sudo cp ' . __DIR__ . '/../../data/patchs/lora/lora.service /etc/systemd/system/');
      exec('sudo chmod 755 /etc/systemd/system/lora.service');
      exec('sudo systemctl daemon-reload');
      exec('sudo systemctl enable lora.service');
      exec('sudo systemctl start lora.service > /dev/null 2>/dev/null &');
    } else {
      message::add(__CLASS__, __('Désactivation Lora', __FILE__));
      exec('sudo systemctl disable --now lora.service > /dev/null 2>/dev/null &');
    }
  }

  public static function configurationLora() {
    $uid = config::byKey('gatewayUID', 'luna');
    log::add(__CLASS__, 'debug', 'UID config > ' . $uid);
    if ($uid) {
      $json = file_get_contents(__DIR__ . "/../../data/patchs/lora/global_conf.json");
      $parseJson = json_decode($json, true);
      $parseJson['gateway_conf']['gateway_ID'] = $uid;
      log::add(__CLASS__, 'debug', 'json globalConfig > ' . json_encode($parseJson));
      file_put_contents(__DIR__ . "/../../data/patchs/lora/global_conf.json", json_encode($parseJson));
      exec("sudo cp " . __DIR__ . "/../../data/patchs/lora/global_conf.json /usr/bin/lora/global_conf.json");
      return true;
    } else {
      return false;
    }
  }

  public static function installLora() {
    if (luna::detectedLora()) {
      message::add(__CLASS__, __('Installation de la partie Lora, car puce Lora detecté', __FILE__));
      if (luna::configurationLora()) {
        sleep(3);
        luna::loraSwitchMaj();
      }
    }
  }

  /* ----- FIN LORA ------ */

  /* ----- DEBUT 4G ----- */

  public static function scanLTEModule() {
    $TTYLTE = exec('sudo find  /sys/devices/platform/ -name "ttyUSB*" | grep "2-1\.1\/" | grep "2-1\.1:1\.2" | grep -v "tty\/"');
    if ($TTYLTE != "") {
      message::add(__CLASS__, __('Puce LTE détecté.', __FILE__));
      config::save('4G', 'OK', 'luna');
      return true;
    } else {
      message::add(__CLASS__, __('Detection de la puce LTE en cours cela peux prendre 2 minutes un message vous avertira une fois le scan fini', __FILE__));
      $ltetrouver = exec('sudo lteSearch');
      if ($ltetrouver == 1) {
        message::add(__CLASS__, __('Detection de la puce LTE fini > puce trouvé', __FILE__));
      } elseif ($ltetrouver == 2) {
        message::add(__CLASS__, __('Detection de la puce LTE fini > puce non presente', __FILE__));
      } else {
        message::add(__CLASS__, __('Erreur lors de la detection de la puce LTE', __FILE__));
      }
      $TTYLTE = exec('sudo find  /sys/devices/platform/ -name "ttyUSB*" | grep "2-1\.1\/" | grep "2-1\.1:1\.2" | grep -v "tty\/"');
      if ($TTYLTE != "") {
        message::add(__CLASS__, __('Puce LTE détecté. Vous pouvez configurer votre operateur depuis la configuration du plugin.', __FILE__));
        config::save('4G', "OK", 'luna');
        return true;
      } else {
        message::add(__CLASS__, __('Puce LTE non-détecté.', __FILE__));
        config::save('4G', "NOK", 'luna');
        return false;
      }
    }
  }

  public static function detectedLte() {
    $scan = config::byKey('4G', 'luna', null);
    if ($scan == null) {
      log::add(__CLASS__, 'debug', 'SCAN');
      return 'scan';
    } elseif ($scan == "NOK") {
      log::add(__CLASS__, 'debug', 'NOK');
      return false;
    } else {
      log::add(__CLASS__, 'debug', 'OK');
      return true;
    }
  }

  public static function installLte() {
    message::add(__CLASS__, __('LTE > Merci de lancer la détection depuis le plugin Luna', __FILE__));
  }

  public static function configjsonlte() {
    log::add(__CLASS__, 'debug', 'CONFIG JSON LTE'  . luna::detectedLte());
    if (luna::detectedLte() === 'false') {
      log::add(__CLASS__, 'debug', 'FAUX');
      return false;
    }
    if (luna::detectedLte() === 'scan') {
      log::add(__CLASS__, 'debug', 'FAUX SCAN');
      return false;
    }
    $luna = eqLogic::byLogicalId('wifi', __CLASS__);
    if (!is_object($luna)) {
      return false;
    }
    $apn = $luna->getConfiguration('lteApn');
    $user = $luna->getConfiguration('lteUser');
    $password = $luna->getConfiguration('ltePassword');
    $pin = $luna->getConfiguration('ltePin');
    log::add(__CLASS__, 'debug', 'APN > ' . $apn);
    log::add(__CLASS__, 'debug', 'USER > ' . $user);
    log::add(__CLASS__, 'debug', 'PASSWORD > ' . $password);

    $exist = luna::isWifiProfileexist('JeedomLTE', 'gsm');

    log::add(__CLASS__, 'debug', 'EXISTE > ' . $exist);
    if ($exist === false) {
      log::add(__CLASS__, 'debug', 'CREATION DU PROFIL JEEDOMLTE');
      exec("sudo nmcli connection add type gsm ifname '*' con-name JeedomLTE connection.autoconnect yes");
    }
    if ($apn != null) {
      exec("sudo nmcli connection modify JeedomLTE gsm.apn $apn");
    } else {
      exec("sudo nmcli connection modify JeedomLTE gsm.apn ''");
    }
    if ($user != null) {
      exec("sudo nmcli connection modify JeedomLTE gsm.username $user");
    } else {
      exec("sudo nmcli connection modify JeedomLTE gsm.username ''");
    }
    if ($password != null) {
      exec("sudo nmcli connection modify JeedomLTE gsm.password $password");
    } else {
      exec("sudo nmcli connection modify JeedomLTE gsm.password ''");
    }
    if ($pin != null) {
      exec("sudo nmcli connection modify JeedomLTE gsm.pin $pin");
    } else {
      exec("sudo nmcli connection modify JeedomLTE gsm.pin ''");
    }

    exec("sudo nmcli connection modify JeedomLTE ipv6.method disabled");

    log::add(__CLASS__, 'debug', 'Fin de la configuration LTE > ' . exec("sudo nmcli connection show JeedomLTE"));
    luna::scanLTEModule();
    luna::lteSwitchMaj();
  }

  public static function lteSwitchMaj() {
    $luna = eqLogic::byLogicalId('wifi', __CLASS__);
    if (is_object($luna)) {
      $actived = $luna->getConfiguration('lteActivation');
    }
    if ($actived == true) {
      message::add(__CLASS__, __('Activation LTE, la premiere connexion peut prendre 10 minutes.', __FILE__));
      log::add(__CLASS__, 'debug', 'Activation LTE');
      exec('sudo nmcli connection modify JeedomLTE connection.autoconnect yes');
      exec("sudo nmcli connection up JeedomLTE");
    } else {
      message::add(__CLASS__, __('Désactivation Data LTE', __FILE__));
      log::add(__CLASS__, 'debug', 'Désactivation Data LTE');
      exec('sudo nmcli connection modify JeedomLTE connection.autoconnect no');
      exec("sudo nmcli connection down JeedomLTE");
    }
  }

  public static function configurationPortSms() {
    $pluginSms = plugin::byId('sms');
    if (is_object($pluginSms)) {
      config::save('port', '/dev/ttyLuna-Lte', 'sms');
    }
  }

  public static function recuperationConfigModem() {
    $modemLte = exec('sudo mmcli --modem=0 -J');
    if ($modemLte == "error: couldn't find modem") {
      log::add(__CLASS__, 'debug', 'Modem non trouvé');
      return false;
    }
    $modem = json_decode($modemLte, true);
    log::add(__CLASS__, 'debug', 'Modem > ' . $modemLte);

    $modem = $modem['modem'];

    $imei = $modem['3gpp']['imei'];
    $operatorName = $modem['3gpp']['operator-name'];
    $signalPercent = $modem['generic']['signal-quality']['value'];
    $state = $modem['generic']['state'];
    $stateFailedReason = $modem['generic']['state-failed-reason'];
    $unlockRequired = $modem['generic']['unlock-required'];
    $unlockRetries = $modem['generic']['unlock-retries'];

    log::add(__CLASS__, 'debug', 'IMEI > ' . $imei);
    log::add(__CLASS__, 'debug', 'OPERATOR NAME > ' . $operatorName);
    log::add(__CLASS__, 'debug', 'SIGNAL PERCENT > ' . $signalPercent);
    log::add(__CLASS__, 'debug', 'STATE > ' . $state);
    log::add(__CLASS__, 'debug', 'STATE FAILED REASON > ' . $stateFailedReason);
    log::add(__CLASS__, 'debug', 'UNLOCK REQUIRED > ' . $unlockRequired);
    log::add(__CLASS__, 'debug', 'UNLOCK RETRIES > ' . $unlockRetries);

    return [
      'imei' => $imei,
      'operatorName' => $operatorName,
      'signalPercent' => $signalPercent,
      'state' => $state,
      'stateFailedReason' => $stateFailedReason,
      'unlockRequired' => $unlockRequired,
      'unlockRetries' => $unlockRetries
    ];
  }

  public static function cronHourly(){
    // executer LTE si pas de ppp0 dans un ifconfig
    $ifconfig = shell_exec('sudo ifconfig');
    if(strpos($ifconfig, 'ppp0') === false){
      luna::configjsonlte();
    }
  }

  /* ------ FIN 4G ----- */

  /* ----- DEBUT SMS  ----- */

  public static function listGlobalSMS(){
    $list = self::listSMS();
    $return = [];
    foreach ($list as $sms) {
      $sms = self::readSMS($sms);
      $return[] = $sms;
    }
    return $return;
  }

  public static function listSMS() {
    $list = shell_exec('sudo mmcli -m 0 --messaging-list-sms');
    $list = explode("\n", $list);
    $listSMS = [];
    foreach ($list as $sms) {
      if (preg_match('/\/org\/freedesktop\/ModemManager1\/SMS\/[0-9]+/', $sms)) {
        $sms = explode('/', $sms);
        $sms = $sms[5];
        $sms = explode(' ', $sms);
        $sms = $sms[0];
        $listSMS[] = $sms;
      }
    }
    return $listSMS;
  }

  public static function readSMS($sms) {
    $sms = shell_exec('sudo mmcli -s ' . $sms);
    $list = explode("\n", $sms);
    $listSMS = [];
    foreach ($list as $sms) {
      if (preg_match('/Content    |    number:/', $sms)) {
        $sms = explode(':', $sms);
        $sms = $sms[1];
        $sms = trim($sms);
        $listSMS['number'] = $sms;
      }
      if (preg_match('/text:/', $sms)) {
        $sms = explode(':', $sms);
        $sms = $sms[1];
        $sms = trim($sms);
        $listSMS['text'] = $sms;
      }
      if (preg_match('/smsc:/', $sms)) {
        $sms = explode(':', $sms);
        $sms = $sms[1];
        $sms = trim($sms);
        $listSMS['smsc'] = $sms;
      }
      if (preg_match('/state:/', $sms)) {
        $sms = explode(':', $sms);
        $sms = $sms[1];
        $sms = trim($sms);
        $listSMS['state'] = $sms;
      }
    }
    return $listSMS;
  }

  public static function deleteSMS($sms) {
    shell_exec('sudo mmcli -s ' . $sms . ' --messaging-delete-sms=' . $sms);
  }

  public static function sendSMS($number, $text) {
    $sms = shell_exec('sudo mmcli -m 0 --messaging-create-sms="text=\'' . $text . '\',number=\'' . $number . '\'"');
    log::add(__CLASS__, 'debug', 'SMS > ' . $sms);
    if (preg_match('/\/org\/freedesktop\/ModemManager1\/SMS\/[0-9]+/', $sms)) {
      log::add(__CLASS__, 'debug', 'SMS > OK');
      $sms = explode('/', $sms);
      $smsNumber = trim($sms[5]);
    }
    log::add(__CLASS__, 'debug', 'SMS > ' . $smsNumber);
    log::add(__CLASS__, 'debug', 'SMS > sudo mmcli -s ' . $smsNumber . ' --send');
    shell_exec('sudo mmcli -s ' . $smsNumber . ' --send');
  }

  /* ----- FIN SMS ----- */

  public static function switchHost($activated = true) {
    //exec("sudo apt remove -y dnsmasq");
    exec("sudo sed -i 's/managed=false/managed=true/g' /etc/NetworkManager/NetworkManager.conf");
    exec("sudo sed 's/^auto/#&/' -i /etc/network/interfaces");
    exec("sudo sed 's/^iface/#&/' -i /etc/network/interfaces");
    if ($activated === true) {
      message::add(__CLASS__, __('Patch du localhost', __FILE__));
      exec("sudo chattr -i /etc/hosts");
      exec("sudo cp " . __DIR__ . "/../../data/patchs/hosts /etc/hosts");
      $hostname = trim(file_get_contents('/etc/hostname'));
      if ($hostname !== 'JeedomLuna') {
        exec('sudo sed -i "s|JeedomLuna|' . $hostname . '|g" /etc/hosts');
      }
    } else {
      exec("sudo chattr -i /etc/hosts");
    }
  }

  public function postSave() {
    $connect = $this->getCmd(null, 'connect');
    if (!is_object($connect)) {
      $connect = new lunaCmd();
      $connect->setLogicalId('connect');
      $connect->setIsVisible(1);
      $connect->setName(__('Connecter Wifi', __FILE__));
      $connect->setOrder(20);
    }
    $connect->setType('action');
    $connect->setSubType('other');
    $connect->setEqLogic_id($this->getId());
    $connect->save();

    $connect = $this->getCmd(null, 'connect2');
    if (!is_object($connect)) {
      $connect = new lunaCmd();
      $connect->setLogicalId('connect2');
      $connect->setIsVisible(1);
      $connect->setName(__('Connecter Wifi 2', __FILE__));
      $connect->setOrder(20);
    }
    $connect->setType('action');
    $connect->setSubType('other');
    $connect->setEqLogic_id($this->getId());
    $connect->save();

    $disconnect = $this->getCmd(null, 'disconnect');
    if (!is_object($disconnect)) {
      $disconnect = new lunaCmd();
      $disconnect->setLogicalId('disconnect');
      $disconnect->setIsVisible(1);
      $disconnect->setName(__('Déconnecter Wifi', __FILE__));
      $disconnect->setOrder(21);
    }
    $disconnect->setType('action');
    $disconnect->setSubType('other');
    $disconnect->setEqLogic_id($this->getId());
    $disconnect->save();

    $disconnect = $this->getCmd(null, 'disconnect2');
    if (!is_object($disconnect)) {
      $disconnect = new lunaCmd();
      $disconnect->setLogicalId('disconnect2');
      $disconnect->setIsVisible(1);
      $disconnect->setName(__('Déconnecter Wifi 2', __FILE__));
      $disconnect->setOrder(21);
    }
    $disconnect->setType('action');
    $disconnect->setSubType('other');
    $disconnect->setEqLogic_id($this->getId());
    $disconnect->save();

    $isconnect = $this->getCmd(null, 'isconnect');
    if (!is_object($isconnect)) {
      $isconnect = new lunaCmd();
      $isconnect->setName(__('Etat Wifi', __FILE__));
      $isconnect->setOrder(22);
    }
    $isconnect->setEqLogic_id($this->getId());
    $isconnect->setLogicalId('isconnect');
    $isconnect->setType('info');
    $isconnect->setSubType('binary');
    $isconnect->save();

    $isconnect = $this->getCmd(null, 'isconnect2');
    if (!is_object($isconnect)) {
      $isconnect = new lunaCmd();
      $isconnect->setName(__('Etat Wifi 2', __FILE__));
      $isconnect->setOrder(22);
    }
    $isconnect->setEqLogic_id($this->getId());
    $isconnect->setLogicalId('isconnect2');
    $isconnect->setType('info');
    $isconnect->setSubType('binary');
    $isconnect->save();

    $lanip = $this->getCmd(null, 'lanip');
    if (!is_object($lanip)) {
      $lanip = new lunaCmd();
      $lanip->setName(__('Lan IP', __FILE__));
      $lanip->setOrder(24);
    }
    $lanip->setEqLogic_id($this->getId());
    $lanip->setLogicalId('lanip');
    $lanip->setType('info');
    $lanip->setSubType('string');
    $lanip->save();

    $wifiip = $this->getCmd(null, 'wifiip');
    if (!is_object($wifiip)) {
      $wifiip = new lunaCmd();
      $wifiip->setName(__('Wifi IP', __FILE__));
      $wifiip->setOrder(25);
    }
    $wifiip->setEqLogic_id($this->getId());
    $wifiip->setLogicalId('wifiip');
    $wifiip->setType('info');
    $wifiip->setSubType('string');
    $wifiip->save();

    $wifiip = $this->getCmd(null, 'wifiip2');
    if (!is_object($wifiip)) {
      $wifiip = new lunaCmd();
      $wifiip->setName(__('Wifi 2 IP', __FILE__));
      $wifiip->setOrder(25);
    }
    $wifiip->setEqLogic_id($this->getId());
    $wifiip->setLogicalId('wifiip2');
    $wifiip->setType('info');
    $wifiip->setSubType('string');
    $wifiip->save();

    $ssid = $this->getCmd(null, 'ssid');
    if (!is_object($ssid)) {
      $ssid = new lunaCmd();
      $ssid->setName(__('SSID du wifi 1', __FILE__));
      $ssid->setOrder(26);
    }
    $ssid->setEqLogic_id($this->getId());
    $ssid->setLogicalId('ssid');
    $ssid->setType('info');
    $ssid->setSubType('string');
    $ssid->save();

    $ssid = $this->getCmd(null, 'ssid2');
    if (!is_object($ssid)) {
      $ssid = new lunaCmd();
      $ssid->setName(__('SSID du wifi 2', __FILE__));
      $ssid->setOrder(26);
    }
    $ssid->setEqLogic_id($this->getId());
    $ssid->setLogicalId('ssid2');
    $ssid->setType('info');
    $ssid->setSubType('string');
    $ssid->save();

    $refresh = $this->getCmd(null, 'refresh');
    if (!is_object($refresh)) {
      $refresh = new lunaCmd();
    }
    $refresh->setName(__('Rafraichir', __FILE__));
    $refresh->setLogicalId('refresh');
    $refresh->setEqLogic_id($this->getId());
    $refresh->setType('action');
    $refresh->setSubType('other');
    $refresh->save();

    $dsled = $this->getCmd(null, 'dsled');
    if (!is_object($dsled)) {
      $dsled = new lunaCmd();
      $dsled->setOrder(10);
    }
    $dsled->setName(__('Led', __FILE__));
    $dsled->setLogicalId('dsled');
    $dsled->setEqLogic_id($this->getId());
    $dsled->setType('action');
    $dsled->setSubType('select');
    $dsled->setConfiguration('listValue', 'g breathe|Vert Respiration;r breathe|Rouge Respiration;b breathe|Bleu Respiration;g blink_fast|Vert Clignotant Rapidement;r blink_fast|Rouge Clignotant Rapidement;b blink_fast|Bleu Clignotant Rapidement;g blink_slow|Vert Clignotant lent;r blink_slow|Rouge Clignotant lent;b blink_slow|Bleu Clignotant lent;g on|Vert On;r on|Rouge On;b on|Bleu On;off|Off');
    $dsled->save();

    $battery = $this->getCmd(null, 'battery');
    if (!is_object($battery)) {
      $battery = new lunaCmd();
      $battery->setName(__('Battery', __FILE__));
      $battery->setOrder(2);
    }
    $battery->setEqLogic_id($this->getId());
    $battery->setLogicalId('battery');
    $battery->setType('info');
    $battery->setSubType('numeric');
    $battery->setUnite('%');
    $battery->save();

    $status = $this->getCmd(null, 'status');
    if (!is_object($status)) {
      $status = new lunaCmd();
      $status->setName(__('Status alimentation', __FILE__));
      $status->setOrder(1);
    }
    $status->setEqLogic_id($this->getId());
    $status->setLogicalId('status');
    $status->setType('info');
    $status->setSubType('string');
    $status->save();

    $tempBattery = $this->getCmd(null, 'tempBattery');
    if (!is_object($tempBattery)) {
      $tempBattery = new lunaCmd();
      $tempBattery->setName(__('Température Batterie', __FILE__));
      $tempBattery->setOrder(1);
      $tempBattery->setUnite('°C');
    }
    $tempBattery->setEqLogic_id($this->getId());
    $tempBattery->setLogicalId('tempBattery');
    $tempBattery->setType('info');
    $tempBattery->setSubType('string');
    $tempBattery->save();
  }

  public function postAjax() {
  }
}

class lunaCmd extends cmd {

  public function execute($_options = array()) {
    if ($this->getType() == '') {
      return '';
    }
    /** @var luna */
    $eqLogic = $this->getEqlogic();
    $action = $this->getLogicalId();
    switch ($action) {
      case 'connect':
        luna::disconnectWifi(1);
        break;
      case 'disconnect':
        luna::connectWifi(1);
      case 'connect2':
        luna::disconnectWifi(2);
        break;
      case 'disconnect2':
        luna::connectWifi(2);
        break;
      case 'dsled':
        luna::dsLed($_options['select']);
        break;
    }
    luna::cron5($eqLogic->getId());
  }
  /*     * **********************Getteur Setteur*************************** */
}
