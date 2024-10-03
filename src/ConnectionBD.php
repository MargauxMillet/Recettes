<?php 

namespace App; 

use \PDO;

class ConnectionBD {

    public static function getPDO() : PDO
    {
        return new PDO('mysql:dbname=RecetteBasket;host=127.0.0.1', 'root', 'Thithi@21/02', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

}

?>