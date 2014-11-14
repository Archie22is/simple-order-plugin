<?php

/*
  Plugin Name: ILogic Catalogue
  Plugin URI: http://www.ilogic.co.za
  Description: Creates Catalogue post types with products
  Version: 1.0
  Author: Archie Makuwa
  Author URI: http://www.aatsol.co.za
 */


//Activation
add_action('init', 'initiate', 10, 2);

add_action('after_setup_theme', 'thumb_support', 99);

register_activation_hook(__FILE__, db_init());
Register_uninstall_hook(__FILE__, db_destroy());


function db_init() {
    global $wpdb;
    $table = $wpdb->prefix . "catalogue_invoices";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        $sql = "CREATE TABLE " . $table .
                " (  
					invoice_id INT NOT NULL AUTO_INCREMENT ,					
					email VARCHAR(255) NULL ,
					name VARCHAR(255) NULL ,
					number VARCHAR(255) NULL ,
					company VARCHAR(255) NULL ,
					product_id INT NULL ,
					product_category VARCHAR(255) NULL ,
					product_name VARCHAR(255) NULL ,
					product_variants LONGTEXT NULL ,
					product_amount INT NULL ,
					date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (invoice_id)
				);";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
	
	
}


function db_destroy() {
    global $wpdb;
    $table = $wpdb->prefix . "catalogue_invoices";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        $sql = "DROP TABLE " . $table;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
	
	
}


function initiate(){
	post_types();
	
	
	add_action('get_header', 'enque_scripts' );

		//AJAX functions
		
		
	add_action('wp_ajax_product_image', 'product_image');
	add_action('wp_ajax_nopriv_product_image', 'product_image');
	//
	add_action('wp_ajax_add_product', 'add_product');
	add_action('wp_ajax_nopriv_add_product', 'add_product');
	//
	add_action('wp_ajax_remove_product', 'remove_product');
	add_action('wp_ajax_nopriv_remove_product', 'remove_product');
	//
	add_action('wp_ajax_check_product', 'check_product');
	add_action('wp_ajax_nopriv_check_product', 'check_product');
	//
	add_action('wp_ajax_send_catalogue', 'send_catalogue');
	add_action('wp_ajax_nopriv_send_catalogue', 'send_catalogue');
	//
	add_action('wp_ajax_load_products', 'load_products');
	add_action('wp_ajax_nopriv_load_products', 'load_products');
		
	
}

function enque_scripts() {
	
	//wp_deregister_script( 'jquery.js' );
	//wp_register_script( 'jquery.js', WP_PLUGIN_URL.'/ilogic-catalogue/js/jquery-1.7.2.min.js');
	//wp_enqueue_script( 'jquery.js' );

	// embed the javascript file that makes the AJAX request
    wp_register_script( 'scripts.js', WP_PLUGIN_URL.'/ilogic-catalogue/js/scripts.js');
    wp_enqueue_script( 'scripts.js' );
	
	wp_register_script( 'jquery.fancybox.js', WP_PLUGIN_URL.'/ilogic-catalogue/js/jquery.fancybox.js');
    wp_enqueue_script( 'jquery.fancybox.js' );
	

	wp_localize_script( 'scripts.js', 'wp_ajax', array( 
		'ajaxurl' => admin_url( 'admin-ajax.php' )
	));
	
	wp_register_style( 'catalogue-style', plugins_url('css/style.css', __FILE__) );
    wp_enqueue_style( 'catalogue-style' );
	
	wp_register_style( 'lightbox-style', plugins_url('js/jquery.fancybox.css', __FILE__) );
    wp_enqueue_style( 'lightbox-style' );
			
}  


