<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use \sb\Gateway as Gateway;

/**
 * NOTE: Normally this method would be called from a project which uses the framework
 * rather than from the framework itself.  Hence the non-existent index.php in the CLI invocations.
 */
final class Controller_Command_Line_Test extends TestCase
{

    /**
     * @dataProvider cli_provider
     */
    public function test_getCommandlineInvocation($method_name, $http_host, $http_args, $expectation)
    {
        $controller = new \sb\Controller\Command\Line();
        
        $output = $controller->getCommandlineInvocation($method_name, $http_host, $http_args);

        $this->assertEquals($expectation, $output);
        
    }


    public function cli_provider()
    {
        return [

            // Malformed path
            ['Blah/Blech', '',  [], ''],

            // Well-formed class path, no method
            ['\Controller\Jobs\Foo', '', [], ''],

            // Well-formed class path with method
            ['\Controllers\Jobs\Foo::bar()', 'hostname', [],
                'php /home/ROSWELL/da42389/surebert-framework/public/index.php --request=/jobs_foo/bar --http_host=hostname'],

            // Well-formed class path with method and http vars
            ['\Controllers\Jobs\Foo::bar()', 'hostname', ['a' => 1, 'b' => 2],
                'php /home/ROSWELL/da42389/surebert-framework/public/index.php --request=/jobs_foo/bar?a=1&b=2 --http_host=hostname'],

        ];
    }
}
