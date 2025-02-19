<?php

// Configuration file for CADViewer Community and CADViewer Enterprise version and standard settings
require 'CADViewer_config.php';
    

if (! function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        $needle_len = strlen($needle);
        return ($needle_len === 0 || 0 === substr_compare($haystack, $needle, - $needle_len));
    }
}




$http_origin = '';

if (isset($_SERVER['HTTP_ORIGIN'])) {
$http_origin = $_SERVER['HTTP_ORIGIN'];
}
elseif (isset($_SERVER['HTTP_REFERER'])) {
$http_origin = $_SERVER['HTTP_REFERER'];
}

if ($checkorigin){
	
	if (in_array($http_origin, $allowed_domains))
	{
		header("Access-Control-Allow-Origin: $http_origin");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
	}	
}
else{
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
}




$fullPath = $_POST['file'];
$file_content = $_POST['file_content'];

$custom_content = "";
try {
	if (isset($_POST['custom_content'])){
		$custom_content = $_POST['custom_content'];    // this is stuff to use to control content
	}
} catch (Exception $e) {
	// do nothing
}


if (str_ends_with($fullPath, ".pdf") || str_ends_with($fullPath, ".html") || str_ends_with($fullPath, ".svg") || str_ends_with($fullPath, ".json") || str_ends_with($fullPath, ".png") || str_ends_with($fullPath, ".xml") || str_ends_with($fullPath, ".rw")){
	// no problem, this is a json redline file or png to pdf convesion
}
else{
	echo("FAILURE: save is not allowed: " . $fullPath);
	exit;
}




//echo $custom_content;

$base64 = 0;

try {
	if (isset($_POST['base64'])){
		$base64 = $_POST['base64'];		
	}
} catch (Exception $e) {
	// do nothing
}

if ($base64==1){
	$file_content = base64_decode($file_content);	
}


$listtype = "";
try {
	if (isset($_POST['listtype'])) {
		$listtype = $_POST['listtype'];	
	}
} catch (Exception $e) {
	// do nothing
}


// 7.6.26   7.7.11
$pos1 = strpos($fullPath, "http:");
$pos2 = strpos($fullPath, "https:");
$basepathpos = strpos($fullPath, $home_dir);
//echo "pos1".is_numeric($pos1)."pos2".is_numeric($pos2);
// home dir . for server location   only if not 
if ( $listtype == "redline" && !(is_numeric($pos1) || is_numeric($pos2) )){
		
	if (is_numeric($basepathpos) && $basepathpos == 0) {
		// do nothing, only if the serverpath is the beginning part of the complete filename
	}
	else 
		$fullPath = $home_dir . $fullPath;
}


if ( $listtype == "serverfolder"){
		
	if (is_numeric($basepathpos) && $basepathpos == 0) {
		// do nothing, only if the serverpath is the beginning part of the complete filename
	}
	else 
		$fullPath = $home_dir . $fullPath;
}





$fullPath = urldecode($fullPath);


//echo 'XX'. $fullPath . 'XX';


if (strpos ( $fullPath , 'http' )>-1){

	$fullPath = str_replace(" ", "%20", $fullPath);
}

//echo "$fullPath";
//echo "$file_content";
echo "";

$basepath =    substr( $fullPath, 0, strrpos ( $fullPath , '/' ));

if (!file_exists($basepath)) {
	mkdir($basepath, 0777, true);
}

if (file_exists($fullPath)) {	
	unlink($fullPath);
//	rename($fullPath, $fullPath . '_old');
}


//$rand = rand ( 0 , 100000 );
//if ($fd = fopen ($fullPath . $rand, "w+")) {


if ($fd = fopen ($fullPath, "w+")) {
//echo "file open!";


	$pieces = str_split($file_content, 1024 * 4);
    foreach ($pieces as $piece) {
        fwrite($fd, $piece, strlen($piece));
    }
    fclose($fd);


	// if HTML file, we need to convert the encoding. 
	$htmlfilepos= strpos($fullPath, ".html");
	if (is_numeric($htmlfilepos) && $htmlfilepos > 0){

		$html = file_get_contents($fullPath);
		$html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");

		// echo "html: " .$html;

		$ifp = fopen($fullPath, "wb");
		fwrite($ifp, $html);
		fclose($ifp);

	}



//	fwrite($fd, $file_content);
//	fclose ($fd);	
		
	$time = time() + 1;
	touch($fullPath, $time);
		
 	echo "Succes "; // .$fullPath;
	exit;
}

 	echo "Could not save file " . $fullPath;
	exit;

?>
