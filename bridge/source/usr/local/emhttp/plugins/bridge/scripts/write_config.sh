#!/bin/bash
#
# Bridge plugin configuration script for unRAID
# By macester macecapri@gmail.com
#
BRIDGECFG=/boot/config/plugins/bridge/bridge.cfg
ETH=$( grep -i "NINTERFACE=" $BRIDGECFG|cut -d \" -f2|sed 's/^//' )
BR=$( grep -i "BRIDGE=" $BRIDGECFG|cut -d \" -f2|sed 's/^//' )
STP=$( grep -i "STPMODE=" $BRIDGECFG|cut -d \" -f2|sed 's/^//' )
FD=$( grep -i "DELAY=" $BRIDGECFG|cut -d \" -f2|sed 's/^//' )


###########
# LOGGING #
###########

# If possible, log events in /var/log/messages:
if [ -f /var/run/syslogd.pid -a -x /usr/bin/logger ]; then
  LOGGER=/usr/bin/logger
else # output to stdout/stderr:
  LOGGER=/bin/cat
fi


# Set interface and bring it up

echo "Bridge Plugin:  Started..." | $LOGGER

/sbin/ifconfig br0 down
echo "Bridge Plugin:  /sbin/ifconfig br0 down" | $LOGGER

/sbin/ifconfig $ETH down
echo "Bridge Plugin:  /sbin/ifconfig $ETH down" | $LOGGER

/sbin/brctl delif br0 $ETH
echo "Bridge Plugin:  /sbin/brctl delif br0 $ETH" | $LOGGER

/sbin/ifconfig br0 up
echo "Bridge Plugin:  /sbin/ifconfig br0 up" | $LOGGER

/sbin/ifconfig $ETH 0.0.0.0 promisc up
echo "Bridge Plugin:  /sbin/ifconfig $ETH 0.0.0.0 promisc up" | $LOGGER

/usr/bin/sed -i 's/$ETH //g' /etc/rc.d/rc.inet1.conf
echo "Bridge Plugin:  /usr/bin/sed -i 's/$ETH //g' /etc/rc.d/rc.inet1.conf" | $LOGGER

/sbin/brctl addbr $BR
echo "Bridge Plugin:  /sbin/brctl addbr $BR" | $LOGGER

/sbin/brctl stp $BR $STP
echo "Bridge Plugin:  /sbin/brctl stp $BR $STP" | $LOGGER

/sbin/brctl setfd $BR $FD
echo "Bridge Plugin:  /sbin/brctl setfd $BR $FD" | $LOGGER

/sbin/brctl addif $BR $ETH
echo "Bridge Plugin:  /sbin/brctl addif $BR $ETH" | $LOGGER

/sbin/ifconfig $BR up
echo "Bridge Plugin:  /sbin/ifconfig $BR up" | $LOGGER

echo "Bridge Plugin:  Finished..." | $LOGGER
