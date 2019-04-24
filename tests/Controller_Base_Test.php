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
    public function test_parseDocblock($docblock_text, $expectation)
    {
        $controller = new \sb\Controller\Base();
        
        $output = $controller->parseDocblock($docblock_text);

        $this->assertEquals($expectation, $output);
    }


    public function docblockProvider()
    {
        return [
            [
                '',
                (object) []
            ],
            [
                '/**
                  * No tags
                  */',
                (object) []
            ],
            [
                '/**
                  * Preamble
                  * @TagWithNoHandler TagVal
                  */',
                (object) []
            ],
            [
                '/**
                  * Recognized Tag / Unrecognized Val
                  * @http_method Foo
                  */',
                (object) []
            ],
            [
                '/**
                  * Recognized Tag / Recognized Val
                  * @http_method get
                  */',
                (object) ['http_method' => 'get']
            ],
            [
                '/**
                  * Recognized Tag / Recognized Val
                  * @http_method get
                  * @input_as_array true
                  * @servable true
                  * @triggers \sb\Controller\Classname
                  */',
                (object) [
                    'http_method'    => 'get',
                    'input_as_array' => true,
                    'servable'       => true,
                    'triggers'       => '\sb\Controller\Classname',
                ]
            ],
        ];
    }
}
