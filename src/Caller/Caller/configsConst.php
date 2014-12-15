<?php
/**
 * [Configuration] : all of library config
 */
$ConfigPrefix 	=	"caller::configRequest.";
return array(


		/**
		* webservice request flag
		**/
		'content_flag'		=>	Config::get( $ConfigPrefix.'content_flag' ),
		'data_flag' 		=>	Config::get( $ConfigPrefix.'data_flag' ),


		/**
		 * HTTP is default 
		 */
		
		'protocal' 			=> Config::get( $ConfigPrefix.'protocal' ), 

		/**
		 * Connection default
		 */
		'default_host'		=> Config::get( $ConfigPrefix.'default_host' ),

		/**
		 * Domain name of service request
		 */
		
		'host' 			=>  Config::get( $ConfigPrefix.'host' ), 

		/**
		 * Client ID 
		 */

		'client_id' 	=> Config::get( $ConfigPrefix.'client_id' ), 

		/**
		 * Secret Key use for indentify application. (Keep secret)
		 */

		'public_key' 	=> Config::get( $ConfigPrefix.'public_key' ),

		/**
		 * Authentication server must take full path URL
		 */

		'authHost'		=> Config::get( $ConfigPrefix.'authHost' ),

		'memcache'		=>	Config::get( $ConfigPrefix.'memcache' ),

		/**
		* 	 ----------------- Access log configuration -------------------
		*	|																|
		*	|	1 . mongoDB storage											|
		*	|	2 . send Alert with e-mail									|
		*	|																|
		*	 --------------------------------------------------------------
		* 								|
		* 								V
		*/

		/**
		* MongoDB config
		*/
		'logDebug' 		=>	Config::get( $ConfigPrefix.'logDebug' ),
		'LogDB'			=>	Config::get( $ConfigPrefix.'LogDB' ),

		/**
		 * Alert e-mail config
		 */

		'alert-mail' 	=> Config::get( $ConfigPrefix.'alert-mail' ),

	
		);
		
		

?>