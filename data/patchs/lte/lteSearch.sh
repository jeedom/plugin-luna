#!/bin/bash

LOGFILE=/var/www/html/log/luna_lteSearch

finddev() {
	cfgpath=`find  /sys/devices/platform/ -name "ttyUSB*" | grep "2-1\.1\/" | grep "2-1\.1:1\.2" | grep -v "tty\/"`
	dev="$(basename $cfgpath 2>/dev/null)"
	echo $dev
}


_4g_on() {
	echo $FUNCNAME >> $LOGFILE

	dev=$(finddev)
	local yyy=`echo $dev | grep ttyUSB | wc -l`
	if [ "$yyy" = "1" ]; then
		return 0
	fi

	while [ ! "$yyy" == "1" ]; do
		echo 0 > /sys/class/leds/ltepwr/brightness
		echo 0 > /sys/class/leds/lteldo/brightness
		echo 0 > /sys/class/leds/lterst/brightness
		sleep 1
		echo 1 > /sys/class/leds/ltepwr/brightness
		echo 1 > /sys/class/leds/lteldo/brightness
		echo 1 > /sys/class/leds/lterst/brightness


		sleep 15

		dev=$(finddev)
		yyy=`echo $dev | grep ttyUSB | wc -l`
	done

	return 0
}

_4g_on