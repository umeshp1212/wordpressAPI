<?php

/* $webservice = file_get_contents('http://erpwebservice.della.in:81/dellastore/exportproductmaster.aspx');
$webservice = substr($webservice, 0, -313);
$webservice_array = json_decode($webservice); */
//print_r($webservice); exit;

$webservice = file_get_contents('http://www.della.in/ws/product-data.php');
print_r($webservice); exit; 
$webservice_array = json_decode($webservice);

echo 'Total products '.count($webservice_array);
echo '<pre>';
	print_r($webservice_array);
echo '</pre>';

?>