function post_types(){
	
 $labels = array(
  'name' => __('Products'),
  'singular_name' => __('Product'),
  'add_new' => __('Add New Product'),
  'add_new_item' => __('Add New Product'),
  'edit_item' => __('Edit Product'),
  'new_item' => __('New Product'),
  'view_item' => __('View Product'),
  'search_items' => __('Search Products'),
  'not_found' =>  __('No Products found'),
  'not_found_in_trash' => __('Nothing found in Trash'),
  'parent' => __( 'Parent Product' ),
  'parent_item_colon' => ''
 );
 
 $args = array(
  'labels' => $labels,
  'public' => true,
  'publicly_queryable' => true,
  'show_ui' => true,
  'query_var' => true,
  'rewrite' => true,
  'capability_type' => 'post',
  'hierarchical' => true,
  'menu_position' => true,
  'exclude_from_search' => false,
  'supports' => array('title','editor','thumbnail'),
  'taxonomies' => array('category')
   ); 
 
 register_post_type( 'product' , $args );
}


function thumb_support(){
	add_theme_support( 'post-thumbnails', array('product') );	 
	set_post_thumbnail_size( 200, 200, true );
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


add_action( 'add_meta_boxes', 'add_product_variants' );
add_action( 'save_post', 'variants_save_postdata' );

function add_product_variants() {
    add_meta_box( 
        'product_variants',
        __( 'Product Variants', 'product_text_ref' ),
        'product_variants_html',
        'product' 
    );
}

function variants_save_postdata( $post_id ){
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){return;}
  if ( !wp_verify_nonce( $_POST['product_nonce'], plugin_basename( __FILE__ ) ) ){return;}
  if ( 'product' == $_POST['post_type'] ){
    if ( !current_user_can( 'edit_post', $post_id ) ){return;}
  }
//  echo '<pre>';
//  print_r($_POST['product_variants']);
//  echo '</pre>';
//  die;
  if(!$_POST['product_variants']){$_POST['product_variants'] = array();}
  	update_post_meta($post_id, 'product_variants', json_encode($_POST['product_variants']));
}

/* Prints the box content */
function product_variants_html( $post ) {
  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'product_nonce' );
  $key_offset = 0;
  // The actual fields for data entry
  echo '<label for="product_variant">Variant</label> ';
  echo '<input type="text" id="product_variant" name="product_variant" value="" size="25" /> ';
  echo '<a id="add_variant_button" href="Javascript:;" class="button">Add</a>';
  echo '<div class="variants_container">';
  
    if(get_post_meta($post->ID, 'product_variants', true)){
	$variants_arr =  json_decode(get_post_meta($post->ID, 'product_variants', true)); 
	foreach($variants_arr as $variant_key => $variant_arr){
		if($variant_key > $key_offset){$key_offset = $variant_key;}	
		//
		$sub_html = '';
		$sub_key = 0;
		foreach($variant_arr as $single_variant_key => $variant){
			if($single_variant_key != 'name'){
				$sub_html .= '
				<div class="variant_val_final">
					'.$variant->name.' &nbsp;&nbsp;&nbsp;&nbsp; 
					<b>Stock:</b> <input type="text" value="'.$variant->stock.'" name="product_variants['.$variant_key.']['.$single_variant_key.'][stock]" />
					<a href="Javascript:;" class="button" onclick="remove_variant_val(this)">Remove</a>
					<input type="hidden" value="'.$variant->name.'" name="product_variants['.$variant_key.']['.$single_variant_key.'][name]" />
				</div>';
				$sub_key++;
			}
		}
		
		echo '<div class="variants_in_container">'.$variant_arr->name.
				'<a href="Javascript:;" class="button" onclick="remove_variant(this)">Remove</a>
				<input class="parent_value" type="hidden" value="'.$variant_arr->name.'" name="product_variants['.$variant_key.']" />
				<div class="variant_val_container">
					<input type="text" size="25" value="" name="product_variant_creator" class="product_variant_creator" >
					<a id="remove_variant_val" href="Javascript:;" class="button" onclick="add_variant_val(this)">Add Value</a>
					<div class="variant_vals_container" id="'.($sub_key++).'">
						<input type="hidden" value="'.$variant_arr->name.'" name="product_variants['.$variant_key.'][name]" />';
						echo $sub_html;	
					echo '</div>
				</div>
			</div>';
		
	}
  }
  
  echo '</div>';
  ?>
   <style>
