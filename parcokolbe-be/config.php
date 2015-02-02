<?php
// Report all PHP errors
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

date_default_timezone_set('Europe/Rome');
setlocale(LC_TIME, 'it_IT');

$dbhost = "localhost";
$dbport = "3306";
$dbuser = "root";
$dbpassword = "root";
$database = "parcokolbe";

define("URL_ORARIO_LEZIONI", "http://italianafitness.it/parco-kolbe/timetable_print.php");