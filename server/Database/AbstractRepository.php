<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database;

use Generator;
use PDO;
use SubitoPuntoItAlert\Exception\InvalidFilterConditionException;
use SubitoPuntoItAlert\Exception\InvalidFilterParameterException;
use SubitoPuntoItAlert\Exception\InvalidOrderByException;
use SubitoPuntoItAlert\Exception\InvalidOrderTypeException;
use SubitoPuntoItAlert\Exception\NoSuchEntityException;

abstract class AbstractRepository
{
    /**
     * The name of the table
     */
    const TABLE_NAME = 'Table';

    /**
     * The name of the primary key in the table model
     */
    const ID_NAME = 'id';

    /**
     * array with the name of the columns
     */
    const COLUMNS_NAME = [];

    /**
     * @var PDO
     */
    private $db;

    public function __construct()
    {
        $this->db = Configuration::getDB();
    }

    /**
     * @param int $id
     * @return AbstractModel
     * @throws NoSuchEntityException
     */
    public function getById(int $id): AbstractModel
    {
        $stmt = $this->getDb()->prepare(
            'SELECT * FROM ' . static::TABLE_NAME .
            ' WHERE ' . static::ID_NAME . ' = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row){
            return $this->hydrateModel($row);
        } else {
            throw new NoSuchEntityException();
        }
    }

    /**
     * @param SearchCriteria $searchCriteria
     * @return Generator
     * @throws InvalidFilterConditionException
     * @throws InvalidFilterParameterException
     * @throws InvalidOrderByException
     * @throws InvalidOrderTypeException
     */
    public function get(SearchCriteria $searchCriteria = null): Generator
    {
        $query = 'SELECT * FROM ' . static::TABLE_NAME;
        $parameterValue = null;
        if ($searchCriteria) {
            $orderBy = $searchCriteria->getOrderBy();
            if ($orderBy && !in_array($orderBy, [static::COLUMNS_NAME, static::ID_NAME])) {
                throw new InvalidOrderByException();
            }
            $parameterName = $searchCriteria->getParameterName();
            if ($parameterName && !in_array($parameterName, static::COLUMNS_NAME)) {
                throw new InvalidFilterParameterException();
            }
            $query .= ' ' . $searchCriteria->getFilter() . ' ' . $searchCriteria->getOrder();
            $parameterValue = [$searchCriteria->getParameterValue()];
        }
        $stmt = $this->getDb()->prepare($query);
        $stmt->execute($parameterValue);
        while ($row = $stmt->fetch()){
            yield $this->hydrateModel($row);
        }
    }

    /**
     * @param AbstractModel $model
     */
    public function save(AbstractModel $model): void
    {
        $parameters = $this->dryModel($model);
        if ($model->getId()) {
            $conditions = array_map(function ($name) {
                return $name . '=?';
            }, static::COLUMNS_NAME);
            $query = 'UPDATE ' . static::TABLE_NAME .
                ' SET ' . implode(', ', $conditions) .
                ' WHERE ' . static::ID_NAME . '=?';
            $parameters[] = $model->getId();
        } else {
            $conditions = array_map(function ($value) {
                return '?';
            }, static::COLUMNS_NAME);
            $query = 'INSERT INTO ' . static::TABLE_NAME .
                ' (' . implode(', ', static::COLUMNS_NAME) . ') ' .
                'VALUES (' . implode(', ', $conditions) . ')';
        }
        $stmt = $this->getDb()->prepare($query);
        $stmt->execute($parameters);
    }

    /**
     * @param int $id
     */
    public function deleteById(int $id): void
    {
        $this->getDb()->exec(
            'DELETE FROM ' . static::TABLE_NAME .
            ' WHERE ' . static::ID_NAME . ' = ' . $id
        );
    }

    /**
     * @param SearchCriteria $searchCriteria
     * @throws InvalidFilterConditionException
     * @throws InvalidFilterParameterException
     */
    public function delete(SearchCriteria $searchCriteria): void
    {
        $query = 'TRUNCATE TABLE ' . static::TABLE_NAME;
        $parameterValue = null;
        if ($searchCriteria) {
            if (!in_array($searchCriteria->getParameterName(), static::COLUMNS_NAME)) {
                throw new InvalidFilterParameterException();
            }
            $query = 'DELETE FROM ' . static::TABLE_NAME . ' ' . $searchCriteria->getFilter();
            $parameterValue = [$searchCriteria->getParameterValue()];
        }
        $stmt = $this->getDb()->prepare($query);
        $stmt->execute($parameterValue);
    }

    /**
     * @param $data
     * @return AbstractModel
     */
    abstract protected function hydrateModel($data): AbstractModel;

    /**
     * @param AbstractModel $model
     * @return array
     */
    abstract protected function dryModel(AbstractModel $model): array;

    /**
     * @return PDO
     */
    protected function getDb(): PDO
    {
        return $this->db;
    }
}
