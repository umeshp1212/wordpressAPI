<?php error_reporting(E_ALL);
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
$webservice = file_get_contents('http://erpwebservice.della.in:81/dellastore/onlinemglist.aspx?ProcType=8');
preg_match_all("/\[[^\]]*\]/", $webservice, $matches);
$json_data=$matches[0];


$json_data2=$json_data[0];

$web_data=json_decode($json_data2);
//print_r($web_data);
foreach($web_data as $web){
	$web_tstamp=strtotime($web->CGTIMESTAMP);
	$unique=true;

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
			add_term_meta ($CategoryID, 'E_Store_Collection_Description', $web->E_Store_Collection_Description, $unique);
			add_term_meta ($CategoryID, 'order', $web->CategorySeqNo, $unique);
			add_term_meta ($CategoryID, 'tstamp', $web_tstamp, $unique);


	}else{

		$CategoryID=$SubGroup[0]->term_id;
		update_term_meta ($CategoryID, '_this_page_header_title_background', $web->CategoryThumbnailImage);
		update_term_meta ($CategoryID, '_category_image_banner', $web->CategoryBannerImage);
		add_term_meta ($CategoryID, 'E_Store_Collection_Description', $web->E_Store_Collection_Description, $unique);
		update_term_meta ($CategoryID, 'order', $web->CategorySeqNo);
		update_term_meta ($CategoryID, 'tstamp', $web_tstamp);


	}
}

?>