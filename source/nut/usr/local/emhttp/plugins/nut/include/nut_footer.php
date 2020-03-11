<?PHP
/* Copyright 2017, Derek Macias.
 * Copyright 2005-2016, Lime Technology
 * Copyright 2015, Dan Landon.
 * Copyright 2015, Bergware International.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */
require_once '/usr/local/emhttp/plugins/nut/include/nut_config.php';

$red    = "class='tooltip-nut red-text'";
$green  = "class='tooltip-nut green-text'";
$orange = "class='tooltip-nut orange-text'";
$all    = $_GET['all']=='true';

function get_ups($name, $ip="localhost")
{
  $output = [];
  if (file_exists('/var/run/nut/upsmon.pid')) {
    // echo "/bin/upsc ".escapeshellarg($name)."@".escapeshellarg($ip)." 2>/dev/null";
    exec("/usr/bin/upsc ".escapeshellarg($name)."@".escapeshellarg($ip)." 2>/dev/null", $rows);
    for ($i=0; $i<count($rows); $i++) {
      $row = array_map('trim', explode(':', $rows[$i], 2));
      $output[$row[0]] = $row[1];
    }
  }
  return $output;
}

function array_key_exists_wildcard ( $arr, $nee )
{
    $nee = str_replace( '\\*', '.*?', preg_quote( $nee, '/' ) );
    return array_values(preg_grep( '/^' . $nee . '$/i', array_keys( $arr ) ));
}

$ups_status = get_ups($nut_name, $nut_ip);
$status = [];
if (count($ups_status)) {
  $online  = ( array_key_exists("ups.status", $ups_status) && stripos($ups_status["ups.status"],'OL')!==false );
  $battery = (array_key_exists("battery.charge",$ups_status)) ? intval(strtok($ups_status['battery.charge'],' ')) : false;
  $load    = (array_key_exists("ups.load", $ups_status)) ? intval(strtok($ups_status['ups.load'],' ')) : 0;

  $power_attr = array_key_exists_wildcard($ups_status, 'ups.*power.nominal');
  if (count($power_attr)) {
    $power =  intval(strtok($ups_status[$power_attr[0]],' '));
  }
  if ($nut_power == 'manual'){
    $power   = intval($nut_powerw);
  }

  if ($battery !== false) {
    if ($online && $battery < 100) $icon = "<span $green title='${nut_name}: online - battery is charging'><i class='fa fa-battery-charging'></i>&thinsp;${battery}%</span>";
    else if ($online && $battery  == 100) $icon = "<span $green title='${nut_name}: online - battery is full'><i class='fa fa-battery-full'></i>&thinsp;${battery}%</span>";
    else if (!$online) $icon = "<span $red title='${nut_name}: offline - battery is discharging'><i class='fa fa-battery-discharging'></i>&thinsp;${battery}%</span>";
    else $icon = "<span $green title='${nut_name}: battery status unknown'><i class='fa fa-battery-discharging'></i>n/a</span>";

    $status[0] = $icon;
  } else {
    $status[0] = "<span style='margin:0 6px 0 12px' title='$nut_name: battery info not available'><i class='fa fa-battery-empty'></i>&thinsp;n/a</span>";
  }
  $wattage = round($power*$load*0.01)."w";
  if ($power && $load) $status[1] = "<span title='${nut_name}: consuming $wattage ($load% of capacity)' ".($load>=90 ? "$red" : "$green")."><i class='fa fa-plug'></i>&thinsp;$wattage</span>";

  echo "<span>".implode('</span><span style="margin:0 6px 0 12px">', $status)."</span>";
} else {
  echo "<span style='margin:0 6px 0 12px' title='$nut_name: UPS info not availabe, check your settings'><i class='fa fa-battery-empty'></i>&thinsp;n/a</span>";
}
?>