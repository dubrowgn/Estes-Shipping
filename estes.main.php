<?php

/**
 * Plugin Name: Wp-e-Commerce Estes Shipping
 * Plugin URI: https://github.com/dubrowgn/Estes-Shipping
 * Description: Estes less-than-load freight module for the WP e-Commerce plugin
 * Version: 1.0.2
 * Date: August 13th, 2013
 * Author: Dustin Brown <dubrowgn@gmail.com>
 * Author URI: 
 */


define('ESTES_FILE_PATH', dirname(__FILE__));

require_once(ESTES_FILE_PATH . '/estes.functions.php');
require_once(ESTES_FILE_PATH . '/class.estes_shipping_module.php');
require_once(ESTES_FILE_PATH . '/class.pseudo_shipping_module.php');


if(is_admin()) {
	
	/* Start of: WordPress Administration */

	/**
	 * Add the estes shipping module to the list of available shipping
	 * modules.
	 */
	function wpsc_estes_shipping_modules() {
		global $wpsc_shipping_modules;
		
		$estes = new estes_shipping_module();
		$wpsc_shipping_modules[$estes->getInternalName()] = $estes;
				
		return $wpsc_shipping_modules;
	} // estes_shipping_modules( )
	add_filter('wpsc_shipping_modules', 'wpsc_estes_shipping_modules');
	
	/**
	 * Add the estes product-specific metadata box to the list of
	 * available product metadata boxes
	 */
	function wpsc_estes_init_meta_box() {
		$pagename = 'wpsc-product';
		$metabox = 'wpsc_estes_meta_box';
		add_meta_box($metabox, __( 'Estes Shipping Settings', 'wpsc_estes' ), $metabox, $pagename, 'normal', 'default' );
	} // wpsc_estes_init_meta_box( )
	add_action('admin_menu', 'wpsc_estes_init_meta_box');

	/**
	 * This function gets called when generating any Wp-e-commerce
	 * product page. It generates the HTML content for the estes
	 * shipping metadata box.
	 */
	function wpsc_estes_meta_box() {
		// cache meta key
		$metaKey = wpsc_estes_get_meta_key();
		
		// cache value key
		$valueKey = "isLtl";
		
		// retrieve estes metadata for product
		$meta = wpsc_estes_get_product_meta();
			
		// output checkbox
		echo "	<input type='hidden' name='meta[" . $metaKey . "][" . $valueKey . "]' value='0' />\n";
		echo "	<input type='checkbox' name='meta[" . $metaKey . "][" . $valueKey . "]' id='wpsc_estes_product_isLtl'" . ($meta[$valueKey] === "on" ? " checked='checked'" : "") . " />\n";
		echo "	<label for='wpsc_estes_product_isLtl'>Product must ship less-than-truckload (LTL) freight</label>\n";
	} // wpsc_estes_meta_box( )

	/* End of: WordPress Administration */
	
} // if
else {
	
	/* Start of: Storefront */
	
	/**
	 * Add the estes shipping module to the list of available shipping
	 * modules. If the cart contains *any* LTL items, all other shipping
	 * modules are replaced with dummy pseudo shipping modules.
	 */
	function wpsc_estes_shipping_modules() {
		global $wpsc_shipping_modules;
		
		// if cart contains LTL items, disable all other shipping methods
		// by replacing them with a pseudo shipping module instance
		if (wpsc_estes_is_ltl_in_cart()) {
			// only need one instance, so cache a new one here
			$pseudo = new pseudo_shipping_module();
			
			// get all the shipping method keys
			$keys = array_keys($wpsc_shipping_modules);
			
			// for each key, replace with pseudo module
			foreach($keys as $key) {
				$wpsc_shipping_modules[$key] = $pseudo;
			} // foreach( key )
		} // if
		
		// always inject estes shipping module
		// if there are no LTL items in the cart, see getQuote()
		$estes = new estes_shipping_module();
		$wpsc_shipping_modules[$estes->getInternalName()] = $estes;
		
		// return the updated list
		return $wpsc_shipping_modules;
	} // estes_shipping_modules( )
	add_filter('wpsc_shipping_modules', 'wpsc_estes_shipping_modules', 103);
	
	function wpsc_estes_no_shipping_options() {
		$estes = new estes_shipping_module();
		
		// complete quote process to generate any error messages;
		$estes->getQuote();
		
		// output any error messages
		foreach($estes->error_messages as $error) {
			echo "<p class='validation-error'>{$error}</p>";
		} // foreach( error )
	} // wpsc_estes_no_shipping_options( )
	add_action('wpsc_before_shipping_of_shopping_cart', 'wpsc_estes_no_shipping_options');
	
	/* End of: Storefront */
	
} // else
?>
