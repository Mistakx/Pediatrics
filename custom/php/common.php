<?php
require_once("custom/php/common.php");

function queryDatabase($query) {
    $link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
    $result = mysqli_query($link,$query);

    return $result;
}

function connectToDatabase() {
    
    $databaseConnection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $connectionError = mysqli_error($databaseConnection);

    if (!$databaseConnection) {
        echo "Erro na ligação: $connectionError.";
        exit();
    }

    return $databaseConnection;

}

function get_enum_values($table, $field) {
    $connectionToDatabase = connectToDatabase();
    $enum_array = array();
    $query = 'SHOW COLUMNS FROM `' . $table . '` LIKE "' . $field . '"';
    $result = mysqli_query($connectionToDatabase, $query);
    $row = mysqli_fetch_row($result);
    preg_match_all('/\'(.*?)\'/', $row[1], $enum_array);
    foreach ($enum_array[1] as $mkey => $mval) {
        $enum_fields[$mkey + 1] = $mval;
    }
    return $enum_fields;
}

// Echoes the login 
function verifyLoginAndCapability($capabilityName) {
    
    if (!(is_user_logged_in())) {
        echo "User isn't logged in.\n";
        exit();
    } else {
        echo "User is logged in.\n";
    }
    
    if (!(current_user_can($capabilityName))) {
        echo "User can't $capabilityName.\n";
        echo "User doesn't have the permissions to view this page.\n";
        exit();
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
