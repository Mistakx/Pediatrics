<?php 

//! Author: Sérgio Oliveira

require_once("custom/php/common.php");
wp_enqueue_style('ag', get_bloginfo( 'wpurl' ) . '/custom/css/ag.css',false,'1.1','all');

function handle_request($databaseConnection) {

    if (array_key_exists("Estado", $_REQUEST))  {

        if ($_REQUEST['Estado'] == "Introducao") { //* User has clicked a subitem
      
            $_REQUEST["Subitem_id"] = $_REQUEST["Subitem"]; //TODO: Does the teacher think this is necessary?
            $subitemID = $_REQUEST['Subitem_id']; 
            allowed_values_table($databaseConnection);
            $subitemNameQuery = $databaseConnection->query("SELECT name FROM subitem WHERE subitem.id = $subitemID");
            $subitemName = mysqli_fetch_assoc($subitemNameQuery)["name"];
            allowed_values_form($subitemID, $subitemName);
            echo "<a href='javascript:history.back()'>Voltar atrás.</a>";
    
        } 
        
        else if ($_REQUEST['Estado'] == "Inserir") { //* User has inserted some value
           
            $_REQUEST["Subitem_id"] = $_REQUEST["Subitem"];
            $subitemID = $_REQUEST['Subitem_id']; 
            $valueToInsert = $_REQUEST['Valor']; 
    
            //! Validations
            $valuesInDatabase = $databaseConnection->query("SELECT value FROM subitem_allowed_value WHERE subitem_allowed_value.subitem_id = $subitemID");
            $valueIsValid = !validateNewElementByName($valueToInsert, "value", $valuesInDatabase);
            if ($valueIsValid) {
                $databaseConnection->query("INSERT INTO subitem_allowed_value (subitem_id, value) VALUES ('$subitemID', '$valueToInsert')");
                echo "Foi inserido o valor permitido $valueToInsert.\n";
                echo "<a href=''>Continuar.</a>";
            }    
    
        }

    }

 else { //* If the user entered the page as usual

        $items = $databaseConnection->query("SELECT id FROM item");

        if ($items->num_rows == 0) { //* If there are no items in the database

            echo "<strong> Não há subitens especificados.</strong>";

        } else {
            allowed_values_table($databaseConnection);
        }
    }

}

function allowed_values_table($databaseConnection) {

    echo "<table>"; //* Table beginning
    
    //* Table header
    echo "<tr> 
            <th> <strong> item </strong> </th>
            <th>id</th>
            <th> <strong> subitem </strong> </th>
            <th>id</th>
            <th>valores permitidos</th>
            <th>estado</th>
            <th>ação</th>
    </tr>";

    //! Query all item names and IDs
    $items = $databaseConnection->query("SELECT name, id FROM item");
    foreach ($items as $item) {
        
        $itemRowSpan = 0;
        $itemRow = "";

        $itemRow.="<tr>";

        // Item name
        $itemRow.="<td rowspan=mistakxItemSpan> $item[name] </td>";

        //! Query all associated subitem names and IDs
        $subitems = $databaseConnection->query("SELECT name, id FROM subitem WHERE subitem.item_id = $item[id]");
        foreach($subitems as $subitem) {

            $subitemRow = "";
            $subitemRowSpan = 0; // Number of allowed values the subitem has

            if ($subitemRowSpan != 0) {$subitemRow.="<tr>";} // First element continues in the same line, after that it's a new row               

            // Subitem ID
            $subitemRow.="<td rowspan=mistakxSubitemSpan> $subitem[id] </td>";

            // Subitem name
            $subitemRow.="<td rowspan=mistakxSubitemSpan> <a href='?Estado=Introducao&Subitem=$subitem[id]'>$subitem[name]</a> </td>";

            //! Query all associated allowed values and IDs
            $allowedValues = $databaseConnection->query("SELECT id, value, state FROM subitem_allowed_value WHERE subitem_allowed_value.subitem_id = $subitem[id]");
            // print_r("ALLOWED VALUES:\n");
            // print_r($allowedValues);
            foreach($allowedValues as $allowedValue) {

                $allowedValueRow = "";

                if ($subitemRowSpan != 0) {$allowedValueRow.="<tr>";} // First element continues in the same line, after that it's a new row               

                // Allowed value ID
                $allowedValueRow.="<td> $allowedValue[id] </td>";

                // Allowed value
                $allowedValueRow.="<td> $allowedValue[value] </td>";
        
                // Allowed value state
                if ($allowedValue["state"] == "active") {
                    $allowedValueRow.="<td> ativo </td>";
                } else {
                   $allowedValueRow.="<td> inativo </td>";
                }

                // Action
                $allowedValueRow.="<td>";
                $editPageLink = get_bloginfo( 'wpurl' ) . "/edicao-de-dados" . "?Estado=Editar&Tipo=gestao-de-valores-permitidos&ID=$allowedValue[id]";
                $allowedValueRow.="<a href=" . $editPageLink . ">[editar]</a> <br>";
                if ($allowedValue["state"] == "active") {
                    $deactivatePageLink = get_bloginfo( 'wpurl' ) . "/edicao-de-dados" . "?Estado=Desativar&Tipo=gestao-de-valores-permitidos&ID=$allowedValue[id]";
                    $allowedValueRow.="<a href=" . $deactivatePageLink . ">[desativar]</a> <br>";
                } else {
                    $activatePageLink = get_bloginfo( 'wpurl' ) . "/edicao-de-dados" . "?Estado=Ativar&Tipo=gestao-de-valores-permitidos&ID=$allowedValue[id]";
                    $allowedValueRow.="<a href=" . $activatePageLink . ">[ativar]</a> <br>";
                }
                $allowedValueRow.="</td>";
                
                if ($subitemRowSpan != 0) {$allowedValueRow.="</tr>";} // First element continues in the same line, after that it's a new row 
                $subitemRow.=$allowedValueRow;
                $subitemRowSpan++;

            }

            if ($subitemRowSpan == 0) { // If subitem doesn't have any allowed values
                $subitemRow.="<td colspan=4>Não há valores predefinidos (subitem)</td>"; // TODO: Make this more dynamic
                $subitemRowSpan++; // The subitem row span can't be 0 because it bugs itself, so make it 1
            }
            if ($subitemRowSpan != 0) {$subitemRow.="</tr>";} // First element continues in the same line, after that it's a new row               
            $itemRowSpan = $itemRowSpan + $subitemRowSpan;
            $itemRow.= str_replace("mistakxSubitemSpan", strval($subitemRowSpan),$subitemRow);

        }

        
        if ($itemRowSpan == 0) { // If item doesn't have any subitems
            $itemRow.="<td colspan=6>Não há valores predefinidos (item)</td>"; // TODO: Make this more dynamic
            $itemRowSpan++; // The item row span can't be 0 because it bugs itself, so make it 1
        } 
        $itemRow.="</tr>"; 
        echo str_replace("mistakxItemSpan", strval($itemRowSpan),$itemRow);

    }

    echo "</table>"; // Table ending  
}

function allowed_values_form($subitemID, $subitemName) {
    echo "<h3>Gestão de valores permitidos - introdução</h3>"; 

    echo "<form method='post'>"; // Form beginning
        echo "<input type='text' name='Valor' placeholder='Valor permitido ($subitemName)' pattern='( ((?!^\d+$)^.+$) | (^(?!\s*$).+) )'>";
        echo "<input type='hidden' name='Estado' value='Inserir'>";
        echo "<input type='reset' value='Limpar'></input>"; //* Clear form button
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
