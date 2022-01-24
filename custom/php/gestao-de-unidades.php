<?php 

//! Author: Sérgio Oliveira

require_once("custom/php/common.php");
wp_enqueue_style('ag', get_bloginfo( 'wpurl' ) . '/custom/css/ag.css',false,'1.1','all');

function handle_request($databaseConnection) {

    if ( array_key_exists("Estado", $_REQUEST) and $_REQUEST['Estado'] == "Inserir") { //* User has inserted some unit type

        echo "<h3>Gestão de unidades - inserção</h3>";

        $unitToInsert = $_REQUEST['Nome'];
        
            //! Validations
            $unitNamesInDatabase = $databaseConnection->query("SELECT name FROM subitem_unit_type");
            $unitNameIsValid = !validateNewElementByName($unitToInsert, "name", $unitNamesInDatabase);
            if ($unitNameIsValid) {
                $databaseConnection->query("INSERT INTO subitem_unit_type (name) VALUES ('$unitToInsert')");
                echo "Foi inserida a unidade $unitToInsert.\n";
                echo "<a href=''>Continuar.</a>";
            }    
        
    } else { //* If the user entered the page as usual, without inserting any unit type
      
        $subitemUnitIDs = $databaseConnection->query("SELECT id FROM subitem_unit_type");
        
        if ($subitemUnitIDs->num_rows == 0) { //* If there are no unit types in the database

            echo "<strong> Não há tipos de unidades.</strong>";

        } else { //* If there are unit types in the database
            
            unit_types_table($databaseConnection); 

        }

        echo "<h3>Gestão de unidades - introdução</h3>"; 
        insert_unit_type_form();

    }

}

function unit_types_table($databaseConnection) {

    $subitemUnits = $databaseConnection->query("SELECT id, name FROM subitem_unit_type");

    echo "<table>"; // Table beginning

    // Table header
    echo "<tr>
            <th> <strong> id </strong> </th>
            <th> <strong> unidade </strong> </th>
            <th> <strong> subitem </strong> </th>
        </tr>";

    //* For each subitem unit type
    foreach($subitemUnits as $subitemUnit) {

        echo "<tr>"; // Begin table row

        echo "<td>" . $subitemUnit["id"] . "</td>"; //* First column
        echo "<td>" . $subitemUnit["name"] . "</td>"; //* Second column

        echo "<td>"; // Third column: nomes subitens que têm respetivo tipo de unidade, aparecendo dentro de parêntesis o nome do item a que pertence esse subitem

        //* Query all the "subitems" that have the same "subitem type" of this loop "subitem unit type"
        $subitems = $databaseConnection->query("SELECT name, item_id FROM subitem WHERE subitem.unit_type_id = " . $subitemUnit["id"]);
        $subitemsString = "";
        foreach($subitems as $subitem) { //* For each "subitem" that has the same "subitem type" of this loop "subitem type"

            $subitemsString = $subitemsString . $subitem["name"];

            // Query the "item" parent of the loop "subtitem"
            $item = $databaseConnection->query("SELECT name FROM item WHERE item.id = " . $subitem["item_id"]);
            $subitemsString = $subitemsString . " (". $item->fetch_assoc()["name"] . "), ";
            


        }

        // Remove the last "," if the unit had a subitem, and echo the subitems string.
        if ($subitemsString != "") {
            $subitemsString = rtrim($subitemsString, ", ");
            $subitemsString = $subitemsString . ".";
            echo $subitemsString;
        }

        echo "</td>";
        
        echo "</tr>"; // End table row

    }

    echo "</table>"; // Table ending  
}

function insert_unit_type_form() {

    echo "<form method='post'>"; // Form beginning
        // (?!^\d+$)^.+$ Not only digits
        // ^(?!\s*$).+ At least one non-space character
        echo "<input type='text' name='Nome' placeholder='Nova unidade' pattern='( ((?!^\d+$)^.+$) | (^(?!\s*$).+) )' >";
        echo "<input type='hidden' name='Estado' value='Inserir'>";
        echo "<input type='reset' value='Limpar'></input>"; //* Clear form button
        echo "<button> Submeter </button>";
    echo "</form>"; // Form ending
    
}

// print_r($_REQUEST);

//* Verify if the user is logged in, and if it has the manage_unit_types capability
verifyLoginAndCapability("manage_unit_types");

//* User information
$currentUser = wp_get_current_user();
printCurrentUsername($currentUser);
printCurrentUserRoles($currentUser);

echo "<br>";

//* Database information
$databaseConnection = connectToDatabase();
handle_request($databaseConnection);

?> 
