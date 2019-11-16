<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database;

use SubitoPuntoItAlert\Exception\InvalidFilterConditionException;
use SubitoPuntoItAlert\Exception\InvalidOrderTypeException;

class SearchCriteria
{
    /**
     * @var array
     */
    private $validConditions = ['eq' => '='];

    /**
     * @var string
     */
    private $parameterName = null;

    /**
     * @var mixed
     */
    private $parameterValue = null;

    /**
     * @var string
     */
    private $condition = null;

    /**
     * @var string
     */
    private $orderType = 'ASC';

    /**
     * @var string
     */
    private $orderBy = null;

    /**
     * @return string
     * @throws InvalidFilterConditionException
     */
    public function getFilter(): string
    {
        $condition = strtolower($this->getCondition());
        $parameterName = $this->getParameterName();
        if (
            is_null($condition) ||
            is_null($parameterName) ||
            is_null($this->getParameterValue())
        ) {
            return '';
        }
        if (array_key_exists($condition, $this->validConditions)) {
            return 'WHERE ' . $parameterName . ' ' . $this->validConditions[$condition] . ' ?';
        }
        throw new InvalidFilterConditionException();
    }

    /**
     * @return string
     * @throws InvalidOrderTypeException
     */
    public function getOrder(): string
    {
        $orderBy = $this->getOrderBy();
        if (is_null($orderBy)) {
            return '';
        }
        $orderType = strtoupper($this->getOrderType());
        if (in_array($orderType, ['ASC', 'DESC'])) {
            return 'ORDER BY ' . $orderBy . ' ' . $orderType;
        }
        throw new InvalidOrderTypeException();
    }

    /**
     * @return null|string
     */
    public function getParameterName(): ?string
    {
        return $this->parameterName;
    }

    /**
     * @param string $parameterName
     * @return SearchCriteria
     */
    public function setParameterName(string $parameterName): SearchCriteria
    {
        $this->parameterName = $parameterName;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCondition(): ?string
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     * @return SearchCriteria
     */
    public function setCondition(string $condition): SearchCriteria
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * @return null|mixed
     */
    public function getParameterValue()
    {
        return $this->parameterValue;
    }

    /**
     * @param $parameterValue
     * @return SearchCriteria
     */
    public function setParameterValue($parameterValue): SearchCriteria
    {
        $this->parameterValue = $parameterValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderType(): string
    {
        return $this->orderType;
    }

    /**
     * @param string $orderType
     * @return SearchCriteria
     */
    public function setOrderType(string $orderType): SearchCriteria
    {
        $this->orderType = $orderType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    /**
     * @param string $orderBy
     * @return SearchCriteria
     */
    public function setOrderBy(string $orderBy): SearchCriteria
    {
        $this->orderBy = $orderBy;
        return $this;
    }
}
