<?php error_reporting(E_ALL); date_default_timezone_set("Asia/Kolkata");
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Sync furniture others category
 *
 * @package storefront
 */ 

$now=strtotime(date('M j Y g:iA', strtotime("-15 minute")));
$now_sql=strtotime(date('M j Y g:iA', strtotime("-15 minute")));



global $wpdb;
$myrows = $wpdb->get_results( "SELECT * FROM della_ws_synctime where id=6" );
$start_time=$myrows[0]->started_tstamp.'<br>';
$end_time=$myrows[0]->completed_tstamp;

$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set started_tstamp='".$now_sql."' where id=6" );


if(isset($_GET['pull_all'])){
	$start_time=strtotime(date('Jan 1 2000 12:00AM'));
}





$webservice = file_get_contents('http://erpwebservice.della.in:81/dellastore/onlinemglist.aspx?ProcType=8');
preg_match_all("/\[[^\]]*\]/", $webservice, $matches);
$json_data=$matches[0];


$json_data2=$json_data[0];

$web_data=json_decode($json_data2);
//print_r($web_data);

$record_count=count($web_data);
$c_counter=1;


foreach($web_data as $web){
	$web_tstamp=strtotime($web->CGTIMESTAMP);
	$unique=true;
if($start_time<$web_tstamp){
	$CategoryCode_args = array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'meta_query' => array(
			 array(
				'key'       => 'CategoryCode',
				'value'     => $web->CategoryCode,
				'compare'   => '='
			 )
		)
	);

	$Category=get_terms($CategoryCode_args);

	if(count($Category)==0){

			$SubGroup_args = array(
		        'taxonomy' => 'product_cat',
		        'hide_empty' => false,
		        'meta_query' => array(
		             array(
		                'key'       => 'SubGroupCode',
		                'value'     => $web->SubGroupCode,
		                'compare'   => '='
		             )
		        )
		    );

		    $SubGroup=get_terms($SubGroup_args); // if the current category has parent then if
		    if(count($SubGroup)>0){
			    $SubGroupID=$SubGroup[0]->term_id;


				$CategoryData = wp_insert_term(
				$web->Category_Name, // the term 
				  'product_cat', // the taxonomy,
				  array('parent'=>$SubGroupID)		  
				); 
			}else{ //else

				/*$SubGroupData = wp_insert_term(
				$web->Sub_Group_Name, // the term 
				'product_cat' // the taxonomy
				); */


			}




			$CategoryID = $CategoryData['term_id'];
			
			add_term_meta ($CategoryID, '_this_page_header_title_background', $web->CategoryThumbnailImage, $unique);
			add_term_meta ($CategoryID, '_category_image_banner', $web->CategoryBannerImage, $unique);
			add_term_meta ($CategoryID, 'CategoryCode', $web->CategoryCode, $unique);
			add_term_meta ($CategoryID, 'E_Store_Description', $web->E_Store_Description, $unique);
			add_term_meta ($CategoryID, 'order', $web->CategorySeqNo, $unique);
			add_term_meta ($CategoryID, 'tstamp', $web_tstamp, $unique);


	}else{

		$CategoryID=$Category[0]->term_id;
		wp_update_term($CategoryID, 'product_cat', array(
			  'name' => $web->Category_Name,
			  
			));
		echo $CategoryID.' '.$web->CategoryBannerImage.'<br>';
		
		update_term_meta ($CategoryID, '_this_page_header_title_background', $web->CategoryThumbnailImage);
		update_term_meta ($CategoryID, '_category_image_banner', $web->CategoryBannerImage);
		update_term_meta ($CategoryID, 'E_Store_Description', $web->E_Store_Description);
		update_term_meta ($CategoryID, 'order', $web->CategorySeqNo);
		update_term_meta ($CategoryID, 'tstamp', $web_tstamp);


	}

	$c_counter++;
	if($c_counter==$record_count){
		 $end=strtotime(date('M j Y g:iA'));
		$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set completed_tstamp='".$end."' where id=6" );
	}

}//time_difference
}
echo 'sync category';
?>