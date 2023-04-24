#!/bin/sh

#RC LOCAL
php /var/www/html/core/php/jeecli.php message add "luna" "correction dmesg"
sudo chmod +x /etc/rc.local
sudo systemctl daemon-reload
sudo systemctl start rc-local
sudo udevadm control --reload-rules
sudo udevadm trigger

#battery_power_switch
php /var/www/html/core/php/jeecli.php message add "luna" "Add new command for switch power"
sudo chmod +x /etc/rc.button/battery_power_switch

#batterie
php /var/www/html/core/php/jeecli.php message add "luna" "Add battery switch"
sudo chmod 755 /usr/bin/batterySwitch
sudo chmod 644 /etc/systemd/system/batterySwitch.service
sudo systemctl enable --now batterySwitch.service

sudo chmod +x /usr/bin/bg96

#done
php /var/www/html/core/php/jeecli.php message add "luna" "correction Up Start Led"
sudo chmod 755 /etc/init.d/done
sudo systemctl restart done

return 0