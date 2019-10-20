<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database;

use PDO;

class Configuration
{
    /**
     * @return PDO
     */
    static function getDB(): PDO
    {
        $dsn = 'mysql:dbname=' . getenv('DB_NAME') . ';host=' . getenv('DB_HOST');
        $user = getenv('DB_USER');
        $password = getenv('DB_PASSWORD');
        //TODO: handle the Exception
        return new PDO($dsn, $user, $password, array(PDO::ATTR_PERSISTENT => true));
    }
}
