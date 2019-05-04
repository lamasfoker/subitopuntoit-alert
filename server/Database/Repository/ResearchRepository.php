<?php

namespace SubitoPuntoItAlert\Database\Repository;

use PDO;
use SubitoPuntoItAlert\Database\Configuration;
use SubitoPuntoItAlert\Database\Model\Research;

class ResearchRepository
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
     * @param string $endpoint
     * @return Research[]
     */
    public function getResearchesByEndpoint(string $endpoint): array
    {
        $stmt = $this->getDb()->prepare(
            'SELECT * FROM Research '.
            'WHERE endpoint = ?'
        );
        $stmt->execute([$endpoint]);
        $researches = [];
        while ($row = $stmt->fetch()){
            $research = new Research($row['endpoint']);
            $research->setRegion($row['region']);
            $research->setCity($row['city']);
            $research->setQuery($row['query']);
            $researches[] = $research;
        }
        return $researches;
    }

    /**
     * @param Research $research
     */
    public function save(Research $research): void
    {
        $this->delete($research);
        $stmt = $this->getDb()->prepare(
            'INSERT INTO Research (endpoint, region, city, query) '.
            'VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $research->getEndpoint(),
            $research->getRegion(),
            $research->getCity(),
            $research->getQuery()
        ]);
    }

    /**
     * @param Research $research
     */
    public function delete(Research $research): void
    {
        $stmt = $this->getDb()->prepare(
            'DELETE FROM Research '.
            'WHERE endpoint = ? AND region = ? AND city = ? AND query = ?'
        );
        $stmt->execute([
            $research->getEndpoint(),
            $research->getRegion(),
            $research->getCity(),
            $research->getQuery()
        ]);
    }

    /**
     * @return Research[]
     */
    public function getAllResearch(): array
    {
        $stmt = $this->getDb()->prepare(
            'SELECT * FROM Research'
        );
        $stmt->execute();
        $researches = [];
        while ($row = $stmt->fetch()){
            $research = new Research($row['endpoint']);
            $research->setRegion($row['region']);
            $research->setCity($row['city']);
            $research->setQuery($row['query']);
            $researches[] = $research;
        }
        return $researches;
    }

    /**
     * @return PDO
     */
    private function getDb(): PDO
    {
        return $this->db;
    }
}
