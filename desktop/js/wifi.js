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

function printWifiList(forced = false, interface = 1) {
  console.log(interface)
  $.ajax({// fonction permettant de faire de l'ajax
    type: "POST", // methode de transmission des données au fichier php
    url: "plugins/luna/core/ajax/luna.ajax.php", // url du fichier php
    data: {
      action: "listWifi",
      mode: forced,
      interface: interface,
    },
    dataType: 'json',
    async: true,
    error: function (request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({ message: data.result, level: 'danger' })
        return
      }
      var options = ''
      for (i in data.result) {
        options += '<option value="' + i + '">'
        options += data.result[i]['ssid'] + ' - Signal : ' + data.result[i]['signal'] + ' Canal : ' + data.result[i]['channel'] + ' Sécurité - ' + data.result[i]['security']
        options += '</option>'
      }
      $('.eqLogicAttr[data-l1key=configuration][data-l2key=wifi' + interface + 'Ssid]').empty().html(options)
    }
  })
}

function saveWifi(interface, datas) {
  console.log(datas)
  $.ajax({
    type: "POST",
    url: "plugins/luna/core/ajax/luna.ajax.php",
    data: {
      action: "saveWifi",
      interface: interface,
      data: datas
    },
    dataType: 'json',
    async: true,
    global: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function (data) {
      printConnectionsList(interface)
      if (data.state != 'ok') {
        //$('#div_alert').showAlert({ message: "suppression SSID : "+ssid, level: 'success' })
        return
      }
    }
  })
}

function printConnectionsList(interface = 1) {
  deviceName = interface - 1
  $.ajax({// fonction permettant de faire de l'ajax
    type: "POST", // methode de transmission des données au fichier php
    url: "plugins/luna/core/ajax/luna.ajax.php", // url du fichier php
    data: {
      action: "listConnections",
    },
    dataType: 'json',
    async: true,
    error: function (request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({ message: data.result, level: 'danger' })
        return
      }
      var table = ''
      for (i in data.result) {
        if (data.result[i]['device'] == 'wlan' + deviceName) {
          table += '<tr><td>' + data.result[i]['name'] + '</td>'
          if (data.result[i]['active'] == 'yes') {
            table += '<td>{{Connecté}}</td>'
          } else {
            table += '<td>{{Non connecté}}</td>'
          }
          table += '<td><i class="fa fa-minus-circle pull-right cursor removeConnection" id=' + data.result[i]['UUID'] + ' data-action="remove"></i></td>'
          table += '</tr>'
        }

      }
      $('#table_wifi' + interface + 'profile tbody').empty().append(table)
      $('.removeConnection').off('click').on('click', function () {
        console.log(this)
        removeConnection(this.id, interface)
      })
    }
  })
}

function removeConnection(UUID, interface) {
  $.ajax({// fonction permettant de faire de l'ajax
    type: "POST", // methode de transmission des données au fichier php
    url: "plugins/luna/core/ajax/luna.ajax.php", // url du fichier php
    data: {
      action: "removeConnection",
      UUID: UUID,
    },
    dataType: 'json',
    async: true,
    global: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({ message: data.result, level: 'danger' })
        return
      }
      printConnectionsList(interface)

    }
  })
}

$('.bt_refreshWifiList').on('click', function () {
  var wifiID = this.id.match(/\d+/)[0];
  console.log(wifiID)
  printWifiList(true, wifiID)
})

$('.wifiEnabledCheck').change(function () {
  var wifiID = this.id.match(/\d+/)[0];
  console.log('activer/desactiver wifi')
  if (this.checked == true) {
    $('#wifi' + wifiID + 'ModeBlock').css('display', 'block')
    const e = new Event("change");
    const element = document.querySelector('.wifiMode')
    element.dispatchEvent(e);
  } else {
    $('#wifi' + wifiID + 'ModeBlock').css('display', 'none')
    $('#wifi' + wifiID + 'ipBlock').css('display', 'none')
    $('#wifi' + wifiID + 'maskBlock').css('display', 'none')
    $('#wifi' + wifiID + 'routerBlock').css('display', 'none')
    $('#wifi' + wifiID + 'dnsBlock').css('display', 'none')
    $('#wifi' + wifiID + 'dnsOptBlock').css('display', 'none')
    $('#wifi' + wifiID + 'hotspotnameBlock').css('display', 'none')
    $('#wifi' + wifiID + 'hotspotpwdBlock').css('display', 'none')
    $('#wifi' + wifiID + 'hotspotdhcpBlock').css('display', 'none')
    $('#wifi' + wifiID + 'hotspotipBlock').css('display', 'none')
    $('#wifi' + wifiID + 'hotspotmaskBlock').css('display', 'none')
    $('#wifi' + wifiID + 'hotspotrouterBlock').css('display', 'none')
    $('#wifi' + wifiID + 'hotspotdnsBlock').css('display', 'none')
    $('#wifi' + wifiID + 'TypeAdressageBlock').css('display', 'none')
    $('#wifi' + wifiID + 'SsidBlock').css('display', 'none')
    $('#wifi' + wifiID + 'PasswordBlock').css('display', 'none')
    $('#table_wifi' + wifiID + 'profile').css('display', 'none')
  }
})

