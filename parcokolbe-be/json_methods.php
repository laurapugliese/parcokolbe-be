<?php
include "classes/Sale.php";
include "utils/common_functions.php";

$classname = isset($_REQUEST["classname"]) ? $_REQUEST["classname"] : "";
$methodname = isset($_REQUEST["methodname"]) ? $_REQUEST["methodname"] : "";

_print_r('classname[' . $classname . ']<br>');
_print_r('methodname[' . $methodname . ']<br>');

$instance = new $classname;
_print_r($instance->$methodname());