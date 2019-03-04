<?php //Create the client object
ini_set('soap.wsdl_cache_enabled', 0);
ini_set('soap.wsdl_cache_ttl', 900);
ini_set('default_socket_timeout', 15);
$soapclient = new SoapClient('http://49.248.24.36:81/dellastore/Product.asmx?wsdl',array(
					'exceptions'=>true,
					'cache_wsdl'=>WSDL_CACHE_NONE,
					'encoding'=>'utf-8'
				));

//Use the functions of the client, the params of the function are in 
//the associative array
$params = array('CategoryName' => 'LIGHTING');
	try {
	$response = $soapclient->Get_ProductList($params);
		echo '<pre>';
		foreach($response as $data) {
			$user1 = json_decode($data);
			foreach($user1 as $user2) {
				print_r($user2);
			}
		}
		echo '</pre>';
	} catch (Exception $e) {
        
        //var_dump($e ->getMessage());
        /*do something*/
    }
?>