<?php error_reporting(E_ERROR); date_default_timezone_set("Asia/Kolkata");
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Sync delete Product
 *
 * @package storefront
 */ 


$now=strtotime(date('M j Y g:iA', strtotime("-15 minute")));
$now_sql=strtotime(date('M j Y g:iA', strtotime("-15 minute")));



global $wpdb;
$myrows = $wpdb->get_results( "SELECT * FROM della_ws_synctime where id=14" );
$start_time=$myrows[0]->started_tstamp.'<br>';
$end_time=$myrows[0]->completed_tstamp;

$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set started_tstamp='".$now_sql."' where id=14" );


if(isset($_GET['pull_all'])){
	$start_time=strtotime(date('Jan 1 2000 12:00AM'));
}








$webservice = file_get_contents('http://erpwebservice.della.in:81/dellastore/onlinemglist.aspx?ProcType=13');
preg_match_all("/\[[^\]]*\]/", $webservice, $matches);
$json_data=$matches[0];


$json_data2=$json_data[0];

$web_data=json_decode($json_data2);
//print_r($web_data);

$record_count=count($web_data);
$c_counter=1;


$i=0;
foreach($web_data as $web){

	//echo $web->ProdSKUCode;
	
	$web_tstamp=strtotime($web->PTimeStamp);
	if($start_time<$web_tstamp){
		 $prodskucode = $web->ProdSKUCode;
		$query = "SELECT della_ws_posts.ID FROM della_ws_posts join della_ws_postmeta on della_ws_posts.ID=della_ws_postmeta.post_id  WHERE (della_ws_postmeta.Meta_key = '_sku' AND della_ws_postmeta.meta_value ='".$prodskucode."' ) ORDER BY della_ws_postmeta.meta_id DESC limit 1";
		$posts = $wpdb->get_results($query);	
		
		
		
		if(count($posts)>0){	
				
				wp_delete_post($posts[0]->ID);
				$query = "DELETE FROM della_ws_postmeta where post_id='".$posts[0]->ID."' ";
				$posts = $wpdb->get_results($query);
				
			
		}
	}


	
}
echo 'sync delete product';
?>