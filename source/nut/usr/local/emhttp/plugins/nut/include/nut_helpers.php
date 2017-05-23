<?
/* get options for battery level */
function get_battery_level($selected=20){
    $range = [1,99];
    rsort($range);
    $options = "";
    foreach(range($range[0], $range[1], 1) as $level){
        $options .= "<option value='$level'";

        // set saved option as selected
        if (intval($selected) === $level)
            $options .= " selected";

        $options .= ">$level</option>";
    }
    return $options;
}
?>