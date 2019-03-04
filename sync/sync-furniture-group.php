<?php error_reporting(E_ALL); date_default_timezone_set("Asia/Kolkata");
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Sync furniture group
 *
 * @package storefront
 */ 

$now=strtotime(date('M j Y g:iA', strtotime("-15 minute")));
$now_sql=strtotime(date('M j Y g:iA', strtotime("-15 minute")));



global $wpdb;
$myrows = $wpdb->get_results( "SELECT * FROM della_ws_synctime where id=4" );
$start_time=$myrows[0]->started_tstamp.'<br>';
$end_time=$myrows[0]->completed_tstamp;

$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set started_tstamp='".$now_sql."' where id=4" );


if(isset($_GET['pull_all'])){
	$start_time=strtotime(date('Jan 1 2000 12:00AM'));
}


$webservice = file_get_contents('http://erpwebservice.della.in:81/dellastore/onlinemglist.aspx?ProcType=3');
preg_match_all("/\[[^\]]*\]/", $webservice, $matches);
$json_data=$matches[0];


$json_data2=$json_data[0];

$web_data=json_decode($json_data2);
//print_r($web_data);

$record_count=count($web_data);
$c_counter=1;


foreach($web_data as $web){
	$web_tstamp=strtotime($web->TIME_STAMP);
	$unique=true;
if($start_time<$web_tstamp){
	$GroupCode_args = array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'meta_query' => array(
			 array(
				'key'       => 'GroupCode',
				'value'     => $web->GroupCode,
				'compare'   => '='
			 )
		)
	);

	$Group=get_terms($GroupCode_args);

	if(count($Group)==0){

		$GroupData = wp_insert_term(
		$web->Group_Name, // the term 
		  'product_cat' // the taxonomy		  
		); 
		$GroupID = $GroupData['term_id'];
		
		add_term_meta ($GroupID, 'GroupCode', $web->GroupCode, $unique);
		add_term_meta ($GroupID, 'order', $web->SeqNo, $unique);
		add_term_meta ($GroupID, 'tstamp', $web_tstamp, $unique);


	}else{

		$GroupID=$Group[0]->term_id;
		wp_update_term($GroupID, 'product_cat', array(
			  'name' => $web->Group_Name,
			  
			));
		update_term_meta ($GroupID, 'order', $web->SeqNo);
		update_term_meta ($GroupID, 'tstamp', $web_tstamp);


	}
	$c_counter++;
	if($c_counter==$record_count){
		 $end=strtotime(date('M j Y g:iA'));
		$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set completed_tstamp='".$end."' where id=4" );
	}

}//time_difference
}
echo 'sync furniture group';
?>