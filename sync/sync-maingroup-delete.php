<?php error_reporting(E_ERROR); date_default_timezone_set("Asia/Kolkata");
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Sync delete Main group
 *
 * @package storefront
 */ 


$now=strtotime(date('M j Y g:iA', strtotime("-15 minute")));
$now_sql=strtotime(date('M j Y g:iA', strtotime("-15 minute")));



global $wpdb;
$myrows = $wpdb->get_results( "SELECT * FROM della_ws_synctime where id=10" );
$start_time=$myrows[0]->started_tstamp.'<br>';
$end_time=$myrows[0]->completed_tstamp;

$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set started_tstamp='".$now_sql."' where id=10" );


if(isset($_GET['pull_all'])){
	$start_time=strtotime(date('Jan 1 2000 12:00AM'));
}








$webservice = file_get_contents('http://erpwebservice.della.in:81/dellastore/onlinemglist.aspx?ProcType=5');
preg_match_all("/\[[^\]]*\]/", $webservice, $matches);
$json_data=$matches[0];


$json_data2=$json_data[0];

$web_data=json_decode($json_data2);
//print_r($web_data);

$record_count=count($web_data);
$c_counter=1;


$i=0;
foreach($web_data as $web){


	
	$web_tstamp=strtotime($web->MGTIMESTAMP);
	if($start_time<$web_tstamp){
		$MainGroupCode_args = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'meta_query' => array(
				 array(
					'key'       => 'MainGroupCode',
					'value'     => $web->MainGroupCode,
					'compare'   => '='
				 )
			)
		);

		$MainGroup=get_terms($MainGroupCode_args);

		if(count($MainGroup)>0){
			wp_delete_term($MainGroup[0]->term_id,'product_cat');
			$query = "DELETE FROM della_ws_termmeta where term_id='".$MainGroup[0]->term_id."' ";
			$posts = $wpdb->get_results($query);
		}
	}


	
}
echo 'sync delete maingroup';
?>