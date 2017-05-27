<?
require_once '/usr/local/emhttp/plugins/nut/include/nut_helpers.php';

$sName = "nut";
$nut_cfg           = parse_ini_file("/boot/config/plugins/$sName/$sName.cfg");
$nut_service     = isset($nut_cfg['SERVICE'])           ? htmlspecialchars($nut_cfg['SERVICE'])        : 'disable';
$nut_manual     = isset($nut_cfg['MANUAL'])           ? htmlspecialchars($nut_cfg['MANUAL'])        : 'disable';
$nut_driver       = isset($nut_cfg['DRIVER'])             ? htmlspecialchars($nut_cfg['DRIVER'])         : 'custom';
$nut_serial        = isset($nut_cfg['SERIAL'])              ? htmlspecialchars($nut_cfg['SERIAL'])         : 'none';
$nut_port          = isset($nut_cfg['PORT'])                ? htmlspecialchars($nut_cfg['PORT'])            : 'auto';
$nut_ip             = isset($nut_cfg['IPADDR'])              ? htmlspecialchars($nut_cfg['IPADDR'])        : 'localhost';
$nut_mode        = isset($nut_cfg['MODE'])               ? htmlspecialchars($nut_cfg['MODE'])           : 'standalone';
$nut_shutdown  = isset($nut_cfg['SHUTDOWN'])      ? htmlspecialchars($nut_cfg['SHUTDOWN'])  : 'sec_timer';
$nut_battery      = isset($nut_cfg['BATTERYLEVEL']) ? intval($nut_cfg ['BATTERYLEVEL'])              : 20;
$nut_seconds     = isset($nut_cfg['SECONDS'])         ? intval($nut_cfg ['SECONDS'])                      : 240;
$nut_timeout      = isset($nut_cfg['TIMEOUT'])          ? intval($nut_cfg ['TIMEOUT'])                       : 240;
$nut_upskill        = isset($nut_cfg['UPSKILL'])           ? htmlspecialchars($nut_cfg ['UPSKILL'])        : 'disable';
$nut_poll            = isset($nut_cfg['POLL'])                ? intval($nut_cfg ['POLL'])                             : 15;
$nut_community = isset($nut_cfg['COMMUNITY'])     ? htmlspecialchars($nut_cfg ['COMMUNITY']) : 'public';
$nut_running = (intval(trim(shell_exec( "[ -f /proc/`cat /var/run/upsmon.pid 2> /dev/null`/exe ] && echo 1 || echo 0 2> /dev/null" ))) === 1 );
?>