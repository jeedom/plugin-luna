<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

if(luna::loraServiceActif() == false){
	$loraAc = '
	<i class="icon_red fas fa-times"></i>
	<a class="btn" id="bt_start_lora">
		<i class="fas fa-check"></i>
		<span class="hidden-xs"> Activation</span>
	</a>';
}else{
	$loraAc = '
	<i class="icon_green fas fa-check"></i>
	<a class="btn" id="bt_stop_lora">
	<i class="fas fa-times"></i>
	<span class="hidden-xs"> Désactivé</span>
	</a>';
}

?>
<div role="tabpanel" class="tab-pane" id="LORAtab"><br />
	<fieldset>
	  	<legend><i class="fa fa-satellite-dish"></i> {{Lora}}</legend>
		  <div class="row">
			<div class="col-sm-2">
				<br />
				<img class="lazy" src="/plugins/luna/data/img/lora.webp" width="100%"/>
			</div>
		<?php
		if(luna::detectedLora()){
		?>
			<div class="col-sm-6">
				<h4> <b>{{Service}}</b> : <?php echo $loraAc; ?></h4>
				<h4><b>{{Gateway UID}}</b> : <?php echo config::byKey('gatewayUID','luna', null); ?></h4>
			</div>
		<?php
		}else{
			?>
				<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{Votre Luna n'as pas de module Lora integré, le plugin prend en charge uniquement les Luna Lora.}}</div>
			<?php
		}
		?>
		</div>
  	</fieldset> 			
</div>


<?php include_file('desktop', 'lora', 'js', 'luna'); ?>