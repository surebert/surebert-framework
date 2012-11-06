<?php

/**
 * Models a JSONRPC 2 request as per the spec proposal at 
 * http://groups.google.com/group/json-rpc/web/json-rpc-1-2-proposal
 *
 * @author paul.visco@roswellpark.org
 * @package JSON_RPC2
 */
namespace sb\JSON\RPC2;

class Request
{

    /**
     * A String containing the name of the procedure to be invoked.
     * @var string
     */
    public $method;

    /**
     * An Array or Object, that holds the actual parameter values for the
     *  invocation of the procedure. Can be omitted if empty.
     * @var array/object
     */
    public $params;

    /**
     * A request identifier, will be returned with the respose
     * @var string
     */
    public $id;

    /**
     * Popultes the properties from json recieved
     * 
     * <code>
     * $request = new \sb\JSON_RPC2_Request();
     * $request->id = 'abc123';
     * $request->method = '+';
     * $request->params = Array(1,2);
     * $response = $request->dispatch('http://mysite.com/json/server', false);
     * print_r($response);
     * </code>
     *
     * @param $json JSON encoded \sb\JSON\RPC2\Response
     * OR
     * @param $method String The method to call
     * @param $params Array/Object The params to send
     * @param $id String The Id of the request
     */
    public function __construct($json = null)
    {

        $num_args = func_num_args();

        //json
        if ($num_args === 1 && !empty($json)) {

            if (mb_detect_encoding($json) == 'UTF-8' && mb_substr($json, 0, 1) != '{') {
                $json = utf8_decode($json);
                $json = mb_substr($json, mb_strpos($json, '{'));
            }

            $o = json_decode($json);

            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    $error = 'No errors';
                    break;
                case JSON_ERROR_DEPTH:
                    $error = 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $error = 'Parse error';
                    break;
            }

            if (!is_object($o)) {
                $this->error = new \sb\JSON\RPC2\Error(-32700, $error);
            }

            foreach (\get_object_vars($this) as $k => $v) {

                if (isset($o->$k)) {
                    $this->$k = $o->$k;
                }
            }
        } elseif ($num_args > 1) {
            $this->method = $args[0];
            $this->params = $args[1];
            $this->id = isset($args[2]) ? $args[2] : uniqid();
        }
    }
}

