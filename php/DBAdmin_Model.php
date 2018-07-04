<?php

class DBAdmin_Model {
    
    public $rootPdo = null;
    
    /**
     * Beendet die Datenbankverbindung
     * @param PDO $pdo
     */
    public function closeDbConnection($pdo) {
        $pdo = null;
    }
    
    
    /**
     * Erstellt eine Datenbank
     * @param string $dbname
     * @return boolean
     */
    public function createDatabase($dbname) {
        $createDb = $this->rootPdo->prepare(
            "CREATE DATABASE IF NOT EXISTS ".$dbname." CHARACTER SET utf8 COLLATE utf8_general_ci;"
            );
        return $createDb->execute();
    }
        
    
    /**
     * Löscht eine Datenbank
     * @param string $dbname
     * @return boolean
     */
    public function deleteDatabase($dbname) {
        $deleteDB = $this->rootPdo->prepare("DROP DATABASE ".$dbname.";");
        return $deleteDB->execute();
    }        
    
    
    /**
     * Liest die Benutzerrechte aus der Datenbank
     * @param string $username
     * @return array
     */
    public function getUserRights($username) {
        $selectUserRights = $this->rootPdo->prepare(
                "SELECT * FROM mysql.user WHERE user = :username;"
                );
        $selectUserRights->bindParam(':username', $username);
        $selectUserRights->execute();
        $result = $selectUserRights->fetchAll();
        return $result;
    }
    
    /**
     * Erstellt eine Verbindung zum MySQL-Server
     * @param string $username
     * @param string $password
     * @return boolean|PDO
     */
    public function openDbConnection($host, $username, $password) {
        $pdo = new PDO("mysql:host=$host;", $username, $password);
        return $pdo;     
    }    
        
    
    /**
     * Sucht eine bestimmte Datenbank
     * @param string $dbname
     * @return array
     */
    public function selectDatabase($dbname) {
        $selectDB = $this->rootPdo->prepare(
            "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = :name;"
            );
        $selectDB->bindParam(':name', $dbname);
        $selectDB->execute();
        $result = $selectDB->fetchAll();
        return $result;
    }
    
    
    /**
     * Liest alle Datenbanken aus, auf die der Benutzer zugriff hat
     * @param string $userShort
     * @param boolean $root
     * @return array
     */
    public function selectDatabases($userShort, $root) {       
        $selectDBs = $this->rootPdo->prepare(
            "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME LIKE :name;"
            );
        if (!$root) {
            $param = 'dev_'.$userShort.'%';
            $selectDBs->bindParam(':name', $param);
        } else {
            $param = 'dev%';
            $selectDBs->bindParam(':name', $param);
        }
        $selectDBs->execute();
        $result = $selectDBs->fetchAll();
        return $result;
    }

        
    /**
     * Gibt das Erstellungsdatum der Datenbank zurück
     * @param string $dbname
     * @return array
     */
    public function selectDbCreateDate($dbname) {
        $selectDate = $this->rootPdo->prepare(
            "SELECT MAX(DATE(last_seen)) FROM sys.x\$statement_analysis "
            . "WHERE query LIKE 'CREATE SCHEMA%' AND query LIKE :dbname;"
            );
        $db = '%'.$dbname.'%';
        $selectDate->bindParam(':dbname', $db);
        $selectDate->execute();
        $result = $selectDate->fetchAll();
        return $result;
    }
    
    
    /**
     * Gibt das Datum des Datenimports zurück
     * @param string $dbname
     * @return array
     */
    public function selectImportDate($dbname) {
        $selectImport = $this->rootPdo->prepare(
            "SELECT MAX(last_seen) FROM sys.x\$statement_analysis "
            . "WHERE query LIKE 'INSERT%' "
            . "AND query LIKE '%/*%*/%'"
            . "AND db = :dbname;"
            );
        $selectImport->bindParam(':dbname', $dbname);
        $selectImport->execute();
        $result = $selectImport->fetchAll();
        return $result;
    }
    
    
    /**
     * Gibt das Datum der letzten Änderung zurück
     * @param string $dbname
     * @return array
     */
    public function selectLastUpdateDate($dbname) {
        $selectUpdate = $this->rootPdo->prepare(
            "SELECT IF(MAX(DATE(UPDATE_TIME)) IS NULL, MAX(DATE(CREATE_TIME)), MAX(DATE(UPDATE_TIME)))"
            ."FROM information_schema.TABLES WHERE TABLE_SCHEMA = :dbname;"
            );
        $selectUpdate->bindParam(':dbname', $dbname);
        $selectUpdate->execute();
        $result = $selectUpdate->fetchAll();
        return $result;
    }
}

