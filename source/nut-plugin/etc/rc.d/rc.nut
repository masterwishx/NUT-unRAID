#!/bin/sh
# Slackware startup script for Network UPS Tools
# Copyright 2010 V'yacheslav Stetskevych
# Edited for unRAID by macester macecapri@gmail.com
# Revised by Derek Macias 2017.05.01
#

PROG="nut"
PLGPATH="/boot/config/plugins/$PROG"
CONFIG=$PLGPATH/$PROG.cfg
DPATH=/usr/bin
export PATH=$DPATH:$PATH

# read our configuration
[ -e "$CONFIG" ] && source $CONFIG


start_driver() {
       /usr/sbin/upsdrvctl -u root start || exit 1
}

start_upsd() {
        /usr/sbin/upsd -u root || exit 1
}

start_upsmon() {
        /usr/sbin/upsmon -u root || exit 1
}

stop() {
        echo "Stopping the UPS services... "
        if pgrep upsd 2>&1 >/dev/null; then
                /usr/sbin/upsd -c stop; fi
        if pgrep upsmon 2>&1 >/dev/null; then
                /usr/sbin/upsmon -c stop; fi
        /usr/sbin/upsdrvctl stop
        sleep 2
        if [ -f /var/run/upsmon.pid ]; then
                  rm /var/run/upsmon.pid; fi
}

write_config() {
# Killpower flag permissions
#[ -e /etc/ups/flag ] && chmod 777 /etc/ups/flag

# Add nut user and group for udev at shutdown
GROUP=$( grep -ic "218" /etc/group )
USER=$( grep -ic "218" /etc/passwd )

if [ $GROUP -ge 1 ]; then
    echo "NUT Group already configured"
else
    groupadd -g 218 nut
fi

if [ $USER -ge 1 ]; then
    echo "NUT User already configured"
else
    useradd -u 218 -g nut -s /bin/false nut
fi

if [ $MANUAL == "disable" ]; then

    # Add the driver config
    if [ $DRIVER == "custom" ]; then
            sed -i "2 s/.*/driver = ${SERIAL}/" /etc/ups/ups.conf
    else
            sed -i "2 s/.*/driver = ${DRIVER}/" /etc/ups/ups.conf
    fi

    # add the port
    sed -i "3 s~.*~port = ${PORT}~" /etc/ups/ups.conf

    # add mode standalone/netserver
    sed -i "1 s/.*/MODE=${MODE}/" /etc/ups/nut.conf

    # Add SNMP-specific config
    if [ $DRIVER == "snmp-ups" ]; then
        var10="pollfreq = ${POLL}"
        var11="community = ${COMMUNITY}"
        var12='snmp_version = v2c'
    else
        var10=''
        var11=''
        var12=''
    fi

    sed -i "4 s/.*/$var10/" /etc/ups/ups.conf
    sed -i "5 s/.*/$var11/" /etc/ups/ups.conf
    sed -i "6 s/.*/$var12/" /etc/ups/ups.conf

    # Set which shutdown script NUT should use
    sed -i "2 s,.*,SHUTDOWNCMD \"/sbin/poweroff\"," /etc/ups/upsmon.conf

    # Set which notification script NUT should use
    sed -i "6 s,.*,NOTIFYCMD \"/usr/sbin/nut-notify\"," /etc/ups/upsmon.conf

    # Set if the ups should be turned off
    if [ $UPSKILL == "enable" ]; then
        var8='POWERDOWNFLAG /etc/ups/flag/killpower'
        sed -i "3 s,.*,$var8," /etc/ups/upsmon.conf
    else
        var9='POWERDOWNFLAG /etc/ups/flag/no_killpower'
        sed -i "3 s,.*,$var9," /etc/ups/upsmon.conf
    fi

    # Link shutdown scripts for poweroff in rc.0 and rc.6
    UDEV=$( grep -ic "/etc/rc.d/rc.nut restart_udev" /etc/rc.d/rc.6 )
    if [ $UDEV -ge 1 ]; then
        echo "UDEV lines already exist in rc.0,6"
    else
        sed -i '/\/bin\/mount -v -n -o remount,ro \//r [ -x /etc/rc.d/rc.nut ] && /etc/rc.d/rc.nut restart_udev' /etc/rc.d/rc.6
    fi
fi

KILL=$( grep -ic "/etc/rc.d/rc.nut shutdown" /etc/rc.d/rc.6 )
if [ $KILL -ge 1 ]; then
    echo "KILL_INVERTER lines already exist in rc.0,6"
else
     sed -i -e '/# Now halt (poweroff with APM or ACPI enabled kernels) or reboot./r [ -x /etc/rc.d/rc.nut ] && /etc/rc.d/rc.nut shutdown' -e //N /etc/rc.d/rc.6
fi

}

case "$1" in
        shutdown) # shuts down the UPS
                echo "Killing inverter..."
                /usr/sbin/upsdrvctl shutdown
                ;;
        start)  # starts everything (for a ups server box)
               sleep 1
               write_config
               sleep 3
               start_driver
               start_upsd
               start_upsmon
                ;;
        start_upsmon) # starts upsmon only (for a ups client box)
                start_upsmon
                ;;
        stop) # stops all UPS-related daemons
               sleep 1
               write_config
               sleep 3
                stop
                ;;
        reload)
                write_config
                /usr/sbin/upsd -c reload
                /usr/sbin/upsmon -c reload
                ;;
        restart_udev)
                echo "Restarting udev to be able to shut the UPS inverter off..."
                /etc/rc.d/rc.udev start
                sleep 10
                ;;
        write_config)
                write_config
                ;;
        *)
                echo "Usage: $0 {start|start_upsmon|stop|shutdown|reload|restart|write_config}"
esac
