<?php

namespace SubitoPuntoItAlert\Database\Repository;

use PDO;
use SubitoPuntoItAlert\Database\Configuration;
use SubitoPuntoItAlert\Database\Model\Announcement;

class AnnouncementRepository
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
     * @param String $endpoint
     * @return Announcement[]
     */
    public function getAnnouncementsByEndpoint(String $endpoint): array
    {
        $stmt = $this->getDb()->prepare(
            'SELECT * FROM Announcement '.
            'WHERE endpoint = ?'.
            'ORDER BY id'
        );
        $stmt->execute([$endpoint]);
        $announcements = [];
        while ($row = $stmt->fetch()){
            $announcement = new Announcement($row['endpoint']);
            $announcement->setDetails($row['details']);
            $announcements[] = $announcement;
        }
        return $announcements;
    }

    /**
     * @param Announcement $announcement
     */
    public function delete(Announcement $announcement): void
    {
        $stmt = $this->getDb()->prepare(
            'DELETE FROM Announcement '.
            'WHERE endpoint = ? AND details = ?'
        );
        $stmt->execute([
            $announcement->getEndpoint(),
            $announcement->getDetails()
        ]);
    }

    /**
     * @param Announcement $announcement
     */
    public function save(Announcement $announcement): void
    {
        $this->delete($announcement);
        $stmt = $this->getDb()->prepare(
            'INSERT INTO Announcement (endpoint, details) '.
            'VALUES (?, ?)'
        );
        $stmt->execute([
            $announcement->getEndpoint(),
            $announcement->getDetails()
        ]);
    }

    /**
     * @param string $endpoint
     */
    public function deleteByEndpoint(string $endpoint): void
    {
        $stmt = $this->getDb()->prepare(
            'DELETE FROM Announcement '.
            'WHERE endpoint = ?'
        );
        $stmt->execute([$endpoint]);
    }

    /**
     * @return PDO
     */
    private function getDb(): PDO
    {
        return $this->db;
    }
}
