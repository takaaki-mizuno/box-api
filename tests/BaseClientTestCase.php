<?php

namespace Tests;

use TakaakiMizuno\Box\Client;

class BaseClientTestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @return Client
     */
    protected function getClient()
    {
        $client = new Client($this->getKeyFile('client_id'), $this->getKeyFile('client_secret'));
        $client->setRefreshToken($this->getKeyFile('refresh_token'));
        $result = $client->refreshAccessToken();
        if (!$result) {
            return null;
        }
        $this->putKeyFile('refresh_token', $client->getRefreshToken());

        return $client;
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

    public function testDummy()
    {
        $this->assertTrue(true);
    }

    /**
     * @param $length
     * @return string
     */
    protected function randomString($length) {
        $seed = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $seed[rand(0, count($seed) - 1)];
        }
        return $result;
    }
}