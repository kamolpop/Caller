<?php
namespace Caller\Caller;
// require_once("CoreOperation.class.php");
use Caller\Caller\CoreOperation;
class Caller {
	
	private static $uri;
	private static $method;
	private static $ID;

	public static function call( $method = 'GET' , $uri , $id = '')
	{
		$method = strtoupper($method);
		$method = ($method == 'UPDATE')?"PUT":$method;
		return new CoreOperation($uri,$method,$id);
	}
 	
 	public static function content_flag(){
        $cf     =   Configs::get('content_flag');
        $df     =   Configs::get('data_flag');
        return $cf && $df;
    }

}
 
?>