.variants_in_container {
	display: block;
	border: 1px solid #DFDFDF;
	border-radius: 4px;
	clear: both;
	float: left;
	padding: 10px;
	margin: 5px;
}
.variants_in_container a {
	margin-left: 10px;
	margin-right: 10px;
}
.variants_container {
	overflow: auto;
}
.variant_val_container {
	float: right;
	display: block;
	border: 1px solid #DFDFDF;
	border-radius: 4px;
	margin: 5px;
	padding: 5px;
}
.variant_val_final {
	display: block;
	border: 1px solid #DFDFDF;
	border-radius: 4px;
	clear: both;
	float: left;
	padding: 10px;
	margin: 5px;
}
</style>
   <script>
	function remove_variant(e){
		jQuery(e).parent('div').remove();	
	}
	
	function remove_variant_val(e){
		jQuery(e).parent('.variant_val_final').remove();	
	}
	
	function add_variant_val(e){
		var input_obj_ = jQuery(e).parent('.variant_val_container').children('.product_variant_creator');
		var parent_key = jQuery(e).parent('.variant_val_container').parent('.variants_in_container').children('.parent_value').attr('name');
		var current_container_obj = jQuery(e).parent('.variant_val_container').children('.variant_vals_container');
		var current_key = parseFloat(current_container_obj.attr('id'));
		
		current_container_obj.prepend('\
		<div class="variant_val_final">\
			'+input_obj_.val()+' &nbsp;&nbsp;&nbsp;&nbsp; \
			 <b>Stock:</b> <input type="text" value="" name="'+parent_key+'['+current_container_obj.attr('id')+'][stock]" />\
			 <a href="Javascript:;" class="button" onclick="remove_variant_val(this)">Remove</a>\
			<input type="hidden" value="'+input_obj_.val()+'" name="'+parent_key+'['+current_container_obj.attr('id')+'][name]" />\
		</div>\
		');
		
		current_key++;
		current_container_obj.attr('id', current_key)
		input_obj_.val('');
	}
	
	var key = <?php echo ++$key_offset; ?>;
  	jQuery('#add_variant_button').click(function(){
		jQuery('.variants_container').prepend('\
			<div class="variants_in_container">'+jQuery('#product_variant').val()+' \
				<a href="Javascript:;" class="button" onclick="remove_variant(this)">Remove</a>\
				<input class="parent_value" type="hidden" value="'+jQuery('#product_variant').val()+'" name="product_variants['+key+']" />\
				<div class="variant_val_container">\
					<input type="text" size="25" value="" name="product_variant_creator" class="product_variant_creator" >\
					<a id="remove_variant_val" href="Javascript:;" class="button" onclick="add_variant_val(this)">Add Value</a>\
					<div class="variant_vals_container" id="0">\
						<input type="hidden" value="'+jQuery('#product_variant').val()+'" name="product_variants['+key+'][name]" />\
					</div>\
				</div>\
			</div>');
		key++;
		jQuery('#product_variant').val("");
	})
	</script>
   <?php
}