$('.wifiMode').change(function () {
  var wifiID = this.id.match(/\d+/)[0];
  if (document.getElementById('wifi' + wifiID + 'EnabledCheck').checked == true) {
    console.log('activer')
    if (this.value == "client") {
      $('#wifi' + wifiID + 'TypeAdressageBlock').css('display', 'block')
      $('#table_wifi' + wifiID + 'profile').css('display', 'table')
      $('#wifi' + wifiID + 'SsidBlock').css('display', 'block')
      $('#wifi' + wifiID + 'PasswordBlock').css('display', 'block')
      $('#wifi' + wifiID + 'hotspotnameBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotpwdBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotdhcpBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotipBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotmaskBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotrouterBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotdnsBlock').css('display', 'none')
      $('#wifi' + wifiID + 'TypeAdressageBlock').css('display', 'none')
      $('#wifi' + wifiID + 'dnsOptBlock').css('display', 'none')
      printConnectionsList(wifiID)
      const e = new Event("change");
      const element = document.querySelector('.wifiTypeAdressage')
      element.dispatchEvent(e);
    } else if (this.value == "hotspot") {
      $('#wifi' + wifiID + 'TypeAdressageBlock').css('display', 'none')
      $('#table_wifi' + wifiID + 'profile').css('display', 'none')
      $('#wifi' + wifiID + 'SsidBlock').css('display', 'none')
      $('#wifi' + wifiID + 'PasswordBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotnameBlock').css('display', 'block')
      $('#wifi' + wifiID + 'hotspotpwdBlock').css('display', 'block')
      $('#wifi' + wifiID + 'hotspotdhcpBlock').css('display', 'block')
      $('#wifi' + wifiID + 'hotspotipBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotmaskBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotrouterBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotdnsBlock').css('display', 'none')
      $('#wifi' + wifiID + 'TypeAdressageBlock').css('display', 'none')
      $('#wifi' + wifiID + 'ipBlock').css('display', 'none')
      $('#wifi' + wifiID + 'maskBlock').css('display', 'none')
      $('#wifi' + wifiID + 'routerBlock').css('display', 'none')
      $('#wifi' + wifiID + 'dnsBlock').css('display', 'none')
      $('#wifi' + wifiID + 'dnsOptBlock').css('display', 'none')
    } else {
      $('#wifi' + wifiID + 'TypeAdressageBlock').css('display', 'none')
      $('#table_wifi' + wifiID + 'profile').css('display', 'none')
      $('#wifi' + wifiID + 'SsidBlock').css('display', 'none')
      $('#wifi' + wifiID + 'PasswordBlock').css('display', 'none')
      $('#wifi' + wifiID + 'dnsOptBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotnameBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotpwdBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotdhcpBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotipBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotmaskBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotrouterBlock').css('display', 'none')
      $('#wifi' + wifiID + 'hotspotdnsBlock').css('display', 'none')

      $('#hotspotEnabledCheck').prop('checked', false)
      $('.wifihotspot').css('display', 'none')
      $('.nohotspot').prop('disabled', false)
      $('#dnsDesactivated').prop('selected', true)
    }
  }
})


$('.wifiTypeAdressage').change(function () {
  var wifiID = this.id.match(/\d+/)[0];
  if (document.getElementById('wifi' + wifiID + 'EnabledCheck').checked == true) {
    if (document.getElementById('wifi' + wifiID + 'Mode').value == "client") {
      if (this.value == "fixe") {
        $('#wifi' + wifiID + 'ipBlock').css('display', 'block')
        $('#wifi' + wifiID + 'maskBlock').css('display', 'block')
        $('#wifi' + wifiID + 'routerBlock').css('display', 'block')
        $('#wifi' + wifiID + 'dnsBlock').css('display', 'block')
        $('#wifi' + wifiID + 'dnsOptBlock').css('display', 'none')
      } else {
        $('#wifi' + wifiID + 'ipBlock').css('display', 'none')
        $('#wifi' + wifiID + 'maskBlock').css('display', 'none')
        $('#wifi' + wifiID + 'routerBlock').css('display', 'none')
        $('#wifi' + wifiID + 'dnsBlock').css('display', 'none')
        $('#wifi' + wifiID + 'dnsOptBlock').css('display', 'block')
      }
    }
  }
})

