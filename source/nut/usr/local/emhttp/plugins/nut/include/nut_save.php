<?
$base     = '/etc/nut/';
$plgpath  = '/boot/config/plugins/nut/ups/';
$editfile = realpath($_POST['editfile']);
$plgfile  = $plgpath.basename($editfile);

if(!strpos($editfile, $base) && file_exists($editfile) && array_key_exists('editdata', $_POST)){
    // remove carriage returns
    $editdata = str_replace("\r", '', $_POST['editdata']);

    if($_POST['manual'] == 'enable'){
        if(! is_dir($plgpath))
            mkdir($plgpath);

        // get previous config file contents and save them
        if(file_exists($plgfile)){
            $plgfile_old = file_get_contents($plgfile);
            file_put_contents($plgfile.'.old', $plgfile_old);
        }

        file_put_contents($plgfile, $editdata);
    }

    file_put_contents($editfile, $editdata);

    // save file contents
    $return_var = file_put_contents($editfile, $editdata);
}else{
    $return_var = false;
}

if($return_var)
    $return = ['success' => true, 'saved' => $editfile];
else
    $return = ['error' => $editfile];

echo json_encode($return);
?>