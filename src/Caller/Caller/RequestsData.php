<?php
namespace Caller\Caller;
class RequestsData {

	private $err_code ;
	private $err_no;
	private $reqTime;
	private $authTime;
	private $raw;
	private $result;

	public function __construct( $url , $method , $input = array() , $protocal){
			
			$err_number 	= 0;
			$ReqSW			= new CURL($url , $method , $input , $protocal);// url , method , input , protocal
		    $result 		= $ReqSW->getResult();
		    $this->raw 		= $result;
		    $httpCode 		= $ReqSW->getHttpCode();
			$header 		= $ReqSW->getHeader();
			$this->reqTime	= $ReqSW->getTime();
		    if($ReqSW->IsSuccess()){
		    	ini_set("memory_limit","256M");
		    	$resdec			= json_decode($result);
		    	@$err_number	= $resdec->header->errno; 

		    	if(!is_object($resdec)){
		    		$err_number = 14;
					$this->err_code = "SERVICE WARNING : ".ErrorResponse::error_message($err_number)."<br/> - URL : ".$url;
		    		$this->err_no 	= $err_number;
		    		return null;
		    	}
		    	if($err_number != 0){
		    		$this->err_code = "SERVICE ERROR : ".ErrorResponse::error_message($err_number)."<br/> - URL : ".$url;
		    		$this->err_no 	= $err_number;
		    	}else{
		    		$this->err_code = "";
		    	}

		    }else{
		    	$this->err_code 	= "HTTP ERROR : ".ErrorResponse::error_message($httpCode)."<br/> - URL : ".$url;
		    	$this->err_no 		= $httpCode;
		    	$result 			= null;
		    }
		    
		    $this->result 	=	$result;
		    return $this;
	}

	public function getData() {
			return $this->result;
	}

	public function getError(){
			return $this->err_no.' '.(($this->err_no!=0)?$this->err_code:"");
	}

	public function getErrorCode(){
			return $this->err_no;
	}

	public function getTime(){
			return $this->reqTime;
	}

	public function getAuthenTime(){
			return $this->authTime;
	}

	public function getRaw(){
			return $this->raw;
	}

	

	

}


?>