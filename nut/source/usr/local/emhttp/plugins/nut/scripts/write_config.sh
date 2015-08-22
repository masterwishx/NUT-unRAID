#!/bin/bash
#
# NUT plugin configuration script for unRAID
# By macester macecapri@gmail.com
#

# Killpower flag permissions
chmod 777 /etc/ups/flag

# Add nut user and group i for udev at shutdown
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


# Edit nut config files with values from plugin settings
NUTCFG=/boot/config/plugins/nut/nut.cfg




driver_custom=$( grep -ic 'DRIVER="custom"' $NUTCFG )
if [ $driver_custom -eq 1 ]; then
        var1=$( grep -i "SERIAL=" $NUTCFG|cut -d \" -f2|sed 's/^/driver = /' )
        sed -i "2 s/.*/$var1/" /etc/ups/ups.conf
else
        var3=$( grep -i "DRIVER=" $NUTCFG|cut -d \" -f2|sed 's/^/driver = /' )
        sed -i "2 s/.*/$var3/" /etc/ups/ups.conf
fi

var6=$( grep -i "PORT=" $NUTCFG|cut -d \" -f2|sed 's/^/port = /' )
sed -i "3 s/.*/$var6/" /etc/ups/ups.conf

var8=$( grep -i "MODE=" $NUTCFG|cut -d \" -f2|sed 's/^/MODE=/' )
sed -i "1 s/.*/$var8/" /etc/ups/nut.conf

var14=$( grep -i "TIMER=" $NUTCFG|cut -d \" -f2|sed 's/^/COUNT_DOWN=/' )
sed -i "6 s/.*/$var14/" /etc/ups/notifycmd

# Set ups poweroff
ups_kill=$( grep -ic 'UPSKILL="enable"' $NUTCFG )
if [ $ups_kill -eq 1 ]; then
    var16='POWERDOWNFLAG /etc/ups/flag/killpower'
    sed -i "3 s,.*,$var16," /etc/ups/upsmon.conf
else
    var18='POWERDOWNFLAG /etc/ups/flag/no_killpower'
    sed -i "3 s,.*,$var18," /etc/ups/upsmon.conf
fi


# Link shutdown scripts for poweroff in rc.0 and rc.6
UDEV=$( grep -ic "/usr/bin/nut_restart_udev" /etc/rc.d/rc.6 )
if [ $UDEV -ge 1 ]; then
    echo "UDEV lines already exist in rc.0,6"
else
    sed -i '/\/bin\/mount -v -n -o remount,ro \//r /usr/local/emhttp/plugins/nut/scripts/txt/udev.txt' /etc/rc.d/rc.6
fi

KILL=$( grep -ic "/usr/bin/nut_kill_inverter" /etc/rc.d/rc.6 )
if [ $KILL -ge 1 ]; then
    echo "KILL_INVERTER lines already exist in rc.0,6"
else
     sed -i -e '/# Now halt (poweroff with APM or ACPI enabled kernels) or reboot./r /usr/local/emhttp/plugins/nut/scripts/txt/kill.txt' -e //N /etc/rc.d/rc.6
fi
