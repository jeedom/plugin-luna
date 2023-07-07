<?php

?>
<div role="tabpanel" class="tab-pane" id="ethernettab"><br />
				<div class="row">
					<div class="col-sm-7">
						<form id="ethernetPanel" class="form-horizontal">
							<fieldset>
							<legend><i class="fas fa-network-wired"></i> {{Ethernet}}</legend>
							<input type="text" class="eqLogicAttr ethernet form-control" data-l1key="id" style="display : none;" />
							<div class="form-group" id='ethernetTypeAdressageBlock'>
									<label class="col-lg-3 control-label">{{Adressage IP}} :</label>
									<div class="col-lg-5">
										<select class="eqLogicAttr ethernet form-control ethernetTypeAdressage" id='ethernetTypeAdressage' data-l1key="configuration" data-l2key="ethernetTypeAdressage">
											<option id="etnernetdhcp" value="dhcp">{{DHCP}}</option>
											<option id="etnernetfixe" value="fixe">{{IP Fixe}}</option>
										</select>
									</div>
								</div>
								<div class="form-group" id='ethernetipBlock' style="display : none;">
									<label class="col-lg-3 control-label">{{Adresse IP}} :</label>
									<div class="col-lg-5">
										<input type="text" class="eqLogicAttr ethernet form-control ethernetip" data-l1key="configuration" data-l2key="ethernetip" placeholder="{{Adresse IP}}" />
									</div>
								</div>
								<div class="form-group" id='ethernetmaskBlock' style="display : none;">
									<label class="col-lg-3 control-label">{{Masque de sous-réseau}} :</label>
									<div class="col-lg-5">
										<input type="text" class="eqLogicAttr ethernet form-control ethernetmask" data-l1key="configuration" data-l2key="ethernetmask" placeholder="{{Masque}}" />
									</div>
								</div>
								<div class="form-group" id='ethernetrouterBlock' style="display : none;">
									<label class="col-lg-3 control-label">{{Routeur}} :</label>
									<div class="col-lg-5">
										<input type="text" class="eqLogicAttr ethernet form-control" data-l1key="configuration" data-l2key="ethernetrouter" placeholder="{{Routeur}}" />
									</div>
								</div>
								<div class="form-group" id='ethernetdnsBlock' style="display : none;">
									<label class="col-lg-3 control-label">{{Serveur DNS}} :</label>
									<div class="col-lg-5">
										<input type="text" class="eqLogicAttr ethernet form-control" data-l1key="configuration" data-l2key="ethernetdns" placeholder="{{DNS}}" />
									</div>
								</div>
								<div class="form-group" id='ethernetdnsOptBlock' style="display : none;">
									<label class="col-lg-3 control-label">{{Remplacer le DNS DHCP}} :</label>
									<div class="col-lg-5">
										<input type="text" class="eqLogicAttr ethernet form-control" data-l1key="configuration" data-l2key="ethernetdnsOpt" placeholder="{{DNS}}" />
									</div>
								</div>
							</fieldset>
						</form>
						<div class="col-sm-2"></div>
						<div class="col-sm-6" style="text-align: center;"><a class="btn btn-sm btn-success eqLogicAction" id="saveEthernet"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a></div>
					</div>
					<div class="col-sm-5">
						<form class="form-horizontal">
							<fieldset>
							
							</fieldset>
						</form>
					</div>
				</div>
			</div>


<?php include_file('desktop', 'ethernet', 'js', 'luna'); ?>