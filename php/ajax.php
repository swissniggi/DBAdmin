<?php
    session_start();
    
    require_once 'DBAdmin_Controller.php';
    $con = new DBAdmin_Controller();
    
    $requests = json_decode(file_get_contents("php://input"));
    $responses = array();
    
    foreach ($requests as $request) {
        $response = new stdClass();
        $response->tid = $request->tid;
        $response->data = new stdClass();
        $response->rows = new stdClass();
        
        try{
            switch ($request->facadeFn) {
                
                // Datenbanken auslesen
                case 'dbadmin.loadDbs':
                    $return = $con->selectDatabases();

                    if (isset($return)) {
                        if ($return instanceof Exception || $return instanceof Error) {
                            $response->errorMsg = $return->getMessage(); 
                        } else {
                            $rows = array();

                            foreach ($return as $database) {
                                // evtl. Daten formatieren
                                $importdate = $database['importdate'];
                                if ($importdate !== '--') {
                                    $importdate = date('d.m.Y', strtotime($importdate));
                                }

                                $changedate = $database['changedate'];
                                if ($changedate !== '--') {
                                    $changedate = date('d.m.Y', strtotime($changedate));
                                }

                                $rows[] = array(
                                    'Datenbankname' => $database['dbname'], 
                                    'Importdatum' => $importdate,
                                    'Änderungsdatum' => $changedate
                                );
                            }                    
                            $response->rows = $rows;
                        }
                    }
                    break;
                
                // Dumps auslesen
                case 'dbadmin.loadDumps':
                    $return = $con->getDumpList();

                    if ($return instanceof Exception || $return instanceof Error) {
                        $response->errorMsg = $return->getMessage(); 
                    } else {
                        $rows = array();

                        foreach ($return as $dump) {
                            $rows[] = array(
                                'value' => $dump,
                                'caption' => $dump
                            );
                        }                    
                        $response->rows = $rows;
                    }
                    break;

                // Einloggen
                case 'dbadmin.login':
                    $return = $con->loginUser($request->data->formData);

                    if ($return instanceof Exception || $return instanceof Error) {
                        $response->errorMsg = $return->getMessage(); 
                    } else {
                        $response->data = array(
                            'success' => 'true'
                        );
                    }
                    break;
                    
                // feststellen ob ein Benutzer angemeldet ist
                case 'dbadmin.checkLogin':
                    if (isset($_SESSION['username'])) {
                        $response->data = array(
                            'username' => $_SESSION['username']
                        );
                    } else {
                        $response->data = array(
                            'username' => false
                        );
                    }
                    break;

                // neue Datenbank erstellen
                case 'dbadmin.create':
                    $return = $con->createDatabase($request->data->formData->newDbname);

                    if ($return instanceof Exception || $return instanceof Error) {
                        $response->errorMsg = $return->getMessage(); 
                    } else {
                        $response->data = array(
                            'success' => 'true'
                        );
                    }
                    break;

                // Dump importieren
                case 'dbadmin.import':
                    $return = $con->importDatabase($request->data->formData);

                    if ($return instanceof Exception || $return instanceof Error) {
                        $response->errorMsg = $return->getMessage(); 
                    } else {
                        $response->data = array(
                            'success' => 'true'
                        );
                    }
                    break;

                // Datenbank exportieren
                case 'dbadmin.export':
                    $return = $con->exportDatabase($request->data->Datenbankname, true);

                    if ($return instanceof Exception || $return instanceof Error) {
                        $response->errorMsg = $return->getMessage(); 
                    } else {
                        $response->data = array(
                            'success' => 'true'
                        );
                    }
                    break;

                // Datenbank duplizieren
                case 'dbadmin.duplicate':
                    $return = $con->duplicateDatabase($request->data->formData->newDbname, $request->data->formData->oldDbname);

                    if ($return instanceof Exception || $return instanceof Error) {
                        $response->errorMsg = $return->getMessage(); 
                    } else {
                        $response->data = array(
                            'success' => 'true'
                        );
                    }
                    break;

                // Datenbank umbenennen
                case 'dbadmin.rename':
                    $return = $con->renameDatabase($request->data->formData->newDbname, $request->data->formData->oldDbname);

                    if ($return instanceof Exception || $return instanceof Error) {
                        $response->errorMsg = $return->getMessage(); 
                    } else {
                        $response->data = array(
                            'success' => 'true'
                        );
                    }
                    break;

                // Datenbank löschen
                case 'dbadmin.delete':
                    $return = $con->deleteDatabase($request->data->Datenbankname);

                    if ($return instanceof Exception || $return instanceof Error) {
                        $response->errorMsg = $return->getMessage(); 
                    } else {
                        $response->data = array(
                            'success' => 'true'
                        );
                    }
                    break;

                // Ausloggen
                case 'dbadmin.logout':
                    $return = $con->logoutUser();                
                    break;

                default: $response->errorMsg = 'Aktion nicht gefunden!';               
            }                               
        } catch (Exception $ex) {
            $response->errorMsg = $ex->getMessage();
        }
        $responses[] = $response;
    }
    // Antwort ausgeben
    print(json_encode($responses));