
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

function pourcentageBattery(pourcentage) {
  $(".NiMH").css("height", 220 * pourcentage / 100);
  setTimeout(function(){
    document.getElementById("r1").checked = false;
    document.getElementById("r3").checked = false;
    document.getElementById("r4").checked = false;
    document.getElementById("r5").checked = false;
    document.getElementById("r6").checked = false;
    document.getElementById("r7").checked = false;
    if(pourcentage == 0){
      console.log("0");
      document.getElementById("r3").checked = true;
      return;
    }else if(pourcentage < 20){
      console.log("20");
      document.getElementById("r4").checked = true;
      return;
    }else if(pourcentage < 40){
      console.log("40");
      document.getElementById("r5").checked = true;
      return;
    }else if(pourcentage < 60){
      console.log("60");
      document.getElementById("r6").checked = true;
      return;
    }else if(pourcentage <= 100){
      console.log("100");
      document.getElementById("r7").checked = true;
      return;
    }
  }, 1000);
}
