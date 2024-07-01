
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



  var rows = document.querySelectorAll('.conn');

  rows.forEach(function(row) {
    row.addEventListener('mouseover', function() {
      this.style.border = '1px solid #94CA00';
      this.style.transform = 'scale(1.02)';
    });

    row.addEventListener('mouseout', function() {
      this.style.border = '';
    });

    row.addEventListener('mousedown', function() {

      this.longPressTimer = setTimeout(() => {
        this.style.backgroundColor = '#94CA00'; 
        Array.from(this.children).forEach(child => {
          child.style.color = '#94CA00'; 
        });
        this.style.transform = 'scale(1.02)';
        this.style.transition = 'transform 0.25s ease, background-color 0.25s ease, color 0.25s ease';
      }, 100);
    });

    row.addEventListener('mouseup', function() {
      clearTimeout(this.longPressTimer);
      this.style.backgroundColor = ''; 
      Array.from(this.children).forEach(child => {
        child.style.color = ''; 
      });
      this.style.transform = '';
      this.style.transition = 'transform 0.25s ease, background-color 0.25s ease, color 0.25s ease';
    });

    row.addEventListener('mouseleave', function() {
      clearTimeout(this.longPressTimer);
      this.style.backgroundColor = '';
      Array.from(this.children).forEach(child => {
        child.style.color = ''; 
      });
      this.style.transform = '';
      this.style.transition = 'transform 0.25s ease, background-color 0.25s ease, color 0.25s ease';
    });
  });

function printEqLogic(_eqLogic) {
  printMacLan()
  printMacWifi()
  printMacLte()
  //printMacWifi2()
}



function changeInformation(key, info = "") {
  if(info == ''){
    var info = "X"
  }
  $(key).empty().append(info)
}




function printMacLan() {
  $.ajax({// fonction permettant de faire de l'ajax
    type: "POST", // methode de transmission des données au fichier php
    url: "plugins/luna/core/ajax/luna.ajax.php", // url du fichier php
    data: {
      action: "macfinder",
      interfa: "eth0",
    },
    dataType: 'json',
    async: true,
    global: false,
    error: function(request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function(data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({ message: data.result, level: 'danger' })
        return
      }
      changeInformation('.macLan', data.result[0])
      changeInformation('.ipLan', data.result[1])
    }
  })
}

function printMacWifi() {
  $.ajax({// fonction permettant de faire de l'ajax
    type: "POST", // methode de transmission des données au fichier php
    url: "plugins/luna/core/ajax/luna.ajax.php", // url du fichier php
    data: {
      action: "macfinder",
      interfa: "wlan0",
    },
    dataType: 'json',
    async: true,
    global: false,
    error: function(request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function(data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({ message: data.result, level: 'danger' })
        return
      }
      changeInformation('.macWifi', data.result[0])
      changeInformation('.ipWifi', data.result[1])
    }
  })
}

function printMacWifi2() {
  $.ajax({// fonction permettant de faire de l'ajax
    type: "POST", // methode de transmission des données au fichier php
    url: "plugins/luna/core/ajax/luna.ajax.php", // url du fichier php
    data: {
      action: "macfinder",
      interfa: "wlan1",
    },
    dataType: 'json',
    async: true,
    global: false,
    error: function(request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function(data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({ message: data.result, level: 'danger' })
        return
      }
      changeInformation('.macWifi2', data.result[0])
      changeInformation('.ipWifi2', data.result[1])
    }
  })
}

function printMacLte() {
  $.ajax({// fonction permettant de faire de l'ajax
    type: "POST", // methode de transmission des données au fichier php
    url: "plugins/luna/core/ajax/luna.ajax.php", // url du fichier php
    data: {
      action: "macfinder",
      interfa: "ppp0",
    },
    dataType: 'json',
    async: true,
    global: false,
    error: function(request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function(data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({ message: data.result, level: 'danger' })
        return
      }
      if(data.result[1] == null){
        $('.macLteCoche').empty().append('<i class="icon_red fas fa-times"></i>')
      }else{
        $('.macLteCoche').empty().append('<i class="icon_green fas fa-check"></i>')
      }
      changeInformation('.ipLte', data.result[1])
      
    }
  })
}

// window.setInterval(function() {
//   printMacLan()
//   printMacWifi()
//   printMacLte()
//   printMacWifi2()
// }, 5000)

$("#table_connexions").sortable({
  axis: "y",
  cursor: "move",
  items: ".conn",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true,
  stop: function(event, ui) {
    $('#table_connexions .conn').each(function(index) {
      $(this).find("td").eq(1).text(index + 1);
    });
  }
});

