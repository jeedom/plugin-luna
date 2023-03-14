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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';


function luna_install() {
	$eqLogic = luna::byLogicalId('wifi', 'luna');
	if (!is_object($eqLogic)) {
		message::add('luna', __('Installation du module Luna', __FILE__));
		$eqLogic = new luna();
		$eqLogic->setLogicalId('wifi');
		$eqLogic->setCategory('multimedia', 1);
		$eqLogic->setName(__('Luna', __FILE__));
		$eqLogic->setEqType_name('luna');
		$eqLogic->setIsVisible(1);
		$eqLogic->setIsEnable(1);
		$eqLogic->save();
	}
	foreach (eqLogic::byType('luna') as $luna) {
		$luna->save();
	}
	luna::patchLuna();
	luna::switchHost();
	luna::installLora();
	luna::install4g();
}

function luna_update() {
	$eqLogic = luna::byLogicalId('wifi', 'luna');
	if (!is_object($eqLogic)) {
		message::add('luna', __('Mise à jour du module Luna', __FILE__));
		$eqLogic = new luna();
		$eqLogic->setLogicalId('wifi');
		$eqLogic->setCategory('multimedia', 1);
		$eqLogic->setName(__('Luna', __FILE__));
		$eqLogic->setEqType_name('luna');
		$eqLogic->setIsVisible(1);
		$eqLogic->setIsEnable(1);
		$eqLogic->save();
	}
	foreach (eqLogic::byType('luna') as $luna) {
		$luna->save();
	}
	luna::patchLuna();
	luna::switchHost();
	luna::installLora();
	luna::install4g();
}
