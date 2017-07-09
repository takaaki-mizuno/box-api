<?php
namespace Tests;

class FileAttributeTest extends BaseClientTestCase
{
    public function testGetFileInfo()
    {
        $client = $this->getClient();

        $name         = $this->randomString(10).'.txt';
        $testDataPath = realpath(__DIR__.'/data/test.dat');
        $file         = $client->uploadFile($name, $testDataPath, 0);

        $this->assertNotEmpty($file);
        $id = $file->getId();

        $file = $client->getFileInfo($id);
        $this->assertNotEmpty($file);

        $this->assertEquals($name, $file->getName());
    }

    public function testRenameFile()
    {
        $client = $this->getClient();

        $name         = $this->randomString(10).'.txt';
        $testDataPath = realpath(__DIR__.'/data/test.dat');
        $file         = $client->uploadFile($name, $testDataPath, 0);

        $this->assertNotEmpty($file);
        $id = $file->getId();

        $name2 = $this->randomString(10).'.txt';

        $file = $client->renameFile($name2, $id);
        $this->assertNotEmpty($file);

        $this->assertEquals($name2, $file->getName());
    }
}
