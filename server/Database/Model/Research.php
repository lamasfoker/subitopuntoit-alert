<?php

namespace SubitoPuntoItAlert\Database\Model;

class Research
{
    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var string
     */
    protected $region;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var string
     */
    protected $lastCheck;

    /**
     * Research constructor.
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
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * @param string $region
     */
    public function setRegion(string $region): void
    {
        $this->region = $region;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getLastCheck(): string
    {
        return $this->lastCheck;
    }

    /**
     * @param string $lastCheck
     */
    public function setLastCheck(string $lastCheck): void
    {
        $this->lastCheck = $lastCheck;
    }

    public function setLastCheckNow(): void
    {
        $this->lastCheck = date("Y-m-d H:i:s");
    }
}
