<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database\Model;

use SubitoPuntoItAlert\Database\AbstractModel;

class Research extends AbstractModel
{
    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $locationParameters;

    /**
     * @var bool
     */
    private $onlyInTitle;

    /**
     * @var string
     */
    private $query;

    /**
     * @var string
     */
    private $lastCheck;

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     * @return Research
     */
    public function setEndpoint(string $endpoint): Research
    {
        $this->endpoint = $endpoint;
        return $this;
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
     * @return Research
     */
    public function setLocation(string $location): Research
    {
        $this->location = $location;
        return $this;
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
     * @return Research
     */
    public function setLocationParameters(string $locationParameters): Research
    {
        $this->locationParameters = $locationParameters;
        return $this;
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
     * @return Research
     */
    public function setQuery(string $query): Research
    {
        $this->query = $query;
        return $this;
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
     * @return Research
     */
    public function setLastCheck(string $lastCheck): Research
    {
        $this->lastCheck = $lastCheck;
        return $this;
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
     * @return Research
     */
    public function setOnlyInTitle(bool $onlyInTitle): Research
    {
        $this->onlyInTitle = $onlyInTitle;
        return $this;
    }
}
