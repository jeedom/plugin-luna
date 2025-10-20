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

$('#bt_start_lora')
  .off('click')
  .on('click', function () {
    var dialog_title = '<i class="fas fa-sign-in-alt fa-rotate-90"></i> {{Activer le module Lora}}'
    var dialog_message = '<center>{{le lora sera activer sur la box.}}</center>'
    bootbox.dialog({
      title: dialog_title,
      message: dialog_message,
      buttons: {
        "{{Annuler}}": {
          className: "btn-danger",
          callback: function () {}
        },
        success: {
          label: "{{Démarrer}}",
          className: "btn-success",
          callback: function () {
            $.ajax({
              type: "POST",
              url: "plugins/luna/core/ajax/luna.ajax.php",
              data: {
                action: "loraSwitchMaj",
                active: "active"
              },
              dataType: 'json',
              error: function (request, status, error) {
                handleAjaxError(request, status, error)
              },
              success: function (data) {
                    location.reload();
                if (data.state != 'ok') {
                    $('#div_alert').showAlert({ message: data.result, level: 'danger' })
                    return
                }
              }
            })
          }
        }
      }
    })
  })

$('#bt_stop_lora')
  .off('click')
  .on('click', function () {
    var dialog_title = '<i class="fas fa-sign-in-alt fa-rotate-90"></i> {{Desactiver le module Lora}}'
    var dialog_message = '<center>{{le lora sera désactivé sur la box.}}</center>'
    bootbox.dialog({
      title: dialog_title,
      message: dialog_message,
      buttons: {
        "{{Annuler}}": {
          className: "btn-danger",
          callback: function () {}
        },
        success: {
          label: "{{Démarrer}}",
          className: "btn-success",
          callback: function () {
            $.ajax({
              type: "POST",
              url: "plugins/luna/core/ajax/luna.ajax.php",
              data: {
                action: "loraSwitchMaj",
                active: "NoActive"
              },
              dataType: 'json',
              error: function (request, status, error) {
                handleAjaxError(request, status, error)
              },
              success: function (data) {
                location.reload();
                if (data.state != 'ok') {
                    $('#div_alert').showAlert({ message: data.result, level: 'danger' })
                    return
                  }
              }
            })
          }
        }
      }
    })
  })

$('#bt_reconfig_lora')
  .off('click')
  .on('click', function () {
    var dialog_title = '<i class="fas fa-cog"></i> {{Reconfigurer le packet forwarder}}'
    var dialog_message = '<center>{{Cette action va relancer la configuration du packet forwarder Lora.<br><br>Souhaitez-vous continuer ?}}</center>'
    
    bootbox.dialog({
      title: dialog_title,
      message: dialog_message,
      buttons: {
        "{{Annuler}}": {
          className: "btn-danger",
          callback: function () {}
        },
        success: {
          label: "{{Reconfigurer}}",
          className: "btn-warning",
          callback: function () {
            $('#div_alert').showAlert({
              message: '{{Reconfiguration en cours...}}',
              level: 'warning'
            })
            $.ajax({
              type: "POST",
              url: "plugins/luna/core/ajax/luna.ajax.php",
              data: {
                action: "reconfigPacketForwarder"
              },
              dataType: 'json',
              error: function (request, status, error) {
                handleAjaxError(request, status, error)
              },
              success: function (data) {
                if (data.state != 'ok') {
                  $('#div_alert').showAlert({
                    message: data.result,
                    level: 'danger'
                  })
                  return
                }
                $('#div_alert').showAlert({
                  message: '{{Le packet forwarder a été reconfiguré avec succès !}}',
                  level: 'success'
                })
              }
            })
          }
        }
      }
    })
  })