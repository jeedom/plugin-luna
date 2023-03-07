#!/bin/sh

#RC LOCAL
sudo chmod +x /etc/rc.local
sudo systemctl daemon-reload
sudo systemctl start rc-local

#battery_power_switch
sudo chmod+x /etc/rc.button/battery_power_switch

#batterie
sudo chmod 755 /usr/bin/batterySwitch
sudo chmod 644 /etc/systemd/system/batterySwitch.service
sudo systemctl enable --now batterySwitch.service
