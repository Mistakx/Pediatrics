<?php

// kU3o7LHl8HKq

require_once("custom/php/common.php");

//! Sérgio

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

//* Validates if the new tuple name being inserted is valid
function validateNewElementByName($tupleName, $tupleNameDatabaseParameter, $databaseTuples) {
    
    if ( $tupleName != "" ) { //* Non empty value
    
        //! Input only has numbers
        if (is_numeric($tupleName)) {
            echo "O valor $tupleName é inválido pois contém apenas números.\n";
            echo "<a href='javascript:history.back()'>Voltar atrás.</a>";
            return 1;
        }

        //! Input only has whitespaces
        if (strlen(trim($tupleName)) == 0) {
            echo "O valor $tupleName é inválido pois contém apenas espaços em branco.\n";
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

//! Filipa

function BackButton(){
    echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");</script>
<noscript>
<a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
</noscript>";
}

function validateInputs($error){
    if (!empty($error)) {
        foreach ($error as $value) {
            echo "$value !<br>";
        }
        BackButton();
    }
    else{
        return true;
    }
}

//! Pedro

function verificar_string($string, $campo) {
	if ($string == '') {
		echo '<b>O campo ';  
		echo $campo;
		echo ' é obrigatório.</b>';
		return false;
	}
	else return true;
}

function testar_permissão($capability) {
    if (!is_user_logged_in() && !current_user_can($capability)) {
        return false;
    }
    elseif(!current_user_can($capability)) {
        return false;
    }
    else {
        return true;
    }
}


?> 
