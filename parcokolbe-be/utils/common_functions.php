<?php

function _print_r($var, $desc = ""){
	$varval = print_r($var, true);
	print "<pre>";
	if($desc != "") print $desc . ": <br/>";
	print $varval;
	print "</pre>\n";
}

function contains($string, $tofind){
	if (strpos($string, $tofind) !== false) {
    	return true;
	}
	return false;
}

function startsWith($string, $tofind){
    if (substr($string, 0, strlen($tofind)) === $tofind){
    	return true;
    }
    return false;
}

function _trim($string){
	return strip(rtrim(ltrim($string)));
}

function strip($string){
	if (!is_empty_string($string)){
		return preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $string);
	}
	return $string;
}

function is_empty_string($string){
	return !(isset($string) && $string != "");
}

$months = array("Gennaio" => "January",
		"Febbraio" => "February",
		"Marzo" => "March",
		"Aprile" => "April",
		"Maggio" => "May",
		"Giugno" => "June",
		"Luglio" => "July",
		"Agosto" => "August",
		"Settembre" => "September",
		"Ottobre" => "October",
		"Novembre" => "November",
		"Dicembre" => "December"
);

function contains_month_string($string){
	global $months;
	$string = strtolower($string);
	foreach ($months as $en){
		if(contains($string, strtolower($en))){
			return true;
		}
	}
	return false;
}

function translate_month_in_string($string){
	global $months;
	$string = strtolower($string);
	foreach ($months as $it => $en){
		$it = strtolower($it);
		if(contains($string, $it)){
			return str_replace($it, $en, $string);
		}
	}
	return $string;
}