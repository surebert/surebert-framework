<?php
/**
 * Models a JSON RPC2 Response from the sb_JSON_RPC2_Server
 * @version 1.0 02/06/09
 * @author visco
 * @package sb_JSON_RPC2
 */
class sb_JSON_RPC2_Response{
	
	/**
	 * Required on success, omitted on failure.
	 * The Value that was returned by the procedure. Its contents is entirely defined by the procedure.
	 * This member MUST be entirely omitted if there was an error invoking the procedure.
	 * @var *
	 */
	public $result;
	
	/**
	 * Required on error, omitted on success.
	 * An Object containing error information about the fault that occurred before, during or after the call.
	 * This member MUST be entirely omitted if there was no such fault.
	 * @var sb_JSON_RPC2_Error
	 */
	public $error;
	
	/**
	 * The same id as in the Request it is responding to. If there was an error before detecting the id in the Request, it MUST be Null.
	 * @var string
	 */
	public $id;
	
	/**
	 * Popultes the properties from json recieved
	 * @param $json JSON encoded sb_JSON_RPC2_Response
	 */
	public function __construct($json = null){
		
            if(!is_null($json)){

                $o = json_decode($json);

                if(!is_object($o)){

                    $this->error = new sb_JSON_RPC2_Error(-32700, 'Parse Error', $json);

                    unset($this->result);

                    return;
                }

                foreach(get_object_vars($this) as $k=>$v){

                    if(isset($o->$k)){
                            $this->$k = $o->$k;
                    }
                }

                if(is_null($this->result)){
                    unset($this->result);
                }

                if(isset($this->result)){
                    unset($this->error);
                }
            }
		
	}
	
}

?>