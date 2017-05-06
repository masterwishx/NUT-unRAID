#!/bin/sh
BOOT="/boot/config/plugins/nut"
DOCROOT="/usr/local/emhttp/plugins/nut"
RC_SCRIPT="/etc/rc.d/rc.nut"
SD_RCFILE="/etc/rc.d/rc.local_shutdown"

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

# add stop to shutdown script
#if ! grep "$RC_SCRIPT" $SD_RCFILE >/dev/null 2>&1
#    then echo -e "\n[ -x $RC_SCRIPT ] && $RC_SCRIPT shutdown" >> $SD_RCFILE
#fi
#[ ! -x $SD_RCFILE ] && chmod u+x $SD_RCFILE