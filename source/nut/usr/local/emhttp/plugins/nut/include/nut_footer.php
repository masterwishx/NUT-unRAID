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
$config = parse_ini_file('/boot/config/plugins/nut/nut.cfg');

//  exit if NUT daemon isn't working
if (! file_exists('/var/run/nut/upsmon.pid')) {
  echo " ";
  exit(0);
}

$red    = "red-text";
$green  = "green-text";
$orange = "orange-text";
$black  = "black-text";

function get_ups($name, $ip="localhost")
{
  $output = [];
  $alarm = 0;
  exec("/usr/bin/upsc ".escapeshellarg($name)."@".escapeshellarg($ip)." 2>/dev/null", $rows);
  for ($i=0; $i<count($rows); $i++) {
    $row = array_map('trim', explode(':', $rows[$i], 2));
    $prop = $row[0];
    if (stripos($prop, "ups.alarm")!== false) {
      $prop = "{$prop}".$alarm++;
    }
    $output[$prop] = $row[1];
  }
  return $output;
}

function array_key_exists_wildcard ( $arr, $nee ) {
  $nee = str_replace( '\\*', '.*?', preg_quote( $nee, '/' ) );
  return array_values(preg_grep( '/^' . $nee . '$/i', array_keys( $arr ) ));
}

function format_time($seconds) {
  $t = round($seconds);
  return sprintf('%02d:%02d:%02d', round($t/3600,0),round($t/60%60,0), round($t%60,0));
}

