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

  /*
   * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
   * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	public static $_widgetPossibility = array();
   */

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
        $str .= put_ini_file("", $v, $i + 1);
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

  public static function lancementRaZ(){
    shell_exec('sudo touch /factoryreset; sudo sync;');
    jeedom::rebootSystem();
  }

  public static function lancementMajRestauration(){
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


  public static function cron5($_eqlogic_id = null) {
    if ($_eqlogic_id !== null) {
      $eqLogics = array(eqLogic::byId($_eqlogic_id));
    } else {
      $eqLogics = eqLogic::byType('wifip');
    }
    foreach ($eqLogics as $luna) {
      log::add(__CLASS__, 'debug', 'Pull Cron luna');
      $luna->wifiConnect();
      if ($luna->getIsEnable() != 1) {
        continue;
      };
      if (!file_exists("/sys/class/net/eth0/operstate")) {
        $ethup = 0;
      } else {
        $ethup = (trim(file_get_contents("/sys/class/net/eth0/operstate")) == 'up') ? 1 : 0;
      }
      if (!file_exists("/sys/class/net/wlan0/operstate")) {
        $wifiup = 0;
      } else {
        $wifiup = (trim(file_get_contents("/sys/class/net/wlan0/operstate")) == 'up') ? 1 : 0;
      }
      $wifisignal = str_replace('.', '', shell_exec("sudo tail -n +3 /proc/net/wireless | awk '{ print $3 }'"));
      $wifiIp = shell_exec("sudo ifconfig wlan0 | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
      $lanIp = shell_exec("sudo ifconfig eth0 | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
      log::add(__CLASS__, 'debug', 'Lan Ip is :' . $lanIp);
      log::add(__CLASS__, 'debug', 'Wifi Ip is :' . $wifiIp);
      $luna->checkAndUpdateCmd('isconnect', $wifiup);
      $luna->checkAndUpdateCmd('isconnecteth', $ethup);
      $luna->checkAndUpdateCmd('signal', $wifisignal);
      $luna->checkAndUpdateCmd('lanip', $lanIp);
      $luna->checkAndUpdateCmd('wifiip', $wifiIp);
      $luna->checkAndUpdateCmd('battery', luna::batteryPourcentage());
      $luna->checkAndUpdateCmd('status', luna::batteryStatus());
      if ($luna->getConfiguration('wifiEnabled', 0) == 1) {
        $luna->checkAndUpdateCmd('ssid', $luna->getConfiguration('wifiSsid', ''));
      } else {
        $luna->checkAndUpdateCmd('ssid', 'Aucun');
      }
    }
  }

  /* ----- START ----- */

  public static function start() {
    log::add(__CLASS__, 'debug', __('Jeedom est démarré, vérification des connexions.', __FILE__));
    $luna = eqLogic::byLogicalId('wifi', __CLASS__);
    if (is_object($luna)) {
      $luna->wifiConnect();
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

  public static function isWifiProfileexist($ssid) {
    $result = shell_exec("nmcli --fields NAME con show");
    $countProfile = substr_count($result, $ssid);
    if ($countProfile > 1) {
      log::add(__CLASS__, 'debug', __('Suppression des profils.', __FILE__));
      shell_exec("nmcli --pretty --fields UUID,TYPE con show | grep wifi | awk '{print $1}' | while read line; do nmcli con delete uuid  $line; done");
      return true;
    } else if ($countProfile == 1) {
      return true;
    } else {
      return false;
    }
  }

  public static function listWifi($forced = false) {
    $eqLogic = eqLogic::byType(__CLASS__);
    log::add(__CLASS__, 'debug', 'Wifi enabled : ' . $eqLogic[0]->getConfiguration('wifiEnabled'));
    $return = [];
    if ($eqLogic[0]->getConfiguration('wifiEnabled') == true || $forced == true) {
      $scanresult = shell_exec('sudo nmcli -f SSID,SIGNAL,SECURITY,CHAN -t -m tabular dev wifi list');
      $results = explode("\n", $scanresult);
      $return = array();
      foreach ($results as $result) {
        log::add(__CLASS__, 'debug', $result);
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
          log::add(__CLASS__, 'debug', $ssid . ' with signal ' . $signal . ' and security ' . $security . ' on channel ' . $chan);
          if (isset($return[$ssid]) && $return[$ssid]['signal'] > $signal) {
            continue;
          }
          $return[$ssid] = array('ssid' => $ssid, 'signal' => $signal, 'security' => $security, 'channel' => $chan);
        }
      }
    }
    return $return;
  }

  public static function getMac($_interface = 'eth0') {
    $interfaceIp = shell_exec("sudo ifconfig $_interface | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
    $interfaceMac = shell_exec("sudo ip addr show $_interface | grep -i 'link/ether' | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}' | sed -n 1p");
    return [$interfaceMac, $interfaceIp];
  }

  public function wifiConnect() {
    if ($this->getConfiguration('wifiEnabled') == true) {
      luna::activeHotSpot();
      if ($this->getConfiguration('hotspotEnabled') == true) {
        return;
      } else {
        $ssid = $this->getConfiguration('wifiSsid', '');
      }
      if (self::isWificonnected($ssid) === false) {
        log::add(__CLASS__, 'debug', __('Non connecté à', __FILE__) . ' ' . $ssid . '. ' . __('Connexion en cours...', __FILE__));
        shell_exec("sudo ip link set wlan0");
        if (self::isWifiProfileexist($ssid) === true) {
          $exec = "sudo nmcli con up '" . $ssid . "'";
        } else {
          $password = $this->getConfiguration('wifiPassword', '');
          if ($password != '') {
            $exec = "sudo nmcli dev wifi connect '" . $ssid . "' password '" . $password . "'";
          } else {
            $exec = "sudo nmcli dev wifi connect '" . $ssid . "'";
          }
        }
        log::add(__CLASS__, 'debug', 'Executing ' . $exec);
        shell_exec($exec);
      }
    } else {
      log::add(__CLASS__, 'debug', 'Executing sudo nmcli dev disconnect wlan0');
      shell_exec('sudo nmcli dev disconnect wlan0');
    }
  }

  /* ----- FIN WIFI ----- */

  /* ----- HotSpot ----- */

  public function testHotspot() {
    $linkForHotspot = __DIR__ . '/../../resources/lnxrouter';
    if ($this->getConfiguration('hotspotEnabled') == true) {
      $pid = shell_exec("sudo bash " . $linkForHotspot . " -l");
      if ($pid != "") {
        luna::activeHotSpot();
      }
    }
  }

  public static function activeHotSpot() {
    log::add(__CLASS__, 'debug', __('Activation du Hotspot.', __FILE__));
    $linkForHotspot = __DIR__ . '/../../resources/lnxrouter';
    $wlanLink = 'wlan0';
    $luna = eqLogic::byLogicalId('wifi', __CLASS__);
    $interfaceInfo = luna::getMac();
    $macAddress = $interfaceInfo[0];
    log::add(__CLASS__, 'debug', 'Informations getMac > '.json_encode($interfaceInfo));
    $strMac = str_replace(':', '', $macAddress);
    $wifiPostFix = substr($strMac, -4);
    if (!is_object($luna)) {
      log::add(__CLASS__, 'debug', __('Hotspot : erreur 1.', __FILE__));
      return;
    }
    if ($luna->getConfiguration('hotspotEnabled') == true) {

      log::add(__CLASS__, 'debug', __('Hotspot activé.', __FILE__));
      log::add(__CLASS__, 'debug', 'Executing sudo nmcli dev disconnect wlan1');

      shell_exec('sudo nmcli dev disconnect wlan1');
      shell_exec('sudo systemctl daemon-reload');
      $pid = shell_exec("sudo bash " . $linkForHotspot . " -l");
      $log = shell_exec("sudo bash " . $linkForHotspot . " --stop " . $pid . " > /dev/null 2>&1");
      log::add(__CLASS__, 'debug', 'Hotspot PID > ' . $pid);
      log::add(__CLASS__, 'debug', 'Hotspot LOG instance sup > ' . $log);
      log::add(__CLASS__, 'debug', 'Hotspot macAddress > ' . $strMac);
      $luna->setConfiguration('dns', 'wlan1');
      $luna->setConfiguration('forwardingIPV4', true);
      $ssid = $luna->getConfiguration('ssidHotspot', 'Jeedomluna-' . $wifiPostFix);
      $mdp = $luna->getConfiguration('mdpHotspot', $strMac);
      if ($ssid == 'Jeedomluna-' . $wifiPostFix) {
        $luna->setConfiguration('ssidHotspot', 'Jeedomluna-' . $wifiPostFix);
      }
      if ($mdp == $strMac) {
        $luna->setConfiguration('mdpHotspot', $strMac);
      }
      $luna->save();

      log::add(__CLASS__, 'debug', __('Mise en place du Profil Hotspot.', __FILE__));
      log::add(__CLASS__, 'debug', 'sudo bash ' . $linkForHotspot . ' --daemon --ap ' . $wlanLink . ' ' . $ssid . ' -p ' . $mdp . ' > /dev/null 2>&1');
      $log = shell_exec('sudo bash ' . $linkForHotspot . ' --daemon --ap ' . $wlanLink . ' ' . $ssid . ' -p ' . $mdp . ' --no-virt > /dev/null 2>&1');
      log::add(__CLASS__, 'debug', 'Hotspot > ' . $log);
    } else {
      shell_exec('sudo systemctl daemon-reload');
      shell_exec('sudo ifconfig wlan1 up');
      $pid = shell_exec("sudo bash " . $linkForHotspot . " -l");
      $log = shell_exec("sudo bash " . $linkForHotspot . " --stop " . $pid . " > /dev/null 2>&1");
    }
  }



  /* ----- FIN Hotspot ----- */

  /* ----- DSLED ----- */
  
  public function dsLed ($demande = 'g on'){
    $dsledExe = __DIR__ . '/../../resources/dsled/dsled';
      exec('sudo '.$dsledExe.' g off');
      exec('sudo '.$dsledExe.' r off');
      exec('sudo '.$dsledExe.' b off');
      exec('sudo pkill -9 dsled');
      if($demande !== 'off'){
        exec('sudo '.$dsledExe.' '.$demande);
      }
  }

  /* ----- FIN DSLED ----- */

   /* ----- BATTERY ----- */
  
   public function batteryPourcentage (){
    return exec('sudo cat /sys/class/power_supply/bq27546-0/capacity');
  }

  public function batteryStatus (){
    return exec('sudo cat /sys/class/power_supply/bq27546-0/status');
  }

  public function batterySwitchMaj(){
    message::add('luna', __('Mise à jour batterie Luna', __FILE__));
    exec('sudo cp '. __DIR__ . '/../../data/patchs/batterySwitch /usr/bin/');
    exec('sudo cp '. __DIR__ . '/../../data/patchs/batterySwitch.service /etc/systemd/system/');
    exec('sudo chmod 755 /usr/bin/batterySwitch');
    exec('sudo chmod 755 /etc/systemd/system/batterySwitch.service');
    exec('sudo systemctl enable --now batterySwitch.service');
  }

  /* ----- FIN BATTERY ----- */

     /* ----- SD ----- */
  
     public function partitionSD (){
      exec('sudo unmount '.$sdSector);
      message::add('luna', __('Patitionnage en cours', __FILE__));
      exec('sudo chmod +x ../../data/patchs/partitionSD.sh');
      exect('sudo ../../data/patchs/partitionSD.sh');
      message::add('luna', __('Carte SD bien partitionnée', __FILE__));
    }

    public function checkPartitionSD () {
      exec('sudo lsblk -f -J 2>&1', $jsonVolumes);
      $response = false;
      foreach($jsonVolumes as $volume){
        log::add(__CLASS__, 'debug', 'JSON VOLUME > ' . json_decode($volume, true));
        log::add(__CLASS__, 'debug', 'JSON VOLUME > ' . $volume);
        $valueVolume = json_decode($volume, true);
        if($valueVolume['name'] === 'mmcblk2' && $valueVolume['fstype'] === 'ext3'){
          log::add(__CLASS__, 'debug', 'trouvé');
          $response = true;
        }
      }
      return $response;
    }

    public function presentSD (){
      $sdSector = "/dev/mmcblk2";
      if(file_exists($sdSector)){
        return true;
      }
      luna::changeBackupToEmmc();
      return false;
    }

    public function BackupOkInSd(){
      if(config::byKey('backup::path') == "/media/"){
        return true;
      }else{
        return false;
      }
    }

    public function mountSD (){
      $sdSector = "/dev/mmcblk2";
      $montage = "/media/";
      exec('sudo unmount '.$sdSector);
      exec('sudo mount '.$sdSector.' '.$montage);
    }

    public function changeBackupToSD (){
      $montage = "/media/";
      config::save('backup::path', $montage);
    }

    public function changeBackupToEmmc (){
      if(luna::BackupOkInSd()){
        $montage = "/var/www/html/backup/";
        config::save('backup::path', $montage);
      }
    }
  
  
    /* ----- FIN SD ----- */


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

    $signal = $this->getCmd(null, 'signal');
    if (!is_object($signal)) {
      $signal = new lunaCmd();
      $signal->setName(__('Signal', __FILE__));
      $signal->setOrder(23);
    }
    $signal->setEqLogic_id($this->getId());
    $signal->setLogicalId('signal');
    $signal->setType('info');
    $signal->setSubType('numeric');
    $signal->save();

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

    $ssid = $this->getCmd(null, 'ssid');
    if (!is_object($ssid)) {
      $ssid = new lunaCmd();
      $ssid->setName(__('SSID', __FILE__));
      $ssid->setOrder(26);
    }
    $ssid->setEqLogic_id($this->getId());
    $ssid->setLogicalId('ssid');
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
    $dsled->setConfiguration('listValue','g breathe|Vert Respiration;r breathe|Rouge Respiration;b breathe|Bleu Respiration;g blink_fast|Vert Clignotant Rapidement;r blink_fast|Rouge Clignotant Rapidement;b blink_fast|Bleu Clignotant Rapidement;g blink_slow|Vert Clignotant lent;r blink_slow|Rouge Clignotant lent;b blink_slow|Bleu Clignotant lent;g on|Vert On;r on|Rouge On;b on|Bleu On;off|Off');
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
  }

  public function postAjax() {
    $this->wifiConnect();
  }
}

class lunaCmd extends cmd {

  public function execute($_options = array()) {
    if ($this->getType() == '') {
      return '';
    }
    $eqLogic = $this->getEqlogic();
    $action = $this->getLogicalId();
    switch ($action) {
      case 'connect':
        $eqLogic->setConfiguration('wifiEnabled', true);
        $eqLogic->save();
        break;
      case 'disconnect':
        $eqLogic->setConfiguration('wifiEnabled', false);
        $eqLogic->save();
        break;
      case 'repair':
        $ssidConf = $eqLogic->getConfiguration('wifiSsid');
        if ($ssidConf == "") {
          $eqLogic->setConfiguration('wifiSsid', shell_exec('iwgetid -r'));
          $eqLogic->save();
          message::add('wifip', __('Sauvegarde du SSID', __FILE__));
        }
        $connFile = shell_exec('nmcli --fields TYPE,FILENAME con show --active | grep -i wifi | cut -c46-600');
        message::add('luna', __('Suppression des profils pour', __FILE__) . ' ' . $connFile);
        shell_exec('sudo find /etc/NetworkManager/system-connections -type f ! -name "' . $connFile . '" -delete');
        message::add('luna', __('Suppression effectuée, veuillez redémarrer.', __FILE__));
        break;
      case 'dsled':
        luna::dsLed( $_options['select']);
        break;
    }
    $eqLogic->cron5($eqLogic->getId());
  }

  /*     * **********************Getteur Setteur*************************** */
}
