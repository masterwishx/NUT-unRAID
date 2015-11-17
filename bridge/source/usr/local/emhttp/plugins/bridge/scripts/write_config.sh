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



# Set interface and bring it up
brctl delif br0 $ETH
sed -i 's/$ETH //g' /etc/rc.d/rc.inet1.conf
brctl addbr $BR
brctl stp $BR $STP
brctl setfd $BR $FD
brctl addif $BR $ETH
ifconfig $BR up
