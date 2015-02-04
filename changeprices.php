<?php
// переключатель для копии/переноса товаров. False - когда обе таблицы находятся в одной базе. True - когда таблицы находятся в разных базах или на разных серверах.
$remoteDB = false;

if(array_key_exists('change', $_GET) && $_GET['change'] == 'yes') {
	// for items with diff prices
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
	// for items that not exist in site DB
	$moveTo = $_GET['move'];

	if($moveTo != 'go_away2' && $moveTo != 'to_first') {
		echo json_encode(array('error'=>'Выбрана неправильная база данных'));
		exit();
	}

	//Это база товаров 1С
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

	if($remoteDB == true) {
		// на вариант БД на разных серверах - здесь указать данные БД сайта
		$dblocation2 = "localhost";
		$dbname2 = "sitebase";
		$dbuser2 = "root";
		$dbpasswd2 = "";
		$testDb = mysqli_connect($dblocation2,$dbuser2,$dbpasswd2, $dbname2);
		mysqli_query($testDb, "SET NAMES 'utf8'");
		// diffServersDB (dbMoveConnect(ссылка на подключение к БД 1С), id или массив id, таблица-источник данных, ссылка на подключение к БД сайта, таблица-цель сайта)
		$result = diffServersDB($dbMoveConnect, $elements, 'xml1c_all_products', $testDb, $moveTo);
	} else {
		$query = 'INSERT INTO ' .$moveTo.' SELECT * FROM xml1c_all_products where id in (\''.$subQ.'\')';
		$result = mysqli_query($dbMoveConnect, $query);		
	}
	
	if ($result) {
		if($moveTo == 'go_away2') {
			// $query = 'DELETE FROM xml1c_all_products where  id in (\''.$subQ.'\')';
			// $result = mysqli_query($dbMoveConnect, $query);
			// if ($result) {
				echo json_encode(array('elements' => $elements, 'action'=>'remove'));				
			// } else {
				// echo json_encode(array('error'=>'Не удалось удалить скопированные данные'));
			// }
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

function diffServersDB(&$dbMoveConnect, $id, $from, &$dbTo, $to) {
	if (is_array($id)) $whereStr = ' in (\'' .implode($id, "','")  . '\')';
	else $whereStr = ' = ' . $id;

	$result = mysqli_query($dbMoveConnect,'SELECT * FROM '.$from.' WHERE id ' . $whereStr);
	$query = array();

	while ($row = mysqli_fetch_array($result, MYSQL_NUM)) {
		$el = json_decode(str_replace('\'', '"', json_encode($row)));
	    $query[] = '(\''.implode('\',\'', $el).'\')';
	}
	//echo 'INSERT INTO `'.$to.'` VALUES '.implode(',', $query).';';
	return mysqli_query($dbTo,'INSERT INTO `'.$to.'` VALUES '.implode(',', $query).';');
	/*$file = str_replace('\\', '/', dirname(__FILE__).'\testtesttestcomp.txt');
	$whereStr = '';
	if (is_array($id)) $whereStr = ' in (\'' .implode($id, "','")  . '\')';
	else $whereStr = ' = ' . $id;
	echo 'SELECT * INTO OUTFILE \'' . $file . '\' FROM xml1c_all_products WHERE id ' . $whereStr;
	$res = mysqli_query($dbMoveConnect,'SELECT * INTO OUTFILE \'' . $file . '\' FROM xml1c_all_products WHERE id ' . $whereStr);
	echo 555;

	if ($res) {
	echo 555;
		echo 'LOAD DATA LOCAL INFILE \''.$file. '\' INTO TABLE go_away';
		$res2 = mysqli_query($dbTo,'LOAD DATA LOCAL INFILE \''.$file. '\' INTO TABLE go_away');
		if ($res) {
			// unlink($file);
			return true;
		} else {
			return false;
		}
	
	} else {
		return false;
	}*/
}



?>