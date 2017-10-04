<?php

namespace Fire\Sql;

use PDO;

class Collection
{

    private $_pdo;

    public function __construct(PDO $pdo)
    {
        $this->_pdo = $pdo;
    }
}
