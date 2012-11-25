<?php

/**
 * Models a JSONRPC 2 response error as per the spec proposal 
 * at http://groups.google.com/group/json-rpc/web/json-rpc-1-2-proposal
 * @author paul.visco@roswellpark.org
 * @package JSON_RPC2
 */
namespace sb\JSON\RPC2;

class Error
{

    /**
     * A Number that indicates the actual error that occurred.
     * The error-codes -32768 .. -32000 (inclusive) are reserved 
     * for pre-defined errors. Any error-code within this range not 
     * defined explicitly below is reserved for future use. 
     * code     message     Meaning
     * -32700     Parse error.     Invalid JSON. An error occurred on the server while parsing the JSON text.
     * -32600     Invalid Request.     The received JSON not a valid JSON-RPC Request.
     * -32601     Method not found.     The requested remote-procedure does not exist / is not available.
     * -32602     Invalid params.     Invalid method parameters.
     * -32603     Internal error.     Internal JSON-RPC error.
     * -32099..-32000     Server error.     Reserved for implementation-defined server-errors.
     * 
     * @var integer
     */
    public $code;

    /**
     * A short description of the error. The message SHOULD be limited 
     * to a concise single sentence.
     * @var string
     */
    public $message;

    /**
     * Additional information, may be omitted. Its contents is entirely 
     * defined by the application (e.g. detailed error information, 
     * nested errors etc.).
     * @var unknown_type
     */
    public $data;

    /**
     * Instantiates an new \sb\JSON_RPC2_Error
     * @param $code Sets the error code property
     * @param $message Sets the human readible message
     * @param $data Sets amy addition data which must send with the error
     */
    public function __construct($code = null, $message = null, $data = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }
}

