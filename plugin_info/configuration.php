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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}
if(config::byKey('4G','luna', false) == true){
    ?>
<form class="form-horizontal">
  <fieldset>
    <div class="form-group">
      <label class="col-md-4 control-label">{{LTE APN}}
        <sup><i class="fas fa-question-circle tooltips" title="{{APN pour la partie LTE}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="lteApn"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{LTE User}}
        <sup><i class="fas fa-question-circle tooltips" title="{{User pour la partie LTE}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="lteUser"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{LTE Password}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Password pour la partie LTE}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="ltePassword"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{LTE PIN}}
        <sup><i class="fas fa-question-circle tooltips" title="{{ne rien mettre si pas de code pin}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="ltePin"/>
      </div>
    </div>
  </fieldset>
  <div class="form-actions">
    <label class="col-md-4 control-label">{{Gestion LTE}}</label>
    <div class="col-md-4">
      <a class="btn btn-info" id="bt_saveLTE"><i class="fas fa-check-circle"></i> {{Re-Lancer}}</a>
      {{N'oubliez pas de sauvegarder avant}}
    </div>
  </div>
</form>
<script>
  $('#bt_saveLTE').off('click').on('click', function() {
    $.ajax({
      type: "POST",
      url: "plugins/luna/core/ajax/luna.ajax.php",
      data: {
        action: "configjsonlte"
      },
      dataType: 'json',
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
      },
      success: function() {
          $('#div_alert').showAlert({
            message: 'Mise à jour LTE',
            level: 'success'
          });
      }
    });
  })
</script>
<?php
}
?>