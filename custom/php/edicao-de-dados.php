<?php 
// kU3o7LHl8HKq

require_once("custom/php/common.php");
wp_enqueue_style('ag', get_bloginfo( 'wpurl' ) . '/custom/css/ag.css',false,'1.1','all');

function handle_request($databaseConnection) {

    if (array_key_exists("Estado", $_REQUEST)) {
    
        
        if ($_REQUEST['Estado'] == "Atualizar") { //* User edited some tuple

            if ($_REQUEST['Tipo'] == "gestao-de-valores-permitidos") { //* User edited allowed value

                $allowedValueToEditID = $_REQUEST["ID"];
                $subitemThatHasAllowedValueIDQuery = $databaseConnection->query("SELECT subitem_id FROM subitem_allowed_value WHERE subitem_allowed_value.id = $allowedValueToEditID "); // This is used because when editing an allowed value, we need to check if the new name already exists in the corresponding subitem.
                $subitemThatHasAllowedValueID = mysqli_fetch_assoc($subitemThatHasAllowedValueIDQuery)["subitem_id"];
                $newValue = $_REQUEST["Valor"];

                //! Validations
                $valuesInDatabase = $databaseConnection->query("SELECT value FROM subitem_allowed_value WHERE subitem_allowed_value.subitem_id = $subitemThatHasAllowedValueID");
                $valueIsValid = !validateNewElementByName($newValue, "value", $valuesInDatabase);
                if ($valueIsValid) {
                    $databaseConnection->query("UPDATE subitem_allowed_value SET value = '$newValue' WHERE subitem_allowed_value.id = $allowedValueToEditID");
                    echo "Valor atualizado com sucesso.\n";
                    echo "<a href=". get_bloginfo( 'wpurl' ) . "/gestao-de-valores-permitidos>Confirmar.</a>\n"; 
                }  

            }



        }

        else if ($_REQUEST['Estado'] == "Ativar") { //* User has activated some tuple


            if ($_REQUEST['Tipo'] == "gestao-de-valores-permitidos") { //* User activated allowed value
        
                $allowedValueToActivateID = $_REQUEST["ID"];
                $databaseConnection->query("UPDATE subitem_allowed_value SET state = 'active' WHERE subitem_allowed_value.id = $allowedValueToActivateID");

            }

            echo "Valor ativado com sucesso.\n";
            echo "<a href=". get_bloginfo( 'wpurl' ) . "/gestao-de-valores-permitidos>Confirmar.</a>\n"; 

        }

        else if ($_REQUEST['Estado'] == "Desativar") { //* User has deactivated some tuple


            if ($_REQUEST['Tipo'] == "gestao-de-valores-permitidos") { //* User deactivated allowed value
        
                $allowedValueToActivateID = $_REQUEST["ID"];
                $databaseConnection->query("UPDATE subitem_allowed_value SET state = 'inactive' WHERE subitem_allowed_value.id = $allowedValueToActivateID");

            }

            echo "Valor desativado com sucesso.\n";
            echo "<a href=". get_bloginfo( 'wpurl' ) . "/gestao-de-valores-permitidos>Confirmar.</a>\n"; 

        }

        else if ($_REQUEST['Estado'] == "Editar") { //* User has clicked edit

            if ($_REQUEST['Tipo'] == "gestao-de-valores-permitidos") { //* User editing manage allowed values page
        
                echo "<h3>Edição de dados: Gestão de valores permitidos</h3>"; 

                $allowedValueToEditID = $_REQUEST["ID"];
                $allowedValueToEditQuery = $databaseConnection->query("SELECT value FROM subitem_allowed_value WHERE subitem_allowed_value.id = $allowedValueToEditID");
                $allowedValueToEdit = mysqli_fetch_assoc($allowedValueToEditQuery)["value"];
                edit_value_form($allowedValueToEditID, $allowedValueToEdit);

            }
    
            echo "<a href='javascript:history.back()'>Voltar atrás.</a>";


        }


    }

}

function edit_value_form($allowedValueToEditID, $allowedValueToEdit) {

    echo "<div class='form-inline'>";
    
    echo "<form method='post'>"; // Form beginning
        echo "<input id='Valor' type='text' name='Valor' placeholder='Valor permitido: $allowedValueToEdit' >";
        echo "<input type='hidden' name='Estado' value='Atualizar'>";
        echo "<input type='hidden' name='Tipo' value=$_REQUEST[Tipo]>";
        echo "<input type='hidden' name='ID' value=$allowedValueToEditID>";
        echo "<input type='reset' value='Limpar'></input>"; //* Clear form button
        echo "<button> Submeter </button>";

    echo "</form>"; // Form ending


    echo "</div>";

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