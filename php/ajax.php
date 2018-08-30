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
                
                if ($return instanceof Exception) {
                    $response->errorMsg = $return->getMessage(); 
                } else {
                    $rows = array();
                    
                    foreach ($return as $database) {
                        $rows[] = array(
                            'Datenbank' => $database['dbname'], 
                            'Importdatum' => $database['importdate'], 
                            'Änderungsdatum' => $database['changedate']
                        );
                    }                    
                    $response->rows = $rows;
                }                
                break;
                
            case 'dbadmin.login':
                $return = $con->loginUser($request->data);
                
                if ($return instanceof Exception) {
                    $response->errorMsg = $return->getMessage(); 
                } else {
                    $response->data = array(
                        'success' => 'true'
                    );
                }
                break;
                
            case 'dbadmin.create':
                $return = $con->createDatabase($request->data->dbname);
                if ($return instanceof Exception) {
                    $response->errorMsg = $return->getMessage(); 
                } else {
                    $response->data = array(
                        'success' => 'true'
                    );
                }
                break;
                
            case 'dbadmin.logout':
                $return = $con->logoutUser();
                if ($return instanceof Exception) {
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