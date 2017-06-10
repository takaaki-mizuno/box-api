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

}
