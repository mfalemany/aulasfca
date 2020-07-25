<?php
define('INICIO',TRUE);
require "../defaultincludes.inc";
require("../lib/Adaptador.php");
require('./rest.php');

$url_base = dirname($_SERVER['SCRIPT_NAME']);
$solicitud = str_replace($url_base,'',$_SERVER['REQUEST_URI']);

if($solicitud == '/'){
	echo json_encode(array('error'=>'Debe proporcionar un end-point')); die;
}

$rest = new Rest();

$solicitud = explode('/',ltrim($solicitud,'/'));
$metodo = array_shift($solicitud);

if(count($solicitud)){
	$rest->$metodo(...$solicitud);
}else{
	$rest->$metodo();
}


?>