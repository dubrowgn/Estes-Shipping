<?php

/**
 * class: pseudo_shipping_module
 *
 * A pseudo shipping module for Wp-e-commerce plugin. It simply always
 * returns an empty list of shipping quotes.
 *
 * @author		Dustin Brown <dubrowgn@gmail.com>
 * @link		https://github.com/dubrowgn/Estes-Shipping
 * @license		GPLv3 <http://www.gnu.org/licenses/>
 */
 
class pseudo_shipping_module {
	var $internal_name;
	var $name;
	var $is_external;
	var $requires_curl;
	var $requires_weight;
	var $needs_zipcode;

	function pseudo_shipping_module () {
		$this->internal_name = "pseudo";
		$this->name = "Pseudo";
		$this->is_external = false;
		$this->requires_curl = false;
		$this->requires_weight = false;
		$this->needs_zipcode = false;

		return true;
	} // pseudo_shipping_module( )
	
	function getName() {
		return $this->name;
	} // getName( )
	
	function getInternalName() {
		return $this->internal_name;
	} // getInternalName( )

	public function getQuote() {
		return array();
	} // getQuote( )
} // class

?>
