<?php 
/*********** Get Access Token *************/
$postdata = http_build_query(
    array (
		'tenant_id'=>'dellagrouponline.onmicrosoft.com',
		'client_id'=>'f156ab73-733c-460b-87c9-14dc7e128c98',
		'client_secret'=>'Vy39jCzxh4CWBBLz92OomhaNwgI+zEjpQtuEeCjM12I=',
		'grant_type'=>'client_credentials',
		'resource'=>'https://dellagroup-devdevaos.sandbox.ax.dynamics.com'
    ) 
);
$opts = array('http' =>
    array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => $postdata
    )
);
$context  = stream_context_create($opts);
$result = file_get_contents('https://login.microsoftonline.com/dellagrouponline.onmicrosoft.com/oauth2/token',false,$context);
//$json_auth=json_encode($result);
$json_auth=json_decode($result,true);
//print_r($json_auth);
$access_type=$json_auth['token_type'];
$access_token=$json_auth['access_token'];
/************ Get Access Token ************/







/*************** Get Data ****************/
$opts = array(
		'http'=>array(
		'method'=>"GET",
		'header'=>"Authorization: ".$access_type.' '.$access_token
	)
);
$context = stream_context_create($opts);
$file = file_get_contents('https://dellagroup-devdevaos.sandbox.ax.dynamics.com/data/RetailKits', false, $context);

$webservice_array = json_decode($file);
echo '<pre>';
print_r($webservice_array);
echo '</pre>';
/*************** Get Data ****************/
?>