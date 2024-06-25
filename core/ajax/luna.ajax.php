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

try {
  require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
  include_file('core', 'authentification', 'php');

  if (!isConnect('admin')) {
    throw new Exception(__('401 - Accès non autorisé', __FILE__));
  }

  /* Fonction permettant l'envoi de l'entête 'Content-Type: application/json'
    En V3 : indiquer l'argument 'true' pour contrôler le token d'accès Jeedom
    En V4 : autoriser l'exécution d'une méthode 'action' en GET en indiquant le(s) nom(s) de(s) action(s) dans un tableau en argument
  */
  ajax::init();

  if (init('action') == 'loop_percentage') {
    luna::loopPercentage();
    ajax::success();
  }


  if(init('action') == 'mountSD'){
    luna::mountSD();
    luna::mountPersistent();
    ajax::success();
  }

  if (init('action') == 'startMigration') {
    luna::startMigration();
    ajax::success();
  }

  if (init('action') == 'lancementMajRestauration') {
    luna::lancementMajRestauration();
    ajax::success();
  }

  if (init('action') == 'lancementRaZ') {
    luna::lancementRaZ();
    ajax::success();
  }

  if (init('action') == 'partitionSD') {
    luna::partitionSD();
    ajax::success();
  }

  if (init('action') == 'configjsonlte') {
    luna::configjsonlte();
    ajax::success();
  }

  if (init('action') == 'scanLTE') {
    luna::scanLTEModule();
    ajax::success();
  }

  if (init('action') == 'loraSwitchMaj') {
    luna::loraSwitchMaj(init('active'));
    ajax::success();
  }

  if (init('action') == 'changeBackupToSD') {
    luna::changeBackupToSD();
    ajax::success();
  }

  if (init('action') == 'changeBackupToEmmc') {
    luna::changeBackupToEmmc();
    ajax::success();
  }

  if (init('action') == 'savePriority') {
    log::add('luna', 'debug', 'savePriority');
    log::add('luna', 'debug', init('priority'));
    ajax::success(luna::savePriority(init('priority')));
  }

  if (init('action') == 'listWifi') {
    $forced = init('mode');
    ajax::success(luna::listWifi($forced, init('interface')));
  }

  if (init('action') == 'saveWifi') {
    ajax::success(luna::saveWifi(init('data'),init('interface')));
  }

  if (init('action') == 'saveEthernet') {
    ajax::success(luna::saveEthernet(init('data')));
  }

  if (init('action') == 'listConnections') {
    ajax::success(luna::listConnections(init('interface')));
  }

  if (init('action') == 'removeConnection') {
    ajax::success(luna::RemoveConnection(init('UUID')));
  }

  if (init('action') == 'macfinder') {
    ajax::success(luna::getMac(init('interfa')));
  }

  if (init('action') == 'recuperationConfigModem') {
    ajax::success(luna::recuperationConfigModem());
  }

  if (init('action') == 'configurationPortSms') {
    ajax::success(luna::configurationPortSms());
  }

  throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
  /*     * *********Catch exeption*************** */
} catch (Exception $e) {
  ajax::error(displayException($e), $e->getCode());
}
