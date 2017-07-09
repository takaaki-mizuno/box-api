<?php
namespace TakaakiMizuno\Box;

class FileVersion
{
    const TYPE_FILE   = 'file';
    const TYPE_FOLDER = 'folder';

    /** @var int */
    private $id = 0;

    /** @var int */
    private $fileId = 0;

    /** @var string */
    private $name = '';

    /** @var \DateTime|null */
    private $modifiedAt = null;

    /** @var \DateTime|null */
    private $createdAt = null;

    /** @var int */
    private $size = 0;

    /** @var string */
    private $modifierEmail = '';

    /** @var bool */
    private $isCurrent = false;

    /**
     * File constructor.
     *
     * @param array $response
     * @param int   $fileId
     * @param bool  $isCurrent
     */
    public function __construct($response, $fileId, $isCurrent = false)
    {
        $this->setAPIResponse($response, $fileId, $isCurrent);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * @return \DateTime|null
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getModifierEmail()
    {
        return $this->modifierEmail;
    }

    /**
     * @return bool
     */
    public function isCurrent()
    {
        return $this->isCurrent;
    }

    /**
     * @param array $response
     * @param int   $fileId
     * @param bool  $isCurrent
     */
    private function setAPIResponse($response, $fileId, $isCurrent = false)
    {
        $this->isCurrent = $isCurrent;
        $this->id        = $response['id'];
        $this->name      = $response['name'];
        $this->fileId    = $fileId;
        $this->size      = array_key_exists('size', $response) ? $response['size'] : 0;
        if (array_key_exists('modified_at', $response) && !empty($response['modified_at'])) {
            $this->modifiedAt = new \DateTime($response['modified_at']);
        }
        if (array_key_exists('created_at', $response) && !empty($response['created_at'])) {
            $this->createdAt = new \DateTime($response['created_at']);
        }
        if (array_key_exists('modified_by', $response) && $response['modified_by']['type'] == 'user') {
            $this->modifierEmail = $response['modified_by']['login'];
        }
    }
}
