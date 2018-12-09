<?php
//npm-badge v. 1.0.0

date_default_timezone_set('UTC');
error_reporting(E_ERROR | E_PARSE);
//Turn errors into exceptions
function myErrorHandler($errno, $errstr, $errfile, $errline) {
   throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("myErrorHandler");
header("Content-type: image/png");
$originalImage = "imgs/npm-badge-350x60.png";
function readableNumber($num) {
    $neg = $num < 0;
    $units = array('', 'K', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y');
    if ($neg){
        $num = -$num;
    }
    if ($num < 1){
        return ($neg ? '-' : '') . $num . ' B';
    }
    $exponent = min(floor(log($num) / log(1000)), count($units) - 1);
    $num = sprintf('%.0F', ($num / pow(1000, $exponent)));
    $unit = $units[$exponent];
    return ($neg ? '-' : '') . $num . $unit;
}
if(file_exists($originalImage)) {
	$im = imagecreatefrompng($originalImage);
	imagesavealpha($im, true); // important to keep the png's transparency 
	if(!$im) {
		die("im is null");
	}
	$color = imagecolorallocate($im, 80, 80, 80);
	$width = 350; // the width of the image
	$height = 60; // the height of the image
	$font = 3; // font size
	
	if (!empty($_GET['name'])) {
	    $name = $_GET['name'];
        try {
            $digit = "npm install " . $name; // digit
            $leftTextPos = 69;
            $topTextPos = 6;
            imagestring($im, $font, $leftTextPos, $topTextPos, $digit, $color);

            // --- Downloads	---
            //Last 12 months
            $now = date_create();
            $actualDate = date_format($now, "Y-m-d");
            date_modify($now,"-1 year");
            $lastDate = date_format($now, "Y-m-d");

            //Download API
            $json = file_get_contents("https://api.npmjs.org/downloads/point/" . $lastDate . ":" . $actualDate . "/" . $name);
            $jsonObj = json_decode($json);

            $font = 3;
            $digit = readableNumber($jsonObj->downloads) . " downloads (last 12 months)";
            $topTextPos = 40;
            imagestring($im, $font, $leftTextPos, $topTextPos, $digit, $color);

            // --- Version ---
            //Registry API
            $json = file_get_contents("https://registry.npmjs.org/" . $name);
            $jsonObj = json_decode($json);

            $font = 4;
            $digit = "ver. " . $jsonObj->{'dist-tags'}->latest;
            $topTextPos = 21;
            imagestring($im, $font, $leftTextPos, $topTextPos, $digit, $color);
		} catch (Exception $e) {
            $font = 2;
            $digit = "Error loading $name info!";
            $leftTextPos = 69;
            $topTextPos = 40;
            imagestring($im, $font, $leftTextPos, $topTextPos, $digit, $color);
        }
	} else {
		$font = 4;
		$digit = "The name parameter is missing!";
		$leftTextPos = 83;
		$topTextPos = 21;
		imagestring($im, $font, $leftTextPos, $topTextPos, $digit, $color);
	}
	imagepng($im);
	imagedestroy($im);
 }
?>