<?php

class DBAdmin_Controller {
       
    protected $model = null;
    
    public function __construct() {
        require_once 'DBAdmin_Model.php';
        $this->model = new DBAdmin_Model;
    }
    
    
    /**
     * Überprüft den Datenbanknamen
     */
    public function checkDbname($dbname) {
        $match = preg_match('/^dev_[a-z]{2}_[a-z]{2,3}_[a-z]{1,50}$/', $dbname);
                                       
        if (!$_SESSION['root']) {
            if ($match === 0) {
                return 'Der Datenbankname muss folgendes Format haben:\n\'Benutzername_Applikation_Organisation\'\nBeispiel: \'dev_xy_wz_kkk\'';
            }
            $dbSubstrings = explode('_', $dbname);
            $check = $dbSubstrings[0].'_'.$dbSubstrings[1];
            
            if ($check !== $_SESSION['username']) {
                return 'Recht zum erstellen einer Datenbank mit diesem Namen fehlt!';
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
    
    
    /**
     * Erstellt eine neue Datenbank
     */
    public function createDatabase($dbname) { 
        try{
            $check = $this->checkDbname($dbname);
        
            if (is_string($check)) {
                throw new Exception($check);            
            }
            $this->openRootDbConnection();

            // Prüfen ob gleichnamige Datenbank existiert
            if (count($this->model->selectDatabase($dbname)) !== 0) {
                throw new Exception('Gleichnamige Datenbank existiert schon!');
            }

            // Datenbank erstellen
            $result = $this->model->createDatabase($dbname);

            if (!$result) {
                throw new Exception('Erstellen der Datenbank fehlgeschlagen!');
            }
        } catch (Exception $ex) {
            return $ex;
        }
        return true;
    }
    
    /**
     * Löscht die gewählte Datenbank
     */
    public function deleteDatabase() { }
        
    
    /**
     * Dupliziert eine Datenbank
     */
    public function duplicateDatabase() { }
        
    
    /**
     * Exportiert eine Datenbank
     */
    public function exportDatabase() { }
        
    
    /**
     * Eruiert, ob der Benutzer root-Rechte hat
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
     * Importiert einen Dump
     */
    public function importDatabase() { }
        
    
    /**
     * Überprüft die Logindaten
     */
    public function loginUser($data) {
        try{
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
            
            try{
                $pdo = $this->model->openDbConnection($host, $username, $password);
            } catch (Exception $ex) {
                return 'Benutzer oder Passwort falsch!';
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
        } catch (Exception $ex) {
            return $ex;
        }
        return true;      
    }
        
    
    /**
     * Logt den angemeldeten Benutzer aus
     */
    public function logoutUser() { 
        unlink(realpath('../config/user_'.$_SESSION['username'].'.conf'));
        session_destroy();
        return true;
    }
    
    /**
     * Öffnet eine Verbindung zu MySQL mit einem root-Benutzer
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
     */
    public function renameDatabase() { }
    
    
    public function selectDatabases() {
        if (isset($_SESSION['username'])) {
            try{
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

                $databases = $this->model->selectDatabases($this->model->rootPdo);
                $this->model->closeDbConnection($this->model->rootPdo);
            } catch (Exception $ex) {
                return $ex;
            }
            return $databases;
        }
    }
}