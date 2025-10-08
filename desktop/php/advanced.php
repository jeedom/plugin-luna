<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$batteryPourcentage = luna::batteryPourcentage();

?>
<div role="tabpanel" class="tab-pane" id="advancedtab"><br />
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> Attention, ces options sont réservées aux utilisateurs avancés. Une mauvaise manipulation peut rendre votre système instable.
    </div>

    <fieldset>

      <!-- === Formulaire changement mot de passe root === -->
      <legend><i class="fas fa-wrench"></i> {{Changement du mot de passe ssh Root}}</legend>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Mot de passe}}</label>
        <div class="input-group col-sm-3">
            <input class="roundedLeft form-control inputPassword" type="text" id="rootPassword"><br>
            <span class="input-group-btn">
                <a class="btn btn-success pull-right" id="saveMdpRoot"><i class="fas fa-check-circle icon-white"></i> {{Sauvegarder}}</a>
            </span>
        </div>
      </div>
      <!-- =============================================== -->

      <!-- === Fichier de log rotate === -->
      <legend><i class="far fa-file"></i> {{Configuration du log rotate}}</legend>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Niveau de conservation}}</label>
        <div class="input-group col-sm-3">
            <select class="eqLogicAttr roundedLeft form-control" id="logRotate" data-l1key="configuration" data-l2key="logRotate">
              <option value=""></option>
              <option value="light">{{Léger (daily - 250M)}}</option>
              <option value="heavy">{{Lourd (weekly - No limit)}}</option>
            </select><br>
            <span class="input-group-btn">
                <a class="btn btn-success pull-right" id="applyLogRotate"><i class="fas fa-check-circle icon-white"></i> {{Appliquer}}</a>
            </span>
        </div>
      </div>
      <!-- =============================================== -->

      <!-- === Factory reset par bouton === -->
      <legend><i class="fas fa-power-off"></i> {{Factory reset par bouton}}</legend>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Bouton reset}}</label>
        <div class="input-group col-sm-3">
            <select class="eqLogicAttr roundedLeft form-control" id="fsReset" data-l1key="configuration" data-l2key="fsReset">
              <option value="activateFsreset">{{Activer}}</option>
              <option value="desactivateFsreset">{{Désactiver}}</option>
            </select><br>
            <span class="input-group-btn">
                <a class="btn btn-success pull-right" id="applyFsreset"><i class="fas fa-check-circle icon-white"></i> {{Appliquer}}</a>
            </span>
        </div>
      </div>
      <!-- =============================================== -->

      <!-- === Failover script === -->
      <legend><i class="fas fa-wifi"></i> {{Failover script}}</legend>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Failover}}
          <sup><i class="fas fa-question-circle tooltips" title="{{Force le basculement vers une autre connexion Internet dans l'ordre suivant : Ethernet -> Wi-Fi -> LTE}}"></i></sup>
        </label>
        <div class="input-group col-sm-3">
            <select class="eqLogicAttr roundedLeft form-control" id="failover" data-l1key="configuration" data-l2key="failover">
              <option value="activateFailover">{{Activer}}</option>
              <option value="desactivateFailover">{{Désactiver}}</option>
            </select><br>
            <span class="input-group-btn">
                <a class="btn btn-success pull-right" id="applyFailover"><i class="fas fa-check-circle icon-white"></i> {{Appliquer}}</a>
            </span>
        </div>
      </div>
      <!-- =============================================== -->

      <!-- === Failover script === -->
      <legend><i class="fas fa-redo"></i> {{Programmation redémarrage box}}</legend>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Programmation}}</label>
      <div class="col-sm-3">
        <div class="input-group">
          <input type="text" class="form-control eqLogicAttr" id="cronRebootBox" data-l1key="configuration" data-l2key="cronRebootBox" placeholder="Assistant cron">
          <span class="input-group-btn">
            <a class="btn btn-default jeeHelper" data-helper="cron" title="Assistant cron">
              <i class="fas fa-question-circle"></i>
            </a>
            <a class="btn btn-success" id="applyCronRebootBox"><i class="fas fa-check"></i> {{Appliquer}}</a>
          </span>
        </div>
      </div>
      </div>
      <!-- =============================================== -->

    </fieldset>
</div>

<?php include_file('desktop', 'advanced', 'js', 'luna'); ?>
<script>
$('#saveMdpRoot').off('click').on('click', function() {
  var newPassword = $('#rootPassword').val();

  if (newPassword.trim() === '') {
    alert('Veuillez entrer un mot de passe');
    return;
  }

  $.ajax({
    type: "POST",
    url: "plugins/luna/core/ajax/luna.ajax.php",
    data: {
      action: "changeRootPassword",
      password: newPassword
    },
    dataType: 'json',
    success: function (data) {
      if (data.state !== 'ok') {
        alert('Erreur : ' + data.result);
        return;
      }
      alert('Mot de passe modifié avec succès');
    },
    error: function (request, status, error) {
      alert('Erreur AJAX : ' + error);
    }
  });
});

$('#applyLogRotate').off('click').on('click', function() {
  var logRotate = $('#logRotate').val();
  $.ajax({
    type: "POST",
    url: "plugins/luna/core/ajax/luna.ajax.php",
    data: {
      action: "applyLogRotate",
      type: logRotate
    },
    dataType: 'json',
    success: function (data) {
      if (data.state !== 'ok') {
        alert('Erreur : ' + data.result);
        return;
      }
      alert('Fichier de log appliqué avec succès');
    },
    error: function (request, status, error) {
      alert('Erreur AJAX : ' + error);
    }
  });
});

$('#applyFsreset').off('click').on('click', function() {
  var fsReset = $('#fsReset').val();
  $.ajax({
    type: "POST",
    url: "plugins/luna/core/ajax/luna.ajax.php",
    data: {
      action: "applyFsreset",
      type: fsReset
    },
    dataType: 'json',
    success: function (data) {
      if (data.state !== 'ok') {
        alert('Erreur : ' + data.result);
        return;
      }
      alert('Modification du bouton reset avec succès');
    },
    error: function (request, status, error) {
      alert('Erreur AJAX : ' + error);
    }
  });
});

$('#applyFailover').off('click').on('click', function() {
  var failover = $('#failover').val();
  $.ajax({
    type: "POST",
    url: "plugins/luna/core/ajax/luna.ajax.php",
    data: {
      action: "applyFailover",
      type: failover
    },
    dataType: 'json',
    success: function (data) {
      if (data.state !== 'ok') {
        alert('Erreur : ' + data.result);
        return;
      }
      alert('Modification du failover avec succès');
    },
    error: function (request, status, error) {
      alert('Erreur AJAX : ' + error);
    }
  });
});

$('#applyCronRebootBox').off('click').on('click', function() {
  var cronReboot = $('#cronRebootBox').val();
  $.ajax({
    type: "POST",
    url: "plugins/luna/core/ajax/luna.ajax.php",
    data: {
      action: "applyCronRebootBox",
      type: cronReboot
    },
    dataType: 'json',
    success: function (data) {
      if (data.state !== 'ok') {
        alert('Erreur : ' + data.result);
        return;
      }
      alert('Modification du cron de redémarrage avec succès');
    },
    error: function (request, status, error) {
      alert('Erreur AJAX : ' + error);
    }
  });
});
</script>