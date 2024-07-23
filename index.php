<?php
/**
 * Plugin Name: Shipping rules 
 * Description: hello
 * Version: 1.0
 * Author: Rupom
 * Text Domain: shp
 * 
 */

define('SHP_DEBUG',true);
define('SHP_VERSION', '1.0.0');
define('SHP_PATH', plugin_dir_path(__FILE__));
define('SHP_URL',plugin_dir_url(__FILE__));
include SHP_PATH . 'shipping_method.php';
function shp_script_callback(){
    $version = SHP_DEBUG ? time() : SHP_VERSION ;
    wp_enqueue_style( 'font_awesome_css', SHP_URL.'assets/css/all.min.css' , false ,$version);
    wp_enqueue_style( 'custom_css', SHP_URL.'assets/css/style.css' , false ,$version);
    wp_enqueue_script( 'shp_main_js', SHP_URL. 'assets/js/shp-main.js', array('jquery'), $version, true);
    // Fetch all published products
    $products = wc_get_products(array(
        'limit' => -1,
        'status' => 'publish'
    ));
    $product_data = array();   //send to js
    foreach ($products as $product) {
        $product_data[] = array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
        );
    }
    $categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false
    ));
    $category_data = array();  //send to js
    foreach ($categories as $category) {
        $category_data[] = array(
            'id' => $category->term_id,
            'name' => $category->name,
        );
    }
    wp_localize_script('shp_main_js', 'dsc_localize_data', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'products' => $product_data,
        'categories' => $category_data,
    ));
}
add_action( 'admin_enqueue_scripts', 'shp_script_callback' );
function shp_admin_menu_callback(){
    add_menu_page( 'shipping-rules', __( 'Shipping Rules', 'shp' ), 'manage_options', 'shipping_rules','shipping_rules_callback',false , 26 );
}
add_action( 'admin_menu', 'shp_admin_menu_callback');
function shipping_rules_callback(){
    include SHP_PATH . 'templates/shipping-rules.php';
}
// apply shipping for product and category 
// add_action('woocommerce_cart_calculate_fees','apply_shippings_callback', 10, 1);
function apply_shippings_callback($cart) {
    $shipping_rows = get_option( 'shipping_fields');
    $product_shippings = array();
    $category_shippings = array();
    $max_amount_shippings = array();
    foreach($shipping_rows as $row_key => $row_value){
        if($row_value['select_shipping'] == 'product_shipping'){
            $product_shippings[$row_value['shipping_item']['product_shipping']] = $row_value['shipping'];
        }elseif($row_value['select_shipping'] == 'category_shipping'){
            $category_shippings[$row_value['shipping_item']['category_shipping']] = $row_value['shipping'];
        }elseif($row_value['select_shipping'] == 'cart_amount_shipping'){
            $max_amount_shippings[$row_value['shipping_item']['cart_amount_shipping']] = $row_value['shipping'];
        }
    }
    $total_shipping = 0;
    foreach($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $product_id = $product->get_id();
        if ($product->is_type('variation')) {
            $product_id = $product->get_parent_id();
        }
        $categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
        // the product shipping
        if(isset($product_shippings[$product_id])) {
            $total_shipping += $product_shippings[$product_id] * $cart_item['quantity'];
        }else{
            foreach ($categories as $category_id) {
                if (isset($category_shippings[$category_id])) {
                    $total_shipping += $category_shippings[$category_id] * $cart_item['quantity'];
                    break;
                }
            }
        }
    }
    // Check cart total min
    $cart_total = $cart->get_cart_contents_total();
    foreach($max_amount_shippings as $max_amount => $shipping_amount){
        if ($cart_total >= $max_amount) {
            $total_shipping += intval($shipping_amount);
        }
    }
    // print_r($cart_total);
    if ($total_shipping > 0) {
        $cart->add_fee(__('Custom shipping', 'wc'), -$total_shipping);
    }
}