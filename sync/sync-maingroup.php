<?php error_reporting(E_ALL); date_default_timezone_set("Asia/Kolkata");
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Sync main group supply solution
 *
 * @package storefront
 */ 


$now=strtotime(date('M j Y g:iA', strtotime("-15 minute")));
$now_sql=strtotime(date('M j Y g:iA', strtotime("-15 minute")));



global $wpdb;
$myrows = $wpdb->get_results( "SELECT * FROM della_ws_synctime where id=2" );
$start_time=$myrows[0]->started_tstamp.'<br>';
$end_time=$myrows[0]->completed_tstamp;

$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set started_tstamp='".$now_sql."' where id=2" );


if(isset($_GET['pull_all'])){
	$start_time=strtotime(date('Jan 1 2000 12:00AM'));
}



$webservice = file_get_contents('http://erpwebservice.della.in:81/dellastore/onlinemglist.aspx?ProcType=2');
preg_match_all("/\[[^\]]*\]/", $webservice, $matches);
$json_data=$matches[0];


$json_data2=$json_data[0];

$c_counter=1;
$web_data=json_decode($json_data2);
$record_count=count($web_data);
//print_r($web_data);
foreach($web_data as $web){
	$web_tstamp=strtotime($web->TIME_STAMP);
	$unique=true;
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

	if(count($MainGroup)==0){

			$Group_args = array(
		        'taxonomy' => 'product_cat',
		        'hide_empty' => false,
		        'meta_query' => array(
		             array(
		                'key'       => 'GroupCode',
		                'value'     => $web->GroupCode,
		                'compare'   => '='
		             )
		        )
		    );

		    $Group=get_terms($Group_args);
		    $GroupID=$Group[0]->term_id;


		$MainGroupData = wp_insert_term(
		$web->Main_Group_Name, // the term 
		  'product_cat', // the taxonomy,
		  array('parent'=>$GroupID)		  
		); 
		$MainGroupID = $MainGroupData['term_id'];
		
		add_term_meta ($MainGroupID, 'MainGroupCode', $web->MainGroupCode, $unique);
		add_term_meta ($MainGroupID, 'order', $web->MainGroupSeqNo, $unique);
		add_term_meta ($MainGroupID, 'tstamp', $web_tstamp, $unique);


	}else{

		$MainGroupID=$MainGroup[0]->term_id;
		wp_update_term($MainGroupID, 'product_cat', array(
			  'name' => $web->Main_Group_Name,
			  
			));
		update_term_meta ($MainGroupID, 'order', $web->MainGroupSeqNo);
		update_term_meta ($MainGroupID, 'tstamp', $web_tstamp);


	}

	$c_counter++;
	if($c_counter==$record_count){
		 $end=strtotime(date('M j Y g:iA'));
		$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set completed_tstamp='".$end."' where id=2" );
	}


	}//time_difference
}

echo 'sync maingroup';

?>66312