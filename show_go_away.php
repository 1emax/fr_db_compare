<?php

/* Текущий скрипт вывода с сайта товаров, у которых цены не совпадают с базой 1С
Базы находятся на разных хостингах
  */





//Это база товаров 1С
$dblocation2 = "localhost";
$dbname2 = "xml1cbase";
$dbuser2 = "root";
$dbpasswd2 = "";

$dbconnect2 = mysqli_connect($dblocation2,$dbuser2,$dbpasswd2, $dbname2);
if (!$dbconnect2) {
	echo "Не удалось подключиться к базе2";	exit();
}

mysqli_query($dbconnect2, "SET NAMES 'utf8'");

require_once('add/header.html');


echo '<h3>Список товаров</h3>';
echo '	<script type="text/javascript" id="js">$(document).ready(function() {
	// вызов плагина
	$("table").tablesorter({
		// устанавливаем сортировку по первой и третьей колонке. по возрастанию
		sortList: [[2,0]],
		headers: {0:{sorter:false}}
	});
}); </script> ';


$query = 'SELECT * FROM go_away';
$result = mysqli_query($dbconnect2, $query);



if($result)
{	
	$count=0;
	echo '<table border="1" width="100%" class="tablesorter" id="myTable">';
	echo '<thead><tr><th>Код1с товара</th><th>Наименование</th><th>Цена в прайсе 1С</th><th>Кол-во на сайте</th></thead><tbody>';


	if($result){
		$db1Sku = rowsToAssoc($result, 'id');
		selDiffPrice($db1Sku);
	} else {
		echo "<p><b>Error: ".mysqli_error()."</b><p>"; 
	  exit();
	}
	echo "</tbody></table>";	

} else {
	echo mysqli_error();
}

mysqli_close($dbconnect2);
echo "finish";


function selDiffPrice($db/*, $db1c*/) {
	// foreach ($db1c as $sku => $el_1c) {
	foreach ($db as $id => $el_1c) {
		// print_r($el_1c); echo '<br>';
		
			echo '<tr><td>'.$el_1c['code1c'].'</td><td>'.$el_1c['name'].'</td><td class="newprice">'.$el_1c['price'].'</td><td>'.$el_1c['stock_all'].'</td></tr>' . "\n";	
		
	}
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