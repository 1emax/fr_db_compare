<?php

/* Текущий скрипт вывода с сайта товаров, у которых цены не совпадают с базой 1С
Базы находятся на разных хостингах
  */

$showGroup = false;


if(array_key_exists('show_group', $_GET) && $_GET['show_group'] != '') $showGroup = $_GET['show_group'];

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

if ($showGroup === false) {
require_once('add/header.html');


echo '<h3>Список товаров</h3>';
echo '	<script type="text/javascript" id="js">$(document).ready(function() {
	// вызов плагина
	$("table").tablesorter({
		// устанавливаем сортировку по первой и третьей колонке. по возрастанию
		headers: {0:{sorter:false}}
	});
}); </script> ';


echo '<select name="show_go_away2" id="show_go_away2">';
echo '<option value="group">Показать группу:</option>';
echo showGroups($dbconnect2);
echo '</select>';
echo ' <input type="button" id="showall" value="Показать всю таблицу"></option>';

echo '<table border="1" width="100%" class="tablesorter" id="myTable">';
echo '<thead><tr><th>Код1с товара</th><th>Наименование</th><th>Цена в прайсе 1С</th><th>Кол-во на сайте</th><th>Група</th></thead><tbody>';
}

if($showGroup !== false) {
	$whereQ = '';
	if($showGroup != 'all') $whereQ = ' WHERE group_id = '  . $showGroup;

	$query = 'SELECT a.*, g.id as gr_id, g.name as gr_name FROM go_away2 as a LEFT JOIN groups as g ON g.id = a.group_id' . $whereQ;
	$result = mysqli_query($dbconnect2, $query);



	if($result)
	{	
		$count=0;
		


		if($result){
			$db1Sku = rowsToAssoc($result, 'id');
			selDiffPrice($db1Sku);
		} else {
			echo "<p><b>Error: ".mysqli_error()."</b><p>"; 
		  exit();
		}

	} else {
		echo mysqli_error($dbconnect2);
	}

}

mysqli_close($dbconnect2);

if ($showGroup === false) {
	echo "</tbody></table>";
	echo "finish";
}


function selDiffPrice($db/*, $db1c*/) {
	// foreach ($db1c as $sku => $el_1c) {
	foreach ($db as $id => $el_1c) {
		// print_r($el_1c); echo '<br>';
		print_r($el_1c);//exit();
			echo '<tr><td>'.$el_1c['code1c'].'</td><td>'.$el_1c['name'].'</td><td class="newprice">'.$el_1c['price'].'</td><td>'.$el_1c['stock_all'].'</td><td class="gr'.$el_1c['gr_id'].'">'.$el_1c['gr_name'].'</td></tr>' . "\n";	
		
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

function showGroups(&$db) {
	$res = mysqli_query($db, 'SELECT DISTINCT id, name from groups');
	$rows = rowsToAssoc($res, 'id');

	$options = '';

	foreach($rows as $row) {
		$options .= '<option value="'.$row['id'].'">'.$row['name'].'</option>' . "\n";
	}

	return $options;
}

?>