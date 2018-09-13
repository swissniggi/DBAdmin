<?php

class DBAdmin_FileIO {       
       
    /**
     * Erstellt eine Liste mit allen Dumps
     * @param string $username
     * @return array
     */
    public function getDumpList($username) {
        $dumps = json_decode(file_get_contents('config/config.json'));
        
        if (!$dumps) {
            throw new Exception('config.json nicht gefunden!');
        }
        
        if (!isset($dumps->dumps)) {
            throw new Exception('Datei config.json ist fehlerhaft!');
        }
        $dumps = $dumps->dumps.'/'.$username;
        
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