<?php

class DBAdmin_Model {
    
    public $rootPdo = null;
    
    /**
     * Erstellt eine Verbindung zum MySQL-Server
     * @param string $username
     * @param string $password
     * @return boolean|PDO
     */
    public function openDbConnection($username, $password) {
        try {
            $pdo = new PDO('mysql:host=localhost', $username, $password);
            return $pdo;
        } catch (PDOException $pex) {
            return false;
        }        
    }
    
    
    /**
     * Beendet die Datenbankverbindung
     * @param PDO $pdo
     */
    public function closeDbConnection($pdo) {
        $pdo = null;
    }
    
    
    /**
     * Liest die Benutzerrechte aus der Datenbank
     * @param string $username
     * @return array
     */
    public function getUserRights($username) {
        try {
            $selectUserRights = $this->rootPdo->prepare(
                    "SELECT * FROM mysql.user WHERE user = :username;"
                    );
            $selectUserRights->bindParam(':username', $username);
            $selectUserRights->execute();
            $result = $selectUserRights->fetchAll();
            return $result;
        } catch (PDOException $pex) {
            return false;
        }
    }
    
    
    /**
     * Liest alle Datenbanken aus, auf die der Benutzer zugriff hat
     * @param string $userShort
     * @param boolean $root
     * @return array
     */
    public function selectDatabases($userShort, $root) {       
        try {
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
        } catch (PDOException $pex) {
            return false;
        }
    }
    
    
    /**
     * Sucht eine bestimmte Datenbank
     * @param string $dbname
     * @return array
     */
    public function selectDatabase($dbname) {
        try {
            $selectDB = $this->rootPdo->prepare(
                "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = :name;"
                );
            $selectDB->bindParam(':name', $dbname);
            $selectDB->execute();
            $result = $selectDB->fetchAll();
            return $result;
        } catch (PDOException $pex) {
            return false;
        }
    }
    
    
    /**
     * Gibt das Erstellungsdatum der Datenbank zurück
     * @param string $dbname
     * @return array
     */
    public function selectDbCreateDate($dbname) {
        try {
            $selectDate = $this->rootPdo->prepare(
                "SELECT MAX(DATE(last_seen)) FROM sys.x\$statement_analysis "
                . "WHERE query LIKE 'CREATE SCHEMA%' AND query LIKE :dbname;"
                );
            $db = '%'.$dbname.'%';
            $selectDate->bindParam(':dbname', $db);
            $selectDate->execute();
            $result = $selectDate->fetchAll();
            return $result;
        } catch (PDOException $pex) {
            return false;
        }
    }
    
    
    /**
     * Gibt das Datum der letzten Änderung zurück
     * @param string $dbname
     * @return array
     */
    public function selectLastUpdateDate($dbname) {
        try {
            $selectUpdate = $this->rootPdo->prepare(
                "SELECT IF(MAX(DATE(UPDATE_TIME)) IS NULL, MAX(DATE(CREATE_TIME)), MAX(DATE(UPDATE_TIME)))"
                ."FROM information_schema.TABLES WHERE TABLE_SCHEMA = :dbname;"
                );
            $selectUpdate->bindParam(':dbname', $dbname);
            $selectUpdate->execute();
            $result = $selectUpdate->fetchAll();
            return $result;
        } catch (PDOException $pex) {
            return false;
        }
    }
    
    
    /**
     * Gibt das Datum des Datenimports zurück
     * @param string $dbname
     * @return array
     */
    public function selectImportDate($dbname) {
        try {
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
        } catch (PDOException $pex) {
            return false;
        }
    }
    
    
    /**
     * Löscht eine Datenbank
     * @param string $dbname
     * @return boolean
     */
    public function deleteDatabase($dbname) {
        try {
            $deleteDB = $this->rootPdo->prepare("DROP DATABASE ".$dbname.";");
            return $deleteDB->execute();
        } catch (PDOException $pex) {
            return false;
        }
    }
    
    
    /**
     * Erstellt eine Datenbank
     * @param string $dbname
     * @return boolean
     */
    public function createDatabase($dbname) {
        try {
            $createDb = $this->rootPdo->prepare(
                "CREATE DATABASE IF NOT EXISTS ".$dbname." CHARACTER SET utf8 COLLATE utf8_general_ci;"
                );
            return $createDb->execute();
        } catch (Exception $ex) {
            return false;
        }
    }
}

