<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database;

use PDO;
use SubitoPuntoItAlert\Exception\InvalidFilterConditionException;

class Counter
{
    /**
     * @var PDO
     */
    private $db;

    public function __construct()
    {
        $this->db = Configuration::getDB();
    }

    /**
     * @param string $table
     * @param SearchCriteria|null $searchCriteria
     * @return int
     * @throws InvalidFilterConditionException
     */
    public function count(string $table, SearchCriteria $searchCriteria = null): int
    {
        $query = 'SELECT COUNT(*) FROM ' . $table;
        $parameterValue = null;
        if ($searchCriteria) {
            $query .= ' ' . $searchCriteria->getFilter();
            $parameterValue = [$searchCriteria->getParameterValue()];
        }
        $stmt = $this->getDb()->prepare($query);
        $stmt->execute($parameterValue);
        return (int) $stmt->fetchColumn();
    }

    /**
     * @return PDO
     */
    private function getDb(): PDO
    {
        return $this->db;
    }
}
