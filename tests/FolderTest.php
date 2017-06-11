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

    public function testRenameFolder()
    {
        $name = $this->randomString(10);

        $client = $this->getClient();
        $folder = $client->createFolder($name, 0);
        $this->assertNotEmpty($folder);

        $rename = 'rename_' . $name;
        $folder = $client->renameFolder($rename, $folder->getId());

        $this->assertNotEmpty($folder);

        print $folder->getFullPath().PHP_EOL;
    }

}
