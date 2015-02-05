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

$dbconnect1 = mysqli_connect($dblocation1,$dbuser1,$dbpasswd1, $dbname1);
if (!$dbconnect1) {
	echo "Не удалось подключиться к базе1";	exit();
}
	
//Это база товаров 1С
$dblocation2 = "localhost";
$dbname2 = "xml1cbase";
$dbuser2 = "root";
$dbpasswd2 = "";

$dbconnect2 = mysqli_connect($dblocation2,$dbuser2,$dbpasswd2, $dbname2);
if (!$dbconnect2) {
	echo "Не удалось подключиться к базе2";	exit();
}


mysqli_query($dbconnect1, "SET NAMES 'utf8'");
mysqli_query($dbconnect2, "SET NAMES 'utf8'");

require_once('add/header.html');

$texts = array();
echo '<h3>Список товаров</h3>';
echo '	<script type="text/javascript" id="js">$(document).ready(function() {
	// вызов плагина
	$("table").tablesorter({
		// устанавливаем сортировку по первой и третьей колонке. по возрастанию
		headers: {0:{sorter:false}}
	});
}); </script> ';

//Ищем все товары на сайте в статусе ВКЛ (active!=NULL)
$query = "SELECT oc_p.*, oc_d.name FROM sitebase.oc_product as oc_p JOIN sitebase.oc_product_description as oc_d ON oc_p.product_id = oc_d.product_id WHERE oc_p.status = 1";
$result = mysqli_query($dbconnect1, $query);

if($result)
{	
	$count=0;
	echo '<div id="search"><input type="text" id="s" placeholder="Поиск"><br><input type="button" id="searchmask" value="Отметить по маске">&nbsp;&nbsp<input type="button" id="clearsearch" value="X"></div><form type="post">';
	echo '<table border="1" width="100%" class="tablesorter" id="myTable">';
	echo '<thead><tr><th><input type="checkbox" id="select_all"></th><th>Наименование</th><th>Цена в прайсе 1С</th><th>Кол-во на складе</th></thead><tbody>';

	$db1Sku = rowsToAssoc($result, 'sku');

	$query_1c = 'SELECT 1c.id, 1c.price,1c.name, 1c.stock_all, 1c.old_del,1c.stock_1_perovo, 1c.code1c FROM xml1cbase.xml1c_all_products as 1c WHERE 1c.code1c IS NOT NULL AND 1c.code1c NOT IN (\''.implode(array_keys($db1Sku), "','").'\') AND 1c.id NOT IN (SELECT id FROM go_away2)';
// echo $query_1c;
	$result_1c = mysqli_query($dbconnect2, $query_1c);
	$frst = mysqli_query($dbconnect2, 'SELECT t.id FROM to_first as t JOIN xml1c_all_products as 1c ON t.id=1c.id');
	$notEmptyQuery = mysqli_num_rows($result_1c)>0 ? true : false;

	if($result_1c && $notEmptyQuery){
		$db_1C = rowsToAssoc($result_1c, 'code1c');
		selDiffPrice('', $db_1C, $texts, rowsToAssoc($frst, 'id'));
	}
	else if(!$notEmptyQuery){
	  echo 'Записей с такими SKU не найдено';
	}	else {
		echo "<p><b>Error: ".mysqli_error()."</b><p>"; 
	  exit();
	}
	echo "</tbody></table>";	

} else {
	echo mysqli_error();
}

mysqli_close($dbconnect1);
echo '<br><input type="submit" name="to_first" id="to_first" value="Выставить в первую очередь"> &nbsp;&nbsp;<select name="go_away2" id="go_away2">';
echo '<option value="group">В группу:</option>';
echo showGroups($dbconnect2);
echo '</select></form><br>';
echo '<script type="text/javascript">var colTexts ='.json_encode($texts).';</script>';
mysqli_close($dbconnect2);
echo "finish";


function selDiffPrice($db, $db1c, &$texts, $frst) {
	foreach ($db1c as $sku => $el_1c) {
			// print_r($db[$sku]);
		$class = '';
		if(isset($texts) && is_array($texts)) $texts[$el_1c['id']] = $el_1c['name'];
		if(isset($frst) &&  array_key_exists($el_1c['id'], $frst) ) $class='highlight';
		// if ($el_1c['price'] != $db[$sku]['price'] && !is_null($db[$sku]['price']) && !is_null($el_1c['price'])) {
			echo '<tr class="elem visible"><td class="'.$class.'"><input type="checkbox" class="checker" name="'.$el_1c['id'].'" id="pr'.$el_1c['id'].'"></td><td class="'.$class.'">'.$el_1c['name'].'</td><td class="oldprice '.$class.'">'.$el_1c['price'].'</td><td class="'.$class.'">'.$el_1c['stock_all'].'</td></tr>' . "\n";	
		// }
	}
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

function rowsToAssoc($dbRows, $rName) {
	$dbRes = array();

	while ($row = mysqli_fetch_assoc($dbRows))
	{
		$dbRes[$row[$rName]] = $row;
	}

	return $dbRes;
}

?>