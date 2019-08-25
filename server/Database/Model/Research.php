<?php
declare(strict_types=1);

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
    protected $location;

    /**
     * @var string
     */
    protected $locationParameters;

    /**
     * @var bool
     */
    protected $onlyInTitle;

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
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getLocationParameters(): string
    {
        return $this->locationParameters;
    }

    /**
     * @param string $locationParameters
     */
    public function setLocationParameters(string $locationParameters): void
    {
        $this->locationParameters = $locationParameters;
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

    public function setLastCheckToday(): void
    {
        $this->lastCheck = date("Y-m-d H:i:s");
    }

    public function setLastCheckYesterday(): void
    {
        $this->lastCheck = date("Y-m-d H:i:s",strtotime("-1 days"));
    }

    /**
     * @return bool
     */
    public function isOnlyInTitle(): bool
    {
        return $this->onlyInTitle;
    }

    /**
     * @param bool $onlyInTitle
     */
    public function setOnlyInTitle(bool $onlyInTitle): void
    {
        $this->onlyInTitle = $onlyInTitle;
    }
}
