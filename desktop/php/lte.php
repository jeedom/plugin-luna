<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

$sms = null;

$plugins = plugin::listPlugin();
foreach ($plugins as $plugin) {
	if ($plugin->getId() == 'sms') {
		$sms = $plugin;
	}
}
$portSmsLuna = "/dev/ttyLuna-Lte";
?>
<div role="tabpanel" class="tab-pane" id="LTEtab"><br />
	<form id="ltePanel" class="form-horizontal">
		<fieldset>
			<legend><i class="fa fa-signal"></i> {{LTE}}</legend>
			<?php
			$isLte = config::byKey('isLte', 'luna');
			if ($isLte == 'LTE') {
			?>
				<div class="row">
					<div class="col-sm-6">
						<h3><i class="fa fa-comments"></i> {{Configuration SMS}}</h3>
						<?php
						if (is_object($sms)) {
							$portSms = config::byKey('port', 'sms');
							$pinSMS = config::byKey('pin', 'sms');
							$pinLte = config::byKey('ltePin', 'luna');
							if ($portSms == '') {
								echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{Le port du plugin SMS n\'est pas configuré}}</div>';
							} else {
								if ($portSms != $portSmsLuna) {
									echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{Le port du plugin SMS n\'est pas configuré sur le port de la Luna}} : ' . $portSmsLuna . '  <a class="btn btn-info" style="float: right;" id="bt_changePortSms"><i class="fas fa-check-circle"></i> {{Changer Automatiquement}}</a></div>';
								}
								$eqLogics = eqLogic::byType('sms');
								if (count($eqLogics) == 0) {
									echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{Aucun équipement SMS n\'est configuré}}</div>';
								} else {
									echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> {{Le plugin SMS est configuré}}</div>';
								}
						?>
								<div class="form-group">
									<label class="col-lg-3 control-label">{{Port Plugin SMS}}
										<sup><i class="fas fa-question-circle tooltips" title="{{Port a configurer dans le plugin SMS}}"></i></sup>
									</label>
									<div class="col-lg-4">
										<input value="<?php echo $portSms; ?>" disabled />
									</div>
								</div>
						<?php
								if ($pinSMS != $ltePin) {
									echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{Les Codes Pin entre les deux plugins sont different.}}</div>';
								}
							}
						} else {
							echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> {{Le plugin SMS n&#39;est pas installé}}</div>';
						}
						?>
					</div>
					<div class="col-sm-6">
						<h3><i class="fa fa-signal"></i> {{Configuration LTE}}</h3>
						<div class="alert alert-warning">
							<i class="fas fa-exclamation-triangle"></i> {{Uniquement si vous avez une carte SIM LTE avec data}}
						</div>
						<div class="form-group">
							<div class="col-lg-3">
							</div>
							<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
						</div>
						<div class="form-group">
							<label class="col-lg-3 control-label">*{{APN}}
								<sup><i class="fas fa-question-circle tooltips" title="{{APN pour la partie LTE /!\ Attention il faut des APN de type ipv4 (bouygue : ebouygtel.com, Orange : orange, free : free).}}"></i></sup>
							</label>
							<div class="col-lg-4">
								<input class="eqLogicAttr form-control form-lte" data-l1key="configuration" data-l2key="lteApn" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-3 control-label">{{Utilisateur}}
								<sup><i class="fas fa-question-circle tooltips" title="{{User pour la partie LTE}}"></i></sup>
							</label>
							<div class="col-lg-4">
								<input class="eqLogicAttr form-control form-lte" data-l1key="configuration" data-l2key="lteUser" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-3 control-label">{{Mot de passe}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Password pour la partie LTE}}"></i></sup>
							</label>
							<div class="col-lg-4">
								<input class="eqLogicAttr form-control form-lte" data-l1key="configuration" data-l2key="ltePassword" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-3 control-label">{{Code Pin}}
								<sup><i class="fas fa-question-circle tooltips" title="{{ne rien mettre si pas de code pin}}"></i></sup>
							</label>
							<div class="col-lg-2">
								<input class="eqLogicAttr form-control form-lte" type="number" data-l1key="configuration" data-l2key="ltePin" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-3 control-label">{{Activation Data}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Prise en charge de la data via la connexion LTE, pour les sim avec data}}"></i></sup>
							</label>
							<div class="col-lg-4">
								<input type="checkbox" class="eqLogicAttr form-control  form-lte" data-l1key="configuration" data-l2key="lteActivation" checked />
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-3 control-label">{{Connexion LTE}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Verification si votre 4G est bien connecté}}"></i></sup>
							</label>
							<div class="col-lg-4 macLteCoche">
							</div>
						</div>
						<br />
						<div class="form-actions">
							<div class="col-lg-12">
								<a class="btn btn-info" id="bt_saveLTE"><i class="fas fa-check-circle"></i>{{Sauvegarder les APN}}</a>
							</div>
						</div>
						<br /><br /><br />
						<i>*{{Seul l'apn est obligatoire}}</i>
						<br /><br /><br />
						<?php
						if($modem = luna::recuperationConfigModem()) {
							?>
							<div class="form-group">
								<label class="col-lg-3 control-label">{{IMEI}}
									<sup><i class="fas fa-question-circle tooltips" title="{{IMEI de la Luna}}"></i></sup>
								</label>
								<div class="col-lg-4">
									<input class="form-control" value="<?php echo $modem['imei']; ?>" disabled />
								</div>
							</div>
							<div class="form-group">
								<label class="col-lg-3 control-label">{{Nom de l'opérateur}}
								</label>
								<div class="col-lg-4">
									<input class="form-control" value="<?php echo $modem['operatorName']; ?>" disabled />
								</div>
							</div>
							<div class="form-group">
								<label class="col-lg-3 control-label">{{Signal}}
								</label>
								<div class="col-lg-4">
									<input class="form-control" value="<?php echo $modem['signalPercent']; ?>" disabled />
								</div>
							</div>
							<div class="form-group">
								<label class="col-lg-3 control-label">{{Etat}}
								</label>
								<div class="col-lg-4">
									<input class="form-control" value="<?php echo $modem['state']; ?>" disabled />
									<input class="form-control" value="<?php echo $modem['stateFailedReasonLabel']; ?>" disabled />
									<input name="stateFailedReason" id="stateFailedReason" hidden class="form-control" value="<?php echo $modem['stateFailedReason']; ?>" />
								</div>
							</div>
							<div class="form-group">
								<label class="col-lg-3 control-label">{{Deblocage SIM}}
								</label>
								<div class="col-lg-4">
									<input class="form-control" value="<?php echo $modem['unlockRequired']; ?>" disabled />
									<input class="form-control" value="<?php json_encode($modem['unlockRetries']); ?>" disabled />
								</div>
							</div>
						<?php
						} else {
							echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> {{Aucun modem n\'est configuré}}</div>';
						}
					?>
					</div>
	</form>
</div>
<?php
			} else {
?>
	<div class="row">
		<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{Votre Luna ne possède pas le module LTE}}</div>
	</div>
<?php
			}
?>
</fieldset>
</form>
</div>


<?php include_file('desktop', 'lte', 'js', 'luna'); ?>