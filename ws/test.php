<?php
$ch = curl_init ( 'https://login.microsoftonline.com/dellagrouponline.onmicrosoft.com/oauth2/token' );
curl_setopt_array ( $ch, array (
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => array (
               'tenant_id'=>'dellagrouponline.onmicrosoft.com',
	'client_id'=>'f156ab73-733c-460b-87c9-14dc7e128c98',
    'client_secret'=>'Vy39jCzxh4CWBBLz92OomhaNwgI+zEjpQtuEeCjM12I=',
    'grant_type'=>'client_credentials',
    'resource'=>'https://dellagroup-devdevaos.sandbox.ax.dynamics.com'
        ) 
) );
//$resp=curl_exec($ch);
//$resp_decode=json_encode($resp);
//echo $resp_decode->access_token;
$content = curl_exec($ch);
$myArray = $content;
//$nnn=(string)substr($myArray, 0, -1);
//$status = curl_getinfo($ch);
curl_close($ch);
//print_r(json_decode(trim($nnn)));
//$abc=json_ecode($nnn);
//echo $abc;
//print_r($abc);
//echo $status;
/* 
$ch2 = curl_init ( 'https://dellagroup-devdevaos.sandbox.ax.dynamics.com/data/ProductMasterColors' );
curl_setopt_array ( $ch2, array (
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => array (
             'access_token'=>''
        ) 
) );
$resp=curl_exec($ch2); */
 ?>