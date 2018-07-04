<?php

class DBAdmin_FileReader {       
    
    /**
     * Exportiert einen Dump via Kommandozeilenbefehl
     * @param string $dbname
     * @return integer
     */
    public function createDump($dbname) {
        // Namen der Zieldatenbank definieren
        $db_parts = explode('.', $dbname);
        $db = $db_parts[0];
        // Pfad des Config-Files angeben
        // es enthält den MySQL-Benutzernamen und das Passwort, sowie den Hostnamen
        $conf = realpath('config/mysql.conf');
        // Dumppfad definieren
        $dbpath = realpath('dumps/').'\\'.$_SESSION['id'].'.sql';
        
        // Dump exportieren
        $command = 'mysqldump --defaults-file="'.$conf.'" --events --routines --triggers '.$db.' > "'.$dbpath.'"';    
        exec($command, $out, $return);
        return $return;
    }
        
    
    /**
     * Löscht einen Dump
     * @param string $path
     */
    private function deleteDump($path) {
        unlink($path);
    }
        
    
    /**
     * Importiert einen Dump via Kommandozeilenbefehl
     * @param string|null $oldDbname
     * @param string|null $newDbname
     * @param boolean $delete
     * @return integer
     */
    public function executeDump($oldDbname, $newDbname, $delete) {
        // Namen der Zieldatenbank definieren
        $dbname = $newDbname === null ? $oldDbname : $newDbname;
        $db_parts = explode('.', $dbname);
        $db = $db_parts[0];
        // Pfad des Config-Files angeben
        // es enthält den MySQL-Benutzernamen und das Passwort, sowie den Hostnamen
        $conf = realpath('config/mysql.conf');
        // Dumpnamen definieren
        $dumpPath = $oldDbname === null ? $_SESSION['id'].'.sql' : $oldDbname;
        $dbpath = realpath('dumps/').'\\'.$dumpPath;
        
        // Dump importieren
        $command = 'mysql --defaults-file="'.$conf.'" '.$db.' < "'.$dbpath.'"';    
        exec($command, $out, $return);
        
        if ($delete && $return === 0) {
            $this->deleteDump($dbpath);
        }
        return $return;
    }
    
    
    /**
     * Erstellt eine Liste mit allen Dumps
     * @return array
     */
    public function getDumpList() {
        $dumpDirectory = realpath('dumps');
        $files = scandir($dumpDirectory);
        $dumpList = [];
        
        for ($i = 2; $i < count($files); $i++) {
            $type = mime_content_type($dumpDirectory.'\\'.$files[$i]);
            // nur SQL-Dateien beachten
            if ($type === "text/plain" && strpos($files[$i], '.sql') !== false) {
                $dumpList[] = $files[$i];
            }
        }       
        return $dumpList;
    }
}