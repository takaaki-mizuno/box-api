<?php
namespace Tests;

class FileVersionTest extends BaseClientTestCase
{
    public function testUploadVersion()
    {
        $client = $this->getClient();

        $name         = $this->randomString(10).'.txt';
        $testDataPath = realpath(__DIR__.'/data/test.dat');
        $file         = $client->uploadFile($name, $testDataPath, 0);

        $this->assertNotEmpty($file);
        $id = $file->getId();

        $testDataPath2 = realpath(__DIR__.'/data/test2.dat');
        $file          = $client->overwriteFile($name.'_rename.txt', $testDataPath2, $id);
        $this->assertNotEmpty($file);

        $distPath = tempnam(sys_get_temp_dir(), '');
        $result   =  $client->downloadFile($id, $distPath);

        $this->assertTrue($result);

        $file1 = file_get_contents($testDataPath2);
        $file2 = file_get_contents($distPath);
        $this->assertTrue($file1 == $file2);

        $versions = $client->getFileVersions($id);

        $this->assertEquals(2, count($versions));
        $this->assertEquals($name, $versions[1]->getName());
    }

    public function testPromoteVersion()
    {
        $client = $this->getClient();

        $name         = $this->randomString(10).'.txt';
        $testDataPath = realpath(__DIR__.'/data/test.dat');
        $file         = $client->uploadFile($name, $testDataPath, 0);

        $this->assertNotEmpty($file);
        $id = $file->getId();

        $testDataPath2 = realpath(__DIR__.'/data/test2.dat');
        $file          = $client->overwriteFile($name, $testDataPath2, $id);
        $this->assertNotEmpty($file);

        $distPath = tempnam(sys_get_temp_dir(), '');
        $result   =  $client->downloadFile($id, $distPath);

        $this->assertTrue($result);

        $file1 = file_get_contents($testDataPath2);
        $file2 = file_get_contents($distPath);
        $this->assertTrue($file1 == $file2);

        $versions = $client->getFileVersions($id);

        $this->assertEquals(2, count($versions));

        $version  = $client->promoteFileVersion($id, $versions[1]->getId());
        $versions = $client->getFileVersions($id);

        print_r($versions);

        $this->assertEquals(3, count($versions));
        $this->assertEquals(true, $versions[0]->isCurrent());
    }
}
