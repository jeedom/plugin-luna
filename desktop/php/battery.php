<?php
if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
$batteryPourcentage = luna::batteryPourcentage();
$tempBattery = luna::batteryTemp();
$status = luna::batteryStatusLuna();
$powerAVG = luna::batteryPowerAvg();

if($status != 'Discharging'){
  $batteryActivated = '{{SECTEUR}}';
}else{
  $batteryActivated = '{{BATTERIE}}';
}

if($status == "Charging"){
  $iconStatus = '<i class="icon_green fas fa-bolt"></i>';
}else{
  $iconStatus = '<i class="icon_red fas fa-bolt"></i>';
}
?>
<div role="tabpanel" class="tab-pane" id="batterytab"><br />
  <fieldset>
	  <legend><i class="fa fa-battery-full"></i> {{Batterie}}</legend>
    <div class="row">
      <div class="col-sm-2">
        <div class="Cylindre">
          <div class="NiMH" id="NiMH"></div>
          <div class="PPole"></div>
        </div>
        <input type="radio" class="cath_NA" id="r1" name="r" style="visibility:hidden" />
        <input type="radio" class="cath_2"  id="r3" name="r" style="visibility:hidden" />
        <input type="radio" class="cath_4"  id="r4" name="r" style="visibility:hidden" />
        <input type="radio" class="cath_6"  id="r5" name="r" style="visibility:hidden" />
        <input type="radio" class="cath_8"  id="r6" name="r" style="visibility:hidden" />
        <input type="radio" class="cath_10" id="r7" name="r" style="visibility:hidden" />
      </div>
      <div class="col-sm-5">
        <h3> <b>{{Batterie}}</b> : <?php echo $batteryPourcentage ?> % </h3><br />
        <h4> <b>{{Température}}</b> : <?php echo $tempBattery ?> °C </h4>
        <h4> <b>{{Status}}</b> : <?php echo $status; ?> <?php echo $iconStatus;?></h4>
        <h4> <b>{{Alimentation actuelle de la Luna}}</b> : <?php echo $batteryActivated ?> </h4>
    </div>
    </div>
  </fieldset> 
</div>

<?php include_file('desktop', 'battery', 'js', 'luna'); ?>
<script>
  pourcentageBattery(<?php echo $batteryPourcentage ?>);
</script>

<style>
.Cylindre {  
  font-family: Lato, sans-serif;
  font-weight: 300;
  letter-spacing: 0.025rem;
  font-size: 12px;
  color: gray;
  overflow: hidden;
  position: relative;
  width: 120px;
  height: 230px;
  margin: 20px auto;
  box-shadow: 0 0 0 1px rgba(128,128,128, 0.15) inset;
  background: linear-gradient(90deg, rgba(128,128,128, 0.2) 0%, rgba(128,128,128, 0.2) 20%, rgba(128,128,128, 0.2) 20%, transparent 50%, transparent 75%, rgba(128,128,128, 0.2) 90%);
  border-radius: 60px/30px;
  /*background-color: rgba(120,120,120,.5);*/
  /*background-image: linear-gradient(90deg,  rgba(0,0,0,.3) 0%, 
		                transparent 25%,rgba(0,0,0,.3) 50%);*/
  /*box-shadow: 0 0 0 1px rgba(255, 255, 255, .2) inset, 
		          0 1px 5px 1px rgba(0, 0, 0, .45) inset;*/
}

/*positive Poles*/
.PPole {
  position: absolute;
  left: 0;
  right: 0;
  top: -1px;
  width: 99%;
  height: 57px;
  border: 1px solid rgba(128,128,128, 0.15);
  border-radius: 60px/30px;
  background: linear-gradient( rgba(128,128,128, 0.25) 0%, rgba(128,128,128, 0.15) 20%, rgba(128,128,128, 0.15) 20%, transparent 50%, transparent 75%, rgba(128,128,128, 0.15) 90%);
  /*background-color: rgba(120,120,120,.2);*/
  /*box-shadow: 0 0 0 1px rgba(0,0,0,1) ;*/
}
.PPole:after {
  content: '';
  position: absolute;
  width: 36%;
  left: 31.5%;
  top: 1px;
  height: 30px;
  border-radius: 60px/27.5px;
  box-shadow: 0 0 0 1px rgba(255,255,255,.35);
  background: linear-gradient(90deg, rgba(128,128,128, 1) 0%, rgba(128,128,128, 0.3) 30%, transparent 50%, transparent 75%, rgba(128,128,128, 1) 90%);
}
.PPole:before {
  content: '';
  position: absolute;
  left: 8%;
  right: 0;
  top: 8%;
  width: 84%;
  height: 73%;
  border-radius: 55px/24px;
  background-color: rgba(128,128,128,.5);
  /*background-color: rgba(120,120,120,.2);*/
  box-shadow: 0 1px 0 1px rgba(255,255,255,.35);
}
.Cylindre:after {
  content: '';
  position: absolute;
  width: 32%;
  left: 33%;
  top: 1px;
  height: 6%;
  border:1px solid rgba(255,255,255,.25);
  border-radius: 60px/25px;
  background-color: rgba(128,128,128,.6);
  box-shadow: 0 0 0 1px rgba(128,128,128,.6);
}
.Cylindre:before {
  content: '';
  position: absolute;
  width: 92%;
  top: 78%;
  bottom: 0;
  height: 20%;
  left: 5px;
  right: 5px;
  border-radius: 60px/25px;

  background-color: rgba(128,128,128,.5);
  box-shadow: 0 0 0 1px rgba(255,255,255,.35);
}


