<?php

class DBAdmin_GUI {    
    
    /**
     * Erstellt das GUI
     * @param string $view
     */
    public function renderGUI($view) {
        switch ($view) {
            
            // GUI der Loginansicht erstellen
            case 'login': 
                echo '<div class="form-div">';
                echo '<form method="post" action="">';
                echo '<label class="label">Username:</label><input type="text" class="input_text" id="username" name="username" />';
                echo '<label class="label">Passwort:</label><input type="password" class="input_text" id="passwort" name="passwort" />';
                echo '<input type="submit" class="input_submit" value="Login" onclick="return checkFields()" />';
                echo '</form>';
                echo '</div>';
                break;
            
            // GUI der Hauptansicht erstellen
            case 'main':                  
                echo '<form class="form_header" method="post" action="">';
                echo '<div id="user"><p>Angemeldet als <b>'.$_SESSION['username'].'</b></p></div>';
                echo '<input type="image" class="input_logout" id="logout" name="logout" title="Ausloggen" src="png/logout.PNG" onclick="return confirmLogout()">';
                echo '</form>';
                
                // HTML-Tabelle generieren
                echo $this->showHTMLTable();
                break;
        }
    }
        
    
    /**
     * Erstellt das Drop-Down-Menu mit den Dumps
     * @return string
     */
    private function showDumpDropDown() {
        require_once 'DBAdmin_FileReader.php';
        $fileReader = new DBAdmin_FileReader();
        $dumpList = $fileReader->getDumpList();
        
        $dropDown = '<select id="select" class="nosee" name="dbselect" size="1" form="dbform">';
        $dropDown .= '<option value="">-- Dump auswählen --</option>';
        
        // pro Dumpname eine Option erstellen
        for ($i = 0; $i < count($dumpList); $i++) {
            $dropDown .= '<option value="'.$dumpList[$i].'">'.$dumpList[$i].'</option>';
        }        
        $dropDown .= '</select>';
        return $dropDown;
    }
    
    
    /**
     * Erstellt die HTML-Tabelle
     * @return string
     */
    private function showHTMLTable() {
        $root = $_SESSION['root'];
        require_once 'DBAdmin_Model.php';
        $model = new DBAdmin_Model();
        
        // Benutzerdaten aus conf-File auslesen
        $userdata = [];
        $conffile = fopen(realpath('config/user.conf'), 'r');
        
        while (!feof($conffile)) {
            $line = fgets($conffile);
            if (mb_strpos($line, '=') !== false) {
                $value = explode('=', $line);
                $userdata[] = trim($value[1]);
            }
        }
        fclose($conffile);
        
        // Anführungszeichen vor und nach dem Passwort entfernen
        $userdata[2] = str_replace('"','',$userdata[2]);
        
        // Datenbankverbindung herstellen
        $model->rootPdo = $model->openDbConnection($userdata[0], $userdata[1], $userdata[2]);
        
        // alle Daten für die der HTML-Tabelle abfragen
        $databases = $model->selectDatabases();
        
        $value = $root === false ? $_SESSION['username'] : '';
        $HTMLTable = '<form id="dbform" method="post" action="">'
                
                // Erstell-, Import- und Exportbutton inkl. Modalbox
                . '<img id="plus" src="png/plus.PNG" title="Neue Datenbank" onclick="showNameField(1)" />'
                . '<img id="import" src="png/import.PNG" title="Dump importieren" onclick="showDumps()" />'
                . '<div id="modalbox" class="modalbox">'               
                . '<div class="inbox">'
                . '<label class="dump_label nosee" id="checkboxlabel"><input id="checkbox" type="checkbox" name="dumpdelete" value="1">&nbsp;Dump nach Import löschen</label>'
                . '<input type="submit" class="close" onclick="return closeModalBox()" value="&times" />'
                . $this->showDumpDropDown()
                . '<input type="text" name="dbname" id="dbname" class="db_text nosee" value="'.$value.'" />'
                . '<input type="submit" class="input_db nosee" id="insert" name="insert" onclick="return checkDump()" value="OK" />'
                . '<input type="submit" class="input_db nosee" id="create" name="create" onclick="return checkDbname(1)" value="OK" />'
                . '</div></div>'
                . '<div id="overload"><div id="load"></div></div>'
                
                // Header der HTML-Tabelle erstellen
                . '<table class="db_table">'
                . '<col class="col">'
                . '<col class="col">'
                . '<col class="col">'
                . '<tr>'
                . '<th>Datenbankname</th>'
                . '<th>Importdatum</th>'
                . '<th>Zuletzt geändert</th>'                
                . '<th></th>'
                . '</tr>';        
                
        $model->closeDbConnection($model->rootPdo);
        
        $no = '';
        // pro Datensatz eine Zeile in die Tabelle einfügen
        for ($i = 0; $i < count($databases); $i++) {
            $no = $i+1;
            $class = 'tablerows';
            
            if ($i%2 !== 0) {
                $class .= ' odd';
            }
            
            $HTMLTable .= '<tr  id="td'.$no.'" class="'.$class.'">'
                        . '<td class="tablecells">'.$databases[$i]['dbname'].'</td>'
                        . '<td>'.$databases[$i]['importdate'].'</td>'
                        . '<td id="db_date">'.$databases[$i]['changedate'].'</td>'                        
                        . '<td><input type="image" class="img" id="del'.$no.'" src="png/trash.PNG" name="delete" title="Löschen" onclick="return confirmDelete('.$no.')" />'
                        . '<input type="image" class="img" id=exp'.$no.'" src="png/export.PNG" name="export" title="Dump erstellen" onclick="return confirmExport('.$no.')" />'
                        . '<img class="img" id="dup'.$no.'" src="png/duplicate.PNG" title="Duplizieren" onclick="showDuplicate('.$no.')" />'
                        . '<img class="img" id="ren'.$no.'" src="png/edit.PNG" title="Umbenennen" onclick="showRename('.$no.')" /></td></tr>';
        }      
        $HTMLTable .= '</table>'
                    . '<input type="hidden" id="hiddenfield" name="selectedDB" value="" />'
                    . '<div id="modalbox2" class="modalbox">'             
                    . '<div class="inbox">'
                    . '<input type="submit" class="close" onclick="return closeModalBox()" value="&times" />'
                    . '<input type="text" name="dbname2" id="dbname2" class="db_text" value="'.$value.'" />'
                    . '<input type="submit" class="input_db nosee" id="duplicate" name="duplicate" onclick="return confirmDuplicate('.$no.')" value="OK" />'
                    . '<input type="submit" class="input_db nosee" id="rename" name="rename" onclick="return confirmRename('.$no.')" value="OK" />'               
                    . '</div></div>'
                    . '</form>';
        return $HTMLTable;
    }

     
    /**
     * Gibt eine vordefinierte Meldung aus
     * @param string $msg
     */
    public function showMessage($msg) {
        switch ($msg) {            
            case 'deleteok': 
                echo '<script type="text/javascript">alert("Datenbank erfolgreich gelöscht.")</script>'; 
                break;
            case 'createok': 
                echo '<script type="text/javascript">alert("Datenbank erfolgreich erstellt.")</script>'; 
                break;
            case 'importok': 
                echo '<script type="text/javascript">alert("Datenbank erfolgreich importiert.")</script>'; 
                break;
            case 'exportok': 
                echo '<script type="text/javascript">alert("Datenbank erfolgreich exportiert.")</script>'; 
                break;
            case 'renameok': 
                echo '<script type="text/javascript">alert("Datenbank erfolgreich umbenannt.")</script>'; 
                break;
            case 'duplicateok': 
                echo '<script type="text/javascript">alert("Datenbank erfolgreich dupliziert.")</script>'; 
                break;
            case 'logout': 
                echo '<script type="text/javascript">alert("Du hast dich erfolgreich ausgeloggt.")</script>'; 
                break;
            default:
                echo '<script type="text/javascript">alert("Fehler! '.trim($msg).'")</script>';
                if (strpos($msg, '[2002]') !== false) {
                    session_destroy();
                }
        }        
        header('refresh:0.5;url=index.php');
    }       
}