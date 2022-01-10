<?php

//! Author: Sérgio Oliveira

require_once("custom/php/common.php");
wp_enqueue_style('ag', get_bloginfo( 'wpurl' ) . '/custom/css/ag.css',false,'1.1','all');

function handle_request($databaseConnection) {

    // print_r("TEST21\n");

    if (array_key_exists("Estado", $_REQUEST)) {
    
        
        if ($_REQUEST['Estado'] == "Atualizar") { //* User edited some tuple

            if ($_REQUEST['Tipo'] == "gestao-de-valores-permitidos") { //* User edited allowed value

                $allowedValueToEditID = $_REQUEST["ID"];
                $subitemThatHasAllowedValueIDQuery = $databaseConnection->query("SELECT subitem_id FROM subitem_allowed_value WHERE subitem_allowed_value.id = $allowedValueToEditID "); // This is used because when editing an allowed value, we need to check if the new name already exists in the corresponding subitem.
                $subitemThatHasAllowedValueID = mysqli_fetch_assoc($subitemThatHasAllowedValueIDQuery)["subitem_id"];
                $newValue = $_REQUEST["Valor"];

                //! Validations
                $allowedValuesInSubitem = $databaseConnection->query("SELECT value FROM subitem_allowed_value WHERE subitem_allowed_value.subitem_id = $subitemThatHasAllowedValueID");
                $valueIsValid = !validateNewElementByName($newValue, "value", $allowedValuesInSubitem);
                if ($valueIsValid) {
                    $databaseConnection->query("UPDATE subitem_allowed_value SET value = '$newValue' WHERE subitem_allowed_value.id = $allowedValueToEditID");
                    echo "Valor atualizado com sucesso.\n";
                    echo "<a href=". get_bloginfo( 'wpurl' ) . "/gestao-de-valores-permitidos>Confirmar.</a>\n"; 
                }  

            }

            else if ($_REQUEST['Tipo'] == "gestao-de-itens") { //* User edited item

                $itemToEditID = $_REQUEST["ID"];
                $itemTypeThatHasItemIDQuery = $databaseConnection->query("SELECT item.item_type_id FROM item WHERE item.id = $itemToEditID "); // This is used because when editing an item, we need to check if the new name already exists in the corresponding item type.
                $itemTypeThatHasItemID = mysqli_fetch_assoc($itemTypeThatHasItemIDQuery)["item_type_id"];
                $newValue = $_REQUEST["Valor"];

                //! Validations
                $itemsInItemType = $databaseConnection->query("SELECT name FROM item WHERE item.item_type_id = $itemTypeThatHasItemID");
                $valueIsValid = !validateNewElementByName($newValue, "name", $itemsInItemType);
                if ($valueIsValid) {
                    $databaseConnection->query("UPDATE item SET name = '$newValue' WHERE item.id = $itemToEditID");
                    echo "Valor atualizado com sucesso.\n";
                    echo "<a href=". get_bloginfo( 'wpurl' ) . "/gestao-de-itens>Confirmar.</a>\n"; 
                }  

            }



        }

        else if ($_REQUEST['Estado'] == "Ativar") { //* User has activated some tuple

            if ($_REQUEST['Tipo'] == "gestao-de-valores-permitidos") { //* User activated allowed value
        
                $allowedValueToActivateID = $_REQUEST["ID"];
                $databaseConnection->query("UPDATE subitem_allowed_value SET state = 'active' WHERE subitem_allowed_value.id = $allowedValueToActivateID");
                echo "Valor ativado com sucesso.\n";
                echo "<a href=". get_bloginfo( 'wpurl' ) . "/gestao-de-valores-permitidos>Confirmar.</a>\n"; 

            }

            else if ($_REQUEST['Tipo'] == "gestao-de-itens") { //* User activated allowed value
        
                $itemToActivateID = $_REQUEST["ID"];
                $databaseConnection->query("UPDATE item SET state = 'active' WHERE item.id = $itemToActivateID");
                echo "Valor ativado com sucesso.\n";
                echo "<a href=". get_bloginfo( 'wpurl' ) . "/gestao-de-itens>Confirmar.</a>\n"; 
            }



        }

        else if ($_REQUEST['Estado'] == "Desativar") { //* User has deactivated some tuple


            if ($_REQUEST['Tipo'] == "gestao-de-valores-permitidos") { //* User deactivated allowed value
        
                $allowedValueToActivateID = $_REQUEST["ID"];
                $databaseConnection->query("UPDATE subitem_allowed_value SET state = 'inactive' WHERE subitem_allowed_value.id = $allowedValueToActivateID");
                echo "Valor desativado com sucesso.\n";
                echo "<a href=". get_bloginfo( 'wpurl' ) . "/gestao-de-valores-permitidos>Confirmar.</a>\n"; 
    
            }

            else if ($_REQUEST['Tipo'] == "gestao-de-itens") { //* User activated allowed value
        
                $itemToDeactivateID = $_REQUEST["ID"];
                $databaseConnection->query("UPDATE item SET state = 'inactive' WHERE item.id = $itemToDeactivateID");
                echo "Valor desativado com sucesso.\n";
                echo "<a href=". get_bloginfo( 'wpurl' ) . "/gestao-de-itens>Confirmar.</a>\n"; 
            }



        }

        else if ($_REQUEST['Estado'] == "Editar") { //* User has clicked edit

            if ($_REQUEST['Tipo'] == "gestao-de-valores-permitidos") { //* User editing manage allowed values page
        
                echo "<h3>Edição de dados: Gestão de valores permitidos</h3>"; 

                $allowedValueToEditID = $_REQUEST["ID"];
                $allowedValueToEditQuery = $databaseConnection->query("SELECT value FROM subitem_allowed_value WHERE subitem_allowed_value.id = $allowedValueToEditID");
                $allowedValueToEdit = mysqli_fetch_assoc($allowedValueToEditQuery)["value"];
                edit_allowed_value_form($allowedValueToEditID, $allowedValueToEdit);

            }
    
            if ($_REQUEST['Tipo'] == "gestao-de-itens") { //* User editing manage items page
        
                echo "<h3>Edição de dados: Gestão de itens</h3>"; 

                $itemToEditID = $_REQUEST["ID"];
                $itemToEditToEditQuery = $databaseConnection->query("SELECT name FROM item WHERE item.id = $itemToEditID");
                $itemToEditToEdit = mysqli_fetch_assoc($itemToEditToEditQuery)["name"];
                edit_item_form($itemToEditID, $itemToEditToEdit);

            }
    

            echo "<a href='javascript:history.back()'>Voltar atrás.</a>";


        }


    }

}

function edit_allowed_value_form($allowedValueToEditID, $allowedValueToEditName) {

    echo "<div class='form-inline'>";
    
    echo "<form method='post'>"; // Form beginning
        echo "<input id='Valor' type='text' name='Valor' placeholder='Valor permitido: $allowedValueToEditName' >";
        echo "<input type='hidden' name='Estado' value='Atualizar'>";
        echo "<input type='hidden' name='Tipo' value=$_REQUEST[Tipo]>";
        echo "<input type='hidden' name='ID' value=$allowedValueToEditID>";
        echo "<input type='reset' value='Limpar'></input>"; //* Clear form button
        echo "<button> Submeter </button>";

    echo "</form>"; // Form ending


    echo "</div>";

}

function edit_item_form($itemToEditID, $itemToEdit) {

    echo "<div class='form-inline'>";
    
    echo "<form method='post'>"; // Form beginning
        echo "<input id='Valor' type='text' name='Valor' placeholder='Item: $itemToEdit' >";
        echo "<input type='hidden' name='Estado' value='Atualizar'>";
        echo "<input type='hidden' name='Tipo' value=$_REQUEST[Tipo]>";
        echo "<input type='hidden' name='ID' value=$itemToEditID>";
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