<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

$checkPartitionSD = luna::checkPartitionSD();
$presentSD = luna::presentSD();
$backupSD = luna::BackupOkInSd();

if($presentSD == 0){
	$PresenceSD = '<i class="icon_red fas fa-times"></i>';
}else{
	$PresenceSD = '<i class="icon_green fas fa-check"></i> ';
}
if($checkPartitionSD == 0){
	$PartitionSD = '<i class="icon_red fas fa-times"></i>
	<a class="btn" id="bt_partitionSD">
	<i class="fas fa-redo"></i>
	<span class="hidden-xs"> Partitionner</span><a/>';
}else{
	$PartitionSD = '<i class="icon_green fas fa-check"></i>
	<a class="btn" id="bt_partitionSD">
	<i class="fas fa-eraser"></i>
	<span class="hidden-xs"> Effacer</span></a>';
}
if($checkPartitionSD == 0){
	$backSD = '<i class="icon_red fas fa-times"></i> <span class="hidden-xs"> Carte SD non partitionnée</span>';
}else{
	if($backupSD == 0){
		$backSD = '
		<i class="icon_red fas fa-times"></i>
		<a class="btn" id="bt_changeBackupToSD">
			<i class="fas fa-check"></i>
			<span class="hidden-xs"> Activation</span>
		</a>';
	}else{
		$backSD = '
		<i class="icon_green fas fa-check"></i>
		<a class="btn" id="bt_changeBackupToEmmc">
		<i class="fas fa-times"></i>
		<span class="hidden-xs"> Désactivé</span>
		</a>';
}
}
?>
<div role="tabpanel" class="tab-pane" id="sdtab"><br />
	<fieldset>
	  <legend><i class="fa fa-sd-card"></i> {{Carte SD}}</legend>
		<div class="row">
			<div class="col-sm-2">
				<br />
				<img class="lazy" src="/plugins/luna/data/img/sd.png" width="100%"/>
			</div>
			<div class="col-sm-5">
				<h3> <b>{{Carte SD détectée}}</b> : <?php echo $PresenceSD; ?></h3><br />
				<?php if($presentSD == 0){
					echo '<h4> <b>{{refresh}}</b> : <a class="btn" id="bt_refresh_sd"> <i class="fas fa-redo"></i></a> </h4>';
				}else{ ?>
					<h4> <b>{{Partition}}</b> : <?php echo $PartitionSD; ?></h4>
					<h4> <b>{{Sauvegarde Jeedom dans la carte}}</b> : <?php echo $backSD; ?></h4><br />
				<?php } ?>
			</div>
		</div>
	</fieldset> 	
</div>

<?php include_file('desktop', 'sd', 'js', 'luna'); ?>