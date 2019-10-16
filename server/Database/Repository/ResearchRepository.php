<?php
declare(strict_types=1);

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
            $research->setLocation($row['location']);
            $research->setLocationParameters($row['locationParameters']);
            $research->setOnlyInTitle($row['onlyInTitle']==='1');
            $research->setQuery($row['query']);
            $research->setLastCheck($row['lastCheck']);
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
            'INSERT INTO Research (endpoint, location, locationParameters, onlyInTitle, query, lastCheck) '.
            'VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $research->getEndpoint(),
            $research->getLocation(),
            $research->getLocationParameters(),
            $research->isOnlyInTitle()?1:0,
            $research->getQuery(),
            $research->getLastCheck()
        ]);
    }

    /**
     * @param Research $research
     */
    public function delete(Research $research): void
    {
        $stmt = $this->getDb()->prepare(
            'DELETE FROM Research '.
            'WHERE endpoint = ? AND location = ? AND locationParameters = ? AND onlyInTitle = ? AND query = ?'
        );
        $stmt->execute([
            $research->getEndpoint(),
            $research->getLocation(),
            $research->getLocationParameters(),
            $research->isOnlyInTitle()?1:0,
            $research->getQuery()
        ]);
    }

    /**
     * @param string $endpoint
     */
    public function deleteByEndpoint(string $endpoint): void
    {
        $stmt = $this->getDb()->prepare(
            'DELETE FROM Research '.
            'WHERE endpoint = ?'
        );
        $stmt->execute([$endpoint]);
    }

    /**
     * @return Research[]
     */
    public function getResearches(): array
    {
        $stmt = $this->getDb()->prepare(
            'SELECT * FROM Research'
        );
        $stmt->execute();
        $researches = [];
        while ($row = $stmt->fetch()){
            $research = new Research($row['endpoint']);
            $research->setLocation($row['location']);
            $research->setLocationParameters($row['locationParameters']);
            $research->setOnlyInTitle($row['onlyInTitle']==='1');
            $research->setLastCheck($row['lastCheck']);
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
