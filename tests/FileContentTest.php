<?php

namespace Tests;

class FileContentTest extends BaseClientTestCase
{
    public function testUpload()
    {
        $client = $this->getClient();

        $testDataPath = realpath(__DIR__.'/data/test.dat');
        $file = $client->uploadFile($this->randomString(10) . '.txt', $testDataPath, 0);

        $this->assertNotEmpty($file);
        $id = $file->getId();

        $distPath = tempnam(sys_get_temp_dir(), '');
        $result =  $client->downloadFile($id, $distPath);

        $this->assertTrue($result);

        $file1 = file_get_contents($testDataPath);
        $file2 = file_get_contents($distPath);
        $this->assertTrue($file1 == $file2);
    }

    public function testUploadVersion()
    {
        $client = $this->getClient();

        $name = $this->randomString(10) . '.txt';
        $testDataPath = realpath(__DIR__.'/data/test.dat');
        $file = $client->uploadFile($name, $testDataPath, 0);

        $this->assertNotEmpty($file);
        $id = $file->getId();

        $testDataPath2 = realpath(__DIR__.'/data/test2.dat');
        $file = $client->overwriteFile($name, $testDataPath2, $id);
        $this->assertNotEmpty($file);

        $distPath = tempnam(sys_get_temp_dir(), '');
        $result =  $client->downloadFile($id, $distPath);

        $this->assertTrue($result);

        $file1 = file_get_contents($testDataPath2);
        $file2 = file_get_contents($distPath);
        $this->assertTrue($file1 == $file2);
    }

}
