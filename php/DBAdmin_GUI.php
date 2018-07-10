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
                echo '<input type="image" class="input_logout" id="logout" name="logout" src="png/logout.PNG" onclick="return confirmLogout()">';
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
        
        
        $HTMLTable = '<form id="dbform" method="post" action="">'
                // Erstell- und Importbutton inkl. Modalbox
                . '<img id="plus" src="png/plus.PNG" onclick="showNameField()"/>'
                . '<img id="import" src="png/import.PNG" onclick="showDumps()"></img>'
                . '<div id="modalbox" class="modalbox">'
                . '<div class="modalbox_inhalt">'               
                . '<div class="inbox">'
                . '<label class="dump_label nosee" id="checkboxlabel"><input id="checkbox" type="checkbox" name="dumpdelete" value="1">&nbsp;Dump nach Import löschen</label>'
                . '<input type="submit" class="close" onclick="return closeModalBox()" value="&times" />'
                . $this->showDumpDropDown()
                . '<input type="text" name="dbname" id="dbname" class="db_text nosee" placeholder="Datenbankname" />'
                . '<input type="submit" class="input_db nosee" id="insert" name="insert" onclick="return checkDump()" value="OK" />'
                . '<input type="submit" class="input_db nosee" id="create" name="create" onclick="return checkDbname(1)" value="OK" />'               
                . '</div>'
                . '</div></div>'
                . '<div id="overload"><div id="load"></div></div>'
                // Header der HTML-Tabelle erstellen
                . '<table class="db_table">'
                . '<colgroup>'
                . '<col class="col">'
                . '<col class="col">'
                . '<col class="col">'
                . '</colgroup>'
                . '<tr>'
                . '<th>Datenbankname</th>'
                . '<th>Importdatum</th>'
                . '<th>Zuletzt geändert</th>'                
                . '<th></th>'
                . '</tr>';
        
        $change = [];
        $import = [];
        
        for ($d = 0; $d < count($databases); $d++) {
            // Datum der letzten Änderung auslesen
            $date = $model->selectLastUpdateDate($databases[$d]['SCHEMA_NAME']);
            
            if ($date[0][0] === null) {
                // Erstelldatum der Datenbank auslesen
                $change[] = '--';
            } else {
                $change[] = date("d.m.Y", strtotime($date[0][0]));
            }
            
            // Datum des letzten Imports ermitteln
            $date = $model->selectImportDate($databases[$d]['SCHEMA_NAME']);
            
            if ($date[0][0] === null) {
                $import[] = '--';
            } else {
                $import[] = date("d.m.Y", strtotime($date[0][0]));
            }
            
        }
        $model->closeDbConnection($model->rootPdo);
        
        $no = '';
        // pro Datenbankname eine Zeile in die Tabelle einfügen
        for ($i = 0; $i < count($databases); $i++) {
            $no = $i+1;
            $class = 'tablerows';
            
            if ($i%2 !== 0) {
                $class .= ' odd';
            }
            
            $HTMLTable .= '<tr  id="td'.$no.'" class="'.$class.'">'
                        . '<td class="tablecells">'.$databases[$i]['SCHEMA_NAME'].'</td>'
                        . '<td>'.$import[$i].'</td>'
                        . '<td id="db_date">'.$change[$i].'</td>'                        
                        . '<td><input type="image" class="img" id="del'.$no.'" src="png/trash.PNG" name="delete" onclick="return confirmDelete('.$no.')" />'
                        . '<img class="img" id="dup'.$no.'" src="png/duplicate.PNG" onclick="showDuplicate('.$no.')" />'
                        . '<img class="img" id="ren'.$no.'" src="png/edit.PNG" onclick="showRename('.$no.')" /></td></tr>';
        }      
        $HTMLTable .= '</table>'
                    . '<input type="hidden" id="hiddenfield" name="selectedDB" value="" />'
                    . '<div id="modalbox2" class="modalbox">'
                    . '<div class="modalbox_inhalt">'               
                    . '<div class="inbox">'
                    . '<input type="submit" class="close" onclick="return closeModalBox()" value="&times" />'
                    . '<input type="text" name="dbname2" id="dbname2" class="db_text" placeholder="Datenbankname" />'
                    . '<input type="submit" class="input_db nosee" id="duplicate" name="duplicate" onclick="return confirmDuplicate('.$no.')" value="OK" />'
                    . '<input type="submit" class="input_db nosee" id="rename" name="rename" onclick="return confirmRename('.$no.')" value="OK" />'               
                    . '</div>'
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
            case 1045: 
                echo '<script type="text/javascript">alert("Benutzer unbekannt oder Passwort falsch!")</script>'; 
                break;
            case 2002:
            case 'HY000':
                echo '<script type="text/javascript">alert("Datenbankverbindung fehlgeschlagen!\r\nDu wirst aus Sicherheitsgründen ausgeloggt.")</script>';
                session_destroy();
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
            case 'usernotexists':
                echo '<script type="text/javascript">alert("Ein Benutzer mit dem angegebenen Kürzel existiert nicht.")</script>';
                break;
            case 'nodump':
                echo '<script type="text/javascript">alert("Die ausgewählte SQL-Datei scheint kein Dump zu sein!\r\nOperation abgebrochen!")</script>';
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
            case false: 
                echo '<script type="text/javascript">alert("Fehler beim Ausführen der Operation.")</script>';
        }        
        header('refresh:0.5;url=index.php');
    }       
}