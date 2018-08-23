<?php

class DBAdmin_Controller {
    
    public $model = null;
    public $gui = null;
       
    public function __construct() {
        $this->getRequest();
    }
    
    
    /**
     * Intepretiert die übergebenen Werte
     */
    private function getRequest() {
        try {
            // Instanz von Klasse DBAdmin_GUI erstellen
            if ($this->gui === null) {
                require_once 'DBAdmin_GUI.php';
                $this->gui = new DBAdmin_GUI();
            }

            // Loginfunktion aufrufen
            if (isset($_POST['username'])) {
                $this->loginUser();

            // Operation 'Datenbank löschen' wurde gestartet
            } else if (isset($_POST['delete_x'])) {
                $this->openRootDbConnection();
                $this->deleteDatabase($_POST['selectedDB']);
                $this->gui->showMessage('deleteok');

            // Operation 'Datenbank erstellen' wurde gestartet
            } else if (isset($_POST['create'])) {
                $this->openRootDbConnection();
                $this->createDatabase($_POST['dbname']);
                $this->model->closeDbConnection($this->model->rootPdo);
                $this->gui->showMessage('createok');

            // Operation 'Datenbank importieren' wurde gestartet
            } else if (isset($_POST['insert'])) {
                $this->openRootDbConnection();
                $this->importDatabase($_POST['dump'], $_POST['selectedDB'], isset($_POST['dumpdelete']));
                $this->model->closeDbConnection($this->model->rootPdo);
                $this->gui->showMessage('importok');
            
            // Operation 'Dump erstellen' ausführen
            } else if (isset($_POST['export_x'])) {
                $this->exportDatabase($_POST['selectedDB'], true);
                $this->gui->showMessage('exportok');
                
            // Operation 'Datenbank umbenennen' wurde gestartet
            } else if (isset($_POST['rename'])) {
                $this->openRootDbConnection();
                $this->renameDatabase($_POST['dbname'], $_POST['selectedDB']);
                $this->gui->showMessage('renameok');

            // Operation 'Datenbank duplizieren' wurde gestartet
            } else if (isset($_POST['duplicate'])) {
                $this->openRootDbConnection();
                $this->duplicateDatabase($_POST['dbname'], $_POST['selectedDB']);
                $this->gui->showMessage('duplicateok');

            // Logoutfuntkion aufrufen
            } else if (isset($_POST['logout_x'])) {
                $this->logoutUser();

            // GUI der Hauptansicht (neu) laden
            } else if (isset($_SESSION['id'])) {
                $this->gui->renderGUI('main');

            // GUI der Loginansicht laden
            } else {
                $this->gui->renderGUI('login');
            }
            
        // Exception abfangen
        } catch (Exception $ex) {
            $this->gui->showMessage($ex->getMessage());
        }
    }
    
    
    /**
     * Überprüft den Datenbanknamen
     * @param string $dbname
     * @return boolean|string
     */
    private function checkDbname($dbname) {            
        $match = preg_match('/^dev_[a-z]{2}_[a-z]{2,3}_[a-z]{1,50}$/', $dbname);
        $dbSubstrings = explode('_', $dbname);
        
        if (!$_SESSION['root']) {
            if ($dbSubstrings[0].$dbSubstrings[1] !== $_SESSION['username']) {
                return 'Recht zum erstellen einer Datenbank mit diesem Namen fehlt!';
            } else if ($match === 1) {
                return 'Der Datenbankname muss folgendes Format haben:\n"Benutzername_Applikation_Organisation"\nBeispiel: "dev_xy_wz_kkk"';
            }
        } else {
            return true;
        }
    }
    
    
    /**
     * Erstellt eine neue Datenbank
     * @param string $dbname
     * @return boolean
     */
    private function createDatabase($dbname) {
        $check = $this->checkDbname($dbname);
        
        if (!$check) {
            throw new Exception($check);            
        }
        
        // Prüfen ob gleichnamige Datenbank existiert
        if (count($this->model->selectDatabase($dbname)) !== 0) {
            throw new Exception('Gleichnamige Datenbank existiert schon!');
        }
        
        // Datenbank erstellen
        $result = $this->model->createDatabase($dbname);
        
        if (!$result) {
            throw new Exception('Erstellen der Datenbank fehlgeschlagen!');
        }
    }
        
    
    /**
     * Löscht die gewählte Datenbank
     * @param string $dbname
     */
    private function deleteDatabase($dbname) {
        $result = $this->model->deleteDatabase($dbname);
        
        if (!$result) {
            throw new Exception('Löschen der Datenbank fehlgeschlagen!');
        }
        $this->model->closeDbConnection($this->model->rootPdo);
    }
        
    
    /**
     * Dupliziert eine Datenbank
     * @param string $newDbname
     * @param string $oldDbname
     */
    private function duplicateDatabase($newDbname, $oldDbname) {
        $newDbFile = $newDbname.'.sql';
        $oldDbFile = $oldDbname.'.sql';
        
        // Datenbankname prüfen
        $check = $this->checkDbname($newDbname);
        
        if (!$check) {
            throw new Exception($check);
        }
        
        // neue Datenbank erstellen
        $this->createDatabase($newDbname); 
        
        // Datenbank exportieren
        $this->exportDatabase($oldDbFile, false);                       
                                  
        // Datenbank importieren
        $this->importDatabase(null, $newDbFile, true);
        
        $this->model->closeDbConnection($this->model->rootPdo);
    }
        
    
    /**
     * Exportiert eine Datenbank
     * @param string $dbname
     * $param boolean $exportOnly
     */
    private function exportDatabase($dbname, $exportOnly) {
        require_once 'DBAdmin_FileReader.php';
        $reader = new DBAdmin_FileReader();
        $reader->createDump($dbname, $exportOnly);
    }
        
    
    /**
     * Eruiert, ob der Benutzer root-Rechte hat
     * @param string $username
     * @return boolean
     */
    private function getUserRights($username) {
        $host = json_decode(file_get_contents('config/dbadmin.json'))->host;
        $result = $this->model->selectUserRights($host, $username);

        if (strpos($result[0][0], 'ALL PRIVILEGES ON *.*') === false) {
            return false;
        } else {
            return true;
        }
    }    
    
    
    /**
     * Importiert einen Dump
     * @param string|null $oldDbname
     * @param string|null $newDbname
     * @param boolean $delete
     */
    private function importDatabase($oldDbname, $newDbname, $delete) {    
        require_once 'DBAdmin_FileReader.php';
        $reader = new DBAdmin_FileReader();
        $reader->executeDump($oldDbname, $newDbname, $delete);
        
        if (!$this->model->insertImportDate($newDbname)) {
            throw new Exception('Speichern des Import-Datums fehlgeschlagen!');
        }
    }
        
    
    /**
     * Überprüft die Logindaten
     */
    private function loginUser() {
        $username = mb_strtolower($_POST['username']);
        $password = $_POST['passwort'];
        
        require_once 'DBAdmin_Model.php';
        $this->model = new DBAdmin_Model();
        $host = json_decode(file_get_contents('config/dbadmin.json'))->host;
        $con = $this->model->openDbConnection($host, $username, $password);               
        $this->model->closeDbConnection($con);

        // mit root zur Datenbank verbinden
        $this->openRootDbConnection();
        
        // eruieren ob der eingeloggte User root-Rechte hat
        $root = $this->getUserRights($username);
        $this->model->closeDbConnection($this->model->rootPdo);
                             
        $_SESSION['root'] = $root;
        $_SESSION['username'] = $username; 
        $_SESSION['id'] = md5($password);
        
        // conf-File für Benutzer erstellen
        $conffile = fopen('config/user.conf', 'w');
        $txt = "[client]\r\nhost=".$host."\r\nuser=".$username."\r\npassword=\"".$password."\"";
        fwrite($conffile, $txt);
        fclose($conffile);
        
        $this->gui->renderGUI('main');
    }
        
    
    /**
     * Logt den angemeldeten Benutzer aus
     */
    private function logoutUser() {
        unlink('config/user.conf');
        session_destroy();
        $this->gui->showMessage('logout');
    }
    
    
    /**
     * Öffnet eine Verbindung zu MySQL mit einem root-Benutzer
     */
    private function openRootDbConnection() {        
        require_once 'DBAdmin_Model.php';
        $this->model = new DBAdmin_Model();
        
        // MySQL-Verbindung herstellen
        $config = json_decode(file_get_contents('config/dbadmin.json'));
        $this->model->rootPdo = $this->model->openDbConnection($config->host, $config->user, $config->password);
    }
    
    
    /**
     * Benennt eine Datenbank um
     * @param string $newDbname
     * @param string $oldDbname
     */
    private function renameDatabase($newDbname, $oldDbname) {
        $newDbFile = $newDbname.'.sql';
        $oldDbFile = $oldDbname.'.sql';
        
        $check = $this->checkDbname($newDbname);
        
        if (!$check) {
            throw new Exception($check);
        }
        
        // neue Datenbank erstellen
        $this->createDatabase($newDbname);
        
        // Datenbank exportieren
        $this->exportDatabase($oldDbFile, false);
        
        // Datenbank löschen
        $this->model->deleteDatabase($oldDbname);        
            
        // Datenbank importieren
        $this->importDatabase(null, $newDbFile, true);       
        $this->model->closeDbConnection($this->model->rootPdo);
    }
}