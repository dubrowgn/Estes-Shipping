<?php

/**
 * class: estes_shipping_module
 *
 * A custom shipping module for the Wp-e-commerce plugin. It allows
 * shipping via Estes less-than-truckload freight. A checkbox is added
 * to product edit pages, under an "Estes Shipping Settings" metadata
 * box. Checking this box flags the product as must be shipped via Estes
 * LTL only. Other shipping options will not be available to the user if
 * the cart contains *any* LTL items. Conversely, Estes shipping will
 * not be available as an option if the cart contrians *no* LTL items.
 *
 * @author		Dustin Brown <dubrowgn@gmail.com>
 * @link		https://github.com/dubrowgn/Estes-Shipping
 * @license		GPLv3 <http://www.gnu.org/licenses/>
 */

class estes_shipping_module {
	var $internal_name;
	var $name;
	var $is_external;
	var $requires_curl;
	var $requires_weight;
	var $needs_zipcode;
	var $error_messages;

	function estes_shipping_module () {
		$this->internal_name = "estes";
		$this->name = "Estes";
		$this->is_external = true;
		$this->requires_curl = true;
		$this->requires_weight = true;
		$this->needs_zipcode = true;
		$this->error_messages = array();

		return true;
	} // estes_shipping_module( )
	
	/* You must always supply this */
	function getName() {
		return $this->name;
	} // getName( )
	
	/* You must always supply this */
	function getInternalName() {
		return $this->internal_name;
	} // getInternalName( )
	
	/* Use this function to return HTML for setting any configuration options for your shipping method
	 * This will appear in the WP E-Commerce admin area under Products > Settings > Shipping
	 *
	 * Whatever you output here will be wrapped inside the right <form> tags, and also
	 * a <table> </table> block
	 */
	public function getForm() {
		$options = wpsc_estes_get_options();
		
		$output .= "<tr>\n";
		$output .= "	<td>Username</td>\n";
		$output .= '	<td><input type="text" name="wpsc_estes_options[username]" value="' . htmlentities($options['username']) . '"></td>' . "\n";
		$output .= "</tr>\n";
		
		$output .= "<tr>\n";
		$output .= "	<td>Password</td>\n";
		$output .= '	<td><input type="password" name="wpsc_estes_options[password]" value="' . htmlentities($options['password']) . '"></td>' . "\n";
		$output .= "</tr>\n";
		
		$output .= "<tr>\n";
		$output .= "	<td>Account Number</td>\n";
		$output .= '	<td><input type="text" name="wpsc_estes_options[account]" value="' . htmlentities($options['account']) . '"></td>' . "\n";
		$output .= "</tr>\n";
		
		$output .= "<tr>\n";
		$output .= "	<td>Origin Country Code (US, AU, UK, etc.)</td>\n";
		$output .= '	<td><input type="text" name="wpsc_estes_options[countryCode]" value="' . htmlentities($options['countryCode']) . '"></td>' . "\n";
		$output .= "</tr>\n";
		
		$output .= "<tr>\n";
		$output .= "	<td>Origin City Name</td>\n";
		$output .= '	<td><input type="text" name="wpsc_estes_options[city]" value="' . htmlentities($options['city']) . '"></td>' . "\n";
		$output .= "</tr>\n";
		
		$output .= "<tr>\n";
		$output .= "	<td>Origin State Code (ID, WY, FL, etc.)</td>\n";
		$output .= '	<td><input type="text" name="wpsc_estes_options[state]" value="' . htmlentities($options['state']) . '"></td>' . "\n";
		$output .= "</tr>\n";
		
		$output .= "<tr>\n";
		$output .= "	<td>Origin Zip Code</td>\n";
		$output .= '	<td><input type="text" name="wpsc_estes_options[zip]" value="' . htmlentities($options['zip']) . '"></td>' . "\n";
		$output .= "</tr>\n";
		
		$output .= "<tr>\n";
		$output .= "	<td>Handling Charge (no dollar sign)</td>\n";
		$output .= '	<td><input type="text" name="wpsc_estes_options[handling]" value="' . htmlentities($options['handling']) . '"></td>' . "\n";
		$output .= "</tr>\n";

		return $output;
	} // getForm( )
	
	/* Use this function to store the settings submitted by the form above
	 * Submitted form data is in $_POST
	 */
	function submit_form() {
		if($_POST['wpsc_estes_options'] != null) {
			$original_options = wpsc_estes_get_options();
			$submitted_options = (array)$_POST['wpsc_estes_options'];
			wpsc_estes_set_options(array_merge($original_options, $submitted_options));
		} // if

		return true;
	} // submit_form( )
	
	/* If there is a per-item shipping charge that applies irrespective of the chosen shipping method
	 * then it should be calculated and returned here. The value returned from this function is used
	 * as-is on the product pages. It is also included in the final cart & checkout figure along
	 * with the results from GetQuote (below)
	 */
	function get_item_shipping(&$cart_item) {
		// get product metadata
		$meta = wpsc_estes_get_product_meta($cart_item->product_id);
		
		// must the product be shipping LTL?
		$isLtl = $meta['isLtl'] === "on";
		
		// if the product must be shipped LTL, add handling charge per each
		if ($isLtl) {
			// make sure the product has a weight
			if (!is_numeric($cart_item->weight) || $cart_item->weight === 0)
				throw new Exception("Product must ship LTL, but has no weight (sku: " . $cart_item->sku . ", name: " . $cart_item->product_name . ")");
			
			// get estes settings
			$options = wpsc_estes_get_options();
			$handling = $options['handling'];
			
			if (!is_numeric($handling))
				throw new Exception("LTL handling charge is not properly configured!");
			
			return (float)$handling * $cart_item->quantity;
		} // if
		
		// product will not be shipped LTL, return $0 handling charge
		return 0;
	} // get_item_shipping( )

