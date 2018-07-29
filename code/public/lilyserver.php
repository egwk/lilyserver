<?php

/**
*
* Lily Server 
* 
* Simple Docker image with a PHP script that makes Lilypond available as HTTP service.
*
* EGWK (c) 2018.
*
*/

function array_get($request, $key, $default = null)
{
    return isset($request[$key]) ? $request[$key] : $default;
}

function get($key, $default = null)
{
    return array_get($_GET, $key, $default);
}

function post($key, $default = null)
{
    return array_get($_POST, $key, $default);
}

function onOff($value, $default = false)
{
    $on  = in_array(strtolower($value), ["true",  "1", "on",  "yes"]);
    $off = in_array(strtolower($value), ["false", "0", "off",  "no"]);
	return $on ? true : ($off ? false : $default);
}

function compileLily($cachefolder, $filename, $png = false)
{
	$format = $png ? "--format=png" : "";
    $result = exec(
		"lilypond --output=$cachefolder$filename $format $cachefolder$filename.ly 2>&1"
	, $error, $retval);
    if ($retval != 0) {
        unset($error[0]);
        unset($error[1]);
        unset($error[2]);
        $error = array_map(function ($e) use ($cachefolder){
            return str_replace($cachefolder, "", $e);
        }, $error);
        http_response_code(400);
        header("Content-Type: text/plain");
        echo implode("<br/>", $error);
        exit;
    }
}

$filename = tempnam();
$cachefolder = "/code/cache/";
$lilycode = base64_decode(post("lilycode"));
file_put_contents("$cachefolder$filename.ly", $lilycode);

if(onOff(post("png")))
{
	compileLily($cachefolder, $filename, true);
	$img = imagecreatefrompng("$cachefolder$filename.png");
	if(onOff(post("autotrim")))
	{
		$cropped = imagecropauto($img, IMG_CROP_WHITE);
		imagedestroy($img);
		$img = $cropped;
	}
	http_response_code(200);
	header("Content-type: image/png");
	imagepng($img, null, 9);
} else {
	compileLily($cachefolder, $filename);
	http_response_code(200);
	header("Content-Type: application/pdf");
	readfile("$cachefolder$filename.pdf");
}

