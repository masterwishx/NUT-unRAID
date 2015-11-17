<?PHP
/* Copyright 2015, Dan Landon.
 * Copyright 2015, Bergware International.
 * Copyright 2015, Lime Technology
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * Edited by macester for Bridge Plugin.
 */
?>
<?
$cfg  = "/boot/config/plugins/bridge/bridge.cfg";
exec("sed -i -e '/^NINTERFACE/c\\NINTERFACE=\"'{$_POST['NINTERFACE']}'\"' $cfg");
exec("sed -i -e '/^BRIDGE/c\\BRIDGE=\"'{$_POST['BRIDGE']}'\"' $cfg");
exec("sed -i -e '/^STPMODE/c\\STPMODE=\"'{$_POST['STPMODE']}'\"' $cfg");
exec("sed -i -e '/^DELAY/c\\DELAY=\"'{$_POST['DELAY']}'\"' $cfg");

if ($_POST['SERVICE']=='enable') exec("/usr/local/emhttp/plugins/bridge/scripts/write_config.sh");
?>