function get_variants($post_id){
	$return = '';
	if(get_post_meta($post_id, 'product_variants', true)){
		$return .= '<div class="products_variants_container">';
		$variants_arr =  json_decode(get_post_meta($post_id, 'product_variants', true)); 
		//echo '<pre>'.print_r($variants_arr, true).'</pre>';
		foreach($variants_arr as $variant_key => $variant_arr){
			$return .= '<b>'.$variant_arr->name.':</b> ';
			$return .= '<select name="'.$variant_key.'">';
			$return .= '<option value="">-- select --</option>';
			foreach($variant_arr as $single_variant_key => $single_variant_arr){
				if($single_variant_key != 'name'){
					$return .= '<option value="'.$single_variant_key.'" >'.$single_variant_arr->name.'</option>';
				}
			}
			$return .= '</select>';
			$return .= '<br /><br />';
		}
		$return .= '</div>';
	}
	if($return == '' || $return == '<div class="products_variants_container"></div>'){
		$return = '<div class="clear_variant" style="clear:both; height:41px;">&nbsp;</div>';
	}
	return $return;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



add_action( 'add_meta_boxes', 'add_product_fields' );
add_action( 'save_post', 'myplugin_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function add_product_fields() {
    add_meta_box( 
        'product_quantity',
        __( 'Product Quantity Available', 'product_text_ref' ),
        'product_field_html',
        'product' 
    );
}

/* Prints the box content */
function product_field_html( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'product_nonce' );

  // The actual fields for data entry
  echo '<label for="product_quantity">';
       _e("Product stock available (eg. 50)", 'product_text_ref' );
  echo '</label> ';
  echo '<input type="text" id="product_quantity" name="product_quantity" value="'.get_post_meta($post->ID, 'quantity', true).'" size="25" />';
}

