<?php

class DBAdmin_FileReader {       
    
    /**
     * Exportiert einen Dump via Kommandozeilenbefehl
     * @param string $dbname
     * @param boolean $exportOnly
     */
    public function createDump($dbname, $exportOnly) {
        // Pfad des Config-Files angeben
        // es enthält den MySQL-Benutzernamen und das Passwort, sowie den Hostnamen
        $mysqlconf = realpath('config/user_'.$_SESSION['username'].'.conf');
        
        if (!is_file($mysqlconf)) {
            throw new Exception('Die Conf-Datei des Users existiert nicht!');
        }
        // Dumppfad definieren
        $dumps = json_decode(file_get_contents('config/config.json'))->dumps;
        $user = $_SESSION['username'];
        $filename = $exportOnly ? $dbname : $_SESSION['id'];
        $dbpath = realpath($dumps).'/'.$user.'/'.$filename.'.sql';
        
        // Dump exportieren
        $command = 'mysqldump --defaults-file="'.escapeshellarg($mysqlconf).'" --events --routines --triggers '
                   .escapeshellarg($dbname).' > "'.escapeshellarg($dbpath).'" 2>&1';    
        exec($command, $out, $return);
        
        if ($return !== 0) {
            throw new Exception($out[0]);
        }
    }
        
    
    /**
     * Importiert einen Dump via Kommandozeilenbefehl
     * @param string $oldDbname
     * @param string $newDbname
     * @param boolean $delete
     */
    public function executeDump($oldDbname, $newDbname, $delete) {
        // Namen der Zieldatenbank definieren
        $db = $newDbname === null ? $oldDbname : $newDbname;
        
        // Pfad des Config-Files angeben
        // es enthält den MySQL-Benutzernamen und das Passwort, sowie den Hostnamen
        $mysqlconf = realpath('config/user_'.$_SESSION['username'].'.conf');
        
        if (!is_file($mysqlconf)) {
            throw new Exception('Die Conf-Datei des Users existiert nicht!');
        }
        // Dumpnamen definieren
        $filename = $oldDbname === null ? $_SESSION['id'].'.sql' : $oldDbname;
        
        $dumps = json_decode(file_get_contents('config/config.json'))->dumps;
        $user = $_SESSION['username'];
        $dbpath = realpath($dumps.'/'.$user.'/'.$filename);        
        
        // Dump importieren
        $command = 'mysql --defaults-file="'.escapeshellarg($mysqlconf).'" '.escapeshellarg($db).' < "'.escapeshellarg($dbpath).'" 2>&1';  
        exec($command, $out, $return);
        
        if ($delete && $return === 0) {            
            unlink($dbpath);
        }
        
        if ($return !== 0) {
            throw new Exception($out[0]);
        }        
    }
    
    
    /**
     * Erstellt eine Liste mit allen Dumps
     * @return array
     */
    public function getDumpList() {
        $dumps = json_decode(file_get_contents('config/config.json'));
        
        if (!$dumps) {
            throw new Exception('config.json nicht gefunden!');
        }
        
        if (!isset($dumps->dumps)) {
            throw new Exception('Datei config.json ist fehlerhaft!');
        }
        $dumps = $dumps->dumps.'/'.$_SESSION['username'];
        
        if (!is_dir($dumps)) {
            if (mkdir($dumps) === false) {
                throw new Exception('Dumps-Ordner für Benutzer konnte nicht erstellt werden!');
            }
        }
        
        $files = scandir($dumps);
        
        if ($files === false) {
            throw new Exception('Ordner '.$files.' konnte nicht durchsucht werden!');
        }
        
        $dumpList = [];
        
        for ($i = 2; $i < count($files); $i++) {
            // nur SQL-Dateien beachten
            if (mb_strpos($files[$i], '.sql') !== false) {
                $dumpList[] = $files[$i];
            }
        }       
        return $dumpList;
    }
}