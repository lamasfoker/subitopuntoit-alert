<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database\Model;

use SubitoPuntoItAlert\Database\AbstractModel;

class Announcement extends AbstractModel
{
    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $details;

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return $this->details;
    }

    /**
     * @param string $details
     * @return Announcement
     */
    public function setDetails(string $details): Announcement
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     * @return Announcement
     */
    public function setEndpoint(string $endpoint): Announcement
    {
        $this->endpoint = $endpoint;
        return $this;
    }
}
