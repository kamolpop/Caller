<?php
namespace Caller\Caller;
class CacheStore{

	private static $memcache;
	private static $host;
	private static $port;

	public static function connect( $hostname ){

		self::getHost( $hostname );
		if(!is_object(self::$memcache)){
			self::$memcache 	=	new Memcache;
		}
		self::$memcache->addServer(self::$host, self::$port);	

	}

	public static function set( $key , $value ){
		self::$memcache->set( $key , $value );
	}

	public static function get( $key ){
		return self::$memcache->get( $key );
	}

	private static function getHost( $hostname ){

		$detail 		= 	Configs::get('memcache');
		$mcserve 		=	$detail[$hostname];
		self::$host 	=	$mcserve['host'];
		self::$port 	=	$mcserve['port'];

	}

	public static function delete($key){
		self::$memcache->delete($key);
	}

	public static function getAllKeys(){
		self::$memcache->getAllKeys();
	}

}
?>