#!/bin/sh
BOOT="/boot/config/plugins/nut"
DOCROOT="/usr/local/emhttp/plugins/nut"

# Add nut user and group for udev at shutdown
if [ $( grep -ic "218" /etc/group ) -eq 0 ]; then
    groupadd -g 218 nut
fi

if [ $( grep -ic "218" /etc/passwd ) -eq 0 ]; then
    useradd -u 218 -g nut -s /bin/false nut
fi

# Update file permissions of scripts
chmod +0755 $DOCROOT/scripts/* \
        /etc/rc.d/rc.nut \
        /usr/sbin/nut-notify

# copy the default
cp -nr $DOCROOT/default.cfg $BOOT/nut.cfg

# remove nut symlink
if [ -L /etc/nut ]; then
    rm -f /etc/nut
    mkdir /etc/nut
fi

# copy conf files
cp -nr $DOCROOT/nut/* /etc/nut

if [ -d $BOOT/ups ]; then
    cp -f $BOOT/ups/* /etc/nut
fi

# copy old pids to new locaition
if [ -d /var/state/ups ]; then
    if [ ! -d /var/run/nut ]; then
        mkdir /var/run/nut
        chown -R 218:218 /var/run/nut
    fi
    if [ -f /var/run/upsmon.pid ]; then
        cp -nr /var/run/upsmon.pid /var/run/nut/upsmon.pid
        rm -rf /var/run/upsmon.pid
    fi
    cp -nr /var/state/ups/* /var/run/nut/
    rm -rf /var/state/ups
fi

# update permissions
if [ -d /etc/nut ]; then
    chown -R 218:218 /etc/nut
    chmod -R -r /etc/nut
fi
