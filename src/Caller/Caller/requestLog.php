<?php
namespace Caller\Caller;
class requestLog{

	private $log;

	public function __construct($info,$error,$method,$result,$id,$flag){
		$this->writeLog( $info , $error , $method , $result , $id , $flag );
		return $this;
	}

	private function LogDB(){

		$config 		=	Configs::get('LogDB');
		$connection 	=	"mongodb://" . $config['host'];
		$options 		=	array();
		
		if(!empty($config['username'])){
			$options['username']	=	$config['username'];
		}
		if(!empty($config['password'])){
			$options['password']	=	$config['password'];
		}

		return new Mongo($connection , $options);
	}

	public function status(){
		return $this->log;
	}

	protected function writeLog ( $info , $error , $method , $result , $id , $flag ) {

		$info_origin 			=	$info;
		$info['timestamp']		=	date("Y-m-d H:i:s");
		$info['service_error']	=	"Success";
		$info['METHOD'] 		=	empty($method)?$info['METHOD']:$method;
		$info['ID'] 			= 	$id;
		$info['result']	 		=	$result;
		$type 					=	explode("/", $info['SERVICE-NAME']);
		$info['type'] 			= 	$type[0];
		if(isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])){
			$info['sourceURL']		=	$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}else{
			$info['sourceURL'] 		=	'Unknow source';
		}

		$errorlist 				=	array();

		if(!$result)
		{
			$out 				=	"Empty";
			$info['Output'] 	=	"<font style='color:#F00;'>".$out."</font>";
			$errorlist['output_error']	=	$out;
		}

		if($error != 0)
		{
			$err 				=	ErrorResponse::error_message($error);
			$info['service_error']	=	"<font style='color:#F00;'>".$err."</font>";	
			$errorlist['service_runtime_error'] 	=	$err;
		}

		//------- insert into mongoDB
		$timestamp 		= 	new MongoDate();
		$m 				= 	$this->LogDB();
		$db 			=	$m->RequestServiceLog;
		$collection 	= 	$db->RequestLog;
		$dat 			=	array(	
									'timestamp'	=>	$timestamp ,
									 'ID'			=> 	(string)$info['ID'],
									 'status' 		=> 	($result&&$error==0)?((!$flag)?"Hold":"Success"):"Failed" , 
									 'type'			=>	$info['type'],
									 'method'		=>  $info['METHOD'],
									 'route'		=> 	$info['SERVICE-NAME'],
									 'fullURL'		=> 	$info['URL'],
									 'clientID'		=>	$info['CLIENT-ID'],
									 'input'		=>	$info['INPUT']['SIMPLE'],
									 'output'		=>	$info['result'],
									 'error'		=>	$errorlist,
									 'info'			=>	$info_origin
									 );
		
		$collection->insert( $dat );
	    
		//------- send mail

		// if( !$result || $error != 0 ) {			
		// 	if($this->sendmail($info)){
		// 		$this->log = "sent to email";	
		// 	}
		// }
		// $this->log = 'success';
	}



	protected function sendmail($info = ''){
		
		$mail 				= 	new PHPMailer ();
		$mail->From 		= 	"api_info@gmail.com";
		$mail->FromName 	= 	"Jobthai failed request";

		foreach (Configs::get('alert-mail') as $key => $address) {
			$mail->AddAddress ($address , $key);
		}
		

		$mail->Subject 	= "Alert! service request fail";

		//----------- Body ----------------
		$strBody 	 	=	"Failed request service on ".$info['sourceURL']."<br/>";
		$strBody		.=	"<br/>Webservice status : " . $info['service_error'] . "<br/>";
		$strBody		.=	"Response data : " . $info['Output'] . "<br/>";
		$strBody 		.= 	"Service type : " . $info['type'] . "<br/>";
		$strBody 		.= 	"<h4>" . "Time : " . $info['timestamp']  . "</h4>";
		$strBody 		.= 	"Method : " . $info['METHOD'] . "<br/>";
		$strBody 		.= 	"Route : " . $info['SERVICE-NAME'] . "<br/>";
		$strBody 		.= 	"ID : " . $info['ID'] . "<br/>";
		$strBody 		.= 	"Full URL Request : " . $info['URL'] . "<br/>";
		$strBody 		.= 	"Client ID : " . $info['CLIENT-ID'] . "<br/>";
		$strBody 		.= 	"<br/></hr>Input : " . (@$info['INPUT']['RAW']) . "<br/>";
		$strBody 		.= 	"Output : " . $info['result'] . "<br/>";

		//----------- Footer --------------
		$foot 			=	"<br/><hr><h4>API Request Alert</h4>";

		$mail->Body 	= $strBody.$foot;
		$mail->IsHTML(true);
		$mail->IsSMTP();
		$mail->Host 	= 'ssl://smtp.gmail.com';
		$mail->Port 	= 465;
		$mail->SMTPAuth = true;
		$mail->Username = 'api.jobthai.info@gmail.com';
		$mail->Password = 'Th1nkneT';

		if(!$mail->Send()) {
			return false;
		}
		else {
			return true;
		}


	}



}
?>