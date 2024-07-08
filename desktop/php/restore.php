<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<div role="tabpanel" class="tab-pane" id="restoretab"><br />
	<fieldset>
	  	<legend><i class="fa fa-clone"></i> {{Restore}}</legend>
		<div class="row">
			<div class="col-sm-6">
				<h3><i class="fas fa-highlighter warning"></i> {{Mise à jour du Recovery}}</h3>
				<div class="alert alert-warning">
					<i class="fas fa-exclamation-triangle"></i> {{Attention, cette opération peut être dangereuse.}}
				</div>
				<span>{{La mise à jour du Recovery, permet de télécharger la}} <b>{{dernière}}</b> {{image de restauration de votre Luna.}} <br /> {{Mais aussi de mettre a jour (via cette image) les composants Linux de celle-ci.}} <br /> {{Votre Luna doit etre}} <b>{{connectée}}</b> {{à internet pour effectuer cette operation.}}</span>
				<br /><br />
				<center>
					<a class="btn btn-warning" id="bt_recoveryUpdate">
						<i class="fas fa-highlighter"></i>
						<span class="hidden-xs"> Lancement mise à jour</span>
					</a>
				</center>
			</div>
			<div class="col-sm-6">
				<h3><i class="fas fa-clone danger"></i> {{Restauration d'usine}}</h3>
				<div class="alert alert-danger">
					<i class="fas fa-exclamation-triangle"></i> {{Attention, cette opération est irréversible.}}
				</div>
				<span>{{Le recovery, permet une}} <b>{{remise a zero d'usine}}</b> {{de la Luna avec l'image inclus dans celle-ci (vous pouvez la mettre à jour via Mise à jour du recovery).}} <br/> {{Attention à bien}} <b>{{sauvegarder}}</b> {{votre box et récuperer celle-ci sur votre ordinateur avant de lancer le recovery.}}</span>
				<br /><br />
				<center>
					<a class="btn btn-danger" id="bt_recovery">
						<i class="fas fa-clone"></i>
						<span class="hidden-xs"> Lancement Recovery</span>
					</a>
				</center>
			</div>
		</div>
  </fieldset> 
</div>

<?php include_file('desktop', 'restore', 'js', 'luna'); ?>