<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<div role="tabpanel" class="tab-pane" id="wifitab"><br />
	<div class="row">
		<div class="col-sm-6">
			<form id="wifi1Panel" class="form-horizontal">
				<fieldset>
					<legend><i class="fa fa-wifi"></i> {{Wifi 1}}</legend>
					<div class="form-group">
						<div class="col-lg-3">
						</div>
						<input type="text" class="eqLogicAttr wifi1 form-control" data-l1key="id" style="display : none;" />
						<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr wifi1 ipfixwifienabled wifiEnabledCheck" data-l1key="configuration" data-l2key="wifi1Enabled" id="wifi1EnabledCheck" unchecked />{{Activer le wifi 1}}</label>
					</div>
					<br />
					<div class="form-group" id='wifi1ModeBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Mode}} :</label>
						<div class="col-lg-5">
							<select class="eqLogicAttr wifi1 form-control wifiMode" id='wifi1Mode' data-l1key="configuration" data-l2key="wifi1Mode">
								<option id="wifi1none" value=""></option>
								<option id="wifi1client" value="client">{{Client Wifi}}</option>
								<option id="wifi1hotspot" value="hotspot">{{Hotspot Wifi}}</option>
							</select>
						</div>
					</div>
					<div class="form-group" id='wifi1TypeAdressageBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Adressage IP}} :</label>
						<div class="col-lg-5">
							<select class="eqLogicAttr wifi1 form-control wifiTypeAdressage" id='wifi1TypeAdressage' data-l1key="configuration" data-l2key="wifi1TypeAdressage">
								<option id="wifi1dhcp" value="dhcp">{{DHCP}}</option>
								<option id="wifi1hotspot" value="fixe">{{IP Fixe}}</option>
							</select>
						</div>
					</div>
					<div class="form-group" id='wifi1ipBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Adresse IP}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi1 form-control wifi1ip" data-l1key="configuration" data-l2key="wifi1ip" placeholder="{{Adresse IP}}" />
						</div>
					</div>
					<div class="form-group" id='wifi1maskBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Masque de sous-réseau}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi1 form-control wifi1mask" data-l1key="configuration" data-l2key="wifi1mask" placeholder="{{Masque}}" />
						</div>
					</div>
					<div class="form-group" id='wifi1routerBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Routeur}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi1 form-control" data-l1key="configuration" data-l2key="wifi1router" placeholder="{{Routeur}}" />
						</div>
					</div>
					<div class="form-group" id='wifi1dnsBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Serveur DNS}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi1 form-control" data-l1key="configuration" data-l2key="wifi1dns" placeholder="{{DNS}}" />
						</div>
					</div>
					<div class="form-group" id='wifi1dnsOptBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Remplacer le DNS DHCP}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi1 form-control" data-l1key="configuration" data-l2key="wifi1dnsOpt" placeholder="{{DNS}}" />
						</div>
					</div>
					<div class="form-group" id='wifi1hotspotnameBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Nom Hotspot}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi1 form-control" data-l1key="configuration" data-l2key="wifi1hotspotname" placeholder="{{Nom}}" />
						</div>
					</div>
					<div class="form-group" id='wifi1hotspotpwdBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Mot de passe Hotspot}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi1 form-control" data-l1key="configuration" data-l2key="wifi1hotspotpwd" placeholder="{{mot de passe}}" />
						</div>
					</div>
					<div class="form-group" id='wifi1hotspotdhcpBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{DHCP du hotspot}} :</label>
						<div class="col-lg-5">
							<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr wifi1 wifiTypeDHCPHotspot" data-l1key="configuration" data-l2key="wifi1hotspotdhcp" id="wifi1TypeDHCPHotspot" unchecked />{{DHCP du hotspot}}</label>
						</div>
					</div>
					<div class="form-group" id='wifi1hotspotipBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Adresse IP}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi1 form-control wifi1ip" data-l1key="configuration" data-l2key="wifi1hotspotip" placeholder="{{Adresse IP}}" />
						</div>
					</div>
					<div class="form-group" id='wifi1hotspotmaskBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Masque de sous-réseau}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi1 form-control wifi1mask" data-l1key="configuration" data-l2key="wifi1hotspotmask" placeholder="{{Masque}}" />
						</div>
					</div>
					<div class="form-group wifi1" id='wifi1SsidBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Réseau wifi}} :</label>
						<div class="col-lg-7">
							<select class="eqLogicAttr wifi1 form-control nohotspot" data-l1key="configuration" data-l2key="wifi1Ssid"></select>
						</div>
						<div class="col-lg-2">
							<a class="btn btn-info bt_refreshWifiList" id="bt_refreshWifi1List"><i class="fas fa-sync-alt"></i></a>
						</div>
					</div>
					<div class="form-group wifi1" id='wifi1PasswordBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Clef}} :</label>
						<div class="col-lg-5">
							<input type="password" class="eqLogicAttr form-control nohotspot" data-l1key="configuration" data-l2key="wifi1Password" />
						</div>
					</div>
				</fieldset>
				<hr style="border: 2px solid !important;">
				<table id="table_wifi1profile" class="table table-bordered table-condensed" style="display : none;">
					<thead>
						<tr>
							<th>{{Nom Réseau}}</th>
							<th>{{Etat}}</th>
							<th>{{Action}}</th>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</form>
			<div class="col-sm-4"></div>
			<div class="col-sm-6" style="text-align: center;"><a class="btn btn-sm btn-success eqLogicAction" id="saveWifi1"><i class="fas fa-check-circle"></i> {{Sauvegarder WIFI 1}}</a></div>
		</div>
		<div class="col-sm-6">
		<form id="wifi2Panel" class="form-horizontal">
				<fieldset>
					<legend><i class="fa fa-wifi"></i> {{Wifi 2}}</legend>
					<div class="form-group">
						<div class="col-lg-3">
						</div>
						<input type="text" class="eqLogicAttr wifi2 form-control" data-l1key="id" style="display : none;" />
						<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr wifi2 ipfixwifienabled wifiEnabledCheck" data-l1key="configuration" data-l2key="wifi2Enabled" id="wifi2EnabledCheck" unchecked />{{Activer le wifi 2}}</label>
					</div>
					<br />
					<div class="form-group" id='wifi2ModeBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Mode}} :</label>
						<div class="col-lg-5">
							<select class="eqLogicAttr wifi2 form-control wifiMode" id='wifi2Mode' data-l1key="configuration" data-l2key="wifi2Mode">
								<option id="wifi2none" value=""></option>
								<option id="wifi2client" value="client">{{Client Wifi}}</option>
								<option id="wifi2hotspot" value="hotspot">{{Hotspot Wifi}}</option>
							</select>
						</div>
					</div>
					<div class="form-group" id='wifi2TypeAdressageBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Adressage IP}} :</label>
						<div class="col-lg-5">
							<select class="eqLogicAttr wifi2 form-control wifiTypeAdressage" id='wifi2TypeAdressage' data-l1key="configuration" data-l2key="wifi2TypeAdressage">
								<option id="wifi2dhcp" value="dhcp">{{DHCP}}</option>
								<option id="wifi2hotspot" value="fixe">{{IP Fixe}}</option>
							</select>
						</div>
					</div>
					<div class="form-group" id='wifi2ipBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Adresse IP}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi2 form-control wifi2ip" data-l1key="configuration" data-l2key="wifi2ip" placeholder="{{Adresse IP}}" />
						</div>
					</div>
					<div class="form-group" id='wifi2maskBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Masque de sous-réseau}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi2 form-control wifi2mask" data-l1key="configuration" data-l2key="wifi2mask" placeholder="{{Masque}}" />
						</div>
					</div>
					<div class="form-group" id='wifi2routerBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Routeur}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi2 form-control" data-l1key="configuration" data-l2key="wifi2router" placeholder="{{Routeur}}" />
						</div>
					</div>
					<div class="form-group" id='wifi2dnsBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Serveur DNS}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi2 form-control" data-l1key="configuration" data-l2key="wifi2dns" placeholder="{{DNS}}" />
						</div>
					</div>
					<div class="form-group" id='wifi2dnsOptBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Remplacer le DNS DHCP}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi2 form-control" data-l1key="configuration" data-l2key="wifi2dnsOpt" placeholder="{{DNS}}" />
						</div>
					</div>
					<div class="form-group" id='wifi2hotspotnameBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Nom Hotspot}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi2 form-control" data-l1key="configuration" data-l2key="wifi2hotspotname" placeholder="{{Nom}}" />
						</div>
					</div>
					<div class="form-group" id='wifi2hotspotpwdBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Mot de passe Hotspot}} :</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi2 form-control" data-l1key="configuration" data-l2key="wifi2hotspotpwd" placeholder="{{mot de passe}}" />
						</div>
					</div>
					<div class="form-group" id='wifi2hotspotdhcpBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{DHCP du hotspot}} :</label>
						<div class="col-lg-5">
							<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr wifi2 wifiTypeDHCPHotspot" data-l1key="configuration" data-l2key="wifi2hotspotdhcp" id="wifi2TypeDHCPHotspot" unchecked />{{DHCP du hotspot}}</label>
						</div>
					</div>
					<div class="form-group" id='wifi2hotspotipBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Adresse IP}} :
							<sup><i class="fas fa-question-circle" tooltip="{{Exemple : si vous choisissez 192.168.4.1, les adresses IP de votre serveur DHCP seront, selon le masque de sous-réseau (255.255.255.0), dans la plage allant de 192.168.4.2 à 192.168.4.254.}}"></i></sup>
						</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi2 form-control wifi1ip" data-l1key="configuration" data-l2key="wifi2hotspotip" placeholder="{{Adresse IP}}" />
						</div>
					</div>
					<div class="form-group" id='wifi2hotspotmaskBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Masque de sous-réseau}} :
							<sup><i class="fas fa-question-circle" tooltip="{{Voir exemple Adresse IP au dessus.}}"></i></sup>
						</label>
						<div class="col-lg-5">
							<input type="text" class="eqLogicAttr wifi2 form-control wifi1mask" data-l1key="configuration" data-l2key="wifi2hotspotmask" placeholder="{{Masque}}" />
						</div>
					</div>
					<div class="form-group wifi2" id='wifi2SsidBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Réseau wifi}} :</label>
						<div class="col-lg-7">
							<select class="eqLogicAttr wifi2 form-control nohotspot" data-l1key="configuration" data-l2key="wifi2Ssid"></select>
						</div>
						<div class="col-lg-2">
							<a class="btn btn-info bt_refreshWifiList" id="bt_refreshWifi2List"><i class="fas fa-sync-alt"></i></a>
						</div>
					</div>
					<div class="form-group wifi2" id='wifi2PasswordBlock' style="display : none;">
						<label class="col-lg-3 control-label">{{Clef}} :</label>
						<div class="col-lg-5">
							<input type="password" class="eqLogicAttr form-control nohotspot" data-l1key="configuration" data-l2key="wifi2Password" />
						</div>
					</div>
				</fieldset>
				<hr style="border: 2px solid !important;">
				<table id="table_wifi2profile" class="table table-bordered table-condensed" style="display : none;">
					<thead>
						<tr>
							<th>{{Nom Réseau}}</th>
							<th>{{Etat}}</th>
							<th>{{Action}}</th>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</form>
			<div class="col-sm-4"></div>
			<div class="col-sm-6" style="text-align: center;"><a class="btn btn-sm btn-success eqLogicAction" id="saveWifi2"><i class="fas fa-check-circle"></i> {{Sauvegarder WIFI 2}}</a></div>
		</div>
	</div>
</div>

<?php include_file('desktop', 'wifi', 'js', 'luna'); ?>