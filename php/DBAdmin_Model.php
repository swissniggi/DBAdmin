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
     * @param string $dbName
     * @return boolean
     */
    public function createDatabase($dbName) {
        $createDb = $this->rootPdo->prepare(
            "CREATE DATABASE ".$dbName." CHARACTER SET utf8 COLLATE utf8_general_ci;"
            );
        return $createDb->execute();
    }
    
    
    /**
     * Exportiert einen Dump via Kommandozeilenbefehl
     * @param string $username
     * @param string $sessionId
     * @param string $dbName
     * @param boolean $exportOnly
     */
    public function createDump($username, $sessionId, $dbName, $exportOnly) {
        // Pfad des Config-Files angeben
        // es enthält den MySQL-Benutzernamen und das Passwort, sowie den Hostnamen
        $mysqlConf = realpath('config/user_'.$username.'.conf');
        
        if (!is_file($mysqlConf)) {
            throw new Exception('Die Conf-Datei des Users existiert nicht!');
        }
        // Dumppfad definieren
        $dumps = json_decode(file_get_contents('config/config.json'))->dumps;
        $user = $username;
        $fileName = $exportOnly ? $dbName : $sessionId;
        $dbPath = realpath($dumps).'/'.$user.'/'.$fileName.'.sql';
        
        // Dump exportieren
        $command = 'mysqldump --defaults-file="'.escapeshellarg($mysqlConf).'" --events --routines --triggers '
                   .escapeshellarg($dbName).' > "'.escapeshellarg($dbPath).'" 2>&1';    
        exec($command, $out, $return);
        
        if ($return !== 0) {
            throw new Exception($out[0]);
        }
    }
        
    
    /**
     * Löscht eine Datenbank
     * @param string $dbName
     * @return boolean
     */
    public function deleteDatabase($dbName) {
        $deleteDB = $this->rootPdo->prepare(
            "DROP DATABASE ".$dbName.";"
            );
        return $deleteDB->execute();
    }
    
    
    /**
     * Importiert einen Dump via Kommandozeilenbefehl
     * @param string $username
     * @param string $sessionId
     * @param string|null $dump
     * @param string $dbName
     * @param boolean $delete
     */
    public function executeDump($username, $sessionId, $dump, $dbName, $delete) {
        // Pfad des Config-Files angeben
        // es enthält den MySQL-Benutzernamen und das Passwort, sowie den Hostnamen
        $mysqlConf = realpath('config/user_'.$username.'.conf');
        
        if (!is_file($mysqlConf)) {
            throw new Exception('Die Conf-Datei des Users existiert nicht!');
        }
        // Dumpnamen definieren
        $fileName = $dump === null ? $sessionId.'.sql' : $dump;
        
        $dumps = json_decode(file_get_contents('config/config.json'))->dumps;
        $user = $username;
        $dbPath = realpath($dumps.'/'.$user.'/'.$fileName);        
        
        // Dump importieren
        $command = 'mysql --defaults-file="'.escapeshellarg($mysqlConf).'" '.escapeshellarg($dbName).' < "'.escapeshellarg($dbPath).'" 2>&1';  
        exec($command, $out, $return);
        
        if ($delete && $return === 0) {            
            unlink($dbPath);
        }
        
        if ($return !== 0) {
            throw new Exception($out[0]);
        }        
    }
    
    
    /**
     * Sucht eine bestimmte Datenbank
     * @param string $dbName
     * @return array
     */
    public function getDatabase($dbName) {
        $getDB = $this->rootPdo->prepare(
            "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = :name;"
            );
        $getDB->bindParam(':name', $dbName);
        $getDB->execute();
        $result = $getDB->fetchAll();
        return $result;
    }
    
    
    /**
     * Liest alle Daten für die HTML-Tabelle aus
     * @param Object $pdo
     * @return array
     */
    public function getDatabases($pdo) {
        $exclude = '';
        if (!$_SESSION['root']) {
           $exclude = "WHERE is_SCHE.SCHEMA_NAME <> 'dbadmin' ";
        }
        
        $getDBs = $pdo->prepare(
            "SELECT is_SCHE.SCHEMA_NAME AS dbName, "
            . "COALESCE(MAX(dba.importDate), '--') AS importDate, "
            . "COALESCE(MAX(DATE(is_TAB.UPDATE_TIME)), '--') AS changeDate "
            . "FROM information_schema.SCHEMATA AS is_SCHE "
            . "LEFT JOIN dbadmin.lastimport AS dba ON is_SCHE.SCHEMA_NAME = dba.dbName "
            . "LEFT JOIN information_schema.TABLES AS is_TAB ON is_SCHE.SCHEMA_NAME = is_TAB.TABLE_SCHEMA "
            . $exclude
            . "GROUP BY is_SCHE.SCHEMA_NAME;"
            );  
        $getDBs->execute();
        $result = $getDBs->fetchAll();
        return $result;
    }     
    
    
    /**
     * Liest die Benutzerrechte aus der Datenbank
     * @param string $host
     * @param string $username
     * @return array
     */
    public function getUserRights($host, $username) {
        $getUserRights = $this->rootPdo->prepare(
            "SHOW GRANTS FOR '".$username."'@'".$host."';"
            );
        $getUserRights->execute();
        $result = $getUserRights->fetchAll();
        return $result;
    }
    
    
    /**
     * Liest alle MySQL-Benutzer aus
     * @return array
     */
    public function getUsers() {
        $getUsers = $this->rootPdo->prepare(
            "SELECT User FROM mysql.user;"
            );
        $getUsers->execute();
        $result = $getUsers->fetchAll();
        return $result;
    }
    
    
    /**
     * Speichert das aktuelle Datum nach erfolgtem Import
     * @param string $dbName
     * @return boolean
     */
    public function insertImportDate($dbName) {
        $insertImportDate = $this->rootPdo->prepare(
            "INSERT INTO dbadmin.lastimport (dbName, importDate) "
            . "VALUES (:dbName, DATE(NOW()));"
            );
        $insertImportDate->bindParam(':dbName', $dbName);
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
}