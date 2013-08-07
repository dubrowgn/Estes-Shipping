<?php

/**
 * class: pseudo_shipping_module
 *
 * A pseudo shipping module for Wp-e-commerce plugin. It simply always
 * returns an empty list of shipping quotes.
 *
 * @author		Dustin Brown <dubrowgn@gmail.com>
 * @link		
 * @license		GPLv3 <http://www.gnu.org/licenses/>
 */
 
class pseudo_shipping_module {
	public function getQuote() {
		return array();
	} // getQuote( )
} // class

?>
