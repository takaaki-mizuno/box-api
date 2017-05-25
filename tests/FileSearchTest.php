<?php

namespace Tests;

class FileSearchTest extends BaseClientTestCase
{
    public function testSearch()
    {
        $client = $this->getClient();

        $files = $client->searchFile('test');
        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            print $file->getFullPath().PHP_EOL;
        }
    }
}
