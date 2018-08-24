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
        $mysqlconf = realpath('config/user.conf');
        
        // Dumppfad definieren
        $dumps = json_decode(file_get_contents('config/dbadmin.json'))->dumps;
        $user = $_SESSION['username'];
        $filename = $exportOnly ? $dbname : $_SESSION['id'];
        $dbpath = realpath($dumps).'/'.$user.'/'.$filename.'.sql';
        
        // Dump exportieren
        $command = 'mysqldump --defaults-file="'.$mysqlconf.'" --events --routines --triggers '
                   .escapeshellarg($dbname).' > "'.escapeshellarg($dbpath).'" 2>&1';    
        exec($command, $out, $return);
        
        if ($return !== 0) {
            throw new Exception($out[0]);
        }
    }
        
    
    /**
     * Importiert einen Dump via Kommandozeilenbefehl
     * @param string|null $oldDbname
     * @param string|null $newDbname
     * @param boolean $delete
     */
    public function executeDump($oldDbname, $newDbname, $delete) {
        // Namen der Zieldatenbank definieren
        $db = $newDbname === null ? $oldDbname : $newDbname;
        
        // Pfad des Config-Files angeben
        // es enthält den MySQL-Benutzernamen und das Passwort, sowie den Hostnamen
        $mysqlconf = realpath('config/user.conf');
        
        // Dumpnamen definieren
        $filename = $oldDbname === null ? $_SESSION['id'].'.sql' : $oldDbname;
        
        $dumps = json_decode(file_get_contents('config/dbadmin.json'))->dumps;
        $user = $_SESSION['username'];
        $dbpath = realpath($dumps.'/'.$user.'/'.$filename);        
        
        // Dump importieren
        $command = 'mysql --defaults-file="'.$mysqlconf.'" '.escapeshellarg($db).' < "'.escapeshellarg($dbpath).'" 2>&1';    
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
        $dumps = json_decode(file_get_contents('config/dbadmin.json'));
        
        if (!$dumps) {
            throw new Exception('dbadmin.json nicht gefunden!');
        }
        
        $dumps = $dumps->dumps.'/'.$_SESSION['username'];
        
        if (!is_dir($dumps)) {
            mkdir($dumps);
        }
        
        $files = scandir($dumps);
        
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