<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database\Repository;

use Generator;
use PDO;
use SubitoPuntoItAlert\Database\Configuration;
use SubitoPuntoItAlert\Database\Model\Announcement;

class AnnouncementRepository
{
    /**
     * @var PDO
     */
    private $db;

    /**
     * @var string
     */
    private $order = 'ASC';

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
            'ORDER BY id ' . $this->getOrder()
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
     * @return Generator
     */
    public function getAnnouncements(): Generator
    {
        $stmt = $this->getDb()->prepare(
            'SELECT * FROM Announcement'.
            'ORDER BY id ' . $this->getOrder()
        );
        $stmt->execute();
        while ($row = $stmt->fetch()){
            $announcement = new Announcement($row['endpoint']);
            $announcement->setDetails($row['details']);
            yield $announcement;
        }
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
     * @param string $order
     */
    public function setOrder(string $order): void
    {
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @return PDO
     */
    private function getDb(): PDO
    {
        return $this->db;
    }
}
