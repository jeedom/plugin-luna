
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

function saveEthernet(datas){
    console.log(datas)
    $.ajax({
      type: "POST", 
      url: "plugins/luna/core/ajax/luna.ajax.php", 
      data: {
        action: "saveEthernet",
        data: datas
      },
      dataType: 'json',
      async: true,
      global: false,
      error: function(request, status, error) {
        handleAjaxError(request, status, error)
      },
      success: function(data) {
        if (data.state != 'ok') {
          //$('#div_alert').showAlert({ message: "suppression SSID : "+ssid, level: 'success' })
          return
        }
      }
    })
  }

$('.ethernetTypeAdressage').change(function() {
   if (this.value == "fixe") {
     $('#ethernetipBlock').css('display', 'block')
     $('#ethernetmaskBlock').css('display', 'block')
     $('#ethernetrouterBlock').css('display', 'block')
     $('#ethernetdnsBlock').css('display', 'block')
     $('#ethernetdnsOptBlock').css('display', 'none')
   } else {
    $('#ethernetipBlock').css('display', 'none')
    $('#ethernetmaskBlock').css('display', 'none')
    $('#ethernetrouterBlock').css('display', 'none')
    $('#ethernetdnsBlock').css('display', 'none')
    $('#ethernetdnsOptBlock').css('display', 'block')
   }
  })

  $('#saveEthernet').off('click').on('click', function() {
    console.log($('.ethernet'))
    jeedom.eqLogic.save({
      type: 'luna',
      eqLogics: $("#ethernetPanel").getValues('.eqLogicAttr'),
      error: function(error) {
        $('#div_alert').showAlert({message: error.message, level: 'danger'});
      },
      success: function() {
       console.log('saveethernetok',$("#ethernetPanel").getValues('.eqLogicAttr'))
      }
    });
    //var ssid = $('.eqLogicAttr[data-l1key=configuration][data-l2key=wifiSsid]').options[liste.selectedIndex].text
  saveEthernet($("#ethernetPanel").getValues('.eqLogicAttr'))

  })