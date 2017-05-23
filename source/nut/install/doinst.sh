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
