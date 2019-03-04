<?php error_reporting(E_ERROR); date_default_timezone_set("Asia/Kolkata");
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Sync products
 *
 * @package storefront
 */ 


$now=strtotime(date('M j Y g:iA', strtotime("-15 minute")));
$now_sql=strtotime(date('M j Y g:iA', strtotime("-15 minute")));



global $wpdb;
$myrows = $wpdb->get_results( "SELECT * FROM della_ws_synctime where id=8" );
$start_time=$myrows[0]->started_tstamp;

$end_time=$myrows[0]->completed_tstamp;

$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set started_tstamp='".$now_sql."' where id=8" );


if(isset($_GET['pull_all'])){
	$start_time=strtotime(date('Jan 1 2018 0:00AM'));
	//$start_time=strtotime(date('Sep 26 2018 7:41PM'));
}








$webservice = file_get_contents('http://erpwebservice.della.in:81/dellastore/onlinemglist.aspx?ProcType=12');
preg_match_all("/\[[^\]]*\]/", $webservice, $matches);
$json_data=$matches[0];


$json_data2=$json_data[0];

$web_data=json_decode($json_data2);
//print_r($web_data);

$record_count=count($web_data);
$c_counter=1;


$i=0;
foreach($web_data as $web){
	

	 $prodskucode = $web->ProdSKUCode;
	$web_tstamp=strtotime($web->PTimeStamp);

	if($start_time<$web_tstamp){
		//echo "HOME52457";
	//if($prodskucode=='HOME52457'){
		//echo "enter";
	 $Product_Name = $web->Product_Name;
		




		$query = "SELECT della_ws_posts.ID FROM della_ws_posts join della_ws_postmeta on della_ws_posts.ID=della_ws_postmeta.post_id  WHERE (della_ws_postmeta.Meta_key = '_sku' AND della_ws_postmeta.meta_value ='".$prodskucode."' ) ORDER BY della_ws_postmeta.meta_id DESC limit 1";
		$posts = $wpdb->get_results($query);	
		
		//print_r($posts);
		
		if(count($posts)==0){			
			$product = array(
				'post_title'    => $Product_Name,
				'post_content'  => '',
				'post_status'   => 'publish',
				'post_author'   => $current_user,
				'post_type'     =>'product'
			);		
			$product_ID = wp_insert_post($product);			
			//echo $i.') Product Inserted with SKU: '.$web->ProdSKUCode.'<br>';
				
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
			wp_update_post($product); 
			$product_ID = $posts[0]->ID;
			//echo $i.') Product updated with SKU: '.$web->ProdSKUCode.'<br>';
			//product to update			
		}



		/**/

		///////////////////// Add/update product metadata /////////////////////
			//echo $web->ProdSeqNo.'<br/>';
			$prod_meta['_sku'] = $web->ProdSKUCode;
			$prod_meta['product_description'] = $web->Product_Description;
			$prod_meta['color'] = $web->Product_Color;
			$prod_meta['finish'] = $web->Product_Finish;
			$prod_meta['material'] = $web->Product_Material;
			$prod_meta['size_feet'] = $web->Product_Size_Feet;
			$prod_meta['size_mtr'] = $web->Product_Size_Mtr;
			$prod_meta['UOMName'] = $web->UOMName;
			$prod_meta['_stock'] = $web->StockQty;
			$prod_meta['delivery'] = $web->DeliveryDays;
			$prod_meta['_price'] = $web->Price;		
			$prod_meta['_regular_price'] = $web->Price;		
			//$prod_meta['thumbnail_image'] = $web->ThumbnailImage;		
			//$prod_meta['ThumbnailCaption'] = $web->ThumbnailCaption;		
			//$prod_meta['gallery_image'] = $web->GalleryImage;		
			//$prod_meta['GalleryCaption'] = $web->GalleryCaption;		
			$prod_meta['_visibility'] = 'visible';
			$prod_meta['ProdSeqNo'] = $web->ProdSeqNo;
			$prod_meta['ProdCollectionSeq'] = $web->ProdCollectionSeq;
			//$prod_meta['menu_order'] = $web->ProdSeqNo;
		
			 foreach($prod_meta as $pkey => $pmeta){
				if($pkey=='_stock'){	
					//print_r($pmeta);
					
					if(trim($pmeta)!=""){
						;		
						if(! add_post_meta($product_ID,$pkey,$pmeta,true)){
							update_post_meta($product_ID,$pkey,$pmeta);
						}
						
						add_post_meta( $product_ID, '_stock_status', 'instock', true );
						add_post_meta( $product_ID, '_manage_stock', 'no', true );
					}else{
						add_post_meta($product_ID,$pkey,'',true);
						add_post_meta( $product_ID, '_stock_status', 'instock', true );
						add_post_meta( $product_ID, '_manage_stock', 'no', true );
					}					
				}else if($pkey=='_regular_price'){
					//if(!empty($pmeta)){	
						if(! add_post_meta($product_ID,$pkey,$pmeta,true)){
							update_post_meta($product_ID,$pkey,$pmeta);
						}
					//}			
				}else{
					//if(trim($pmeta)!=""){
						if(! add_post_meta($product_ID,$pkey,$pmeta,true)){
							update_post_meta($product_ID,$pkey,$pmeta);
						}
					//}			
				}			
			}  
		///////////////////// Add/update product metadata /////////////////////
			
			$term_array=array();
			$main_cat_id='';
			$sub_cat_id='';
			$sub_cat_name='';
			$cat_name='';
			
			if(!empty($web->MainGroupCode)){
				$MainGroupCodeArray=array(
					'key'       => 'MainGroupCode',
					'value'     => $web->MainGroupCode,
					'compare'   => '='
				 );				
			}
			$SubGroupCodeArray=array();
			if(!empty($web->SubGroupCode)){
				$SubGroupCodeArray=array(
					'key'       => 'SubGroupCode',
					'value'     => $web->SubGroupCode,
					'compare'   => '='
				 );
			}
			$DSProdCategoryTypeIdArray=array();
			if(!empty($web->DSProdCategoryTypeId)){				
				$DSProdCategoryTypeIdArray=array(
					'key'       => 'CategoryCode',
					'value'     => $web->DSProdCategoryTypeId,
					'compare'   => '='
				 );				
			}
			
			$DSProdRangeIdArray=array();
			if(!empty($web->DSProdRangeId)){				
				$DSProdRangeIdArray=array(
					'key'       => 'DSProdRangeId',
					'value'     => $web->DSProdRangeId,
					'compare'   => '='
				 );				
			}

		//	print_r($DSProdCategoryTypeIdArray);
				 
			$MainGroup_args = array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'meta_query' => array(
					 array(
						'key'       => 'DSProdRangeId',
						'value'     => $web->DSProdRangeId,
						'compare'   => '='
					 )
				)
			);
				 
			$shop_collection_parent=get_terms($MainGroup_args);
			$shop_id = $shop_collection_parent[0]->term_id;	
			$Category_args = array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'meta_query' => array(
					'relation' => 'OR',
					$MainGroupCodeArray,
					$SubGroupCodeArray,
					$DSProdCategoryTypeIdArray,
					$DSProdRangeIdArray
				)
			);
			
			$Category=get_terms($Category_args);
			//print_r($Category_args);
			//print_r($Category);
			$MainGroupID =  $Category[0]->term_id;
			foreach($Category as $cat){				
				array_push($term_array,$cat->term_id);
			}
			array_push($term_array,$shop_id);
			//print_r($term_array);	
				
		$terms2 = get_the_terms($product_ID, 'product_cat');
		//print_r($terms2);
		//echo "<br/><br/>";
		foreach($terms2 as $term){
		    wp_remove_object_terms($product_ID, $term->term_id, 'product_cat');
		}
		///////////////////// for shop by collection name and product connection /////////////////////	
		
		wp_set_object_terms($product_ID, $term_array, 'product_cat', true);



		/**/
		$c_counter++;
	if($c_counter==$record_count){
		 $end=strtotime(date('M j Y g:iA'));
		$myrows = $wpdb->get_results( "UPDATE della_ws_synctime set completed_tstamp='".$end."' where id=8" );
	}

//}
//time_difference
	
}
}
echo 'sync products';
?>