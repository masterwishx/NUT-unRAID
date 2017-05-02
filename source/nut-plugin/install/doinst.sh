#!/bin/sh

# Update file permissions of scripts
chmod +0755 /usr/local/emhttp/plugins/nut/scripts/* \
	/etc/rc.d/rc.nut

cp -nr /usr/local/emhttp/plugins/nut/ups /boot/config/plugins/nut
rm -rf /etc/httpd
ln -sfT /boot/config/plugins/nut/ups /etc/ups
