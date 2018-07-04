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
                echo '<input type="submit" class="input_logout" id="logout" name="logout" value="Logout" onclick="return confirmLogout()">';
                echo '</form>';                          
                // HTML-Tabelle generieren
                echo $this->showHTMLTable();          
                echo '<div class="form-div">';
                echo '<div id="overload"><div id="load"></div></div>';
                echo '<form method="post" id="dbform" action="">';
                echo '<input type="hidden" id="hiddenfield" name="selectedDB" value="">';
                echo '<input type="submit" class="input_submit input_db" name="create" value="Datenbank erstellen" onclick="return checkDbname()" />';
                echo '<input type="submit" class="input_submit input_db" name="delete" value="Datenbank löschen" onclick="return confirmDelete()" />';                          
                echo '<label class="label db_label">neuer Datenbankname:<input type="text" class="input_text db_text" id="dbname" name="dbname" /></label>';
                echo '<input type="submit" class="input_submit input_db" name="rename" value="Datenbank umbenennen" onclick="return confirmRename()" />';
                echo '<input type="submit" class="input_submit input_db" name="duplicate" value="Datenbank duplizieren" onclick="return confirmDuplicate()" />';
                echo '<input type="submit" class="input_submit input_db" name="insert" value="Dump importieren" onclick="return checkDump()" />';
                // Drop-Down Menu generieren
                echo $this->showDumpDropDown();
                echo '<br />';
                echo '<label class="dump_label"><input id="checkbox" type="checkbox" name="dumpdelete" value="1">&nbsp;Dump nach Import löschen</label>';
                echo '</form>';
                echo '</div>';
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
        
        $dropDown = '<select id="select" name="dbselect" size="1" form="dbform">';
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
        $userShort = $_SESSION['userShort'];
        $root = $_SESSION['root'];
        require_once 'DBAdmin_Model.php';
        $model = new DBAdmin_Model();
        // Benutzername und Passwort aus JSON-File holen
        // Datenbankverbindung herstellen
        $conf = DBAdmin_Controller::_setDbData();
        $model->rootPdo = $model->openDbConnection($conf["host"], $conf["user"], $conf["password"]);
        
        // alle Datenbanknamen abfragen, für die der Benutzer Berechtigung hat
        $databases = $model->selectDatabases($userShort, $root);
        
        // Header der HTML-Tabelle erstellen
        $HTMLTable = '<table class="db_table">'
                . '<colgroup>'
                . '<col class="col">'
                . '<col class="col">'
                . '<col class="col">'
                . '</colgroup>'
                . '<tr>'
                . '<th>Datenbankname</th>'
                . '<th>Zuletzt geändert</th>'
                . '<th>Importdatum</th>'
                . '</tr>';
        
        $dates = [];
        $import = [];
        
        for ($d = 0; $d < count($databases); $d++) {
            // Datum der letzten Änderung auslesen
            $date = $model->selectLastUpdateDate($databases[$d]['SCHEMA_NAME']);
            
            if ($date[0][0] === null) {
                // Erstelldatum der Datenbank auslesen
                $date = $model->selectDbCreateDate($databases[$d]['SCHEMA_NAME']);
            }
            
            $dates[] = date("d.m.Y", strtotime($date[0][0]));
            // Datum des letzten Imports ermitteln
            $date = $model->selectImportDate($databases[$d]['SCHEMA_NAME']);
            
            if ($date[0][0] === null) {
                $import[] = '--';
            } else {
                $import[] = date("d.m.Y", strtotime($date[0][0]));
            }
            
        }
        $model->closeDbConnection($model->rootPdo);
        
        // pro Datenbankname eine Zeile in die Tabelle einfügen
        for ($i = 0; $i < count($databases); $i++) {
            $no = $i+1;
            $class = 'tablerows';
            if ($i%2 !== 0) {
                $class .= ' odd';
            }
            $HTMLTable .= '<tr  id="td'.$no.'" class="'.$class.'" onclick="changeColor('.$no.')">'
                        . '<td class="tablecells">'.$databases[$i]['SCHEMA_NAME'].'</td>'
                        . '<td id="db_date">'.$dates[$i].'</td>'
                        . '<td>'.$import[$i].'</tr>';
        }
        
        $HTMLTable .= '</table>';
        return $HTMLTable;
    }

     
    /**
     * Gibt eine vordefinierte Meldung aus
     * @param string $msg
     */
    public function showMessage($msg) {
        switch ($msg) {
            case 'loginfail': 
                echo '<script type="text/javascript">alert("Benutzer unbekannt oder Passwort falsch!")</script>'; 
                break;
            case 'noconnection': 
                echo '<script type="text/javascript">alert("Datenbankverbindung fehlgeschlagen!")</script>'; 
                break;
            case 'norights': 
                echo '<script type="text/javascript">alert("Du hast keine Berechtigung für diesen Vorgang!")</script>'; 
                break;
            case 'wrongname':
                echo '<script type="text/javascript">alert("Der gewählte Datenbankname entspricht nicht den Konventionen!")</script>';
                break;
            case 'deleteok': 
                echo '<script type="text/javascript">alert("Datenbank erfolgreich gelöscht.")</script>'; 
                break;
            case 'exists': 
                echo '<script type="text/javascript">alert("Eine Datenbank mit diesem Namen existiert bereits!")</script>'; 
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
            case 'dberror':
                echo '<script type="text/javascript">alert("Datenbankfehler! Login nicht möglich!")</script>';
                break;
            case false: 
                echo '<script type="text/javascript">alert("Fehler beim Ausführen der Operation.")</script>';
            default:
                echo '<script type="text/javascript">alert("'.$msg.'")</script>';
        }
        
        header('refresh:0.5;url=index.php');
    }       
}