/* When the post is saved, saves our custom data */
function myplugin_save_postdata( $post_id ) {
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  if ( !wp_verify_nonce( $_POST['product_nonce'], plugin_basename( __FILE__ ) ) )
      return;

  if ( 'product' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  $mydata = $_POST['product_quantity']; 
  update_post_meta($post_id, 'quantity', $mydata);

}

function get_product_categories(){
	$return = array();
	$args = array(
						'type'                     => 'product',
						'child_of'                 => 0,
						'parent'                   => '',
						'orderby'                  => 'name',
						'order'                    => 'ASC',
						'hide_empty'               => 1,
						'hierarchical'             => 1,
						'exclude'                  => '',
						'include'                  => '',
						'number'                   => '',
						'taxonomy'                 => 'category',
						'pad_counts'               => false );
	
		 $categories = get_categories( $args );
		 
		 foreach($categories as $category){
			
			 if($category->category_parent == 0){
			 	$return[$category->cat_ID] = array('name' => $category->name);
			 }
			 
			 $args_children = array(
						'type'                     => 'product',
						'child_of'                 => 0,
						'parent'                   => $category->cat_ID,
						'orderby'                  => 'name',
						'order'                    => 'ASC',
						'hide_empty'               => 1,
						'hierarchical'             => 1,
						'exclude'                  => '',
						'include'                  => '',
						'number'                   => '',
						'taxonomy'                 => 'category',
						'pad_counts'               => false );
						
			$categories_children = get_categories( $args_children );
			foreach($categories_children as $child_category){
				$return[$category->cat_ID]['children'][$child_category->cat_ID] = array('name' => $child_category->name);
			}
			 
		 }
		 return $return;
}

function setUrlVariables($add_arr = array()) {
	$arg = array();
	$string = "?";
	$vars = $_GET;
	$vars = array_merge($vars, $add_arr); 
	if(isset($vars["page_id"])){unset($vars["page_id"]);$string = "&";}
	for ($i = 0; $i < func_num_args(); $i++){
		if($i != 0){
			$arg[func_get_arg($i)] = func_get_arg(++$i);
		}
	}
	foreach (array_keys($arg) as $key){
		$vars[$key] = $arg[$key];
	}
	foreach (array_keys($vars) as $key){
		if ($vars[$key] != "" && $key != "page_id") $string.= $key . "=" . $vars[$key] . "&";
	}
	if (SID != "" && SID != "SID" && $_GET["PHPSESSID"] == ""){
		$string.= htmlspecialchars(SID) . "&";
	}
	return htmlspecialchars(substr($string, 0, -1));
}


// [catalogue results="6" page="1"]


function show_products( $atts ) {
	$products_arr = array();
	extract( shortcode_atts( array(
		'results' => 'something',
		'page' => 'something else',
	), $atts ) );
	$page = 1;
	if(isset($_GET['products_page'])){
		$page = $_GET['products_page'];
	}
	
	$count_products = wp_count_posts('product');
	
	$args = array(
    'numberposts'     => 10000,
    'offset'          => 0,
    'orderby'         => 'post_date',
    'order'           => 'DESC',
    'post_type'       => 'product',
    'post_status'     => 'publish' );
	
	$posts_array = get_posts( $args );
	$display_cat = 'All Products';
	$display_sub_cat = '';
	foreach($posts_array as $post){
		$category = get_the_category($post->ID); 

		if($category[0]->parent != 0){
			$main_category = get_the_category_by_ID( $category[0]->parent);
			$sub_category = $category[0]->name;
		}else{
			$main_category = $category[0]->name;
			$sub_category = '';
		}
		
			
		if(isset($_GET['products_category'])){ 		$display_cat = $_GET['products_category'];				if($_GET['products_category'] != 		$main_category){ 	continue;	}	}
		if(isset($_GET['products_sub_category'])){ 	$display_sub_cat = 		'<br /><font color="#959595">'.$_GET['products_sub_category'].'<font>';	if($_GET['products_sub_category'] != $sub_category){ 	continue;	}	}
		
			$products_arr[] = array(
				"category" => $main_category, 
				"sub category" => $sub_category,
				"ID" => $post->ID, 
				"title" => $post->post_title,
				"thumbnail" => get_the_post_thumbnail( $post->ID)
			);

	
	}
	
	//echo '<pre>';print_r($products_arr ); echo '</pre>';
	
	$pages_available = ceil(count($products_arr)/$results);
	
	sort($products_arr);
	
	
	$start_category = true;
	$third = 1;
	$third_class = '';
	$return = '<div class="catalogue">';
	
	$limit = $results;
	$offset = ($page-1)*$limit;
	

	
	
	//print_r($products_arr );
	foreach($products_arr as $key => $product){
		if($key >= $offset && $key < ($limit+$offset)){
		
			if($start_category){// start new section
				$start_category = false;							
				$return .= '<span class="cat_title '.$display_cat.'">'.$display_cat.$display_sub_cat.'</span>';
				$return .= '<ul class="catalogue">';
			}
			$set_sub_category = '';
			if(isset($_GET['products_sub_category'])){$set_sub_category = $_GET['products_sub_category']; unset($_GET['products_sub_category']);}
			if(isset($_GET['products_page'])){unset($_GET['products_page']);}
			
			
			if($product['category'] != ''){
				
				$product_link_main  = '<a href="'.get_permalink().setUrlVariables(array('products_category'=>$product['category'])).'">'.$product['category'].'</a>'; 
			}else{
				$product_link_main = '<a href="'.get_permalink().setUrlVariables().'">Uncategorized</a>'; 
			}
			if($product['sub category'] != ''){
				$product_link_sub = ' / '.'<a href="'.get_permalink().setUrlVariables(array('products_category'=>$product['category'], 'products_sub_category'=>$product['sub category'])).'">'.$product['sub category'].'</a>'; 
			}else{
				$product_link_sub = '';
			}
			
			if(isset($set_sub_category)){if($set_sub_category != ''){$_GET['products_sub_category'] = $set_sub_category;}}
			
			if($third == 3){  $third = 0; $third_class = 'last_product_in_row';}else{$third_class = '';}$third++;
				
				$return .= '<li class="'.$third_class.'">';
				$return .=  '<a href="'.get_permalink( $product['ID']).'">';
				$return .=  '<div class="product_image_container">'.$product['thumbnail'].'<img src="'.WP_PLUGIN_URL.'/ilogic-catalogue/images/formforce_image_watermark.png" class="water_mark" /></div>';
				$return .=  '<div class="product_title">'.$product['title'].'</div>' ;
				$return .=  '</a>';
				$return .=  '<div class="product_navigation">'.$product_link_main .$product_link_sub.'</div>';
				$return .=  '<div class="product_quantity_container">Quantity: <input type="text" class="product_input" value="100" /></div>';
				// variants
				$return .=  get_variants($product['ID']);
				$return .= 	'<input type="button" value="ADD TO MY ORDER" class="product_add_to_cart" id="'.$product['ID'].'" />';
				$return .= '</li>';
				
				if($third_class != ''){//add a clearing div
					$return .= '<div class="product_row_clear"></div>';
				}
			}
	}
	$return .= '</ul></div>';
	
	
	
	//pagination....
	$p_i = 1;
	$return .= '<ul class="products_pagination">';
	
	$page_cat = '';
	
	if(isset($_GET['products_category'])){
			$page_cat = '&products_category='.$_GET['products_category'];
	}
	
	if($set_sub_category != ''){
		$page_cat .= '&products_sub_category='.$set_sub_category;
	}
	
	while($p_i <= $pages_available){
		$current_class = '';
		if($p_i == $page){
			$current_class = ' class="current_page" ';
		}
		$return .= '<li>'.'<a '.$current_class.' href="http://formforce.co.za/products/?products_page='.$p_i.$page_cat.'">'.l_zero($p_i).'</a></li>';
		$p_i++;
	}
	$return .= '</ul>';
	return $return;
}

function l_zero($value){
			 if($value < 10){
				 return "0".$value;
			 }else{
				 return $value;
			 }
		 }
add_shortcode( 'catalogue', 'show_products' );

 //________________________________________________________\\
///////////////////////////ajax\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

function product_image(){
	$product_id = $_POST['product_id'];
	$response = array('html' => '');
	
	$response['html'] = get_the_post_thumbnail( $product_id, 'large');
	
	echo json_encode($response);
	die();
}

function get_url_vars($string){
	$return_arr = array();
	$arr = explode('&', $string);
	foreach($arr as $val){
		$start = strpos($val, '[');
		$end = strpos($val, ']');
		$key = substr($val, $start+1, strlen($val)-$end-2 );
		$val_start = strpos($val, '=');
		$val_ = substr($val, $val_start+1);
		if($val_ != ''){
			$return_arr[$key] = $val_;
		}
	}
	return $return_arr;	
}

function check_variants_avail($arr, $product_id, $amount){
	$variants = get_object_vars ( json_decode(get_post_meta($product_id, 'product_variants', true)));
	$available = true;
	foreach($arr as $key => $value){
		if($variants[$key]->{$value}->stock < $amount){
			$available = false;	
		}
	}
	return $available;
} 

function get_variant_names($arr, $product_id){
	$return_arr = array();
	$variants = get_object_vars ( json_decode(get_post_meta($product_id, 'product_variants', true)));
	foreach($arr as $key => $value){
		array_push($return_arr, array("parent" => $variants[$key]->name, "child" => $variants[$key]->{$value}->name));
	}	
	return $return_arr;
}

function add_product(){
	
	$variants_arr = get_url_vars($_POST['variants']);
	$product_id = $_POST['product_id'];
	$amount = $_POST['amount'];
	
	$response = array();
	$amount_available = get_post_meta($product_id, 'quantity', true);
	$category = get_the_category($product_id); 
	
	if(count($variants_arr ) > 0){
		
		$response['available'] = check_variants_avail($variants_arr, $product_id, $amount);
	}else{
		$response['available'] = ($amount > $amount_available ? false : true);
	}

	if($response['available']){
		if(!isset($_SESSION)){session_start();}
		if(!isset($_SESSION['cart'])){$_SESSION['cart'] = array();}	
		
		if($category[0]->cat_name == NULL){$category[0]->cat_name = 'Uncategorized';}
		$_SESSION['cart'][$product_id] = array("amount" => $amount, "category" => $category[0]->cat_name, "name" => get_the_title($product_id), "variants" => get_variant_names($variants_arr,$product_id), "variants_original" => $variants_arr);
		$response['cart'] = $_SESSION['cart'];
	}
	//session_destroy();
	echo json_encode($response);
	die();
}

function load_products(){

	if(!isset($_SESSION)){session_start();}
	if(!isset($_SESSION['cart'])){$_SESSION['cart'] = array();}	
	$response['cart'] = $_SESSION['cart'];
	
	//session_destroy();
	echo json_encode($response);
	die();
}

function remove_product(){
	if(!isset($_SESSION)){session_start();}
	$product_id = $_POST['product_id'];
	$response = array('success');
	unset($_SESSION['cart'][$product_id]);
	echo json_encode($response);
	die();
}

function get_variants_session($product_id){
	if(!isset($_SESSION)){session_start();}
	if(!isset($_SESSION['cart'][$product_id]['variants_original'])){
		return array();
	}else{
		return $_SESSION['cart'][$product_id]['variants_original'];
	}
}

function check_product(){

	$product_id = $_POST['product_id'];
	$amount = $_POST['amount'];
	$response = array();
	$amount_available = get_post_meta($product_id, 'quantity', true);
	$category = get_the_category($product_id); 
	
	//get the variants
	$variants_arr = get_variants_session($product_id);
	//print_r($variants_arr);
	if(count($variants_arr ) > 0){
		$response['available'] = check_variants_avail($variants_arr, $product_id, $amount);
	}else{
		$response['available'] = ($amount > $amount_available ? false : true);
	}
	//
	
	//$response['available'] = ($amount > $amount_available ? false : true);
	
	if($response['available']){
		if(!isset($_SESSION)){session_start();}
		if(!isset($_SESSION['cart'])){$_SESSION['cart'] = array();}	
		if($category[0]->cat_name == NULL){$category[0]->cat_name = 'Uncategorized';}
		$_SESSION['cart'][$product_id] = array("amount" => $amount, "category" => $category[0]->cat_name, "name" => get_the_title($product_id), "variants" => get_variant_names($variants_arr,$product_id), "variants_original" => $variants_arr );
	}
	//session_destroy();
	echo json_encode($response);
	die();
}


function send_catalogue(){
	global $wpdb;
	if(!isset($_SESSION)){session_start();}
	
	$email = $_POST['email'];
	$company = $_POST['company'];
	$name = $_POST['name'];
	$number = $_POST['number'];
	$html = '<p>Please find below order from: <br/> '.$company.'(Name: '.$name.', Number: '.$number.', Email: '.$email.', )</p><table border="1">';
	$html .= '<tr><th>Category</th><th>Name</th><th>Amount</th><th>Variants</th></tr>';
	foreach($_SESSION['cart'] as $product_id => $product){
		$flem = '';
		foreach($product['variants'] as $var){
			$flem .= $var['parent'].': '.$var['child'].'<br>';
		}
		//insert into database as well....
		$wpdb->insert( 
			'wp_catalogue_invoices', array( 
				'email' => $email, 
				'name' => $name, 
				'number' => $number, 
				'company' => $company, 
				'product_id' => $product_id, 
				'product_category' => $product['category'], 
				'product_name' => $product['name'], 
				'product_amount' => $product['amount'],
				'product_variants' => json_encode($product['variants'])
		  	 ) 
		  ); 
		  
		$html .= '<tr>'.'<td>'.$product['category'].'</td>'.'<td>'.$product['name'].'</td>'.'<td>'.$product['amount'].'</td>'.'<td>'.$flem.'</td>'.'</tr>';
	}
	$html .= '</table>';

	
	 //change this to your email.
    $to = "archie@ilogic.co.za";		// recipient email address	
    $from = "orders@formforce.co.za";
    $subject = "Form Force Order";

    $headers  = "From: $from\r\n";
    $headers .= "Content-type: text/html\r\n";
    mail($to, $subject, $html, $headers); 
	session_destroy();
	echo json_encode(array('success'));
	die();
}


?>
