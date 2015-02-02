<?php
include "config.php";
include "classes/Sale.php";
include "utils/common_functions.php";

$classname = isset($_REQUEST["classname"]) ? $_REQUEST["classname"] : null;
$methodname = isset($_REQUEST["methodname"]) ? $_REQUEST["methodname"] : null;

if(isset($classname) && isset($methodname)){
	
	$instance = new $classname;
	$result = call_user_func_array(array($instance, $methodname), array());
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
}