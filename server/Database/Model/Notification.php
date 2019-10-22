<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database\Model;

class Notification
{
    /**
     * @var string
     */
    private $message = '';

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @param string $endpoint
     */
    public function __construct(string $endpoint)
    {
        $this->setEndpoint($endpoint);
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
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
