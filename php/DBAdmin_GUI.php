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
     * Erstellen der 4 Modalboxen
     * @return string
     */
    private function setModalBoxCreate() {
        $root = $_SESSION['root'];
        $value = $root === false ? $_SESSION['username'] : '';
        
        $modalBox = '<form class="dbform" method="post" action="">'
                . '<div id="modalbox_create" class="modalbox">'               
                . '<div class="inbox">'
                . '<div id="titlebar_create" class="titlebar"></div>'                
                . '<input type="button" class="close" onclick="return closeModalBox(\'create\')" value="&times" />'                
                . '<input type="text" name="dbname" id="dbname_create" class="db_text nosee" value="'.$value.'" />'               
                . '<input type="submit" class="input_db" id="create" name="create" onclick="return checkDbname(\'create\')" value="OK" />'
                . '</div></div></form>';
        return $modalBox;
    }
    
    
    private function setModalBoxInsert() {
        // Form benötigt Id damit Wert von Select übergeben wird
        $modalBox = '<form id="insert_form" class="dbform" method="post" action="">'
                . '<div id="modalbox_insert" class="modalbox">'               
                . '<div class="inbox">'
                . '<div id="titlebar_insert" class="titlebar"></div>'
                . '<label class="dump_label nosee" id="checkboxlabel"><input id="checkbox" type="checkbox" name="dumpdelete" value="1">&nbsp;Dump nach Import löschen</label>'
                . '<input type="button" class="close" onclick="return closeModalBox(\'insert\')" value="&times" />'
                . $this->showDumpDropDown()
                . '<input type="hidden" id="hiddenfield_insert" name="selectedDB" value="" />'
                . '<input type="submit" class="input_db nosee" id="insert" name="insert" onclick="return checkDump()" value="OK" />'
                . '</div></div></form>';
        return $modalBox;
    }
    
    
    private function setModalBoxDuplicate() {
        $root = $_SESSION['root'];
        $value = $root === false ? $_SESSION['username'] : '';
        
        $modalBox = '<form class="dbform" method="post" action="">'
                . '<div id="modalbox_duplicate" class="modalbox">'               
                . '<div class="inbox">'
                . '<div id="titlebar_duplicate" class="titlebar"></div>'                
                . '<input type="button" class="close" onclick="return closeModalBox(\'duplicate\')" value="&times" />'
                . '<input type="text" name="dbname" id="dbname_duplicate" class="db_text nosee" value="'.$value.'" />'
                . '<input type="hidden" id="hiddenfield_duplicate" name="selectedDB" value="" />'
                . '<input type="submit" class="input_db nosee" id="duplicate" name="duplicate" onclick="return confirmDuplicateOrRename(\'duplicate\')" value="OK" />'
                . '</div></div></form>';
        return $modalBox;
    }
    
    
    private function setModalBoxRename() {
        $root = $_SESSION['root'];
        $value = $root === false ? $_SESSION['username'] : '';
        
        $modalBox = '<form class="dbform" method="post" action="">'
                . '<div id="modalbox_rename" class="modalbox">'               
                . '<div class="inbox">'
                . '<div id="titlebar_rename" class="titlebar"></div>'                
                . '<input type="button" class="close" onclick="return closeModalBox(\'rename\')" value="&times" />'
                . '<input type="text" name="dbname" id="dbname_rename" class="db_text nosee" value="'.$value.'" />'
                . '<input type="hidden" id="hiddenfield_rename" name="selectedDB" value="" />'
                . '<input type="submit" class="input_db nosee" id="rename" name="rename" onclick="return confirmDuplicateOrRename(\'rename\')" value="OK" />'
                . '</div></div></form>';
        return $modalBox;
    }
        
    
    /**
     * Erstellt das Drop-Down-Menu mit den Dumps
     * @return string
     */
    private function showDumpDropDown() {
        require_once 'DBAdmin_FileReader.php';
        $fileReader = new DBAdmin_FileReader();
        $dumpList = $fileReader->getDumpList();
        
        $dropDown = '<select id="select" name="dump" size="1" form="insert_form">';
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
        
        $HTMLTable = '<img id="plus" src="png/plus.PNG" title="Neue Datenbank" onclick="showModalBox(0, \'create\')" />'
                . $this->setModalBoxCreate()
                . '<div id="overload"><div id="load"></div></div>'

                // Header der HTML-Tabelle erstellen
                . '<div id="table_div"><table class="db_table">'
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
                    . '<td>'
                    . '<form class="form_img" method="post" action="">'
                    . '<input type="hidden" id="hiddenfield_delete_'.$no.'" name="selectedDB" value="" />'                        
                    . '<input type="image" class="img" id="del'.$no.'" src="png/trash.PNG" name="delete" title="Löschen" onclick="return confirmDeleteOrExport('.$no.', \'delete\')" />'
                    . '</form>'                        
                    . '<img class="img" id="imp'.$no.'" src="png/import.PNG" name="import" title="Dump importieren" onclick="showModalBox('.$no.', \'insert\')" />'
                    . '<form class="form_img" method="post" action="">'
                    . '<input type="hidden" id="hiddenfield_export_'.$no.'" name="selectedDB" value="" />'
                    . '<input type="image" class="img" id=exp'.$no.'" src="png/export.PNG" name="export" title="Dump erstellen" onclick="return confirmDeleteOrExport('.$no.', \'export\')" />'
                    . '</form>'
                    . '<img class="img" id="dup'.$no.'" src="png/duplicate.PNG" title="Duplizieren" onclick="showModalBox('.$no.', \'duplicate\')" />'
                    . '<img class="img" id="ren'.$no.'" src="png/edit.PNG" title="Umbenennen" onclick="showModalBox('.$no.', \'rename\')" /></td></tr>';
        }      
        $HTMLTable .= '</td></table></div>'                    
                    . $this->setModalBoxInsert()
                    . $this->setModalBoxDuplicate()
                    . $this->setModalBoxRename();
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