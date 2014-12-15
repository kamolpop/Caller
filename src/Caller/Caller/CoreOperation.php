<?php
namespace Caller\Caller;
use Caller\Caller\Configs;
use Caller\Caller\ErrorResponse;
use Caller\Caller\RequestsData;
use Caller\Caller\CURL;
use Caller\Caller\requestLog;
use Caller\Caller\CacheStore;
use Caller\Caller\RouteList;

class CoreOperation {
       
    private $_param         = array();
    private $_data          = array();
    private $input          = array();
    private $file_data      = array();
    private $_host; 
    private $request_time;
    private $err;
    private $error; 
    private $rawResponse;
    private $service;
    private $_ID;
    private $method;
    private $result;
    private $protocal; 
    private $totalTime;
    private $headError;
    private $atr = false;
    private $log;
    private $content_flag;
    private $data_flag;


    /**
     * [__construct Check token before sending request]
     * @param [type] $service [route & prefix]
     * @param [type] $method  [method]
     * @param string $ID      []
     */
    public function __construct( $service , $method , $ID = '' )
    {  
        Configs::startSession();
        $this->_host    = Configs::get('default_host');
        $this->_ID      = $ID;
        $this->service  = $service;
        $this->method   = $method;
        $this->content_flag     =   Configs::get('content_flag');
        $this->data_flag        =   Configs::get('data_flag');
    }

    public function setParams($param , $value = '')
    {
        $this->makeArray('param', $param , $value);
        return $this;
    }

    public function setInput( $data = '' , $value = '' ){

        if($this->method == 'PUT' && empty($this->input['_method'])){
            $this->method            = 'POST';
            $this->_data['_method']  = 'PUT';
        }

        $this->makeArray('data' , $data , $value);
        $this->input = $this->_data;

        return $this;
    }

    public function getInput($json = false){
        $input = array_merge($this->_data); // copy array _data
        unset($input['_method']);
        if($json){
            return json_encode($input);
        }else{
            return $input;
        }
    }

    private function makeArray( $type , $data , $value ){
        if(is_array($data)){
            foreach ($data as $key => $val) {

                if($type=='data' ){ $this->_data[$key] = $val; }
                if($type=='param'){ $this->_param[$key] = $val; }
            }
        }else{

            if($type=='data' ){ $this->_data[$data] = $value;}
            if($type=='param'){ $this->_param[$data] = $value;} 
        }
    } 


    public function IsFiles($f){
        
        foreach ($f as $key => $value) {
            if(isset($f[$key]['tmp_name'])&&isset($f[$key]['type'])&&isset($f[$key]['size']))
            {
                return true;
            }
        }
        return false;
    }
    /**
     * [upload any files by get_content]
     * @param  array  $files 
     * @return this object        
     */


    public function upload($files=array())
    {
        
        $files=(empty($files))?$_FILES:$files;

        if(empty($files)){
            $files  =   $_FILES;
        }else{
            $fls    =   array();
            array_push($fls, $files);
        }

        if(!$this->IsFiles($fls))return $this;
        
        foreach ($fls as $key => $file) {
            $fileKey   =   $key;
            if($file['size'] > 0 && $file['error']==0){
                
                $fc = file_get_contents($file['tmp_name']);
                $this->file_data[$fileKey]              = $file;
                $this->file_data[$fileKey]['content']   = base64_encode($fc);
                $this->file_data[$fileKey]['MD5']       = md5($this->file_data[$fileKey]['content']);
                unset($this->file_data[$fileKey]['tmp_name']);
                unset($this->file_data[$fileKey]['error']);
                $this->input[$fileKey] = $this->file_data[$fileKey];
            }
        }
        return $this; 
    }

    private function setProtocal(){
        $protocal   = (empty($this->protocal)?Configs::get("protocal"):$this->protocal);
        $protocal   = (empty($protocal)?'http':$protocal)."://";
        return $protocal;
    }

    public function connection( $host = '' ) {
        $host   =   empty($host)?Configs::get('default_host'):$host;
        $H      =   Configs::get('host');
        $H      =   $H[$host];
        $this->_host    =   $H;
        return $this;
    }

    private function genUrl()
    {
        $protocal   =  $this->setProtocal();
        $host       =  $this->_host;
        $host       =  (strpos($host, "http://")||strpos($host, "https://"))?$host:$protocal.$host;
        $prefix     =  $this->service;
        $real_url   =  $host."/".$prefix;

        if(!empty($this->_ID)){
            $real_url .= "/".$this->_ID;
        }
        if(!empty($this->_param))
        {
            $params       =  http_build_query($this->_param); 
            $real_url    .=  "?".$params;
        }
        return $real_url;
    } 

