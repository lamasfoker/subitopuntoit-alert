<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database\Model;

use SubitoPuntoItAlert\Database\AbstractModel;

class Subscription extends AbstractModel
{
    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var string
     */
    private $contentEncoding;

    /**
     * @var string
     */
    private $authToken;

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     * @return Subscription
     */
    public function setEndpoint(string $endpoint): Subscription
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     * @return Subscription
     */
    public function setPublicKey(string $publicKey): Subscription
    {
        $this->publicKey = $publicKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentEncoding(): string
    {
        return $this->contentEncoding;
    }

    /**
     * @param string $contentEncoding
     * @return Subscription
     */
    public function setContentEncoding(string $contentEncoding): Subscription
    {
        $this->contentEncoding = $contentEncoding;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    /**
     * @param string $authToken
     * @return Subscription
     */
    public function setAuthToken(string $authToken): Subscription
    {
        $this->authToken = $authToken;
        return $this;
    }
}
