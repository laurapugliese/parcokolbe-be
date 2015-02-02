<?php
include "../utils/DBUtils.php";

class Sale{
	
	function getSale(){
		$query = "SELECT id, nome, categoria FROM sale";
		$db = new DBUtils();
		$result = $db->execute_query($query);
		return $result;
	}
	
	function getSalaById($idsala){
		$query = "SELECT id, nome, categoria FROM sale WHERE id = $idsala";
		$db = new DBUtils();
		$result = $db->execute_query($query);
		return $result;
	}
	
}