<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database\Repository;

use Generator;
use PDO;
use SubitoPuntoItAlert\Database\Configuration;
use SubitoPuntoItAlert\Database\Model\Subscription;
use SubitoPuntoItAlert\Exception\MissingSubscriptionException;

class SubscriptionRepository
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
     * @return Subscription
     * @throws MissingSubscriptionException
     */
    public function getSubscription(string $endpoint): Subscription
    {
        $stmt = $this->getDb()->prepare(
            'SELECT * FROM Subscription '.
            'WHERE endpoint = ? LIMIT 1'
        );
        $stmt->execute([$endpoint]);
        $subscription = new Subscription($endpoint);
        $row = $stmt->fetch();
        if ($row){
            $subscription->setAuthToken($row['authToken']);
            $subscription->setContentEncoding($row['contentEncoding']);
            $subscription->setPublicKey($row['publicKey']);
        } else {
            throw new MissingSubscriptionException();
        }
        return $subscription;
    }

    /**
     * @return Generator
     */
    public function getSubscriptions(): Generator
    {
        $stmt = $this->getDb()->prepare(
            'SELECT * FROM Subscription '
        );
        $stmt->execute();
        while ($row = $stmt->fetch()){
            $subscription = new Subscription($row['endpoint']);
            $subscription->setAuthToken($row['authToken']);
            $subscription->setContentEncoding($row['contentEncoding']);
            $subscription->setPublicKey($row['publicKey']);
            yield $subscription;
        }
    }

    /**
     * @param Subscription $subscription
     */
    public function save(Subscription $subscription): void
    {
        //TODO: if a getter is void what's appened?
        //TODO: use commit instead of execute
        $this->delete($subscription->getEndpoint());
        $stmt = $this->getDb()->prepare(
            'INSERT INTO Subscription (endpoint, contentEncoding, authToken, publicKey) '.
            'VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $subscription->getEndpoint(),
            $subscription->getContentEncoding(),
            $subscription->getAuthToken(),
            $subscription->getPublicKey()
        ]);
    }

    /**
     * @param string $endpoint
     */
    public function delete(string $endpoint): void
    {
        $stmt = $this->getDb()->prepare(
            'DELETE FROM Subscription WHERE endpoint = ?'
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
