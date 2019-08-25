<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database\Model;

class Announcement
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
     * Announcement constructor.
     * @param String $endpoint
     */
    public function __construct(String $endpoint)
    {
        $this->setEndpoint($endpoint);
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return $this->details;
    }

    /**
     * @param string $details
     */
    public function setDetails(string $details): void
    {
        $this->details = $details;
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
     */
    private function setEndpoint(string $endpoint): void
    {
        $this->endpoint = $endpoint;
    }
}
