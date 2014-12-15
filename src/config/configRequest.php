<?php
/**
 * [Configuration] : all of library config
 */
return array(


		/**
		* webservice request flag
		**/
		'content_flag'		=>	true ,
		'data_flag' 		=>	true ,


		/**
		 * HTTP is default 
		 */
		
		'protocal' 			=> 'http', 

		/**
		 * Connection default
		 */
		'default_host'		=> 'devSite',

		/**
		 * Domain name of service request
		 */
		
		'host' 			=>  array(
									'productionSite'	=>	'example.com' ,

									'testSite'			=>	'test.example.com' ,

									'devSite'			=>	'dev.example.com'
								), 

		/**
		 * Client ID 
		 */

		'client_id' 	=> '--- Client ID ---',

		/**
		 * Secret Key use for indentify application. (Keep secret)
		 */

		'public_key' 	=> '--- Public Key ---', 

		/**
		 * Authentication server must take full path URL
		 */

		'authHost'		=> 'http://auth.com/authen',

		'memcache'	=>	array( 
								"server1" 	=>	array(

														"host" 		=> 	"192.168.101.1" ,

														"port"		=>	"11410" ,
													),
							),

		
		/**
		* 	 ----------------- Access log configuration --------------------
		*	|																|
		*	|	1 . mongoDB storage											|
		*	|	2 . send Alert with e-mail									|
		*	|	3 . Enable domain list										|
		*	|																|
		*	 ---------------------------------------------------------------
		*/

		/**
		* MongoDB config
		*/
		'logDebug' 	=>	false,
		'LogDB'		=>	array( 
								"host" 		=> 	"localhost" ,

								"port"		=>	"27017" ,
								
								"username"	=> 	"" ,
								
								"password"	=>	"" ,
							),

		/**
		 * Alert e-mail config
		 */

		'alert-mail' 	=> array(

									"mail1" 		=>	"example@email.com" , 

								),

		
	);

?>