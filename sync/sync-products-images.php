<?php error_reporting(E_ERROR);  date_default_timezone_set("Asia/Kolkata");
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Sync products images
 *
 * @package storefront
 */ 

$now=strtotime(date('M j Y g:iA', strtotime("-15 minute")));
$now_sql=strtotime(date('M j Y g:iA', strtotime("-15 minute")));



global $wpdb;
$myrows = $wpdb->get_results( "SELECT * FROM della_ws_synctime where id=8" );
$start_time=$myrows[0]->started_tstamp.'<br>';
$end_time=$myrows[0]->completed_tstamp;

$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set started_tstamp='".$now_sql."' where id=9" );


if(isset($_GET['pull_all'])){
	$start_time=strtotime(date('Jan 1 2000 12:00AM'));
}








$webservice = file_get_contents('http://erpwebservice.della.in:81/dellastore/onlinemglist.aspx?ProcType=14');
preg_match_all("/\[[^\]]*\]/", $webservice, $matches);
$json_data=$matches[0];


$json_data2=$json_data[0];

$web_data=json_decode($json_data2);
//print_r($web_data);
$record_count=count($web_data);
$c_counter=1;



$i=0;
foreach($web_data as $web){
	

	$prodskucode = $web->SKUCode;
	$web_tstamp=strtotime($web->PITimeStamp);
	if($start_time<$web_tstamp){ 
		
		$query = "SELECT della_ws_posts.ID FROM della_ws_posts join della_ws_postmeta on della_ws_posts.ID=della_ws_postmeta.post_id  WHERE (della_ws_postmeta.Meta_key = '_sku' AND della_ws_postmeta.meta_value ='".$prodskucode."' ) ORDER BY della_ws_postmeta.meta_id DESC limit 1";
		$posts = $wpdb->get_results($query);	
		
		//print_r($posts);
		
		if(count($posts)==0){			
				
		}else{
			//print_r($posts);
			$product = array(
				'ID'            => $posts[0]->ID,
				'post_title'    => $Product_Name,
				'post_content'  => '',
				'post_status'   => 'publish',
				'post_author'   => $current_user,
				'post_type'     =>'product'
			);		
			//wp_update_post($product); 
			$product_ID = $posts[0]->ID;
			
			//product to update			
		



		/**/

		///////////////////// Add/update product metadata /////////////////////
				
			//$prod_meta['thumbnail_image'] = $web->ThumbnailImage;		
			//$prod_meta['ThumbnailCaption'] = $web->ThumbnailCaption;		
			//$prod_meta['gallery_image'] = $web->GalleryImage;		
			//$prod_meta['GalleryCaption'] = $web->GalleryCaption;	

			if($web->ImageType=='Gallery'){
				//$prod_meta['gallery_image'] = $web->ImageLink;
				echo $web->ImageLink;
				$query2 = "SELECT * FROM della_ws_postmeta where meta_key='gallery_image' and meta_value='".$web->ImageLink."' ";
						$gall_img = $wpdb->get_results($query2);
						print_r($gall_img);
				if(count($gall_img)==0){
					add_post_meta($product_ID,'gallery_image',$web->ImageLink);
				}		
				
							
						
						//echo $i.') gallery  with SKU: '.$prodskucode.'<br>';
			}

			if($web->ImageType=='Thumbnail'){
				//$prod_meta['thumbnail_image'] = $web->ImageLink;
				if(! add_post_meta($product_ID,'thumbnail_image',$web->ImageLink,true)){
							update_post_meta($product_ID,'thumbnail_image',$web->ImageLink);
						}
						//echo $i.') thumbnail  with SKU: '.$prodskucode.'<br>';
			}	
			
			 /*foreach($prod_meta as $pkey => $pmeta){
				
					if(!empty($pmeta)){	
						
					}			
							
			}  */
		///////////////////// Add/update product metadata /////////////////////

			}
		/**/
		$c_counter++;
	if($c_counter==$record_count){
		 $end=strtotime(date('M j Y g:iA'));
		$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set completed_tstamp='".$end."' where id=8" );
	}

}//time_difference
	
}
echo 'sync products images';
?>