
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

$('#bt_changeBackupToSD').off('click').on('click', function() {
    var dialog_title = '<i class="fas fa-sign-in-alt fa-rotate-90"></i> {{Activer backup sur la SD}}'
    var dialog_message = '<center>{{les backups seront maintenant archivés sur cette carte SD.}}</center>'
    bootbox.dialog({
      title: dialog_title,
      message: dialog_message,
      buttons: {
        "{{Annuler}}": {
          className: "btn-danger",
          callback: function() {
          }
        },
        success: {
          label: "{{Démarrer}}",
          className: "btn-success",
          callback: function() {
            $.ajax({
              type: "POST",
              url: "plugins/luna/core/ajax/luna.ajax.php",
              data: {
                action: "changeBackupToSD"
              },
              dataType: 'json',
              error: function(request, status, error) {
                handleAjaxError(request, status, error)
              },
              success: function(data) {
                location.reload()
                if (data.state != 'ok') {
                  $('#div_alert').showAlert({ message: data.result, level: 'danger' })
                  return
                }
              }
            })
          }
        },
      }
    })
  })
  
  $('#bt_changeBackupToEmmc').off('click').on('click', function() {
    var dialog_title = '<i class="fas fa-sign-in-alt fa-rotate-90"></i> {{Activer backup sur la Box}}'
    var dialog_message = '<center>{{les backups seront maintenant archivés sur la box.}}</center>'
    bootbox.dialog({
      title: dialog_title,
      message: dialog_message,
      buttons: {
        "{{Annuler}}": {
          className: "btn-danger",
          callback: function() {
          }
        },
        success: {
          label: "{{Démarrer}}",
          className: "btn-success",
          callback: function() {
            $.ajax({
              type: "POST",
              url: "plugins/luna/core/ajax/luna.ajax.php",
              data: {
                action: "changeBackupToEmmc"
              },
              dataType: 'json',
              error: function(request, status, error) {
                handleAjaxError(request, status, error)
              },
              success: function(data) {
                location.reload()
                if (data.state != 'ok') {
                  $('#div_alert').showAlert({ message: data.result, level: 'danger' })
                  return
                }
              }
            })
          }
        },
      }
    })
  })

  $('#bt_partitionSD').off('click').on('click', function() {
    var dialog_title = '<i class="fas fa-sign-in-alt fa-rotate-90"></i> {{Partition de la carte SD}}'
    var dialog_message = '<center>{{Attention cela supprimera le contenu de votre carte SD}}</center>'
    bootbox.dialog({
      title: dialog_title,
      message: dialog_message,
      buttons: {
        "{{Annuler}}": {
          className: "btn-danger",
          callback: function() {
          }
        },
        success: {
          label: "{{Démarrer}}",
          className: "btn-success",
          callback: function() {
            $.ajax({
              type: "POST",
              url: "plugins/luna/core/ajax/luna.ajax.php",
              data: {
                action: "partitionSD"
              },
              dataType: 'json',
              error: function(request, status, error) {
                handleAjaxError(request, status, error)
              },
              success: function(data) {
                location.reload()
                if (data.state != 'ok') {
                  $('#div_alert').showAlert({ message: data.result, level: 'danger' })
                  return
                }
              }
            })
          }
        },
      }
    })
  })

  $('#bt_refresh_sd').off('click').on('click', function() {
    location.reload()
  });