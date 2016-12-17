<?php

/*
 * Resource: Adam Khoury: https://youtu.be/nlCfOcETQUo
 * Uses GD Library functions
 */

function image_resize($target, $newcopy, $w, $h, $ext){

    // Get first two elements in the getimagesize array of target image (width and height), and store in variables ($w_orig & $h_orig)
    list($w_orig, $h_orig) = getimagesize($target);


    $scale_ratio = $w_orig / $h_orig;

    // $w and $h values coming from function call, not the original ($w_orig & $h_orig)
    if(($w / $h) > $scale_ratio){
        $w = $h * $scale_ratio;
    }
    else {
        $h = $w / $scale_ratio;
    }


    // Initialize new variable
    $img = '';

    // Create new image based on image type and store in $img variable
    $ext = strtolower($ext);
    if($ext == 'gif'){
        $img = imagecreatefromgif($target);
    }
    elseif($ext == 'png'){
        $img = imagecreatefrompng($target);
    }
    else {
        $img = imagecreatefromjpeg($target);
    }

    // Make black rectangle
    $tci = imagecreatetruecolor($w, $h);

    // imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) 10 parameters!
    imagecopyresampled($tci, $img, 0, 0, 0, 0, $w, $h, $w_orig, $h_orig);

    // Output image to browser or file based on extension. Creates JPEG file from the given image (http://php.net/manual/en/function.imagejpeg.php)
    if($ext == 'gif'){
        imagegif($tci, $newcopy);
    }
    elseif($ext == 'jpg'){
        imagejpeg($tci, $newcopy, 80);
    }
    else {
        imagepng($tci, $newcopy, 80);
    }
}
