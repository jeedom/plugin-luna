
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


$('#bt_saveLTE').off('click').on('click', function() {
  jeedom.eqLogic.save({
    type: 'luna',
    eqLogics: $("#ltePanel").getValues('.eqLogicAttr'),
    error: function(error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function() {
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
            location.reload();
        }
      });
    }
    })
  })

  $('#bt_changePortSms').off('click').on('click', function() {
    $.ajax({
      type: "POST",
      url: "plugins/luna/core/ajax/luna.ajax.php",
      data: {
        action: "configurationPortSms"
      },
      dataType: 'json',
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
      },
      success: function() {
          $('#div_alert').showAlert({
            message: 'Changement du port SMS',
            level: 'success'
          });
          location.reload();
      }
    });
  })

  $('#bt_scanLTE').off('click').on('click', function() {
    console.log('scanLTE');
    $.ajax({
      type: "POST", 
      url: "plugins/luna/core/ajax/luna.ajax.php", 
      data: {
        action: "scanLTE"
      },
      dataType: 'json',
      async: true,
      global: false,
      error: function(request, status, error) {
        handleAjaxError(request, status, error)
      },
      success: function(data) {
        if (data.state != 'ok') {
          return
        }
      }
    })
  })

  document.getElementById('bt_saveUnlockSim')?.addEventListener('click', function() {
    domUtils.ajax({
      type: "POST",
      url: "plugins/luna/core/ajax/luna.ajax.php",
      data: {
        action: "unlockSim"
      },
      dataType: 'json',
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
      },
      success: function() {
          $('#div_alert').showAlert({
            message: '{{Déblocage de la SIM}}',
            level: 'success'
          });
          location.reload();
      }
    });
  });
  

  document.getElementById('bt_startJeedomLTE')?.addEventListener('click', function() {
    if (window.intervalAlertModem) {
      clearInterval(window.intervalAlertModem);
    }
    window.intervalAlertModem = setInterval(function() {
      $('#div_alert').showAlert({ message: '{{Activation du modem en cours}}...', level: 'success' });
    }, 5000);
    var intervalModem = setInterval(function() {
      $.showLoading();
    }, 1000);
    domUtils.ajax({
      type: "POST",
      url: "plugins/luna/core/ajax/luna.ajax.php",
      data: {
        action: "startJeedomLTE"
      },
      dataType: 'json',
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
      },

      success: function(data) {
        if (data.state != 'ok') {
          $('#div_alert').showAlert({ message: data.result, level: 'danger' });
        } else {
            clearInterval(window.intervalAlertModem);
            clearInterval(intervalModem); 
            $.hideLoading();
            location.reload();
        }
      }
    });
  });