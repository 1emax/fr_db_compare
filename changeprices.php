<?php

if(array_key_exists('change', $_GET) && $_GET['change'] == 'yes') {
	$elements = json_decode($_POST['val'], true);

	$dblocation1 = "localhost";
	$dbname1 = "sitebase";
	$dbuser1 = "root";
	$dbpasswd1 = "";

	$dbconnect1 = mysqli_connect($dblocation1,$dbuser1,$dbpasswd1, $dbname1);
	if (!$dbconnect1) {
		echo json_encode(array('error'=>'Не удалось подключиться к базе1'));
		exit();
	} else {
		mysqli_query($dbconnect1, "SET NAMES 'utf8'");
	}

	$subQuery = createUpdQ($elements);

	$query = 'UPDATE sitebase.oc_product SET price = CASE product_id ' . $subQuery . ' END WHERE product_id IN ('.implode(array_keys($elements), ',').')';
	
	$result = mysqli_query($dbconnect1, $query);
	if ($result) {
		$res = mysqli_query($dbconnect1, 'SELECT product_id, price FROM sitebase.oc_product WHERE product_id IN ('.implode(array_keys($elements),',') .')' );
		$dbData = rowsToAssoc($res, 'product_id');
		echo json_encode($dbData);
	} else {
		echo json_encode(array('error'=>'Не удалось обновить данные'));
	}

} else if(array_key_exists('move', $_GET) && $_GET['move'] != '') {
	$moveTo = $_GET['move'];

	if($moveTo != 'go_away' && $moveTo != 'to_first') {
		echo json_encode(array('error'=>'Выбрана неправильная база данных'));
		exit();
	}

	$dblocation1 = "localhost";
	$dbname1 = "xml1cbase";
	$dbuser1 = "root";
	$dbpasswd1 = "";
	$dbMoveConnect = mysqli_connect($dblocation1,$dbuser1,$dbpasswd1, $dbname1);

	if (!$dbMoveConnect) {
		echo json_encode(array('error'=>'Не удалось подключиться к базе'));
		exit();
	} else {
		mysqli_query($dbMoveConnect, "SET NAMES 'utf8'");
	}

	$elements = json_decode($_POST['val'], true);
	$subQ = implode($elements, '\',\'');
	$query = 'INSERT INTO ' .$moveTo.' SELECT * FROM xml1c_all_products where id in (\''.$subQ.'\')';
	$result = mysqli_query($dbMoveConnect, $query);
	if ($result) {
		if($moveTo == 'go_away') {
			$query = 'DELETE FROM xml1c_all_products where  id in (\''.$subQ.'\')';
			$result = mysqli_query($dbMoveConnect, $query);
			if ($result) {
				echo json_encode(array('elements' => $elements, 'action'=>'remove'));				
			} else {
				echo json_encode(array('error'=>'Не удалось удалить скопированные данные'));
			}
		} else {
			echo json_encode(array('elements' => $elements, 'action'=>'highlight'));
		}
	} else {
		echo json_encode(array('error'=>'Не удалось обновить данные'));
	}

}

function createUpdQ($elements) {
	$subQ = '';
	foreach ($elements as $key => $value) {
		$subQ .= 'WHEN '.$key.' THEN '.$value. ' ';
	}
	return $subQ;
}

function rowsToAssoc($dbRows, $rName) {
	$dbRes = array();

	while ($row = mysqli_fetch_assoc($dbRows))
	{
		$dbRes[$row[$rName]] = $row;
	}

	return $dbRes;
}



?>