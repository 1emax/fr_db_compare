<?php

/* Текущий скрипт вывода с сайта товаров, у которых цены не совпадают с базой 1С
Базы находятся на разных хостингах
  */

echo "start";

//Это база товаров сайта
$dblocation1 = "localhost";
$dbname1 = "sitebase";
$dbuser1 = "root";
$dbpasswd1 = "";

// $log_file = 'error.log';

$dbconnect1 = mysqli_connect($dblocation1,$dbuser1,$dbpasswd1, $dbname1);
if (!$dbconnect1) {
	echo "Не удалось подключиться к базе1";	exit();
}

// if (!@mysqli_select_db($dbname1, $dbconnect1)) {
// 	echo "Не выбранна база данных1"; 	exit();
// }

//Это база товаров 1С
$dblocation2 = "localhost";
$dbname2 = "xml1cbase";
$dbuser2 = "root";
$dbpasswd2 = "";
// $log_file = 'error.log';
$dbconnect2 = mysqli_connect($dblocation2,$dbuser2,$dbpasswd2, $dbname2);
if (!$dbconnect2) {
	echo "Не удалось подключиться к базе2";	exit();
}

// if (!@mysqli_select_db($dbname2, $dbconnect2)) {
// 	echo "Не выбранна база данных2"; 	exit();
// }


mysqli_query($dbconnect1, "SET NAMES 'utf8'");
mysqli_query($dbconnect2, "SET NAMES 'utf8'");

require_once('add/header.html');


echo '<h3>Список товаров</h3>';
echo '	<script type="text/javascript" id="js">$(document).ready(function() {
	// вызов плагина
	$("table").tablesorter({
		// устанавливаем сортировку по первой и третьей колонке. по возрастанию
		sortList: [[0,0],[2,0]]
	});
}); </script> ';

//Ищем все товары на сайте в статусе ВКЛ (active!=NULL)
$query = "SELECT oc_p.*, oc_d.name FROM sitebase.oc_product as oc_p JOIN sitebase.oc_product_description as oc_d ON oc_p.product_id = oc_d.product_id WHERE oc_p.status = 1";
$result = mysqli_query($dbconnect1, $query);

if($result)
{	
	$count=0;
	echo "<table border=1 width='100%' class='tablesorter' id='myTable'>";
	echo "<thead><tr><th>№</th><th>ID на сайте</th><th>Код1с товара</th><th>Наименование</th><th>Цена на сайте</th><th>Цена в прайсе 1С</th><th>Кол-во на сайте</th></thead><tbody>";


	$db1Sku = rowsToAssoc($result);

	$query_1c = 'SELECT price,old_del,stock_1_perovo FROM xml1cbase.xml1c_all_products WHERE code1c in (\''.implode(array_keys($db1Sku), "','").'\')';
	$result_1c = mysqli_query($dbconnect2, $query_1c);
	$notEmptyQuery = mysqli_num_rows($result_1c)>0 ? true : false;

	if($result_1c && $notEmptyQuery){
		//echo mysqli_num_rows($result_1c) . '<br>';
		$db_1C = rowsToAssoc($result_1c);
		selDiffPrice($db1Sku, $db_1C);
	  // $price1c = mysqli_result($result_1c,0,'price');
	  // $old_del1c = mysqli_result($result_1c,0,'old_del');
	  // $stock1c = mysqli_result($result_1c,0,'stock_1_perovo');
	}
	else if(!$notEmptyQuery){
	  echo 'Записей с такими SKU не найдено';
	}	else {
		echo "<p><b>Error: ".mysqli_error()."</b><p>"; 
	  exit();
	}


	/*
	// 1. Находим текущую цену товара
		$price = $row["price"];
		
		// 2. По коду 1С ищем в xml1cbasefull таблице соответствующие товары и их цены и актуальность
		$query_1c = "SELECT price,old_del,stock_1_perovo FROM xml1cbase.xml1c_all_products WHERE code1c = '".$row["sku"]."'";
		$result_1c = mysqli_query($query_1c, $dbconnect2);
		$notEmptyQuery = mysqli_num_rows($result_1c)>0 ? true : false;
		if($result_1c && $notEmptyQuery){
			//echo mysqli_num_rows($result_1c) . '<br>';
		  $price1c = mysqli_result($result_1c,0,'price');
		  $old_del1c = mysqli_result($result_1c,0,'old_del');
		  $stock1c = mysqli_result($result_1c,0,'stock_1_perovo');
		}
		else if(!$notEmptyQuery){
		  //
		}	else {
			echo "<p><b>Error: ".mysqli_error()."</b><p>"; 
		  exit();
		}	
		
		// 3. Если цены не совпадают на сайте и файле 1с - выводим код1С товаров
		if ($price != $price1c) {
			// 3. По ID товара находим его название в базе сайта
			$query_name = "SELECT name FROM sitebase.oc_product_description WHERE product_id = '".$row["product_id"]."'";
			$result_name = mysqli_query($query_name, $dbconnect1);
			if($result_name){
			  $name = mysqli_result($result_name,0,'name');
			}
			else {
			  echo "<p><b>Error: ".mysqli_error()."</b><p>"; exit();
			}			
		
			$count++;
			//echo "<table border=1 width='100%' class='tablesorter' id='myTable'>";
			//echo "<thead><tr><th>ID на сайте</th><th>Код1с товара</th><th>Цена на сайте 03M</th><th>Цена в прайсе 1С</th><th>Кол-во на сайте</th></thead><tbody>";
			echo "<tr><td>$count</td><td>".$row["product_id"]."</td><td>".$row["sku"]."</td><td>".$name."</td><td>".$price."</td><td>".$price1c."</td><td>".$row["quantity"]."</td></tr>";	
			//echo "</tbody></table>";	
			
		}
	*/







	echo "</tbody></table>";	

}
else {
echo mysqli_error();
}

mysqli_close($dbconnect1);
mysqli_close($dbconnect2);
echo "finish";


function selDiffPrice($db, $db1c) {
	foreach ($db1c as $sku => $el) {
			print_r($db[$sku]);

		if ($el_1c['price'] != $db[$sku]['price']) {
			//echo '<tr><input type="checkbox" name="'.$db[$sku]['product_id'].'"></td><td>'.$db[$sku]['name'].'</td><td>'.$db[$sku]['sku'].'</td><td>'.$db[$sku]['price'].'</td><td>'.$el_1c['price'].'</td><td>'.$db[$sku]['quantity'].'</td></tr>';	
		}
	}
}

function rowsToAssoc($dbRows) {
	$dbRes = array();

	while ($row = mysqli_fetch_assoc($dbRows))
	{
		$dbRes[$row['sku']] = $row;
	}

	return $dbRes;
}

?>