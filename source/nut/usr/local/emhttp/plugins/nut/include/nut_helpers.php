<?
/* get options for battery level */
function get_battery_options($selected=20){
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

/* get options for time intervals */
function get_minute_options($time){
    $options = '';
        for($i = 1; $i <= 60; $i++){
            $options .= '<option value="'.($i*60).'"';
            if(intval($time) === ($i*60))
                $options .= ' selected';

            $options .= '>'.$i.'</option>';
        }
    return $options;
}

?>