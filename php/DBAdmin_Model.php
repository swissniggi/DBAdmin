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
     * Speichert das aktuelle Datum nach erfolgtem Import
     * @param string $dbname
     */
    public function insertImportDate($dbname) {
        $insertImportDate = $this->rootPdo->prepare(
                "INSERT INTO devimport.lastimport (dbname, importdate)"
                . "VALUES (:dbname, DATE(NOW()));"
                );
        $insertImportDate->bindParam(':dbname', $dbname);
        $insertImportDate->execute();
    }
    
    
    /**
     * Erstellt eine Verbindung zum MySQL-Server
     * @param string $username
     * @param string $password
     * @return boolean|PDO
     */
    public function openDbConnection($host, $username, $password) {
        $pdo = new PDO("mysql:host=$host;", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
     * Liest alle Daten für die HTML-Tabelle aus
     * @param string $userShort
     * @param boolean $root
     * @return array
     */
    public function selectDatabases($userShort, $root) {       
        $selectDBs = $this->rootPdo->prepare(
            "SELECT is_SCHE.SCHEMA_NAME AS dbname, "
                . "COALESCE(MAX(dev_imp.importdate), '--') AS importdate, "
                . "COALESCE(MAX(DATE(is_TAB.UPDATE_TIME)), '--') AS changedate "
                . "FROM information_schema.SCHEMATA AS is_SCHE "
                . "LEFT JOIN devimport.lastimport AS dev_imp ON is_SCHE.SCHEMA_NAME = dev_imp.dbname "
                . "LEFT JOIN information_schema.TABLES AS is_TAB ON is_SCHE.SCHEMA_NAME = is_TAB.TABLE_SCHEMA "
                . "WHERE SCHEMA_NAME LIKE :name "
                . "GROUP BY is_SCHE.SCHEMA_NAME;"
            );
        if (!$root) {
            $param = 'dev_'.$userShort.'%';
            $selectDBs->bindParam(':name', $param);
        } else {
            $param = 'dev\_%';
            $selectDBs->bindParam(':name', $param);
        }
        $selectDBs->execute();
        $result = $selectDBs->fetchAll();
        return $result;
    }     
    
    
    /**
     * Liest die Benutzerrechte aus der Datenbank
     * @param string $username
     * @return array
     */
    public function selectUserRights($username) {
        $selectUserRights = $this->rootPdo->prepare(
                "SELECT * FROM mysql.user WHERE user = :username;"
                );
        $selectUserRights->bindParam(':username', $username);
        $selectUserRights->execute();
        $result = $selectUserRights->fetchAll();
        return $result;
    }
    
    
    /**
     * Liest alle MySQL-Benutzer aus
     * @return array
     */
    public function selectUsers() {
        $selectUsers = $this->rootPdo->prepare(
                "SELECT User FROM mysql.user;"
                );
        $selectUsers->execute();
        $result = $selectUsers->fetchAll();
        return $result;
    }
}