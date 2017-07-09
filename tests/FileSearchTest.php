<?php
namespace Tests;

class FileSearchTest extends BaseClientTestCase
{
    public function testSearch()
    {
        $client = $this->getClient();

        $files = $client->searchFile('rename');

        $this->assertNotEmpty($files);

//        foreach ($files as $file) {
//            print $file->getFullPath().PHP_EOL;
//        }
    }
}
