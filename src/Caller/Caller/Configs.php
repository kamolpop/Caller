<?php
namespace Caller\Caller;
/**
 * [Config Class] : config adapter
 */
class Configs {

	public static $config;
	
/**
 * [Use for get value of config parameter]
 * @param  [String] $index [index of config array]
 * @return [String]        [description]
 */
	public static function get($index)
	{
		if(empty(self::$config)){
			self::$config = include("configsConst.php");
			self::$config['token_key'] = '_jobthai_token='.self::$config["client_id"];
		}
		
		if( strpos( $index , 'default')!==false ){
			$conf 	=	self::$config[preg_replace("/^(default_)/", "" , $index)];
			$conf 	=	$conf[self::$config[$index]];
		}else{
			$conf 	=	self::$config[$index];
		}

		return $conf;
	}

	private static function is_session_started()
	{
	    if ( php_sapi_name() !== 'cli' ) {
	        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
	            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
	        } else {
	            return session_id() === '' ? FALSE : TRUE;
	        }
	    }
	    return FALSE;
	}

	public static function startSession(){
		if(!self::is_session_started()){
			@session_start();
			return TRUE;
			
		}else{

			return FALSE;
		}
	}

}
?>