sudo wget https://github.com/jeedom/plugin-luna/raw/beta/data/patchs/root/usr/bin/fsreset -O /userdata/fsreset
sudo cp /userdata/fsreset /usr/bin/fsreset
sudo fsreset
sudo reboot