<?php

/**
 * Support functions Estes Shipping Module
 * 
 * @author		Dustin Brown <dubrowgn@gmail.com>
 * @link		https://github.com/dubrowgn/Estes-Shipping
 * @license		GPLv3 <http://www.gnu.org/licenses/>
*/

/**
 * Dumps the $comment parameter to the page using echo, inside HTML
 * style comments. Useful for viewing debugging information.
 */
function wpsc_estes_comment($comment) {
	echo "\n<!-- {$comment} -->\n";
} // wpsc_estes_comment( )

/**
 * Gets the Estes metadata key.
 */
function wpsc_estes_get_meta_key() {
	return WPSC_META_PREFIX . "estes";
} // wpsc_estes_get_meta_key( )

/**
 * Gets the product metadata for the specified productID (WP postID).
 * If no productID is specified, get_the_ID() is called to pull the
 * productID from context instead.
 */
function wpsc_estes_get_product_meta($post_id = null) {
	// cache the estes metadata key
	$metaKey = wpsc_estes_get_meta_key();
	
	// get the current postID if $post_id is null
	if ($post_id === null)
		$post_id = get_the_ID();
	
	// retreive product metadata
	$meta = get_post_meta($post_id, '_wpsc_product_metadata', true);

	// metadata lookup successful? return estes product metadata
	if (!empty($meta) && !empty($meta[$metaKey]))
		return $meta[$metaKey];

	// attempt legacy lookup
	$meta = get_post_meta($post_id, $metaKey, true);

	// legacy metadata lookup successful? return
	if (!empty($meta))
		return $meta;
	
	// return empty array
	return array();
} // wpsc_estes_get_product_meta( )

/**
 * Gets Estes specific Wordpress options. This is just a wrapper around
 * get_option(), using the correct Estes options key.
 */
function wpsc_estes_get_options() {
	return (array)get_option(WPSC_META_PREFIX . "estes_options");
} // wpsc_estes_get_options( )

/**
 * Updates Estes specific Wordpress options. This is just a wrapper
 * around update_option(), using the correct Estes options key.
 */
function wpsc_estes_set_options($options) {
	update_option(WPSC_META_PREFIX . "estes_options", $options);
} // wpsc_estes_set_options( )

/**
 * Returns true if the cart contains any items marked as 'must be
 * shipped via LTL' in their metadata, false otherwise.
 */
function wpsc_estes_is_ltl_in_cart() {
	global $wpsc_cart;
	
	// determine if any items require LTL shipping
	foreach((array)$wpsc_cart->cart_items as $item) {
		// get product metadata
		$meta = wpsc_estes_get_product_meta($item->product_id);
		
		// must the product be shipped LTL?
		if ($meta['isLtl'] === "on")
			return true;
	} // foreach( item )
	
	return false;
} // wpsc_estes_is_ltl_in_cart( )

/**
 * Gets the value for the specified key, checking $POST first and then
 * $_SESSION. If the given key exists in $POST, the value is cached in
 * $_SESSION. Returns null if neither location contains the given key.
 */
function wpsc_estes_get_cacheable_post_value($key) {
	// first, check POST for $key
	if (isset($_POST[$key])) {
		// update session cache and return value from POST
		$_SESSION["estes_$key"] = $_POST[$key];
		return $_POST[$key];
	} // if
	
	// next, check $_SESSION
	if (isset($_SESSION["estes_$key"])) {
		// return value from session cache
		return $_SESSION["estes_$key"];
	} // if

	// no value found for $key, return null
	return null;
} // wpsc_estes_get_post_value( )

?>
