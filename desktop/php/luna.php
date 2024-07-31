<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('luna');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
$eqLogic = luna::byLogicalId('wifi', 'luna');

$isLte = null;
$lte = config::byKey('isLte', 'luna');
if (isset($lte)) {
	if($lte == 'LTE'){
		$isLte = 'LTE';
	}else if($lte == 'NOLTE'){
		$isLte = 'NOLTE';
	}else{
		$isLte = null;
	}
}
sendVarToJS('isLte', $isLte);


?>

<div class="row row-overflow">
	<!-- Page d'accueil du plugin -->
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<?php
		if (count($eqLogics) == 0) {
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
		}
			// Liste des équipements du plugin
			echo '<div class="eqLogicThumbnailContainer" >';
				echo '<div class="eqLogicDisplayCard cursor" style="display:none;" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			echo '</div>';

		?>
	</div> <!-- /.eqLogicThumbnailDisplay -->

	<!-- Page de présentation de l'équipement -->
	<div class="col-xs-12 eqLogic" style="display:none;">
		<!-- barre de gestion de l'équipement -->
		<div class="input-group pull-right" style="display:inline-flex;">
			<span class="input-group-btn">
				<!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a>
			</span>
		</div>
		<!-- Onglets -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Général}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Commandes}}</a></li>
			<li role="presentation"><a href="#wifitab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-wifi"></i> {{WIFI}}</a></li>
			<li role="presentation"><a href="#ethernettab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-network-wired"></i> {{Ethernet}}</a></li>
			<?php
			if($isLte == 'LTE'){
				echo '<li role="presentation"><a href="#LTEtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-signal" ></i> {{LTE}}</a></li>';
			}
			?>
			<?php
			if(luna::detectedLora()){
				echo '<li role="presentation"><a href="#LORAtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-satellite-dish"></i> {{Lora}}</a></li>';
			}
			?>
			
			<li role="presentation"><a href="#batterytab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-battery-full"></i> {{Batterie}}</a></li>
			<li role="presentation"><a href="#sdtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-sd-card"></i> {{Carte SD}}</a></li>
			<li role="presentation"><a href="#restoretab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-clone"></i> {{Restore}}</a></li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab"><br />
				<div class="row">
					<div class="col-sm-7">
						<form id="lunaPanel" class="form-horizontal">
							<fieldset>
								<legend>{{Général}}<i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i></legend>
								<div class="form-group">
									<label class="col-lg-3 control-label">{{Nom de l'équipement}}</label>
									<div class="col-lg-4">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}" />
									</div>

								</div>
								<div class="form-group">
									<label class="col-lg-3 control-label">{{Objet parent}}</label>
									<div class="col-lg-4">
										<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
											<option value="">{{Aucun}}</option>
											<?php
											foreach (jeeObject::all() as $object) {
												echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label"></label>
									<div class="col-sm-9">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
									</div>
								</div>
								<legend><i class='fa fa-cogs'></i>{{Priorité des connexions}}</legend>
								<input type="text" id="priority" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="priority" style="display : none;" />

								<div class="table-responsive">
									<table id="table_connexions" class="table table-bordered table-condensed">
										<thead>
											<tr>
											    <th style="width:50px;"></th>
												<th style="min-width:50px;width:70px;"> {{Priorité}}</th>
												<th style="min-width:50px;width:70px;"> {{Type}}</th>
												<th style="min-width:120px;width:250px;">{{Nom}}</th>
												<th style="width:130px;">{{Metric}}</th>
												
											</tr>
										</thead>
										<tbody>
											<?php
											 $scanresult = shell_exec('sudo nmcli -f UUID,NAME,TYPE,ACTIVE -t -m tabular con show --active');
											 $results = explode("\n", $scanresult);
											 $return = array();
											 foreach ($results as $result) {
											   	$result = str_replace('\:', '$%$%', $result);
											   	$result = preg_replace("#(\r\n|\n\r|\n|\r)#","",$result);
											   	$conDetail = explode(':', $result);
											   	$conUUID = $conDetail[0];
											   	$conName = $conDetail[1];
											   	$conType = $conDetail[2];
												$conMetric = shell_exec('sudo nmcli -f ipv4.route-metric -t -m tabular con show '.$conUUID);
												$conMetric = preg_replace("#(\r\n|\n\r|\n|\r)#","",$conMetric);
											   	$return[] = array('UUID' => $conUUID, 'name' => $conName, 'type' => $conType, 'metric' => $conMetric);
											   	log::add('luna', 'debug', json_encode($return));

											 }
											 usort($return, function($a, $b) { return $a['metric'] <=> $b['metric']; });
											// usort($return, fn($a, $b) => $a['metric'] <=> $b['metric']);
											$displayIndex = 1;
											foreach($return as $index => $conn){											
												if($conn['name'] != 'tun0' && $conn['name'] != "" ){
													echo '<tr class="conn" id="'.$conn['UUID'].'"><td class="arrowSortable"><i class="icon fas fa-arrows-alt-v"></i></td><td>'.($displayIndex).'</td><td>'.$conn['type'].'</td><td>'.$conn['name'].'</td><td>'.$conn['metric'].'</td></tr>';
													$displayIndex++;
												}
											}
											?>
										</tbody>
									</table>
								</div>
							</fieldset>
						</form>
						<div class="col-sm-4"></div>
						<div class="col-sm-6" style="text-align: center;"><a class="btn btn-sm btn-success eqLogicAction" id="saveLuna"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a></div>
					</div>
					<div class="col-sm-5">
						<form class="form-horizontal">
							<fieldset>
								<legend><i class="fa fa-info-circle"></i> {{Informations}}</legend>
								<div class="form-group">
									<label class="col-lg-4 control-label">{{Adresse MAC ethernet}}</label>
									<div class="col-lg-4">
										<span class="label label-info macLan" style="font-size:1em;cursor:default;"></span>
									</div>
								</div>
								<div class="form-group">
									<label class="col-lg-4 control-label">{{Adresse Ip ethernet}}</label>
									<div class="col-lg-4">
										<span class="label label-info ipLan" style="font-size:1em;cursor:default;"></span>
									</div>
								</div>
								<div class="form-group">
									<label class="col-lg-4 control-label">{{Adresse MAC wifi}}</label>
									<div class="col-lg-4">
										<span class="label label-info macWifi" style="font-size:1em;cursor:default;"></span>
									</div>

								</div>
								<div class="form-group">
									<label class="col-lg-4 control-label">{{Adresse Ip wifi}}</label>
									<div class="col-lg-4">
										<span class="label label-info ipWifi" style="font-size:1em;cursor:default;"></span>
									</div>
								</div>
								<?php
								if(config::byKey('isLte', 'luna') == 'LTE'){
								?>
									<div class="form-group">
										<label class="col-lg-4 control-label" id="labelLTE">{{Adresse Ip LTE}}</label>
										<div class="col-lg-4">
											<span class="label label-info ipLte" style="font-size:1em;cursor:default;"></span>
										</div>
									</div>
								<?php
								}
								?>
								<legend><i class="fa fa-info-circle"></i> {{Outils Administration}}</legend>
								<div class="form-group">
										<div class="alert alert-warning">
											<i class="fas fa-exclamation-triangle"></i> {{Se référer à la documentation du plugin pour plus d'informations}}
											<a class="btn btn-info btn-sm tippied" target="_blank" href="https://doc.jeedom.com/fr_FR/plugins/home%20automation%20protocol/luna/beta" data-title="Accéder à la documentation du plugin"><i class="fas fa-book"></i> Documentation</a>
										</div>
										<div style="display:flex;flex-direction:column;width:100%;">
											   <div style="display:flex;">
													<label>{{Relancer configuration du Plugin}}</label>
														<div style="margin-left:20px;">
														<a class="btn btn-success btn-xs" id="bt_reloadConfig"><i class="fas fa-play"></i></a>
														</div>
												</div>
												<div style="display:flex;">
													<label>{{Nettoyer Configuration Wifi}}</label>
													<div style="margin-left:20px;">
														<a class="btn btn-success btn-xs" id="bt_cleanWifi"><i class='icon fas fa-broom'></i></a>
													</div>
												</div>

												
										</div>
									
								</div>
								<!-- <div class="form-group">
									<label class="col-lg-4 control-label">{{Adresse MAC wifi 2}}</label>
									<div class="col-lg-4">
										<span class="label label-info macWifi2" style="font-size:1em;cursor:default;"></span>
									</div>

								</div> -->
								<!-- <div class="form-group">
									<label class="col-lg-4 control-label">{{Adresse Ip wifi 2}}</label>
									<div class="col-lg-4">
										<span class="label label-info ipWifi2" style="font-size:1em;cursor:default;"></span>
									</div>
								</div> -->
							</fieldset>
						</form>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-7">
						<form class="form-horizontal">
							<fieldset>

							</fieldset>
						</form>
					</div>
					<div class="col-sm-5">

					</div>
				</div>
			</div>

			<?php
				//include des Tabs
				include_file('desktop', 'wifi', 'php', 'luna');
				include_file('desktop', 'ethernet', 'php', 'luna');
				include_file('desktop', 'lte', 'php', 'luna');
				include_file('desktop', 'lora', 'php', 'luna');
				include_file('desktop', 'battery', 'php', 'luna');
				include_file('desktop', 'sd', 'php', 'luna');
				include_file('desktop', 'restore', 'php', 'luna');
			?>

			<div role="tabpanel" class="tab-pane" id="commandtab">
	
				<div class="table-responsive">
					<table id="table_cmd" class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th class="hidden-xs" style="min-width:50px;width:70px;">ID</th>
								<th style="min-width:200px;width:350px;">{{Nom}}</th>
								<th>{{Type}}</th>
								<th style="min-width:260px;">{{Options}}</th>
								<th>{{Etat}}</th>
								<th style="min-width:80px;width:200px;">{{Actions}}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>


<style>

.arrowSortable:hover{
	cursor: move;

}

.conn.hover-style {
  border: 1px solid #94CA00;
  transform: scale(1.02);
}


</style>

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php include_file('desktop', 'luna', 'js', 'luna'); ?>
<script>



  if(isLte === null || isLte == undefined || isLte == ''){
 
	if (window.intervalAlertLTE) {
      clearInterval(window.intervalAlertLTE);
  	}
  	window.intervalAlertLTE = setInterval(function() {
      $('#div_alert').showAlert({ message: 'Configuration en cours de votre plugin...', level: 'success' });
  	}, 2000);
	var intervalLTE = setInterval(function() {
		$.showLoading();
	}, 1000);
    $.ajax({
      type: "POST",
      url: "plugins/luna/core/ajax/luna.ajax.php",
      data: {
        action: "isLTELuna",
      },
      dataType: 'json',
	  async: true,
      global: false,
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
      },
      success: function(data) {
        if (data.state != 'ok') {
          $('#div_alert').showAlert({ message: data.result, level: 'danger' });
        } else {
			clearInterval(window.intervalAlertLTE);
			clearInterval(intervalLTE);		
			if(data.result == 'LTE'){
				$('#div_alert').showAlert({ message: 'LTE Operationnel', level: 'danger' });
			}
			$.hideLoading();
			location.reload();
        }
      }
    });
  }



	setTimeout(() => {
		document.querySelector('.eqLogicDisplayCard[data-eqlogic_id="<?php echo $eqLogic->getId() ?>"]')?.click()
	}, 100);

</script>

<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js'); ?>
