<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Apple\Message;

/**
 * Class AppleMessageApsData.
 */
class AppleMessageApsData
{
    /**
     * @var string|null|array
     */
    private $alert = null;
    /**
     * @var int|null
     */
    private $badge = null;
    /**
     * @var string|null
     */
    private $sound = null;
    /**
     * @var string|null
     */
    private $category = null;
    /**
     * @var bool
     */
    private $contentAvailable = false;

    /**
     * @return string
     */
    public function getAlert()
    {
        return $this->alert;
    }

    /**
     * @param mixed $alert
     */
    public function setAlert($alert)
    {
        $this->alert = $alert;
    }

    /**
     * @return int
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * @param int $badge
     */
    public function setBadge(int $badge = null)
    {
        $this->badge = $badge;
    }

    /**
     * @return string
     */
    public function getSound()
    {
        return $this->sound;
    }

    /**
     * @param string $sound
     */
    public function setSound(string $sound = null)
    {
        $this->sound = $sound;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory(string $category = null)
    {
        $this->category = $category;
    }

    /**
     * @return bool
     */
    public function isContentAvailable(): bool
    {
        return $this->contentAvailable;
    }

    /**
     * @param bool $contentAvailable
     */
    public function setContentAvailable(bool $contentAvailable)
    {
        $this->contentAvailable = $contentAvailable;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $aps = [];

        if ($this->alert !== null) {
            $aps['alert'] = $this->alert;
        }

        if ($this->badge !== null) {
            $aps['badge'] = $this->badge;
        }

        if ($this->sound !== null) {
            $aps['sound'] = $this->sound;
        }

        if ($this->category !== null) {
            $aps['category'] = $this->category;
        }

        if ($this->contentAvailable === true) {
            $aps['content-available'] = 1;
        }

        return $aps;
    }
}
