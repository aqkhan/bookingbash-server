<?php
/*
Template Name: Booking Bash Image Resizer
*/
if (!empty($_REQUEST['u'])  && !empty($_REQUEST['width']) && !empty($_REQUEST['height'])){
    $img = $_REQUEST['u'];
    $w = $_REQUEST['width'];
    $h = $_REQUEST['height'];
    $resize = wp_get_image_editor($img);
    if (!is_wp_error($resize)){
        $resize->resize($w, $h, true);
        $resize->stream($mime_type = 'image/jpeg');
    }
}
else{
    return false;
}