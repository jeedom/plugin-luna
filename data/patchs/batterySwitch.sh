#!/bin/sh

### BEGIN INIT INFO
# Provides:          batterySwitch
# Required-Start:
# Required-Stop:     $remote_fs $syslog
# Should-Stop:       $remote_fs $syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: Switch Battery connexion.
### END INIT INFO

# Note: this init script and related code is only useful if you
# run a sysvinit system,

led_blink() {
  echo timer > /sys/class/leds/$1/trigger
  echo 100 > /sys/class/leds/$1/delay_off
  echo 100 > /sys/class/leds/$1/delay_on
  echo 0,$2,0 > /sys/class/leds/$1/rgb_value
}

batterySwitch()
{
case "$1" in
     start|restart|reload|force-reload)
         i2cset -f -y 0 0x6a 0x09 0x44
         return 0
         ;;
     stop)
         led_blink led0 1
         led_blink led1 1
         led_blink led2 1
         led_blink led3 1
         led_blink led4 1
         led_blink led5 1
         sleep 2
         i2cset -f -y 0 0x6a 0x09 0x20
         return 0
         ;;
     show)
        STATUS_BATTERY = i2cget -f -y 0 0x6a 0x09
        if STATUS_BATTERY == 0x44 then
            echo "Activated"
        else
            echo "Desactivated"
        fi
        ;;
     *)
         echo "Usage: batterySwitch.sh {stop|reload|force-reload|show}"
         return 1
         ;;
 esac

}
batterySwitch "$@"