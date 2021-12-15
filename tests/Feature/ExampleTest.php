<?php
namespace Tests\Feature;
use PHPUnit\Framework\TestCase;
use Woski\Application;


class ExampleTest extends TestCase
{
    /**
     * Test Application GET
     */
    public function testApplicationGET()
    {
        $app = new Application;
    
        $get = $app->get('/', function($req, $res){
            // some code
        });

        $this->assertNotNull($get, "Not Null");
    }
}