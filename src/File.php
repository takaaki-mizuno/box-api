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

    /** @var \DateTime|null */
    private $modifiedAt = null;

    /** @var \DateTime|null */
    private $createdAt = null;

    /** @var string */
    private $modifierEmail = '';

    /** @var int $fileVersionId */
    private $fileVersionId = 0;

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
     * @return string
     */
    public function getModifierEmail()
    {
        return $this->modifierEmail;
    }

    /**
     * @return int
     */
    public function getFileVersionId()
    {
        return $this->fileVersionId;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
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

        if (array_key_exists('modified_at', $response) && !empty($response['modified_at'])) {
            $this->modifiedAt = new \DateTime($response['modified_at']);
        }
        if (array_key_exists('created_at', $response) && !empty($response['created_at'])) {
            $this->createdAt = new \DateTime($response['created_at']);
        }
        if (!empty($response['parent'])) {
            $this->parentId = $response['parent']['id'];
        }
        if (array_key_exists('modified_by', $response) && $response['modified_by']['type'] == 'user') {
            $this->modifierEmail = $response['modified_by']['login'];
        }
        if (array_key_exists('file_version', $response) && $response['file_version']['type'] == 'file_version') {
            $this->fileVersionId = $response['file_version']['id'];
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
