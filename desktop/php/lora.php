<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<div role="tabpanel" class="tab-pane" id="LORAtab"><br />
	<fieldset>
	  	<legend><i class="fa fa-satellite-dish"></i> {{Lora}}</legend>
		<?php
		if(luna::detectedLora()){
		?>
		<div class="row">
			<div class="col-sm-6">
				<h3><i class="fa fa-comments"></i> {{Gateway UID}}</h3>
				<?php echo config::byKey('gatewayUID','luna', null); ?>
			</div>
		</div>
		<?php
		}else{
			?>
			<div class="row">
				<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{Votre Luna n'as pas de module Lora integré, le plugin prend en charge uniquement les Luna Lora.}}</div>
			</div>
			<?php
		}
		?>
  	</fieldset> 			
</div>


<?php include_file('desktop', 'lora', 'js', 'luna'); ?>