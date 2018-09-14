<?php

class DBAdmin_Controller {

    protected $model = null;   
    protected $root = false;
    protected $sessionId = null;
    protected $username = null;
    
    public function __construct() {
        session_start();
        require_once 'DBAdmin_Model.php';
        $this->model = new DBAdmin_Model;

        if (count($_SESSION) > 1) {
            $this->root = $_SESSION['root'];
            $this->sessionId = $_SESSION['id'];
            $this->username = $_SESSION['username'];
        }
    }
    
    
    /**
     * Überprüft den Datenbanknamen
     * @param string $dbName
     */
    public function checkDbname($dbName) {
        $match = preg_match('/^dev_[a-z]{2}_[a-z]{2,3}_[a-z]{1,50}$/', $dbName);
        $umlaute = preg_match('/([äÄöÖüÜ])/', $dbName);
                                                          
        if ($umlaute === 0) {
            if (!$this->root) {
                if ($match === 0) {
                    throw new Exception(
                            "Der Datenbankname muss folgendes Format haben:".
                            "<br />'Benutzername_Applikation_Organisation'".
                            "<br />Beispiel: 'dev_xy_wz_kkk'"
                            );
                }
                $dbSubstrings = explode('_', $dbName);
                $check = $dbSubstrings[0].'_'.$dbSubstrings[1];

                if ($check !== $this->username) {
                    throw new Exception('Recht zum erstellen einer Datenbank mit diesem Namen fehlt!');
                }
            }
        } else {
            throw new Exception("Der Datenbankname darf keine Umlaute enthalten!");        
        }
    }
    
    
    /**
     * Erstellt eine Datenbank
     * @param string $dbName
     * @return \Throwable|boolean
     */
    public function createDatabase($dbName) {        
        try {
            $this->checkDbname($dbName);
                    
            $this->openRootDbConnection();

            // Prüfen ob gleichnamige Datenbank existiert
            if (count($this->model->getDatabase($dbName)) !== 0) {
                throw new Exception('Gleichnamige Datenbank existiert schon!');
            }

            if (!$this->model->createDatabase($dbName)) {
                throw new Exception('Erstellen der Datenbank fehlgeschlagen!');
            }
            $this->model->closeDbConnection($this->model->rootPdo);
            $return = true;
        } catch (Throwable $ex) {
            $return = $ex;
        }
        return $return;
    }
    
    
    /**
     * Löscht eine Datenbank
     * @param string $dbName
     * @return \Throwable|boolean
     */
    public function deleteDatabase($dbName) {   
        try {
            $this->openRootDbConnection();
            $result = $this->model->deleteDatabase($dbName);            
        
            if (!$result) {
                throw new Exception('Löschen der Datenbank fehlgeschlagen!');
            }
            $this->model->closeDbConnection($this->model->rootPdo);
            $return = true;
        } catch (Throwable $ex) {
            $return = $ex;
        }
        return $return;
    }
        
    
    /**
     * Dupliziert eine Datenbank
     * @param string $newDbName
     * @param string $oldDbName
     * @return \Throwable|boolean
     */
    public function duplicateDatabase($newDbName, $oldDbName) {        
        try {
            // Datenbankname prüfen
            $this->checkDbname($newDbName);
           
            $this->openRootDbConnection();
            
            // neue Datenbank erstellen
            $this->createDatabase($newDbName);

            // Datenbank exportieren
            $this->exportDatabase($oldDbName, false);

            // Datenbank importieren
            $data = new stdClass();
            $data->database = $newDbName;
            $data->delete  = true;
            $this->importDatabase($data);

            $this->model->closeDbConnection($this->model->rootPdo);
            $return = true;
        } catch (Throwable $ex) {
            $return = $ex;
        }
        return $return;
    }
        
    
    /**
     * 
     * @param string $dbName
     * @param boolean $exportOnly
     * @return \Throwable|boolean
     */
    public function exportDatabase($dbName, $exportOnly) {        
        try {
            $this->model->createDump($this->username, $this->sessionId, $dbName, $exportOnly);
            $return = true;
        } catch (Throwable $ex) {
            $return = $ex;
        }
        return $return;
    }
        
    
    /**
     * Gibt ein Array mit Datenbanken zurück
     * @return \Throwable|array
     */
    public function getDatabases() {
        try {
            // Benutzerdaten aus conf-File auslesen            
            $userData = [];
            $userConf = realpath('config').'/user_'.$this->username.'.conf';

            if (!is_file($userConf)) {
                throw new Exception('Die Conf-Datei des Users wurde nicht gefunden!');
            }
            $confFile = fopen($userConf, 'r');

            if (!$confFile) {
                throw new Exception('fopen ist fehlgeschlagen!');
            }

            while (($line = fgets($confFile)) !== false) {          
                if (mb_strpos($line, '=') !== false) {
                    $value = explode('=', $line);
                    $userData[] = trim($value[1]);
                }
            }
            fclose($confFile);

            // Anführungszeichen vor und nach dem Passwort entfernen
            $userData[2] = str_replace('"','',$userData[2]);

            // Datenbankverbindung herstellen
            $this->model->rootPdo = $this->model->openDbConnection($userData[0], $userData[1], $userData[2]);

            $result = $this->model->getDatabases($this->model->rootPdo);
            
            $return = [];
            
            foreach ($result as $database) {
                // evtl. Daten formatieren
                $importDate = $database['importDate'];
                if ($importDate !== '--') {
                    $importDate = date('d.m.Y', strtotime($importDate));
                }

                $changeDate = $database['changeDate'];
                if ($changeDate !== '--') {
                    $changeDate = date('d.m.Y', strtotime($changeDate));
                }
                
                $return[] = array(
                    'Datenbankname' => $database['dbName'], 
                    'Importdatum' => $importDate,
                    'Änderungsdatum' => $changeDate
                );
            }
            $this->model->closeDbConnection($this->model->rootPdo);
        } catch (Throwable $ex) {
            $return = $ex;
        }
        return $return;
    }
        
    
    /**
     * Gibt ein Array mit den Dumpnamen zurück
     * @return \Throwable|array
     */
    public function getDumpList() {
        if (isset($this->username)) {
            try{
                require_once 'DBAdmin_FileIO.php';
                $fileIO = new DBAdmin_FileIO();
                $return = $fileIO->getDumpList($this->username);
            } catch (Throwable $ex) {
                $return = $ex;
            }
            return $return;
        }
    }
    
    
    /**
     * Ermittelt ob Benutzer Root-Rechte hat
     * @param string $username
     * @return boolean
     */
    public function getUserRights($username) {
        $conf = realpath('config');
        $host = json_decode(file_get_contents($conf.'/config.json'));
        
        if (!isset($host->host)) {
            throw new Exception('Datei config.json ist fehlerhaft!');
        }
        $result = $this->model->getUserRights($host->host, $username);

        if (strpos($result[0][0], 'ALL PRIVILEGES ON *.*') === false) {
            return false;
        } else {
            return true;
        }
    }    
    
    
    /**
     * Importiert einen Dump in die Datenbank
     * @param Object $data
     * @return \Throwable|boolean
     */
    public function importDatabase($data) { 
        $dbName = $data->database;
        $dump = isset($data->dumps) ? $data->dumps : null;
        $delete = $data->delete;        
        try {
            $this->model->executeDump($this->username, $this->sessionId, $dump, $dbName, $delete);

            $this->openRootDbConnection();
            if (!$this->model->insertImportDate($dbName)) {
                throw new Exception('Speichern des Import-Datums fehlgeschlagen!');
            }
            $this->model->closeDbConnection($this->model->rootPdo);
            $return = true;
        } catch (Throwable $ex) {
            $return = $ex;
        }
        return $return;
    }
        
    
    /**
     * Logt den Benutzer ein
     * @param Object $data
     * @return \Throwable|boolean
     */
    public function loginUser($data) {        
        try {
            $username = mb_strtolower($data->username);
            $password = $data->password;

            require_once 'DBAdmin_Model.php';
            $this->model = new DBAdmin_Model();

            $conf = realpath('config');
            if (!is_file($conf.'/config.json')) {
                throw new Exception('Datei config.json nicht gefunden!');
            }

            $config = json_decode(file_get_contents($conf.'/config.json'));

            if (!isset($config->host)) {
                throw new Exception('Datei config.json ist fehlerhaft!');
            }
            $host = $config->host;                  
            
            try {
                $pdo = $this->model->openDbConnection($host, $username, $password);
            } catch (Throwable $ex) {
                throw new Exception('Benutzername oder Passwort falsch!');
            }
            $this->model->closeDbConnection($pdo);

            $this->openRootDbConnection();

            // Rechte des Benutzers abfragen
            $root = $this->getUserRights($username);
            $this->model->closeDbConnection($this->model->rootPdo);                                     

            // conf-File für Benutzer erstellen
            $confFile = fopen($conf.'/user_'.$username.'.conf', 'w');

            if (!$confFile) {
                throw new Exception('fopen ist fehlgeschlagen!');
            }
            $txt = "[client]\r\nhost=".$host."\r\nuser=".$username."\r\npassword=\"".$password."\"";
            $write = fwrite($confFile, $txt);

            if ($write === false) {
                throw new Exception('fwrite ist fehlgeschlagen!');
            }
            fclose($confFile);
            
            $return = array(
                'root' => $root,
                'username' => $username,
                'id' => md5($password)
            );
        } catch (Throwable $ex) {
            $return = $ex;
        }
        return $return;      
    }
        
    
    /**
     * Logt den Benutzer aus
     * @return boolean
     */
    public function logoutUser() { 
        unlink(realpath('config/user_'.$this->username.'.conf'));
        session_destroy();
        return true;
    }
    
    
    /**
     * Erstellt eine Verbindung zur Datenbank
     */
    public function openRootDbConnection() {
        require_once 'DBAdmin_Model.php';
        $this->model = new DBAdmin_Model();
        
        // MySQL-Verbindung herstellen
        $conf = realpath('config');
        $config = json_decode(file_get_contents($conf.'/config.json'));
        
        if (!isset($config->host)) {
            throw new Exception('Datei config.json ist fehlerhaft!');
        }
        $this->model->rootPdo = $this->model->openDbConnection($config->host, $config->user, $config->password);
    }
    
    
    /**
     * Benennt eine Datenbank um
     * @param string $newDbName
     * @param string $oldDbName
     * @return \Throwable|boolean
     */
    public function renameDatabase($newDbName, $oldDbName) {        
        try {
            $this->checkDbname($newDbName);

            $this->openRootDbConnection();

            // neue Datenbank erstellen
            $this->createDatabase($newDbName);

            // Datenbank exportieren
            $this->exportDatabase($oldDbName, false);

            // Datenbank löschen
            $this->model->deleteDatabase($oldDbName);        

            // Datenbank importieren
            $data = new stdClass();
            $data->database = $newDbName;
            $data->delete = true;
            $this->importDatabase($data);
            
            $this->model->closeDbConnection($this->model->rootPdo);
            $return = true;
        } catch (Throwable $ex) {
            $return = $ex;
        }
        return $return;
    }
    
    
    /**
     * Analysiert die Requests
     */
    public function run() {
        $requests = json_decode(file_get_contents("php://input"));
        $responses = array();
        
        $lastUsedDatabase = null;
        
        // letzte verwendete Datenbank abrufen
        if (isset($_SESSION['lastUsedDatabase'])) {
            $lastUsedDatabase = $_SESSION['lastUsedDatabase'];
        }

        if (isset($requests)) {
            foreach ($requests as $request) {
                $response = new stdClass();
                $response->tid = $request->tid;               
                
                try {
                    switch ($request->facadeFn) {

                        // feststellen ob ein Benutzer angemeldet ist
                        case 'dbadmin.checkLogin':
                            $response->data = new stdClass();
                            
                            if (isset($this->username)) {
                                $response->data = array(
                                    'username' => $this->username
                                );
                            } else {
                                $response->data = array(
                                    'username' => false
                                );
                            }
                            break;
                            
                        // neue Datenbank erstellen
                        case 'dbadmin.create':
                            $response->data = new stdClass();
                            $return = $this->createDatabase($request->data->formData->newDbName);

                            if ($return instanceof Exception || $return instanceof Error) {
                                $response->errorMsg = $return->getMessage(); 
                            } else {
                                $lastUsedDatabase = $request->data->formData->newDbName;
                                $response->data = array(
                                    'success' => 'true'
                                );
                            }
                            break;
                            
                        // Datenbank löschen
                        case 'dbadmin.delete':
                            $response->data = new stdClass();
                            $return = $this->deleteDatabase($request->data->Datenbankname);

                            if ($return instanceof Exception || $return instanceof Error) {
                                $response->errorMsg = $return->getMessage(); 
                            } else {
                                $response->data = array(
                                    'success' => 'true'
                                );
                            }
                            break;
                            
                        // Datenbank duplizieren
                        case 'dbadmin.duplicate':
                            $response->data = new stdClass();
                            $return = $this->duplicateDatabase($request->data->formData->newDbName, $request->data->formData->oldDbName);

                            if ($return instanceof Exception || $return instanceof Error) {
                                $response->errorMsg = $return->getMessage(); 
                            } else {
                                $lastUsedDatabase = $request->data->formData->newDbName;
                                $response->data = array(
                                    'success' => 'true'
                                );
                            }
                            break;
                            
                        // Datenbank exportieren
                        case 'dbadmin.export':
                            $response->data = new stdClass();
                            $return = $this->exportDatabase($request->data->Datenbankname, true);

                            if ($return instanceof Exception || $return instanceof Error) {
                                $response->errorMsg = $return->getMessage(); 
                            } else {
                                $lastUsedDatabase = $request->data->Datenbankname;
                                $response->data = array(
                                    'success' => 'true'
                                );
                            }
                            break;
                            
                        // Dump importieren
                        case 'dbadmin.import':
                            $response->data = new stdClass();
                            $return = $this->importDatabase($request->data->formData);

                            if ($return instanceof Exception || $return instanceof Error) {
                                $response->errorMsg = $return->getMessage(); 
                            } else {
                                $lastUsedDatabase = $request->data->formData->database;
                                $response->data = array(
                                    'success' => 'true'
                                );                              
                            }
                            break;
                            
                        // Datenbanken auslesen
                        case 'dbadmin.loadDbs':
                            $response->rows = new stdClass();
                            $response->selectFilters = new stdClass();
                            $return = $this->getDatabases();

                            if ($return instanceof Exception || $return instanceof Error) {
                                $response->errorMsg = $return->getMessage(); 
                            } else {                                                   
                                $response->rows = $return;
                                
                                if (isset($lastUsedDatabase)) {
                                    $response->selectFilters->field = 'Datenbankname';
                                    $response->selectFilters->value = $lastUsedDatabase;
                                    $lastUsedDatabase = null;
                                } else {
                                    $response->selectFilters = null;
                                }                                                                
                            }
                            break;

                        // Dumps auslesen
                        case 'dbadmin.loadDumps':
                            $response->rows = new stdClass();
                            $return = $this->getDumpList();

                            if ($return instanceof Exception || $return instanceof Error) {
                                $response->errorMsg = $return->getMessage(); 
                            } else {
                                $rows = array();

                                foreach ($return as $dump) {
                                    $rows[] = array(
                                        'value' => $dump,
                                        'caption' => $dump
                                    );
                                }                    
                                $response->rows = $rows;
                            }
                            break;

                        // Einloggen
                        case 'dbadmin.login':
                            $response->data = new stdClass();
                            $return = $this->loginUser($request->data->formData);

                            if ($return instanceof Exception || $return instanceof Error) {
                                $response->errorMsg = $return->getMessage(); 
                            } else {
                                $response->data = array(
                                    'success' => 'true'
                                );
                                // Daten in Session speichern
                                $_SESSION['root'] = $return['root'];
                                $_SESSION['username'] = $return['username']; 
                                $_SESSION['id'] = $return['id'];
                            }
                            break;
                            
                        // Ausloggen
                        case 'dbadmin.logout':
                            $return = $this->logoutUser();                
                            break;

                        // Datenbank umbenennen
                        case 'dbadmin.rename':
                            $response->data = new stdClass();
                            $return = $this->renameDatabase($request->data->formData->newDbName, $request->data->formData->oldDbName);

                            if ($return instanceof Exception || $return instanceof Error) {
                                $response->errorMsg = $return->getMessage(); 
                            } else {
                                $lastUsedDatabase = $request->data->formData->newDbName;
                                $response->data = array(
                                    'success' => 'true'
                                );
                            }
                            break;

                        default: 
                            $response->errorMsg = 'Aktion nicht gefunden!';   

                    }                               
                } catch (Exception $ex) {
                    $response->errorMsg = $ex->getMessage();
                }
                $responses[] = $response;
            }
            // letzte verwendete Datenbank in Session speichern
            $_SESSION['lastUsedDatabase'] = $lastUsedDatabase;            
            
            // Antwort ausgeben
            print(json_encode($responses));
        } else {
            // HTML laden
            echo file_get_contents('template/main.html');
        }
    }
}