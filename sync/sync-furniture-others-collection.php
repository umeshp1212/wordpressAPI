<?php error_reporting(E_ERROR); date_default_timezone_set("Asia/Kolkata");
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Sync Collection
 *
 * @package storefront
 */ 
$now=strtotime(date('M j Y g:iA', strtotime("-15 minute")));
$now_sql=strtotime(date('M j Y g:iA', strtotime("-15 minute")));



global $wpdb;
$myrows = $wpdb->get_results( "SELECT * FROM della_ws_synctime where id=7" );
$start_time=$myrows[0]->started_tstamp.'<br>';
$end_time=$myrows[0]->completed_tstamp;

$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set started_tstamp='".$now_sql."' where id=7" );


if(isset($_GET['pull_all'])){
	$start_time=strtotime(date('Jan 1 2000 12:00AM'));
}





$webservice = file_get_contents('http://erpwebservice.della.in:81/dellastore/onlinemglist.aspx?ProcType=10');
preg_match_all("/\[[^\]]*\]/", $webservice, $matches);
$json_data=$matches[0];


$json_data2=$json_data[0];

$web_data=json_decode($json_data2);


$record_count=count($web_data);
$c_counter=1;

//print_r($web_data);
foreach($web_data as $web){
	$web_tstamp=strtotime($web->RTIMESTAMP);
	$unique=true;

if($start_time<$web_tstamp){
	$CollectionCode_args = array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'meta_query' => array(
			 array(
				'key'       => 'DSProdRangeId',
				'value'     => $web->CollectionCode,
				'compare'   => '='
			 )
		)
	);
	
	echo 'collcode'.$web->CollectionCode.'<br/>';
	$Collection=get_terms($CollectionCode_args);
	//print_r($Collection);
	//echo count($Collection);

	if(count($Collection)==0){

		if($web->SubGroupCode==""){

		$SubGroup_args = array(
		        'taxonomy' => 'product_cat',
		        'hide_empty' => false,
		        'meta_query' => array(
		             array(
		                'key'       => 'MainGroupCode',
		                'value'     => $web->MainGroupCode,
		                'compare'   => '='
		             )
		        )
		    );

		    $SubGroup=get_terms($SubGroup_args); // if the current category has parent then if	


		}
		else{
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
		}

		    if(count($SubGroup)>0){
		    	echo 'will insert';
			    $SubGroupID=$SubGroup[0]->term_id;



			    $has_sub_collection = get_terms(
					array(
						'taxonomy' => 'product_cat',
						'parent' => $SubGroupID,
						'hide_empty' => false,
						'name' => 'VIEW ALL COLLECTION'
					)
				);
			  //  echo count($has_main_collection);
			   // print_r($has_main_collection);
				if(count($has_sub_collection)==0){

					$sub_collection = wp_insert_term(
					'VIEW ALL COLLECTION', // the term 
					'product_cat', // the taxonomy
						array(
							'parent' =>$SubGroupID
						)
					);

					$shop_by_collection_id=$sub_collection['term_id'];

				}else{


					 $shop_by_collection_id=$has_sub_collection[0]->term_id;


				}

				//echo $web->Collection_Name;
				//print_r($shop_by_collection_id);
				$CollectionData = wp_insert_term(
				$web->Collection_Name, // the term 
				  'product_cat', // the taxonomy,
				  array('parent'=>$shop_by_collection_id)		  
				); 
				echo 'inserterd';
			}



			print_r($CollectionData);
			//echo $web->CollectionCode;
			$CollectionID = $CollectionData['term_id'];
			
			
			add_term_meta ($CollectionID, '_this_page_header_title_background', $web->CollectionThumbnailImage, $unique);
			add_term_meta ($CollectionID, '_category_image_banner', $web->CollectionBannerImage, $unique);
			add_term_meta ($CollectionID, 'DSProdRangeId', $web->CollectionCode, $unique);
			add_term_meta ($CollectionID, 'E_Store_Collection_Description', $web->E_Store_Collection_Description, $unique);
			add_term_meta ($CollectionID, 'order', $web->CollectionSeqNo, $unique);
			add_term_meta ($CollectionID, 'tstamp', $web_tstamp, $unique);


	}else{

		$CollectionID=$Collection[0]->term_id;
		wp_update_term($CollectionID, 'product_cat', array(
			  'name' => $web->Collection_Name,
			  
			));
		update_term_meta ($CollectionID, '_this_page_header_title_background', $web->CollectionThumbnailImage);
		update_term_meta ($CollectionID, '_category_image_banner', $web->CollectionBannerImage);
		update_term_meta ($CollectionID, 'DSProdRangeId', $web->CollectionCode);
		update_term_meta ($CollectionID, 'E_Store_Collection_Description', $web->E_Store_Collection_Description);
		update_term_meta ($CollectionID, 'order', $web->CollectionSeqNo);
		update_term_meta ($CollectionID, 'tstamp', $web_tstamp);


		//$query = "delete from della_ws_termmeta where meta_key='CategoryCode' and meta_value='".$web->CollectionCode."'";
		//$posts = $wpdb->get_results($query);


	}
	$c_counter++;
	if($c_counter==$record_count){
		 $end=strtotime(date('M j Y g:iA'));
		$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set completed_tstamp='".$end."' where id=7" );
	}

}//time_difference
}
echo 'sync collection';
?>