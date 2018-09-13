<?php

class DBAdmin_Controller {
       
    protected $model = null;
    
    public function __construct() {
        require_once 'DBAdmin_Model.php';
        $this->model = new DBAdmin_Model;
    }
    
    
    /**
     * Überprüft den Datenbanknamen
     * @param string $dbname
     */
    public function checkDbname($dbname) {
        $match = preg_match('/^dev_[a-z]{2}_[a-z]{2,3}_[a-z]{1,50}$/', $dbname);
        $umlaute = preg_match('/([äÄöÖüÜ])/', $dbname);
                                                          
        if (!$_SESSION['root']) {
            if ($match === 0) {
                throw new Exception(
                        "Der Datenbankname muss folgendes Format haben:".
                        "<br />'Benutzername_Applikation_Organisation'".
                        "<br />Beispiel: 'dev_xy_wz_kkk'"
                        );
            }
            $dbSubstrings = explode('_', $dbname);
            $check = $dbSubstrings[0].'_'.$dbSubstrings[1];

            if ($check !== $_SESSION['username']) {
                throw new Exception('Recht zum erstellen einer Datenbank mit diesem Namen fehlt!');
            }
        } else if ($umlaute !== 0) {
            throw new Exception("Der Datenbankname darf keine Umlaute enthalten!");        
        }
    }
    
    
    /**
     * Erstellt eine Datenbank
     * @param string $dbname
     * @return \Throwable|boolean
     */
    public function createDatabase($dbname) {        
        try {
            $this->checkDbname($dbname);
                    
            $this->openRootDbConnection();

            // Prüfen ob gleichnamige Datenbank existiert
            if (count($this->model->selectDatabase($dbname)) !== 0) {
                throw new Exception('Gleichnamige Datenbank existiert schon!');
            }

            // Datenbank erstellen
            if (!$this->model->createDatabase($dbname)) {
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
     * @param string $dbname
     * @return \Throwable|boolean
     */
    public function deleteDatabase($dbname) {   
        try {
            $this->openRootDbConnection();
            $result = $this->model->deleteDatabase($dbname);            
        
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
     * @param string $newDbname
     * @param string $oldDbname
     * @return \Throwable|boolean
     */
    public function duplicateDatabase($newDbname, $oldDbname) {        
        try {
            // Datenbankname prüfen
            $this->checkDbname($newDbname);
           
            $this->openRootDbConnection();
            
            // neue Datenbank erstellen
            $this->createDatabase($newDbname);

            // Datenbank exportieren
            $this->exportDatabase($oldDbname, false);

            // Datenbank importieren
            $data = new stdClass();
            $data->database = $newDbname;
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
     * @param string $dbname
     * @param boolean $exportOnly
     * @return \Throwable|boolean
     */
    public function exportDatabase($dbname, $exportOnly) {        
        try {
            require_once 'DBAdmin_FileReader.php';
            $reader = new DBAdmin_FileReader();
            $reader->createDump($dbname, $exportOnly);
            $return = true;
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
        if (isset($_SESSION['username'])) {
            try{
                require_once 'DBAdmin_FileReader.php';
                $fileReader = new DBAdmin_FileReader();
                $return = $fileReader->getDumpList();
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
        $conf = realpath('../config');
        $host = json_decode(file_get_contents($conf.'/config.json'));
        
        if (!isset($host->host)) {
            throw new Exception('Datei config.json ist fehlerhaft!');
        }
        $result = $this->model->selectUserRights($host->host, $username);

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
        $dbname = $data->database;
        $dump = isset($data->dumps) ? $data->dumps : null;
        $delete = $data->delete;        
        try {            
            require_once 'DBAdmin_FileReader.php';
            $reader = new DBAdmin_FileReader();
            $reader->executeDump($dump, $dbname, $delete);

            $this->openRootDbConnection();
            if (!$this->model->insertImportDate($dbname)) {
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

            $conf = realpath('../config');
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
               
            // mit root zur Datenbank verbinden
            $this->openRootDbConnection();

            // eruieren ob der eingeloggte User root-Rechte hat
            $root = $this->getUserRights($username);
            $this->model->closeDbConnection($this->model->rootPdo);                                     

            // conf-File für Benutzer erstellen
            $conffile = fopen($conf.'/user_'.$username.'.conf', 'w');

            if (!$conffile) {
                throw new Exception('fopen ist fehlgeschlagen!');
            }
            $txt = "[client]\r\nhost=".$host."\r\nuser=".$username."\r\npassword=\"".$password."\"";
            $write = fwrite($conffile, $txt);

            if ($write === false) {
                throw new Exception('fwrite ist fehlgeschlagen!');
            }
            fclose($conffile);

            $_SESSION['root'] = $root;
            $_SESSION['username'] = $username; 
            $_SESSION['id'] = md5($password);
            $return = true;
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
        unlink(realpath('../config/user_'.$_SESSION['username'].'.conf'));
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
        $conf = realpath('../config');
        $config = json_decode(file_get_contents($conf.'/config.json'));
        
        if (!isset($config->host)) {
            throw new Exception('Datei config.json ist fehlerhaft!');
        }
        $this->model->rootPdo = $this->model->openDbConnection($config->host, $config->user, $config->password);
    }
    
    
    /**
     * Benennt eine Datenbank um
     * @param string $newDbname
     * @param string $oldDbname
     * @return \Throwable|boolean
     */
    public function renameDatabase($newDbname, $oldDbname) {        
        try {
            $this->checkDbname($newDbname);

            $this->openRootDbConnection();

            // neue Datenbank erstellen
            $this->createDatabase($newDbname);

            // Datenbank exportieren
            $this->exportDatabase($oldDbname, false);

            // Datenbank löschen
            $this->model->deleteDatabase($oldDbname);        

            // Datenbank importieren
            $data = new stdClass();
            $data->database = $newDbname;
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
     * Gibt ein Array mit Datenbanken zurück
     * @return \Throwable|array
     */
    public function selectDatabases() {
        if (isset($_SESSION['username'])) {
            try {
                // Benutzerdaten aus conf-File auslesen            
                $userdata = [];
                $userconf = realpath('../config').'/user_'.$_SESSION['username'].'.conf';

                if (!is_file($userconf)) {
                    throw new Exception('Die Conf-Datei des Users wurde nicht gefunden!');
                }
                $conffile = fopen($userconf, 'r');

                if (!$conffile) {
                    throw new Exception('fopen ist fehlgeschlagen!');
                }

                while (($line = fgets($conffile)) !== false) {          
                    if (mb_strpos($line, '=') !== false) {
                        $value = explode('=', $line);
                        $userdata[] = trim($value[1]);
                    }
                }
                fclose($conffile);

                // Anführungszeichen vor und nach dem Passwort entfernen
                $userdata[2] = str_replace('"','',$userdata[2]);

                // Datenbankverbindung herstellen
                $this->model->rootPdo = $this->model->openDbConnection($userdata[0], $userdata[1], $userdata[2]);

                $return = $this->model->selectDatabases($this->model->rootPdo);
                $this->model->closeDbConnection($this->model->rootPdo);
            } catch (Throwable $ex) {
                $return = $ex;
            }
            return $return;
        }
    }
}