	/**
	 * This function returns an Array of possible shipping choices, and associated costs.
	 * This is for the cart in general, per item charges (from get_item_shipping, above)
	 * will be added on as well.
	 */
	function getQuote() {
		// if there are no LTL items in cart, return no quotes available
		if (!wpsc_estes_is_ltl_in_cart())
			return array();
		
		// get country from POST
		$country = wpsc_estes_get_cacheable_post_value('country');

		// get zipcode from POST
		$zipcode = wpsc_estes_get_cacheable_post_value('zipcode');

		// assume address is residential, unless explicitly commercial
		$residential = wpsc_estes_get_cacheable_post_value('residential') !== 'false';
		
		// total cart weight
		$weight = wpsc_cart_weight_total();
		
		// validation
		if (empty($country) || empty($zipcode) || empty($weight))
			return array();
		
		// pull quote info from session if cache is valid to avoid
		// making multiple cURL requests to Estes
		$cache = $_SESSION['wpsc_shipping_cache'][$this->internal_name];
		if ($cache != null && !empty($cache)) {
			if ($cache['country'] === $country &&
				$cache['zipcode'] === $zipcode &&
				$cache['weight'] === $weight &&
				$cache['residential'] === $residential)
			{
				$rates = $cache['rates'];
				if (!empty($rates))
					return $cache['rates'];
			} // if
		} // if
		
		// calculate shipping rates
		$rates = $this->_makeRequest($country, $zipcode, $weight, $residential);
		
		// build cache values
		$values = array('country' => $country, 'zipcode' => $zipcode, 'weight' => $weight, 'rates' => $rates, 'residential' => $residential);
		
		// cache results
		$_SESSION['wpsc_shipping_cache'][$this->internal_name] = $values;
		
		// add an error message if no rates returned
		if (empty($rates))
			$this->error_messages[] = "Sorry! Shipping to your address needs special attention. Please call us to complete your order!";
		
		// return rates
		return $rates;
	} // getQuote( )
	
	/**
	 * Sends request to Estes using cURL
	 * 
	 * @access protected
	 * @return array of response or empty array if response is invalid
	 */
	protected function _makeRequest($country, $zip, $weight, $residential) {
		// get plugin options
		$options = wpsc_estes_get_options();
		
		// create curl object
		$ch = curl_init();
		
		try {
			// build request body
			$requestID = date("Y-m-d H:i:s");
			$class = "100";
			$houseDelivery = $residential ? "<rat1:accessorials><rat1:accessorialCode>HD</rat1:accessorialCode></rat1:accessorials>" : "";
			
			$request = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:rat="http://ws.estesexpress.com/ratequote" xmlns:rat1="http://ws.estesexpress.com/schema/2012/12/ratequote"><soapenv:Header><rat:auth><rat:user>' . $options['username'] . '</rat:user><rat:password>' . $options['password'] . '</rat:password></rat:auth></soapenv:Header><soapenv:Body><rat1:rateRequest><rat1:requestID>' . $requestID . '</rat1:requestID><rat1:account>' . $options['account'] . '</rat1:account><rat1:originPoint><rat1:countryCode>' . $options['countryCode'] . '</rat1:countryCode><rat1:postalCode>' . $options['zip'] . '</rat1:postalCode><rat1:city>' . $options['city'] . '</rat1:city><rat1:stateProvince>' . $options['state'] . '</rat1:stateProvince></rat1:originPoint><rat1:destinationPoint><rat1:countryCode>' . $country . '</rat1:countryCode><rat1:postalCode>' . $zip . '</rat1:postalCode></rat1:destinationPoint><rat1:payor>S</rat1:payor><rat1:terms>PPD</rat1:terms><rat1:stackable>N</rat1:stackable><rat1:baseCommodities><rat1:commodity><rat1:class>' . $class . '</rat1:class><rat1:weight>' . $weight . '</rat1:weight></rat1:commodity></rat1:baseCommodities>' . $houseDelivery . '</rat1:rateRequest></soapenv:Body></soapenv:Envelope>';
			
			// build request headers
			$headers = array(
				'Host: www.estes-express.com',
				'Content-type: text/xml; charset="utf-8"',
				"Accept: text/xml",
				"Cache-Control: no-cache",
				"Pragma: no-cache",
				'SOAPAction: "http://ws.estesexpress.com/ratequote/getQuote"',
				"Content-length: " . strlen($request)
			);
			
			// set curl options
			curl_setopt($ch, CURLOPT_URL, "https://www.estes-express.com/rating/ratequote/services/RateQuoteService?wsdl"); // set url
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return the transfer as a string
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request); // fill request body
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // fill request headers
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ???
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // ???
			curl_setopt($ch, CURLOPT_POST, true); // use POST method, not GET

			// execute request and store reponse in $output
			$output = curl_exec($ch);
			
			// get shipping name
			preg_match('/<rat:serviceLevel>(.*?)<\/rat:serviceLevel>/', $output, $matches);
			$name = $matches[1];
			
			// get shipping price
			preg_match('/<rat:standardPrice>(.*?)<\/rat:standardPrice>/', $output, $matches);
			$cost = 0.0 + $matches[1];
			
			// response validation
			if (empty($name) || empty($cost))
				return array();
			
			// return shipping rate as new array
			return array($name => $cost);
		} // try
		catch(Exception $e) {
			curl_close($ch);
			throw $e;
		} // catch

		// close curl object
		curl_close($ch);

		// return response
		return array();
	} // _makeRequest( )
} // class

?>
