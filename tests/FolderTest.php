<?php

namespace Tests;

class FolderTest extends BaseClientTestCase
{
    public function testFolderList()
    {
        $client = $this->getClient();

        $files = $client->filesInFolder(0);

        $this->assertTrue(count($files) > 0);

        foreach ($files as $file) {
            print $file->getFullPath().PHP_EOL;
        }
    }

    public function testCreateFolder()
    {
        $client = $this->getClient();
        $file = $client->createFolder($this->randomString(10), 0);
        $this->assertNotEmpty($file);

        print $file->getFullPath().PHP_EOL;
    }

    /**
     * @param $length
     * @return string
     */
    private function randomString($length) {
        $seed = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $seed[rand(0, count($seed) - 1)];
        }
        return $result;
    }
}
