<?php

//$webservice = file_get_contents('http://49.248.24.36:81/dellastore/ExportMainGroupSubGroup.aspx');
$webservice = file_get_contents('http://erpwebservice.della.in:81/dellastore/ExportMainGroupSubGroup.aspx');
//remove form from the json values to get clean json data
$webservice = substr($webservice, 0, -598);

$webservice_array = json_decode($webservice);

echo 'Total Categories '.count($webservice_array);
echo '<pre>';
print_r($webservice_array);
echo '</pre>';



?>