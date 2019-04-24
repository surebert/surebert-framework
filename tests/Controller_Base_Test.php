<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use \sb\Gateway as Gateway;

/**
 */
final class Controller_Base_Test extends TestCase
{

    /**
     * @dataProvider docblockProvider
     */
    public function test_parseDocblock($docblock_text, $method_name, $expectation)
    {
        $controller = new \sb\Controller\Base();
        
        $output = $controller->parseDocblock($docblock_text, $method_name);

        $this->assertEquals($expectation, $output);
    }


    public function docblockProvider()
    {
        $method_name = '\\A\\B::c';
        return [
            [
                '',
                $method_name,
                (object) ['method_name' => $method_name]
            ],
            [
                '/**
                  * No tags
                  */',
                $method_name,
                (object) ['method_name' => $method_name]
            ],
            [
                '/**
                  * Preamble
                  * @TagWithNoHandler TagVal
                  */',
                $method_name,
                (object) ['method_name' => $method_name]
            ],
            [
                '/**
                  * Recognized Tag / Unrecognized Val
                  * @http_method Foo
                  */',
                $method_name,
                (object) ['method_name' => $method_name]
            ],
            [
                '/**
                  * Recognized Tag / Recognized Val
                  * @http_method get
                  */',
                $method_name,
                (object) [
                    'method_name' => $method_name,
                    'http_method' => 'get'
                ]
            ],
            [
                '/**
                  * Recognized Tag / Recognized Val
                  * @http_method get
                  * @input_as_array true
                  * @servable true
                  * @triggers \sb\Controller\Classname
                  */',
                $method_name,
                (object) [
                    'method_name'    => $method_name,
                    'http_method'    => 'get',
                    'input_as_array' => true,
                    'servable'       => true,
                    'triggers'       => '\sb\Controller\Classname',
                ]
            ],
        ];
    }
}