$('.wifiTypeDHCPHotspot').change(function () {
  var wifiID = this.id.match(/\d+/)[0];
  if (this.checked == true) {
    $('#wifi' + wifiID + 'hotspotipBlock').css('display', 'block')
    $('#wifi' + wifiID + 'hotspotmaskBlock').css('display', 'block')
    $('#wifi' + wifiID + 'hotspotrouterBlock').css('display', 'block')
    $('#wifi' + wifiID + 'hotspotdnsBlock').css('display', 'block')
  }else{
    $('#wifi' + wifiID + 'hotspotipBlock').css('display', 'none')
    $('#wifi' + wifiID + 'hotspotmaskBlock').css('display', 'none')
    $('#wifi' + wifiID + 'hotspotrouterBlock').css('display', 'none')
    $('#wifi' + wifiID + 'hotspotdnsBlock').css('display', 'none')
  }
})

$('#hotspotEnabledCheck').change(function () {
  if (this.checked == true) {
    $('.wifihotspot').css('display', 'block')
    $('.nohotspot').prop('disabled', true)
    $('#dnsWlan0').prop('disabled', false)
    $('#dnsEth0').prop('disabled', true)
    $("#dnsSelect option:selected").each(function () {
      if ($(this).val() == 'eth0' || $(this).val() == 'desactivated') {
        $('#dnsWlan0').prop('selected', true)
      }
    })
  } else {
    $('.wifihotspot').css('display', 'none')
    $('.nohotspot').prop('disabled', false)
    $('#dnsWlan0').prop('disabled', true)
    $('#dnsEth0').prop('disabled', false)
    $("#dnsSelect option:selected").each(function () {
      if ($(this).val() == 'wlan0') {
        $('#dnsDesactivated').prop('selected', true)
      }
    })
  }
})

$('#saveWifi1').off('click').on('click', function () {
  console.log('saveWifi1')
  console.log($('.wifi'))
  jeedom.eqLogic.save({
    type: 'luna',
    eqLogics: $("#wifi1Panel").getValues('.eqLogicAttr'),
    error: function (error) {
      $('#div_alert').showAlert({ message: error.message, level: 'danger' });
    },
    success: function () {
      console.log('savewifi1ok', $("#wifi1Panel").getValues('.eqLogicAttr'))
    }
  });

  /*if (document.getElementById('wifi1EnabledCheck').checked == false) {
    hideAllChamps();
  }*/

  //var ssid = $('.eqLogicAttr[data-l1key=configuration][data-l2key=wifiSsid]').options[liste.selectedIndex].text
  saveWifi(1, $("#wifi1Panel").getValues('.eqLogicAttr'))

})

// $('#saveWifi2').off('click').on('click', function () {
//   console.log('saveWifi2')
//   console.log($("#wifi2Panel").getValues('.eqLogicAttr'))
//   console.log('saveWifi20')
//   jeedom.eqLogic.save({
//     type: 'luna',
//     eqLogics: $("#wifi2Panel").getValues('.eqLogicAttr'),
//     error: function (error) {
//       $('#div_alert').showAlert({ message: error.message, level: 'danger' });
//     },
//     success: function () {
//       console.log('savewifi2ok', $("#wifi2Panel").getValues('.eqLogicAttr'))
//     }
//   });
//   saveWifi(2, $("#wifi2Panel").getValues('.eqLogicAttr'))

// })

/*function hideAllChamps(wifiID) {
  console.log('#wifi'+wifiID+'TypeAdressageBlock')
  $('#wifi'+wifiID+'TypeAdressageBlock').css('display', 'none')
  $('#wifi'+wifiID+'dnsOptBlock').css('display', 'none')
  $('#table_wifi'+wifiID+'profile').css('display', 'none')
  $('#wifi'+wifiID+'SsidBlock').css('display', 'none')
  $('#wifi'+wifiID+'PasswordBlock').css('display', 'none')
  $('#wifi'+wifiID+'hotspotnameBlock').css('display', 'block')
  $('#wifi'+wifiID+'hotspotpwdBlock').css('display', 'block')
  $('#wifi'+wifiID+'ipBlock').css('display', 'none')
  $('#wifi'+wifiID+'maskBlock').css('display', 'none')
  $('#wifi'+wifiID+'routerBlock').css('display', 'none')
  $('#wifi'+wifiID+'dnsBlock').css('display', 'none')
  $('#wifi'+wifiID+'dnsOptBlock').css('display', 'none')
}*/

/*$(document).ready(function () {
  if (document.getElementById('wifi1EnabledCheck').checked == false) {
    console.log('hide 1')
    hideAllChamps(1);
  }
  if (document.getElementById('wifi2EnabledCheck').checked == false) {
    hideAllChamps(2);
  }
});*/