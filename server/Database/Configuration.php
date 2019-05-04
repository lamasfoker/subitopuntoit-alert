<?php

namespace SubitoPuntoItAlert\Database;

use PDO;

class Configuration
{
    /**
     * @return PDO
     */
    static function getDB(): PDO
    {
        $dsn = 'mysql:dbname=subitopuntoitalert;host=127.0.0.1';
        $user = 'root';
        $password = '';
        //TODO: handle the Exception
        return new PDO($dsn, $user, $password, array(PDO::ATTR_PERSISTENT => true));
    }
}
