<?php
include "config.php";
include "libs/simple_html_dom.php";
include "libs/Array2XML.php";
include "utils/common_functions.php";
include 'classes/Sale.php';

define("CHIAVE_CORSO", "corso");
define("CHIAVE_ISTRUTTORE", "istruttore");
define("CHIAVE_ORA_INIZIO", "ora_inizio");
define("CHIAVE_ORA_FINE", "ora_fine");

$html = file_get_html(URL_ORARIO_LEZIONI);
//$html = file_get_html("sample_orario_lezioni.html");
$htmlplain = $html->plaintext;

$settimana_dal = "";
$settimana_al = "";

$array_corsi = array();

// Itero tutte le tabelle delle sale con relativi orari
foreach($html->find('table.calendario') as $table_sala){
	
	// Recupero il nome della sala dalla caption della tabella
	$caption_obj = $table_sala->find('caption', 0);
	$caption = isset($caption_obj) ? $caption_obj->plaintext : "";
	
	// Divido il nome dalla sala dalla settimana degli orari
	$caption_array = explode(" - ", $caption);
	$sala = "";
	if(isset($caption_array) && count($caption_array) == 2){
		$sala = strtolower($caption_array[0]);
		$periodo = strtolower($caption_array[1]);
		
		// Recupero la data settimana "dal"
		$startsAt = strpos($periodo, "dal") + strlen("dal");
		$endsAt = strpos($periodo, "al", $startsAt);
		$dal = _trim(substr($periodo, $startsAt, $endsAt - $startsAt));
		// Traduco il mese da italiano ad inglese
		$dal = translate_month_in_string($dal);
		$mese_dal = contains_month_string($dal) ? date('F', strtotime($dal)) : "";
		
		// Recupero la data settimana "al"
		$startsAt = strpos($periodo, " al") + strlen(" al");
		$endsAt = strlen($periodo);
		$al = _trim(substr($periodo, $startsAt, $endsAt - $startsAt));
		// Traduco il mese da italiano ad inglese
		$al = translate_month_in_string($al);
		$mese_al = contains_month_string($al) ? date('F', strtotime($al)) : "";
		
		// Prima di convertire le due date devo verificare che entrambe siano 
		// comprensive del mese, se "dal" e "al" hanno lo stesso mese è presente
		// solo nella variabile "al", al contrario lo inserisco
		if (is_empty_string($mese_dal)){
			$dal .= " " . $mese_al;
		}
		
		// Formatto le date "dal" e "al"
		$settimana_dal = date('d-m-Y', strtotime($dal));
		$settimana_al = date('d-m-Y', strtotime($al));
	}
	
	// Itero tutte le righe della tabella
	foreach ($table_sala->find('tr') as $i => $tr){
		$giorno = "";
		$array_corso = array();
		
		// Itero tutte le colonne di ciascuna riga della tabella
		foreach ($tr->find('td') as $j => $td){
			
			// Se il td contiene il corso devo recuperare i dati
			$tdClass = $td->getAttribute('class');
			if ($tdClass == "reserved_cells"){
				
				// In base all'indice del td valorizzo la data del corso
				$start_date = strtotime($settimana_dal);
				$end_date = strtotime($settimana_al);
				
				while ($start_date <= $end_date) {
					if (date("N", $start_date) == $j){
						$giorno = date('d-m-Y', $start_date);
						break;
					}
					$start_date = strtotime('+1 day', $start_date);
				}
				
				// Recupero il corso dal tag <a> all'interno del <td>
				foreach ($td->find('a') as $a){
					
					// Recupero l'orario del corso dal tag <strong>
					$strong_obj = $a->find('strong', 0);
					$orario = isset($strong_obj) ? $strong_obj->plaintext : "";
					
					// Separo l'orario di inizio dall'orario di fine
					$orari = explode("-", $orario);
					$ora_inizio = "";
					$ora_fine = "";
					if(isset($orari) && count($orari) == 2){
						$ora_inizio = $orari[0];
						$ora_fine = $orari[1];
					}
					
					// Recupero il nome del corso dal tag <div id="activity">
					$corso_obj = $a->find('div#activity', 0);
					$corso = isset($corso_obj) ? $corso_obj->plaintext : "";
					
					// Recupero il nome dell'istruttore del corso dal tag <div id="label">
					$istruttore_obj = $a->find('div#label', 0);
					$istruttore = isset($istruttore_obj) ? $istruttore_obj->plaintext : "";

					// Creo l'array del corso
					$array_corso = array(CHIAVE_ORA_INIZIO => $ora_inizio,
										 CHIAVE_ORA_FINE => $ora_fine,
										 CHIAVE_CORSO => strip($corso),
										 CHIAVE_ISTRUTTORE => $istruttore
					);
					
					// Aggiungo il corso all'array di sale e giorni
					$array_corsi[$sala][$giorno][] = $array_corso;
				}
			}
		}
	}
}

// Recupero dal DB l'elenco di sale
$sale = new Sale();
$vals = $sale->getSale();
// Per recuperare più semplicemente l'id della sala creo un array
// in cui la chiave è il nome della sala
$array_sale = array();
foreach ($vals as $sala){
	$array_sale[$sala["nome"]] = $sala["id"];
}

// Devo iterare l'elenco di corsi ed inserire i dati nel DB
foreach ($array_corsi as $sala => $giorni){
	foreach ($giorni as $giorno => $corsi){
		foreach ($corsi as $corso){
			// Recupero l'id della sala dal nome
			$idsala = $array_sale[$sala];
			if (!is_empty_string($idsala)){
				$nomecorso = $corso[CHIAVE_CORSO];
				$ora_inizio = $corso[CHIAVE_ORA_INIZIO];
				$ora_fine = $corso[CHIAVE_ORA_FINE];
				$istruttore = $corso[CHIAVE_ISTRUTTORE];
				$giorno = date('Y-m-d', strtotime($giorno));
				// Compongo la query di inserimento per il corso
				$query = "INSERT INTO corsi (idsala, giorno, corso, ora_inizio, ora_fine, istruttore) ";
				$query .= "VALUES ($idsala, '$giorno', '$nomecorso', '$ora_inizio', '$ora_fine', '$istruttore') ";
				$query .= "ON DUPLICATE KEY UPDATE istruttore = VALUES(istruttore), data_aggiornamento = CURRENT_TIMESTAMP;";
				
				// Eseguo la query
				$db = new DBUtils();
				$db->execute_insert_query($query);
			}
		}
	}
}