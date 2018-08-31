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
        
        switch ($request->facadeFn) {
            case 'dbadmin.loadDbs':
                $return = $con->selectDatabases();
                
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
                            'Datenbank' => $database['dbname'], 
                            'Importdatum' => $importdate,
                            'Änderungsdatum' => $changedate
                        );
                    }                    
                    $response->rows = $rows;
                }                
                break;
                
            case 'dbadmin.loadDumps':
                $return = $con->getDumpList();
                
                if ($return instanceof Exception || $return instanceof Error) {
                    $response->errorMsg = $return->getMessage(); 
                } else {
                    $rows = array();
                    
                    foreach ($return as $dump) {
                        $rows[] = array(
                            'Dumpname' => $dump
                        );
                    }                    
                    $response->rows = $rows;
                }
                break;
                
            case 'dbadmin.login':
                $return = $con->loginUser($request->data);
                
                if ($return instanceof Exception || $return instanceof Error) {
                    $response->errorMsg = $return->getMessage(); 
                } else {
                    $response->data = array(
                        'success' => 'true'
                    );
                }
                break;
                
            case 'dbadmin.create':
                $return = $con->createDatabase($request->data->newDbname);
                if ($return instanceof Exception || $return instanceof Error) {
                    $response->errorMsg = $return->getMessage(); 
                } else {
                    $response->data = array(
                        'success' => 'true'
                    );
                }
                break;
                
            case 'dbadmin.import':
                $return = $con->importDatabase($request->data);
                if ($return instanceof Exception || $return instanceof Error) {
                    $response->errorMsg = $return->getMessage(); 
                } else {
                    $response->data = array(
                        'success' => 'true'
                    );
                }
                break;
                
            case 'dbadmin.export':
                $return = $con->exportDatabase($request->data->Datenbank, true);
                if ($return instanceof Exception || $return instanceof Error) {
                    $response->errorMsg = $return->getMessage(); 
                } else {
                    $response->data = array(
                        'success' => 'true'
                    );
                }
                break;
                
            case 'dbadmin.duplicate':
                $return = $con->duplicateDatabase($request->data->newDbname, $request->data->oldDbname);
                if ($return instanceof Exception || $return instanceof Error) {
                    $response->errorMsg = $return->getMessage(); 
                } else {
                    $response->data = array(
                        'success' => 'true'
                    );
                }
                break;
                
            case 'dbadmin.rename':
                $return = $con->renameDatabase($request->data->newDbname, $request->data->oldDbname);
                if ($return instanceof Exception || $return instanceof Error) {
                    $response->errorMsg = $return->getMessage(); 
                } else {
                    $response->data = array(
                        'success' => 'true'
                    );
                }
                break;
                
            case 'dbadmin.delete':
                $return = $con->deleteDatabase($request->data->Datenbank);
                if ($return instanceof Exception || $return instanceof Error) {
                    $response->errorMsg = $return->getMessage(); 
                } else {
                    $response->data = array(
                        'success' => 'true'
                    );
                }
                break;
                
            case 'dbadmin.logout':
                $return = $con->logoutUser();
                if ($return instanceof Exception || $return instanceof Error) {
                    $response->errorMsg = $return->getMessage(); 
                } else {
                    $response->data = array(
                        'success' => 'true'
                    );
                }
                break;

            default: $response->errorMsg = 'Aktion nicht gefunden!';               
        }                       
        $responses[] = $response;
    }   
    print(json_encode($responses));