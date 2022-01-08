<?php

require_once("custom/php/common.php");

function connectToDatabase() {
    
    $databaseConnection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $connectionError = mysqli_error($databaseConnection);

    if (!$databaseConnection) {
        echo "Erro de comunicação: $connectionError.";
        exit();
    }

    return $databaseConnection;

}

function verifyLoginAndCapability($capabilityName) {
    
    if (!(is_user_logged_in())) {
        exit("O utilizador não está autenticado.\n");
    } 
    
    echo "O utilizador está autenticado.\n";
    
    if (!(current_user_can($capabilityName))) {
        echo "O utilizador não tem a capability $capabilityName.\n";
        exit("Não tem autorização para aceder a esta página.\n");
    } 

    echo "O utilizador pode $capabilityName.\n";
    


}

function printCurrentUserRoles($currentUser){
    echo "Role do utilizador: ";
    if ( !empty( $currentUser->roles ) && is_array( $currentUser->roles ) ) {
        foreach ( $currentUser->roles as $role )
            echo $role, " ";
        
    }
    echo "\n";    
}

function printCurrentUsername($currentUser){
    $currentUserUsername = $currentUser->user_login;
    echo "Nome do utilizador: $currentUserUsername\n";
}

//! Validates if the new tuple name being inserted is valid
function validateNewElementByName($tupleName, $tupleNameDatabaseParameter, $databaseTuples) {
    
    if ( $tupleName != "" ) { //* Non empty value
    
        //! Input only has numbers
        if (is_numeric($tupleName)) {
            echo "O valor $tupleName é inválido pois contém apenas números.\n";
            echo "<a href='javascript:history.back()'>Voltar atrás.</a>";
            return 1;
        }

        $tupleAlreadyExists = FALSE;
        foreach ($databaseTuples as $databaseTuple) { // Check if tuple already exists in the database                
            if ($tupleName == $databaseTuple[$tupleNameDatabaseParameter]) {
                $tupleAlreadyExists = TRUE;
                break;
            }
        }
        
        //! Input already exists in database
        if ($tupleAlreadyExists) {

            echo "O valor $tupleName já existe na base de dados.\n";
            echo "<a href='javascript:history.back()'>Voltar atrás.</a>";
            return 1;
        }
    
        else { //* Tuple doesn't already exist
        
           return 0;
    
        }

    }

    else { //! Input is empty
    
        echo "O valor enviado foi vazio.\n";
        echo "<a href='javascript:history.back()'>Voltar atrás.</a>";
        return 1;
    
    }
    
}
?> 