$status = [];
$ups_status = get_ups($nut_name, $nut_ip);
if (count($ups_status)) {
  $online           = ( array_key_exists("ups.status", $ups_status) && stripos($ups_status["ups.status"],'OL')!==false );
  $battery          = (array_key_exists("battery.charge",$ups_status)) ? intval(strtok($ups_status['battery.charge'],' ')) : false;
  $load             = (array_key_exists("ups.load", $ups_status)) ? intval(strtok($ups_status['ups.load'],' ')) : 0;
  $realPower        = (array_key_exists("ups.realpower", $ups_status)) ? intval(strtok($ups_status['ups.realpower'],' ')) : NULL;
  $realPowerNominal = (array_key_exists("ups.realpower.nominal", $ups_status)) ? intval(strtok($ups_status['ups.realpower.nominal'],' ')) : NULL;
  $powerNominal     = (array_key_exists("ups.power.nominal", $ups_status)) ? intval(strtok($ups_status['ups.power.nominal'],' ')) : NULL;

  if ($nut_power == 'manual') {
    $powerNominal = intval($nut_powerva);
    $realPowerNominal = intval($nut_powerw);
    if ($realPowerNominal >= 0)
      $realPower = -1;
  }

  $ups_alarm = array_key_exists_wildcard($ups_status, 'ups.alarm*');
  if (count($ups_alarm)) {
    $alarms = "";
    foreach ($ups_alarm as $al) {
      $alarms .= "<div><i class='fa fa-exclamation-circle orange-text'></i>&nbsp;".$ups_status[$al]."</div>";
    }
    $status[3] = "<span id='nut_alarm' class='tooltip-nut $orange' data=\"$alarms\"><i class='fa fa-bell faa-ring animated'></i></span>";
  }

  if ($battery !== false) {
    $battery_runtime = array_key_exists($nut_runtime, $ups_status) ? format_time($ups_status[$nut_runtime]) : "n/a";
    if ($online && $battery < 100) $icon = "<span id='".($config['FOOTER_STYLE'] == 1 ? "copyright" : "nut_battery")."' class='tooltip-nut ".($config['FOOTER_STYLE'] == 1 ? "$black" : "$green")."' data='{$nut_name}: online - battery is charging'><i class='fa fa-battery-charging'></i>&thinsp;{$battery}&thinsp;%</span>";
    else if ($online && $battery  == 100) $icon = "<span id='".($config['FOOTER_STYLE'] == 1 ? "copyright" : "nut_battery")."' class='tooltip-nut ".($config['FOOTER_STYLE'] == 1 ? "$black" : "$green")."' data='{$nut_name}: online - battery is full'><i class='fa fa-battery-full'></i>&thinsp;{$battery}&thinsp;%</span>";
    else if (!$online) $icon = "<span id='".($config['FOOTER_STYLE'] == 1 ? "copyright" : "nut_battery")."' class='tooltip-nut $red' data='{$nut_name}: offline - battery is discharging - est. $battery_runtime left'><i class='fa fa-battery-discharging'></i>&thinsp;{$battery}&thinsp;%</span>";
    else $icon = "<span id='".($config['FOOTER_STYLE'] == 1 ? "copyright" : "nut_battery")."' class='tooltip-nut ".($config['FOOTER_STYLE'] == 1 ? "$black" : "$green")."' data='{$nut_name}: battery status unknown'><i class='fa fa-battery-discharging'></i>n/a</span>";

    $status[0] = $icon;
  } else {
    $status[0] = "<span id='nut_battery'class='tooltip-nut' style='margin:0 6px 0 12px' data='$nut_name: battery info not available'><i class='fa fa-battery-empty'></i>&thinsp;n/a</span>";
  }

  # ups.power.nominal (in VA) or compute from load and ups.power.nominal
  $apparentPower = $powerNominal > 0 && $load ? round($powerNominal * $load * 0.01) : -1;

  # ups.realpower (in W)
  $realPower = $realPower > 1 && $load ? $realPower : -1;
  # if no ups.realpower compute from load and ups.realpower.nominal (in W)
  if ($realPower < 0)
    $realPower = $realPowerNominal && $load ? round($realPowerNominal * $load * 0.01) : -1;

  if ($realPower >= 0 && $apparentPower >= 0) {
    $status[1] = "<span id='".($config['FOOTER_STYLE'] == 1 ? "copyright" : "nut_power")."' class='tooltip-nut " . ($load >= 90 ? "$red" : ($config['FOOTER_STYLE'] == 1 ? "$black" : "$green")) . "' data='[{$nut_name}] Load: $load&thinsp;% - Real power: $realPower&thinsp;W - Apparent power: $apparentPower&thinsp;VA'><i class='fa fa-plug'></i>&thinsp;{$realPower}&thinsp;W ({$apparentPower}&thinsp;VA)</span>";
  } else if ($realPower >= 0 && $load) {
    $status[1] = "<span id='".($config['FOOTER_STYLE'] == 1 ? "copyright" : "nut_power")."' class='tooltip-nut " . ($load >= 90 ? "$red" : ($config['FOOTER_STYLE'] == 1 ? "$black" : "$green")) . "' data='[{$nut_name}] Load: $load&thinsp;% - Real power: $realPower&thinsp;W'><i class='fa fa-plug'></i>&thinsp;{$realPower}&thinsp;W</span>";
  } else if ($apparentPower >= 0){
    $status[1] = "<span id='".($config['FOOTER_STYLE'] == 1 ? "copyright" : "nut_power")."' class='tooltip-nut " . ($load >= 90 ? "$red" : ($config['FOOTER_STYLE'] == 1 ? "$black" : "$green"))."' data='[{$nut_name}] Load: $load&thinsp;% - Apparent power: $apparentPower&thinsp;VA'><i class='fa fa-plug'></i>&thinsp;{$apparentPower}&thinsp;VA</span>";
  }

  echo "<span style='margin:0 6px 0 12px'>".implode('</span><span style="margin:0 6px 0 6px">', $status)."</span>";
} else {
  echo "<span style='margin:0 6px 0 12px' id='nut_power' class='tooltip-nut' data='$nut_name: UPS info not availabe, check your settings'><i class='fa fa-battery-empty'></i>&nbsp;n/a</span>";
}
?>
