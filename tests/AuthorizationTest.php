<?php
namespace Tests;

class AuthorizationTest extends BaseClientTestCase
{
    public function testGet()
    {
        $client = $this->getClient();
        $this->assertNotEmpty($client);
    }
}
