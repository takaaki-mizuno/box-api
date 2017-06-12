<?php

namespace Tests;

use TakaakiMizuno\Box\Client;

class BaseClientTestCase extends \PHPUnit\Framework\TestCase
{

    public function testDummy()
    {
        $this->assertTrue(true);
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        $client = new Client($this->getKeyFile('client_id'), $this->getKeyFile('client_secret'),
            $this->getKeyFile('public_key_id'), $this->getKeyFile('user_id'), $this->getKeyFile('type'));
        $key = file_get_contents(realpath(__DIR__.'/data/private_key.pem'));
        $result = $client->getAccessTokenWithJWT($key,
            $this->getKeyFile('private_key_pass'));
        if (!$result) {
            return null;
        }

        //        $this->putKeyFile('refresh_token', $client->getRefreshToken());

        return $client;
    }

    /**
     * @param $length
     * @return string
     */
    protected function randomString($length)
    {
        $seed = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $seed[rand(0, count($seed) - 1)];
        }

        return $result;
    }

    private function getKeyFile($name)
    {
        $data = file_get_contents(realpath(__DIR__.'/data/'.$name.'.txt'));

        return trim($data);
    }

    private function putKeyFile($name, $value)
    {
        file_put_contents(realpath(__DIR__.'/data/'.$name.'.txt'), $value);
    }
}