<?php
namespace Tests\Http;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    public function testGet()
    {
        $client   = new \TakaakiMizuno\Box\Http\Client();
        $request  = new \TakaakiMizuno\Box\Http\Request('https://secure.php.net/', 'get');
        $response = $client->request($request);

        $this->assertEquals(200, $response->getStatus());
    }
}
