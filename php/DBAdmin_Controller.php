<?php

class DBAdmin_Controller {
    
    public $model = null;
    public $gui = null;
    
    /**
     * Intepretiert die übergebenen Werte
     */
    public function getRequest() {
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
            } else if (isset($_POST['delete'])) {
                $this->openRootDbConnection();
                $msg = $this->deleteDatabase($_POST['selectedDB']);
                $this->gui->showMessage($msg);

            // Operation 'Datenbank erstellen' wurde gestartet
            } else if (isset($_POST['create'])) {
                $this->openRootDbConnection();
                $msg = $this->createDatabase($_POST['dbname']);
                $this->gui->showMessage($msg);

            // Operation 'Datenbank importieren' wurde gestartet
            } else if (isset($_POST['insert'])) {
                $newDb = null;          
                // wenn DB ausgewählt wurde:
                // Name der DB als Importziel setzen
                if (isset($_POST['selectedDB']) && $_POST['selectedDB'] !== '') {                
                    $newDb = $_POST['selectedDB'].'.sql';

                // wenn DB-Name eingegeben wurde:
                // DB erstellen
                } else if (isset($_POST['dbname']) && $_POST['dbname'] !== '') {
                    $newDb = $_POST['dbname'].'.sql';
                    $this->openRootDbConnection();
                    $return = $this->createDatabase($_POST['dbname']);
                    // Vorgang abbrechen falls Datenbank bereits existiert
                    // oder CREATE fehlgeschlagen ist
                    if ($return === 'exists') {
                        $this->gui->showMessage('exists');
                        exit();
                    } else if ($return === false) {
                        $this->gui->showMessage(false);
                    }           
                }
                // Dump importieren
                $msg = $this->importDatabase($_POST['dbselect'], $newDb, isset($_POST['dumpdelete']));
                $this->gui->showMessage($msg);

            // Operation 'Datenbank umbenennen' wurde gestartet
            } else if (isset($_POST['rename'])) {
                $this->openRootDbConnection();
                $msg = $this->renameDatabase($_POST['dbname'], $_POST['selectedDB']);
                $this->gui->showMessage($msg);

            // Operation 'Datenbank duplizieren' wurde gestartet
            } else if (isset($_POST['duplicate'])) {
                $this->openRootDbConnection();
                $msg = $this->duplicateDatabase($_POST['dbname'], $_POST['selectedDB']);
                $this->gui->showMessage($msg);

            // Logoutfuntkion aufrufen
            } else if (isset($_POST['logout'])) {
                $this->logoutUser();

            // GUI der Hauptansicht neu laden
            } else if (isset($_SESSION['id'])) {
                $this->gui->renderGUI('main');

            // GUI der Loginansicht laden
            } else {
                $this->gui->renderGUI('login');
            }
        } catch (Exception $ex) {
            $this->gui->showMessage($ex->getCode());
        }
    }
    
    
    /**
     * Erstellt eine neue Datenbank
     * @param string $dbname
     * @return boolean|string
     */
    private function createDatabase($dbname) {
        $check = self::_checkDbname($dbname);
        if ($check !== true) {
            $this->gui->showMessage($check);
            exit();
        }
        
        // Prüfen ob gleichnamige Datenbank existiert
        if (count($this->model->selectDatabase($dbname)) !== 0) {
            return 'exists';
        }
        
        // Datenbank erstellen
        $return = $this->model->createDatabase($dbname);
        $this->model->closeDbConnection($this->model->rootPdo);
        
        if ($return) {
            return 'createok';
        } else {
            return false;
        } 
    }
        
    
    /**
     * Löscht die gewählte Datenbank
     * @param string $dbname
     * @return boolean|string
     */
    private function deleteDatabase($dbname) {
        $check = self::_checkDbname($dbname);
        if ($check !== true) {
            $this->gui->showMessage($check);
            exit();
        }
        
        $this->model->closeDbConnection($this->model->rootPdo);
        $return = $this->model->deleteDatabase($dbname);
        
        if ($return) {
            return 'deleteok';
        } else {
            return false;      
        }       
    }
        
    
    /**
     * Dupliziert eine Datenbank
     * @param string $newDbname
     * @param string $oldDbname
     * @return boolean|string
     */
    private function duplicateDatabase($newDbname, $oldDbname) {
        $newDbFile = $newDbname.'.sql';
        $oldDbFile = $oldDbname.'.sql';
        
        // Datenbankname prüfen
        $check = self::_checkDbname($oldDbname);
        if ($check !== true) {
            $this->gui->showMessage($check);
            exit();
        }
        
        // Datenbank exportieren
        $msg = $this->exportDatabase($oldDbFile);        
        
        // neue Datenbank erstellen
        if ($msg !== false) {
            $msg = $this->model->createDatabase($newDbname);
        } else {
            return false;
        }    
        
        // Datenbank importieren
        if ($msg !== false) {
            $msg = $this->importDatabase(null, $newDbFile, true);
        } else {
            return false;
        }
        
        $this->model->closeDbConnection($this->model->rootPdo);
        
        if (!$msg) {
            return false;
        } else {
            return 'duplicateok';
        }
    }
        
    
    /**
     * Exportiert eine Datenbank
     * @param string $dbname
     * @return boolean|string
     */
    private function exportDatabase($dbname) {
        require_once 'DBAdmin_FileReader.php';
        $reader = new DBAdmin_FileReader();
        $return = $reader->createDump($dbname);
        
        if ($return === 0) {
            return 'exportok';
        } else {
            return false;
        }
    }
        
    
    /**
     * Eruiert, ob der Benutzer root-Rechte hat
     * @param string $username
     * @return boolean
     */
    private function getUserRights($username) {
        $result = $this->model->getUserRights($username);
        
        // die Berechtigungen sind im Array unter Index 2 - 30 zu finden
        // Y = hat Berechtigung, N = hat Berechtigung nicht
        for ($i = 2; $i <= 30; $i++) {
            if ($result[0][$i] !== 'Y') {
                return false;
            }
        }
        return true;
    }    
    
    
    /**
     * Importiert einen Dump
     * @param string|null $oldDbname
     * @param string|null $newDbname
     * @param boolean $delete
     * @return boolean|string
     */
    private function importDatabase($oldDbname, $newDbname, $delete) {    
        require_once 'DBAdmin_FileReader.php';
        $reader = new DBAdmin_FileReader();
        $return = $reader->executeDump($oldDbname, $newDbname, $delete);
        
        if ($return === 0) {
            return 'importok';
        } else {
            return false;
        }       
    }
        
    
    /**
     * Überprüft die Logindaten
     */
    private function loginUser() {
        $username = $_POST['username'];
        $password = $_POST['passwort'];
        require_once 'DBAdmin_Model.php';
        $this->model = new DBAdmin_Model();
        $config = self::_setDbData();
        $con = $this->model->openDbConnection($config["host"], $username, $password);               
        $this->model->closeDbConnection($con);

        // mit root zur Datenbank verbinden
        $this->openRootDbConnection();
        // eruieren ob der eingeloggte User root-Rechte hat
        $root = $this->getUserRights($username);
        $this->model->closeDbConnection($this->model->rootPdo);
                
        // bei Standard-benutzer: Kürzel in Session speichern
        if (!$root) {
            $userShort = substr($username, 0, 2);
            $_SESSION['userShort'] = $userShort;
        } else {
            $_SESSION['userShort'] = '';
        }
              
        $_SESSION['root'] = $root;
        $_SESSION['id'] = md5($password);
        $this->gui->renderGUI('main');
    }
        
    
    /**
     * Logt den angemeldeten Benutzer aus
     */
    private function logoutUser() {
        session_unset();
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
        $config = self::_setDbData();
        $this->model->rootPdo = $this->model->openDbConnection($config["host"], $config["user"], $config["password"]);
    }
    
    
    /**
     * Benennt eine Datenbank um
     * @param string $newDbname
     * @param string $oldDbname
     * @return boolean|string
     */
    private function renameDatabase($newDbname, $oldDbname) {
        $newDbFile = $newDbname.'.sql';
        $oldDbFile = $oldDbname.'.sql';
        
        $check = self::_checkDbname($oldDbname);
        if ($check !== true) {
            $this->gui->showMessage($check);
            exit();
        }
        
        // Datenbank exportieren
        $msg = $this->exportDatabase($oldDbFile);
        
        // Datenbank löschen
        if ($msg !== false) {
            $msg = $this->model->deleteDatabase($oldDbname);
        } else {
            return false;
        }
        
        // neue Datenbank erstellen
        if ($msg !== false) {
            $msg = $this->model->createDatabase($newDbname);
        } else {
            return false;
        }    
        
        // Datenbank importieren
        if ($msg !== false) {
            $msg = $this->importDatabase(null, $newDbFile, true);
        } else {
            return false;
        }
        
        $this->model->closeDbConnection($this->model->rootPdo);
        
        if (!$msg) {
            return false;
        } else {
            return 'renameok';
        }
    }

    
    // --------------------- //
     ## Statische Funktionen##
    // --------------------- //   
    
    /**
     * Überprüft den Datenbanknamen
     * @param string $dbname
     * @return boolean|string
     */
    private static function _checkDbname($dbname) {
        $dbSubstrings= explode('_', $dbname);
    
        $match = preg_match('/^dev_[a-z]{2}_[a-z]{2,3}_[a-z]{1,50}$/', $dbname);
        
        if ($match === 1) {
            if (!$_SESSION['root'] && $dbSubstrings[1] !== $_SESSION['userShort']) {
                return 'norights';
            } else {
                return true;
            }
        } else {
            return 'wrongname';
        }
    }
        
    
    /**
     * Liest die Anmeldedaten für MySQL aus dem Config-File
     * @return array
     */
    public static function _setDbData() {
        // Benutzername und Passwort aus Config-File holen
        $file = file('config/mysql.conf');
        $config = [];
        
        foreach ($file as $line) {
            $line = trim($line);
            if (strpos($line, '=') !== false) {
                $data = explode('=', $line);
                //$key = $data[0];
                $config[$data[0]] = $data[1];
            }
        }
        return $config;
    }
}