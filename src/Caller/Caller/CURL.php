<?php
namespace Caller\Caller;
class CURL{

	protected $result;
	protected $httpCode;
	protected $resultHeader;
	protected $header;
	protected $reqTime;
	protected $AuthenTime;

	/**
	 * [__construct]
	 * @param string $url      [full url to call destination service]
	 * @param string $method   [GET , POST , PUT , DELETE]
	 * @param array  $input    [input from input form]
	 * @param string $protocal [http , https]->[default=http]
	 */
	public function __construct($url , $method = 'GET' , $input = array() , $protocal = 'http'){
		//----------Set Header-------------
		$this->setHeader();

		//----------Sending Request-------------------
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($input));
	    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $protocal);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    if (isset($_SERVER["SERVER_NAME"])) {
	    	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["SERVER_NAME"]); 
	    }
		
		//----------Contain Response Data---
		$this->result 			= curl_exec($ch);
		$this->httpCode 		= curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$header_size 			= curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$this->reqTime  		= curl_getinfo($ch, CURLINFO_TOTAL_TIME);	
		
		$this->resultHeader 	= substr($this->result, 0, $header_size);
	}

	public function getResult(){
	    return $this->result;
	}	

	public function getHttpCode(){
		return $this->httpCode;
	}

	public function getHeader(){
		return $this->resultHeader;
	}

	public function getTime(){
		return $this->reqTime;
	}
	/**
	 * [success On success]
	 * @return [type] [description]
	 */
	public function IsSuccess(){
		return ($this->httpCode==200)?true:false;
	}

	/**
	 * [setHeader use for set header value before sending request]
	 */ 
	protected function setHeader(){

		$CLIENTID 			= Configs::get("client_id");
		$PUBLICKEY 			= Configs::get("public_key");

		@$header_authen		= array(
							'Authorization: Jobthai Web service;v1.0',
							'Client-ID: ' . $CLIENTID,
							'Public-Key: ' . $PUBLICKEY,
							'Session-ID: ' . session_id());

		$this->header 	= $header_authen;

	}

}
?>