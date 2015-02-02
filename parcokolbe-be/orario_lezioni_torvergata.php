<?php
include "../../../common/utils/common_functions.php";
include "../../../common/utils/Constants.php";
include "../../../common/lib/simple_html_dom.php";
include "../../../common/lib/Array2XML.php";
include "config.php";

function __print_r($var, $desc = ""){
	$varval = print_r($var, true);
	print "<pre>";
	if($desc != "") print $desc . ": <br/>";
	print $varval;
	print "</pre>\n";
}

error_reporting(-1);
$html = file_get_html(SCIENZE_INFORMATICA_ORARIO);
$htmlplain = $html->plaintext;


//Recupero sigle delle materie, Docenti e Crediti formativi

$ora = null;
preg_match('/\((dal(.*?))\)/is', $htmlplain, $result);
$periodo = isset($result[1]) ? $result[1] : "";
$result = null;

// Recupero sigle delle materie, Docenti e Crediti formativi
$findTable = false;
foreach($html->find('p') as $i => $p){
	$prev = $p->prev_sibling();
	$next = $p->next_sibling();
	if($prev != null && isset($prev) && $prev->tag == "table"){
		$findTable = true;
	}else if($next != null && isset($next) && $next->tag == "table"){
		$findTable = false;
	}else if($findTable){
		$elems = explode(':', $p->plaintext);
		
		if(is_array($elems) && count($elems) == 2){
			__print_r($p->plaintext);
			$tmp = explode('(', $elems[1]);
			// Definisco la sigla della materia
			$sigla = $elems[0];
			
			// Inizializzo le variabili		
			$materia = "";
			$docente = "";
			$cfu = "";	
			$aula = "";
			
			if(is_array($tmp) && count($tmp) > 0){
				$materia = $tmp[0];
				// Se la materia contiene una virgola è presente al suo interno il docente
				if(contains($materia, ',')){
					$tmp2 = explode(',', $materia);
					$materia = $tmp2[0];
					$docente = $tmp2[1];					
				}
				
				// Verifico che all'interno della materia non sia presente il docente
				$prefixs = array("Prof.", "Ing.", "Dott.", "Prof.ssa", "Dott.ssa");
				
				
				// Se sono presenti due parentesi è specificata anche l'aula
				if(count($tmp) == 3){
					$aula = str_replace(')', '', $tmp[2]);
				}
				
				// Recupero dei CFU
				$cfuarr = explode(')', $tmp[1]);		
				$cfu = $cfuarr[0];				
				if(contains($cfu, '2 modulo')){
					$cfu = str_replace('2 modulo', '', $cfu);
					$materia .= " (2 modulo)";
				}
				if(is_array($cfuarr) && count($cfuarr) > 1 && $docente == ""){
					$docente = $cfuarr[1];
				}
				$cfu = strtoupper($cfu);
			}
			$materie[trim($sigla)] = array('sigla' 		=> trim($sigla),
								 	 	   'materia' 	=> trim(str_replace("  ", "", $materia)),
								 	 	   'docente' 	=> trim(str_replace("  ", "", $docente)),
								 	 	   'cfu' 		=> trim($cfu),
								 	 	   'aula' 		=> trim($aula));
		}
	}
	//__print_r('***********************');
}
__print_r($materie, 'materie');
die;
/*
foreach($html->find('p') as $i => $b){
	if($b->next_sibling()->tag != 'table'){
		$sigla = $b->plaintext;
		echo $b->next_sibling()->tag;
		preg_match('/' . preg_quote($sigla) . ':\s+(.*?)(\n|\r|$)/is', $htmlplain, $result);
		$detail = count($result) >= 2 ? $result[1] : '';
		$details = explode(' - ', $detail);
		if(count($details) == 3){
			$materia = $details[0];
			$docente = $details[1];
			$cfu = $details[2];
		}else if(count($details) == 2){
			$materia = $details[0];
			$a = explode('(', $details[1]);
			$docente = $a[0];
			$cfu = str_replace(')', '', $a[1]);
		}
		$cfu = explode('(', $cfu);
		$cfu = $cfu[0];
		$materie[$sigla] = array('sigla' => $sigla,
								 'materia' => $materia,
								 'docente' => $docente,
								 'cfu' => $cfu);
	}
}
*/
die;
//Recupero l'orario delle lezioni
foreach($html->find('b') as $i => $b){
	if($b->next_sibling()->tag == 'table'){
		$annoaula = explode(',', $b->plaintext);
		$anno = $annoaula[0];
		$aula = $annoaula[1];
		$table = $b->next_sibling();
		$header = array();
		$lezioni = array();
		foreach($table->find('tr') as $i => $tr){
			foreach($tr->find('td') as $j => $td){
				$val = $td->plaintext;
				if($i == 0) $header[] = $val;
				if($j == 0) {
					$ora = $val;
				}else if($val != null && $val != "" && $val != "&nbsp;" && $j <= 5 && $i != 0){
					$giorno = $header[$j];
					$mataula = explode(' (', $val);
					if(count($mataula) == 2){
						$aula = str_replace(')', '', $mataula[1]);
						$val = $mataula[0];
					}
					$lezioni[] = array('giorno'  => $giorno,
							  		   'ora' 	 => $ora,
							  		   'lezione' => $val,
							  		   'aula' => $aula,
							           'docente' => $materie[$val]['docente'],
							           'longdesc' => $materie[$val]['materia'],
							           'cfu' => $materie[$val]['cfu']);    
				}
			}
		}
		$orari['orario'][] = array('@attributes' => array('anno' => $anno, 'periodo' => $periodo), 'lezione' => $lezioni);
	}		
}
print_r('<pre');
print_r($orari);
print_r('</pre>');
die;

//Scrivo l'xml
$xml = Array2XML::createXML('orari', $orari);
$filename = XML_FILES_ROOT_JOB . XML_FILES_DIR . 'orario_lezioni.xml';
if(file_exists($filename)){
	unlink($filename);
}
$fp = fopen($filename, 'w');
fwrite($fp, $xml->saveXML());
fclose($fp);

echo 'DONE';