/*Liquid*/
.NiMH {
  position: absolute;
  left: 5px;
  right: 5px;
  bottom: 5px;
  height: 0;
  padding-top: 30px;
  border-radius: 54px/24px;
  background: linear-gradient(90deg, rgba(0, 204, 0,.6) 0%, rgba(0, 170, 0, .6) 50%, rgba(0, 204, 0,.6) 100%);
  box-shadow: 0 0 10px #00cc00;
  transition: 0.5s linear;
}
.NiMH:after {
  content: '';
  position: absolute;
  left: .5%;
  right: 0;
  top: 0;
  width: 99%;
  height: 50px;
  border-radius: 54px/25px;
  background-color: rgba(0, 170, 0, .35); /*(only dealer)*/
  box-shadow: 0 0 10px #00cc00 inset;
  /*background-color: rgba(255,232,63,.9);
	box-shadow: 0 0 0 1px rgba(255, 255, 255, .2) inset, 
		          0 1px 5px 1px rgba(0, 0, 0, .45) inset;*/
}

/*Empty - 0% - 20% - (Red)*/
.cath_NA:checked ~ .Cylindre .NiMH {
  height: 12px;
  background: linear-gradient(90deg, rgba(255, 0, 0,.6) 0%, rgba(195, 0, 0,.6) 50%, rgba(255, 0, 0,.6) 100%);
  box-shadow: 0 0 10px #ff0000;
  transform: scale(0);
  -webkit-transform: scale(0);
  -o-transform: scale(0);
  -moz-transform: scale(0);
}
.cath_NA:checked  ~ .Cylindre .NiMH:after {
  background-color: rgba(195, 0, 0,.35) ;
  box-shadow: 0 0 10px #ff0000 inset;
}
.cath_0:checked  ~ .Cylindre .NiMH {
  background: linear-gradient(90deg, rgba(255, 0, 0,.8) 0%, rgba(195, 0, 0,.6) 50%, rgba(255, 0, 0,.8) 100%);
  box-shadow: 0 0 10px #ff0000;
  height: 18.55px;
}
.cath_0:checked  ~ .Cylindre .NiMH:after {
  background-color: rgba(195, 0, 0,.35) ;
  box-shadow: 0 0 10px #ff0000 inset;
}
.cath_2:checked  ~ .Cylindre .NiMH {
  background: linear-gradient(90deg, rgba(255, 0, 0,.8) 0%, rgba(195, 0, 0,.6) 50%, rgba(255, 0, 0,.8) 100%);
  box-shadow: 0 0 10px #ff0000;
}
.cath_2:checked  ~ .Cylindre .NiMH:after {
  background-color: rgba(195, 0, 0,.35) ;
  box-shadow: 0 0 10px #ff0000 inset;
}

 /*40% - 60% - (Yellow)*/
.cath_4:checked  ~ .Cylindre .NiMH {
  background: linear-gradient(90deg, rgba(255, 204, 0,.7) 0%, rgba(204, 163, 0,.6) 50%, rgba(255, 204, 0,.7) 100%);
  box-shadow: 0 0 10px #ffcc00;
}
.cath_4:checked  ~ .Cylindre .NiMH:after {
  background-color: rgba(204, 163, 0,.35);
  box-shadow: 0 0 10px #ffcc00 inset;
}
.cath_6:checked  ~ .Cylindre .NiMH {
  background: linear-gradient(90deg, rgba(255, 204, 0,.7) 0%, rgba(204, 163, 0,.6) 50%, rgba(255, 204, 0,.7) 100%);
  box-shadow: 0 0 10px #ffcc00;
}
.cath_6:checked  ~ .Cylindre .NiMH:after {
  background-color: rgba(204, 163, 0,.35);
  box-shadow: 0 0 10px #ffcc00 inset;
}

</style>