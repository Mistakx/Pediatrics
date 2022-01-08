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

?> 
