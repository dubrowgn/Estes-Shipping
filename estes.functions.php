<?php

/**
 * Support functions Estes Shipping Module
 * 
 * @author		Dustin Brown <dubrowgn@gmail.com>
 * @link		https://github.com/dubrowgn/Estes-Shipping
 * @license		GPLv3 <http://www.gnu.org/licenses/>
*/

function wpsc_estes_get_meta_key() {
	return WPSC_META_PREFIX . "estes";
} // wpsc_estes_get_meta_key( )

function wpsc_estes_get_product_meta($post_id = null) {
	// cache the estes metadata key
	$metaKey = wpsc_estes_get_meta_key();
	
	// get the current postID if $post_id is null
	if ($post_id === null)
		$post_id = get_the_ID();
	
	// retreive metadata, unwrapping the inner array if successful
	$meta = get_post_meta($post_id, $metaKey);
	if (!empty($meta))
		$meta = $meta[0];
	
	// return whatever we found
	return $meta;
} // wpsc_estes_get_product_meta( )

function wpsc_estes_get_options() {
	return (array)get_option(WPSC_META_PREFIX . "estes_options");
} // wpsc_estes_get_options( )

function wpsc_estes_set_options($options) {
	update_option(WPSC_META_PREFIX . "estes_options", $options);
} // wpsc_estes_set_options( )

function wpsc_estes_is_ltl_in_cart() {
	global $wpsc_cart;
	
	// determine if any items require LTL shipping
	foreach($wpsc_cart->cart_items as $item) {
		// get product metadata
		$meta = wpsc_estes_get_product_meta($item->product_id);
		
		// must the product be shipped LTL?
		if ($meta['isLtl'] === "on")
			return true;
	} // foreach( item )
	
	return false;
} // wpsc_estes_is_ltl_in_cart( )

?>
