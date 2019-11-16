<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database\Model;

use SubitoPuntoItAlert\Database\AbstractModel;

class Notification extends AbstractModel
{
    /**
     * @var string
     */
    private $message = '';

    /**
     * @param string $message
     * @return Notification
     */
    public function setMessage(string $message): Notification
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $endpoint
     * @return Notification
     */
    public function setEndpoint(string $endpoint): Notification
    {
        $this->setId($endpoint);
        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->getId();
    }
}
