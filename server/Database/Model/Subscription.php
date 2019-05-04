<?php

namespace SubitoPuntoItAlert\Database\Model;

class Subscription
{
    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var string
     */
    protected $contentEncoding;

    /**
     * @var string
     */
    protected $authToken;

    /**
     * Subscription constructor.
     * @param string $endpoint
     */
    public function __construct(string $endpoint)
    {
        $this->setEndpoint($endpoint);
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

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
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
     */
    public function setContentEncoding(string $contentEncoding): void
    {
        $this->contentEncoding = $contentEncoding;
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
     */
    public function setAuthToken(string $authToken): void
    {
        $this->authToken = $authToken;
    }
}
