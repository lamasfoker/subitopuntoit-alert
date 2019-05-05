<?php

namespace SubitoPuntoItAlert\Database\Repository;

use PDO;
use SubitoPuntoItAlert\Database\Configuration;
use SubitoPuntoItAlert\Database\Model\Subscription;

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
            //TODO: there isn't any subscription
        }
        return $subscription;
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