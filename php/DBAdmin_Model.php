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
        $deleteDB = $this->rootPdo->prepare(
            "DROP DATABASE ".$dbname.";"
            );
        return $deleteDB->execute();
    }
    
    
    /**
     * Speichert das aktuelle Datum nach erfolgtem Import
     * @param string $dbname
     * @return boolean
     */
    public function insertImportDate($dbname) {
        $insertImportDate = $this->rootPdo->prepare(
            "INSERT INTO dbadmin.lastimport (dbname, importdate)"
            . "VALUES (:dbname, DATE(NOW()));"
            );
        $insertImportDate->bindParam(':dbname', $dbname);
        return $insertImportDate->execute();
    }
    
    
    /**
     * Erstellt eine Verbindung zum MySQL-Server
     * @param string $host
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
     * @return array
     */
    public function selectDatabases() {
        $exclude = '';
        if (!$_SESSION['root']) {
           $exclude = "WHERE is_SCHE.SCHEMA_NAME <> 'dbadmin' ";
        }
        
        $selectDBs = $this->rootPdo->prepare(
            "SELECT is_SCHE.SCHEMA_NAME AS dbname, "
            . "COALESCE(MAX(dba.importdate), '--') AS importdate, "
            . "COALESCE(MAX(DATE(is_TAB.UPDATE_TIME)), '--') AS changedate "
            . "FROM information_schema.SCHEMATA AS is_SCHE "
            . "LEFT JOIN dbadmin.lastimport AS dba ON is_SCHE.SCHEMA_NAME = dba.dbname "
            . "LEFT JOIN information_schema.TABLES AS is_TAB ON is_SCHE.SCHEMA_NAME = is_TAB.TABLE_SCHEMA "
            . $exclude
            . "GROUP BY is_SCHE.SCHEMA_NAME;"
            );  
        $selectDBs->execute();
        $result = $selectDBs->fetchAll();
        return $result;
    }     
    
    
    /**
     * Liest die Benutzerrechte aus der Datenbank
     * @param string $username
     * @return array
     */
    public function selectUserRights($host, $username) {
        $selectUserRights = $this->rootPdo->prepare(
            "SHOW GRANTS FOR '".$username."'@'".$host."';"
            );
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