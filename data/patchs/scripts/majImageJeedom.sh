sudo mount /dev/mmcblk1p9 /userdata
sudo wget https://images.jeedom.com/luna/update.img -O /userdata/update.img
sudo /usr/bin/updateEngine --image_url=/userdata/update.img --savepath=/userdata/update.img --misc=update --partition=0x3B00 --reboot
