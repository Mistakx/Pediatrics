<?php 
// kU3o7LHl8HKq

require_once("custom/php/common.php");
wp_enqueue_style('ag', get_bloginfo( 'wpurl' ) . '/custom/css/ag.css',false,'1.1','all');

function handle_request($databaseConnection) {

    if ( array_key_exists("Estado", $_REQUEST) and $_REQUEST['Estado'] == "Editar") { //* User has clicked edit

        if ( array_key_exists("Tipo", $_REQUEST) and $_REQUEST['Tipo'] == "gestao-de-valores-permitidos") { //* User editing manage allowed values page
      
            echo "<h3>Edição de dados: Gestão de valores permitidos</h3>"; 

            $allowedValueToEditID = $_REQUEST["ID"];
            $allowedValueToEditQuery = $databaseConnection->query("SELECT value FROM subitem_allowed_value WHERE subitem_allowed_value.id = $allowedValueToEditID");
            $allowedValueToEdit = mysqli_fetch_assoc($allowedValueToEditQuery)["value"];
            edit_value_form($allowedValueToEditID, $allowedValueToEdit);

        }


    }
  
    echo "<a href='javascript:history.back()'>Voltar atrás.</a>";


}

function edit_value_form($allowedValueToEditID, $allowedValueToEdit) {

    echo "<form method='post'>"; // Form beginning
        echo "<input type='text' name='Valor' placeholder='Valor permitido: $allowedValueToEdit' >";
        echo "<input type='hidden' name='Estado' value='Inserir'>";
        echo "<button> Submeter </button>";
    echo "</form>"; // Form ending
}




// print_r($_REQUEST);

//* Verify if the user is logged in, and if it has the manage_unit_types capability
verifyLoginAndCapability("manage_allowed_values");

//* User information
$currentUser = wp_get_current_user();
printCurrentUsername($currentUser);
printCurrentUserRoles($currentUser);

echo "<br>";

//* Database information
$databaseConnection = connectToDatabase();
handle_request($databaseConnection);
?> 