    private function errorDetail(){
        $raw    =   $this->rawResponse;
        $st     =   intval(strpos($raw, '<header>'));
        $en     =   intval(strpos($raw, '</header>'));
        $head   =   substr($raw, $st , $en-$st);
        $st     =   intval(strpos($head, '<h3 class="exc-title">'));
        $en     =   intval(strpos($head, '</span>
                  <button'));
        $head   =   substr($head, $st , $en-$st);
        $head   =   str_replace(" ", "" , $head);
        $head   =   str_replace("\n", "" , $head);
        return $head;
    }
    
    public function useHttps(){
        $this->protocal = "https";
        return $this;
    }

    public function execute()
    {
        //------ prepare data -------------
        if($this->method!='GET'){
            $this->setInput('');
            $this->_data    =   $this->cleanArray($this->_data);
        }

        $url                =   $this->genUrl();
        $method             =   $this->method;
        $input              =   $this->input;
        $isHttps            =   ($this->protocal=='https')?true:false;

        if($this->data_flag){
            $request            =   new RequestsData ($url , $method , $input , $isHttps);
            $req                =   $request->getData();
            $this->request_time =   $request->getTime();
            $this->AuthenTime   =   $request->getAuthenTime();
            $this->error        =   $request->getError();
            $this->rawResponse  =   $request->getRaw();
            $res_decode         =   json_decode($req);
            $this->err          =   $request->getErrorCode();
            $this->headError    =   $this->errorDetail();
            if(is_object($res_decode)){
                $callResult     =   $res_decode;
                if($this->err == 1 || $this->err == 2){
                    $this->result   = $callResult;
                }else{  
                    $this->result   =   $req;
                }
            }
        }
            $logDebug  =   Configs::get('logDebug');
            if($this->method != 'GET' && $logDebug){
                $meth       =   $this->method;
                $thisinfo    =   $this->info();
                $type        =   $thisinfo['SERVICE-NAME'];

                if(!empty($this->_data['_method'])){
                    $meth   =   $this->_data['_method'];
                }

                if( !$this->data_flag || ( $this->err != 0 || !$this->getResult() ) ){
                    
                    if( !empty($this->_ID) ){

                        $type           =   $this->mapPercent( $type , RouteList::get() , 90);
                        $fixKey         =   'service_fix_'.date("Ymd")."_".$type;

                        CacheStore::connect('memcache1');
                        $fixData        =   CacheStore::get( $fixKey );

                        $fixData[ $this->_ID."_".$meth ]    =   date("Y-m-d H:i:s") ;
                        CacheStore::set( $fixKey , $fixData ); 
                    }
                    if(!$this->data_flag){
                        $this->result   =   json_encode(array('body'=>'1'));
                    }
                }
                $this->log    =   new requestLog( $thisinfo , $this->err , $this->_data['_method'] , $this->getResult() , $this->_ID , $this->data_flag );
                
            }
        
        return $this;
    }

    protected function mapPercent( $str1 , $str2 , $percent ){
        
        $similar    =   '';
        $point      =   0;
        foreach ($str2 as $key => $value) {
            similar_text( $str1 , $value , $p ); 
            if($p >= $point){
                $similar    =   $value;
                $point      =   $p;
            }
        }

        return ($point >= $percent)?$similar:$str1;

    }

    public function getResult($assoc = false){

        $result     =   json_decode($this->result,$assoc);
        $result     =   $this->getBody($result,$assoc);
        return $result;
    }

    public function getError($detail = false){
        $error  =   $this->error;
        $error  .=   ($detail)?"<p> - Error detail ".$this->err.": ".$this->headError."</p>":"";
        return $error;
    }  

    public function Raw(){
        return $this->rawResponse;
    }

    private function getBody($obj , $assoc){
        if (empty($obj)) {
            return null;
        }
        if($assoc){
            if(!isset($obj['body']['type'])){
                $body = $obj['body'];
                return $body;
            }elseif($obj['body']['type'] == 'pdf'){
                header("Content-type: application/pdf");
                header("content-disposition:attachment;filename='pdf".date("YmdHis").".".$obj['body']['type']);
                echo $body = base64_decode($obj['body']['content']);exit;
            } 
        }else{

            if(!isset($obj->body->type)){
                $body = $obj->body;
                return $body;
            }elseif($obj->body->type == 'pdf'){
                header("Content-type: application/pdf");
                header("content-disposition:attachment;filename='pdf".date("YmdHis").".".$obj->body->type);
                echo $body = base64_decode($obj->body->content);exit;
            } 
        }
    }
 
    public function info(){
            
            $info['Content Flag']       =   ($this->content_flag)?"ON":"OFF";
            $info['Data Flag']          =   ($this->data_flag)?"ON":"OFF";

            if($this->data_flag){
                $info['REQUEST-STATUS']     =   ($this->err!=0)?"Fail":"Success";
                $info['AUTHRq']             =   ($this->atr)?"TRUE":'FALSE';
                $info['REQUEST-TIME']       =   $this->request_time."s.";

                if(!empty($_SESSION['auth_time'])){
                    $info['AUTHEN-TIME']    =   $_SESSION['auth_time']."s.";
                }
                $info['TOTAL-TIME']         =   $this->request_time+@$info['AUTHEN-TIME']."s.";
            }

            $info['DOMAIN']             =   $this->_host;
            $info['URL']                =   $this->genUrl();
            $info['CLIENT-ID']          =   Configs::get('client_id');
            // $info['TOKEN-ID']           =   $_SESSION[Configs::get('token_key')];
            $info['SERVICE-NAME']       =   $this->service;
            $info['METHOD']             =   $this->method;
            $info['URL-PARAMETERS']     =   $this->_param;
            $info['INPUT']['RAW']       =   json_encode($this->_data);
            $info['INPUT']['SIMPLE']    =   $this->_data;
            $info['INPUT']              =   $this->cleanArray($info['INPUT']);
            $info['Log']                =   (!is_object($this->log))?'':$this->log->status();

            unset($_SESSION['auth_time']);

        $info   =   $this->cleanArray($info);
        return $info;
    }

    public function cleanArray($arr){
        foreach ($arr as $key => $value) {
            if(empty($value)){unset($arr[$key]);}
        }
        return $arr;
    }
    
    
}

?>