// $("#table_connexions").sortable({ axis: "y", cursor: "move", items: ".conn", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true })
$("#table_cmd").sortable({ axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true })

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = {configuration: {}}
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {}
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
  tr += '<td class="hidden-xs">'
  tr += '<span class="cmdAttr" data-l1key="id"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<div class="input-group">'
  tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
  tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
  tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
  tr += '</div>'
  tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
  tr += '<option value="">{{Aucune}}</option>'
  tr += '</select>'
  tr += '</td>'
  tr += '<td>'
  tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '
  tr += '<div style="margin-top:7px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '</div>'
  tr += '</td>'
  tr += '<td>';
  tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>'; 
  tr += '</td>';
  tr += '<td>'
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> Tester</a>'
  }
  tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
  tr += '</tr>'
  $('#table_cmd tbody').append(tr)
  var tr = $('#table_cmd tbody tr').last()
  jeedom.eqLogic.buildSelectCmd({
    id:  $('.eqLogicAttr[data-l1key=id]').value(),
    filter: {type: 'info'},
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'})
    },
    success: function (result) {
      tr.find('.cmdAttr[data-l1key=value]').append(result)
      tr.setValues(_cmd, '.cmdAttr')
      jeedom.cmd.changeType(tr, init(_cmd.subType))
    }
  })
}

function ajax_loop_percentage() {
  $.ajax({
    type: "POST",
    url: "plugins/luna/core/ajax/luna.ajax.php",
    data: {
      action: "loop_percentage"
    },
    dataType: 'json',
    error: function(request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function(data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({ message: data.result, level: 'danger' })
        return
      }
    }
  })
}


function ajax_start_percentage() {
  $.ajax({
    type: "POST",
    url: "plugins/luna/core/ajax/luna.ajax.php",
    data: {
      action: "start_percentage"
    },
    dataType: 'json',
    error: function(request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function(data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({ message: data.result, level: 'danger' })
        return
      }
    }
  })
}


// $('#saveLuna').off('click').on('click', function() {
//   $.showLoading();
//   $("#priority").val($("#table_connexions").sortable('toArray'));
//   $.ajax({
//     type: "POST",
//     url: "plugins/luna/core/ajax/luna.ajax.php",
//     data: {
//       action: "savePriority",
//       priority: $("#table_connexions").sortable('toArray')
//     },
//     dataType: 'json',
//     async: true,
//     global: false,
//     error: function(request, status, error) {
//       handleAjaxError(request, status, error)
//     },
//     success: function(data) {
//       if (data.state != 'ok') {
//         $('#div_alert').showAlert({ message: data.result, level: 'danger' })
//         return
//       }else{
//         //$.hideLoading();
//         location.reload();
//       }
//     }
//   })
//   jeedom.eqLogic.save({
//     type: 'luna',
//     eqLogics: $("#lunaPanel").getValues('.eqLogicAttr'),
//     error: function(error) {
//       $('#div_alert').showAlert({message: error.message, level: 'danger'});
//     },
//     success: function() {

//     }
//   });
// });

$('#saveLuna').off('click').on('click', function() {
    if (window.intervalAlertId) {
      clearInterval(window.intervalAlertId);
  }
  window.intervalAlertId = setInterval(function() {
      $('#div_alert').showAlert({ message: 'Modifications en cours, veuillez patientez .....', level: 'success' });
  }, 6000);
  var intervalId = setInterval(function() {
    $.showLoading();
}, 1000);
  $("#priority").val($("#table_connexions").sortable('toArray'));

  var savePriorityPromise = new Promise(function(resolve, reject) {
    $.ajax({
      type: "POST",
      url: "plugins/luna/core/ajax/luna.ajax.php",
      data: {
        action: "savePriority",
        priority: $("#table_connexions").sortable('toArray')
      },
      dataType: 'json',
      async: true,
      global: false,
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
        reject(error);
      },
      success: function(data) {
        if (data.state != 'ok') {
          $('#div_alert').showAlert({ message: data.result, level: 'danger' });
          reject(data.result);
        } else {
          resolve();
        }
      }
    });
  });

  savePriorityPromise.then(function() {
    clearInterval(intervalId);
    clearInterval(intervalAlertId);
    $('#div_alert').showAlert({ message: 'Rechargement de la page en cours.....', level: 'success' });
    jeedom.eqLogic.save({
      type: 'luna',
      eqLogics: $("#lunaPanel").getValues('.eqLogicAttr'),
      error: function(error) {
        $('#div_alert').showAlert({message: error.message, level: 'danger'});
      },
      success: function() {
  
      }
    });
    location.reload();
  }).catch(function(error) {
    clearInterval(intervalId);
    clearInterval(intervalAlertId);
    console.error("Erreur lors de la sauvegarde de la priorité", error);
  });

});