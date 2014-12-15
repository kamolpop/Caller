<?php 
class Authen{

	private $token;
	public  $error;
	private static $header = array();
/**
 * [__construct]
 */
	public function __construct()
    {
			self::$token = @$_SESSION[Configs::get('token_key')];
    }
/**
 * [chkToken] : check token in authentication server
 * @return [boolean] [true , false]
 */
	public static function chkToken(){

		if(empty(self::$token)){
			$url 		= Configs::get("authHost");
			$auth 		= new CURL($url);
		    if($auth->IsSuccess()){
			    $res_ar 				= json_decode($auth->getResult());
				$httpCode 				= $auth->getHttpCode();
				$_SESSION['auth_time']	= $auth->getTime();
		    	$error_code 	= $res_ar->errno;
			    if( $error_code == 0 && isset($res_ar->token) && !empty($res_ar->token) && property_exists($res_ar,'token') ){
			    	self::setTokenId($res_ar->token);
			    	return true;
			    }else{
			    	return false;
			    }
			}else{
				return false;
			}

		}
	}

/**
 * [setTokenId] : set token key into session
 * @param [string] $token [requested token key]
 */
	private static function setTokenId($token)
	{
		$_SESSION[Configs::get('token_key')] = $token;

	}

}


?>