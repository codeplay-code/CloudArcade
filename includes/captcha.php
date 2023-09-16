<?php

header("Content-type: image/png");

function get_random_string($length = 5) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
$str = get_random_string();

$img_handle = ImageCreate(80, 35) or die("X");
$back_color = ImageColorAllocate($img_handle, 102, 102, 153);
$txt_color = ImageColorAllocate($img_handle, 255, 255, 255);
ImageString($img_handle, 30, 15, 10, $str, $txt_color);
Imagepng($img_handle);

session_start();
$_SESSION['captcha'] = $str;

?>