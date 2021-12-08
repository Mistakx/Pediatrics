<?php
require_once("custom/php/common.php");

function connectToDatabase() {
    
    $databaseConnection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $connectionError = mysqli_error($databaseConnection);

    if (!$databaseConnection) {
        echo "Erro na ligação: $connectionError.";
        exit();
    }

    return $databaseConnection;

}

function verifyLoginAndCapability($capabilityName) {
    
    if (!(is_user_logged_in())) {
        exit("User isn't logged in.\n");
    } else {
        echo "User is logged in.\n";
    }
    
    if (!(current_user_can($capabilityName))) {
        echo "User can't $capabilityName.\n";
        exit("User doesn't have the permissions to view this page.\n");
    } else {
        echo "User can $capabilityName.\n";
    }


}

function printCurrentUserRoles($currentUser){
    echo "Roles: ";
    if ( !empty( $currentUser->roles ) && is_array( $currentUser->roles ) ) {
        foreach ( $currentUser->roles as $role )
            echo $role, " ";
        
    }
    echo "\n";    
}

function printCurrentUsername($currentUser){
    $currentUserUsername = $currentUser->user_login;
    echo "Username: $currentUserUsername\n";
}

?> 
