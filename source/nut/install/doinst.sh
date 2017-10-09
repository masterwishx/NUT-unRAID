#!/bin/sh
BOOT="/boot/config/plugins/nut"
DOCROOT="/usr/local/emhttp/plugins/nut"

# Update file permissions of scripts
chmod +0755 $DOCROOT/scripts/* \
        /etc/rc.d/rc.nut \
        /usr/sbin/nut-notify

#copy the default configs if they don't exist
cp -nr $DOCROOT/ups $BOOT
cp -nr $DOCROOT/default.cfg $BOOT/nut.cfg

# remove nut config directory and symlink to plugin directory on flash drive
rm -rf /etc/ups
ln -sfT $BOOT/ups /etc/ups

rm -rf /etc/nut
ln -sfT $BOOT/ups /etc/nut

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
