<?php
namespace TakaakiMizuno\Box;

class File
{
    const TYPE_FILE   = 'file';
    const TYPE_FOLDER = 'folder';

    /** @var int */
    private $id = 0;

    /** @var int */
    private $parentId = 0;

    /** @var string */
    private $name = '';

    /** @var string */
    private $type = '';

    /** @var int */
    private $size = 0;

    /** @var array */
    private $pathCollection = array();

    /**
     * File constructor.
     *
     * @param array $response
     */
    public function __construct($response)
    {
        $this->setAPIResponse($response);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFullPath()
    {
        $path = array('');
        foreach ($this->pathCollection as $pathCollection) {
            $path[] = $pathCollection['name'];
        }
        $path[] = $this->name;

        return implode('/', $path);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @return bool
     */
    public function isFile()
    {
        return $this->type == self::TYPE_FILE;
    }

    /**
     * @return bool
     */
    public function isFolder()
    {
        return $this->type == self::TYPE_FILE;
    }

    /**
     * @param array $response
     */
    private function setAPIResponse($response)
    {
        $this->id   = $response['id'];
        $this->name = $response['name'];
        $this->type = $response['type'];
        $this->size = array_key_exists('size', $response) ? $response['size'] : 0;

        if (!empty($response['parent'])) {
            $this->parentId = $response['parent']['id'];
        }

        if (array_key_exists('path_collection', $response)) {
            {
                foreach ($response['path_collection']['entries'] as $entry) {
                    if ($entry['id'] == 0) {
                        continue;
                    }
                    $this->pathCollection[] = array(
                        'id'   => $entry['id'],
                        'name' => $entry['name'],
                    );
                }
            }
        }
    }
}
