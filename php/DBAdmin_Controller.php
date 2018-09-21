<?php

class DBAdmin_Controller {

    protected $model = null;
    protected $sessionId = null;
    protected $username = null;
    
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    public function __construct() {
        session_start();

        if (count($_SESSION) > 1) {
            $this->sessionId = $_SESSION['id'];
            $this->username = $_SESSION['username'];
        }
        require_once 'DBAdmin_Model.php';
        $this->model = new DBAdmin_Model;
    }
    
    
    // --------------------------------------------------------------
    // PUBLIC MEMBERS
    // --------------------------------------------------------------
    /**
     * Analysiert die Requests
     */
    public function run() {
        $requests = json_decode(file_get_contents("php://input"));
        
        if (isset($requests)) {
            $responses = array();
            $lastUsedDatabase = null;

            // letzte verwendete Datenbank abrufen
            if (isset($_SESSION['lastUsedDatabase'])) {
                $lastUsedDatabase = $_SESSION['lastUsedDatabase'];
            }
            
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
                            $return = $this->_createDatabase($request->data->formData->newDbName);

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
                            $return = $this->_deleteDatabase($request->data->Datenbankname);

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
                            $return = $this->_duplicateDatabase($request->data->formData->newDbName, $request->data->formData->oldDbName);

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
                            $return = $this->_exportDatabase($request->data->Datenbankname, true);

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
                            $return = $this->_importDatabase($request->data->formData);

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
                            $return = $this->_getDatabases();

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
                            $return = $this->_getDumpList();

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
                            $return = $this->_loginUser($request->data->formData);

                            if ($return instanceof Exception || $return instanceof Error) {
                                $response->errorMsg = $return->getMessage(); 
                            } else {
                                $response->data = array(
                                    'success' => 'true'
                                );
                                // Daten in Session speichern
                                $_SESSION['username'] = $return['username']; 
                                $_SESSION['id'] = $return['id'];
                            }
                            break;
                            
                        // Ausloggen
                        case 'dbadmin.logout':
                            $return = $this->_logoutUser();                
                            break;

                        // Datenbank umbenennen
                        case 'dbadmin.rename':
                            $response->data = new stdClass();
                            $return = $this->_renameDatabase($request->data->formData->newDbName, $request->data->formData->oldDbName);

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
    
    
    // --------------------------------------------------------------
    // PRIVATE MEMBERS
    // --------------------------------------------------------------
    /**
     * Erstellt eine Datenbank
     * @param string $dbName
     * @return \Throwable|boolean
     */
    private function _createDatabase($dbName) {        
        try {
            $this->_openDbConnection();

            // Prüfen ob gleichnamige Datenbank existiert
            if (count($this->model->getDatabase($dbName)) !== 0) {
                throw new Exception('Gleichnamige Datenbank existiert schon!');
            }

            try {
                $this->model->createDatabase($dbName); 
            } catch (Exception $ex) {
                throw new Exception('Erstellen der Datenbank fehlgeschlagen!<br />Möglicherweise hast du keine Berechtigung.');
            }
            $this->model->closeDbConnection($this->model->pdo);
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
    private function _deleteDatabase($dbName) {   
        try {
            $this->_openDbConnection();
            $result = $this->model->deleteDatabase($dbName);            
        
            if (!$result) {
                throw new Exception('Löschen der Datenbank fehlgeschlagen!');
            }
            $this->model->closeDbConnection($this->model->pdo);
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
    private function _duplicateDatabase($newDbName, $oldDbName) {        
        try {
            $this->_openDbConnection();
            
            // neue Datenbank erstellen
            $this->_createDatabase($newDbName);

            // Datenbank exportieren
            $this->_exportDatabase($oldDbName, false);

            // Datenbank importieren
            $data = new stdClass();
            $data->database = $newDbName;
            $data->delete  = true;
            $this->_importDatabase($data);

            $this->model->closeDbConnection($this->model->pdo);
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
    private function _exportDatabase($dbName, $exportOnly) {        
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
    private function _getDatabases() {
        try {
            $this->_openDbConnection();

            $result = $this->model->getDatabases();
            
            $return = [];
            
            foreach ($result as $database) {
                $numberOfTables = $this->model->getNumberOfTables($database['dbName']);
                $sizeOfDatabase = $this->model->getDatabaseSize($database['dbName']);
                
                if (isset($sizeOfDatabase[0]['dbSize'])) {
                    $dbSize = $sizeOfDatabase[0]['dbSize'].' KB';
                } else {
                    $dbSize = '0 KB';
                }

                // evtl. Daten formatieren
                $importDate = $database['importDate'];
                if ($importDate !== '--') {
                    $importDate = date('d.m.Y H:i', strtotime($importDate));
                }

                $changeDate = $database['changeDate'];
                if ($changeDate !== '--') {
                    $changeDate = date('d.m.Y H:i', strtotime($changeDate));
                }
                
                $return[] = array(
                    'Datenbankname' => $database['dbName'], 
                    'Importdatum' => $importDate,
                    'Änderungsdatum' => $changeDate,
                    'AnzahlTabellen' => $numberOfTables[0]['numberOfTables'],
                    'DatenbankGrösse' => $dbSize
                );
            }
            $this->model->closeDbConnection($this->model->pdo);
        } catch (Throwable $ex) {
            $return = $ex;
        }
        return $return;
    }
        
    
    /**
     * Gibt ein Array mit den Dumpnamen zurück
     * @return \Throwable|array
     */
    private function _getDumpList() {
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
     * Importiert einen Dump in die Datenbank
     * @param Object $data
     * @return \Throwable|boolean
     */
    private function _importDatabase($data) { 
        $dbName = $data->database;
        $dump = isset($data->dumps) ? $data->dumps : null;
        $delete = $data->delete;        
        try {
            $this->model->executeDump($this->username, $this->sessionId, $dump, $dbName, $delete);

            $this->_openDbConnection();
            if (!$this->model->insertImportDate($dbName)) {
                throw new Exception('Speichern des Import-Datums fehlgeschlagen!');
            }
            $this->model->closeDbConnection($this->model->pdo);
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
    private function _loginUser($data) {        
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
    private function _logoutUser() { 
        unlink(realpath('config/user_'.$this->username.'.conf'));
        session_destroy();
        return true;
    }
    
    
    /**
    * Stellt eine Verbindung zu Datenbank her
    */
    private function _openDbConnection() {
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
        $this->model->pdo = $this->model->openDbConnection($userData[0], $userData[1], $userData[2]);
    }
    
    
    /**
     * Benennt eine Datenbank um
     * @param string $newDbName
     * @param string $oldDbName
     * @return \Throwable|boolean
     */
    private function _renameDatabase($newDbName, $oldDbName) {        
        try {
            $this->_openDbConnection();

            // neue Datenbank erstellen
            $this->_createDatabase($newDbName);

            // Datenbank exportieren
            $this->_exportDatabase($oldDbName, false);

            // Datenbank löschen
            $this->model->deleteDatabase($oldDbName);        

            // Datenbank importieren
            $data = new stdClass();
            $data->database = $newDbName;
            $data->delete = true;
            $this->_importDatabase($data);
            
            $this->model->closeDbConnection($this->model->pdo);
            $return = true;
        } catch (Throwable $ex) {
            $return = $ex;
        }
        return $return;
    }
    
}
