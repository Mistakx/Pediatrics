<?php 
// kU3o7LHl8HKq

require_once("custom/php/common.php");

function handle_request($databaseConnection) {

    if ( array_key_exists("Estado", $_REQUEST) and $_REQUEST['Estado'] == "Inserir") { //* User has inserted some unit type

        echo "<h3>Gestão de unidades - inserção</h3>"; 

        $unitToInsert = $_REQUEST['Nome'];

        if ( $unitToInsert != "") { //* Non empty unit type name

            $unitNamesInDatabase = $databaseConnection->query("SELECT name FROM subitem_unit_type"); // TODO: Not necessary, query was executed before on the main function, and can be sent as parameter
            $unitNameAlreadyExists = FALSE;
            foreach ($unitNamesInDatabase as $unitNameInDatabase) { // Check if unit already exists in the database                
                if ($unitToInsert == $unitNameInDatabase["name"]) {
                    $unitNameAlreadyExists = TRUE;
                    break;
                }
            }
            
            if ($unitNameAlreadyExists) {
                echo "O valor $unitToInsert já existe na base de dados.\n";
                echo "<a href=''>Voltar atrás.</a>";

            } else {
                $databaseConnection->query("INSERT INTO subitem_unit_type (name) VALUES ('$unitToInsert')");
                echo "Foi inserido o valor $unitToInsert.\n";
                echo "<a href=''>Continuar.</a>";
            }

        } else { //* Empty unit type name
            echo "O nome da unidade enviada foi inválido.\n";
            echo "<a href=''>Voltar atrás.</a>";
        }
    
    } else { //* If the user entered the page as usual, without inserting any unit type
      
        $subitemTypes = $databaseConnection->query("SELECT * FROM subitem_unit_type");
        
        if ($subitemTypes->num_rows == 0) { //* If there are no unit types in the database

            echo "<strong> Não há tipos de unidades.</strong>";

        } else { //* If there are unit types in the database
            
            unit_types_table($databaseConnection, $subitemTypes); 

        }

        echo "<h3>Gestão de unidades - introdução</h3>"; 
        insert_unit_type_form();

    }

}

function unit_types_table($databaseConnection) {

    $subitemTypes = $databaseConnection->query("SELECT * FROM subitem_unit_type");

    echo "<table>"; // Table beginning

    // Table header
    echo "<tr>
            <th> <strong> id </strong> </th>
            <th> <strong> unidade </strong> </th>
            <th> <strong> subitem </strong> </th>
        </tr>";

    //* For each subitem type
    foreach($subitemTypes as $subitemType) {


        echo "<tr>"; // Begin table row

        echo "<td>" . $subitemType["id"] . "</td>"; //* First column
        echo "<td>" . $subitemType["name"] . "</td>"; //* Second column

        echo "<td>"; // Third column: nomes subitens que têm respetivo tipo de unidade, aparecendo dentro de parêntesis o nome do item a que pertence esse subitem

        // Query all the "subitems" that have the same "subitem type" of this loop "subitem type"
        $subitems = $databaseConnection->query("SELECT * FROM subitem WHERE subitem.unit_type_id = " . $subitemType["id"]);

        //* For each "subitem" that has the same "subitem type" of this loop "subitem type"
        foreach($subitems as $subitem) {

            echo $subitem["name"];

            // Query the "item" parent of the loop "subtitem"
            $item = $databaseConnection->query("SELECT * FROM item WHERE item.id = " . $subitem["item_id"]);
            echo " (". $item->fetch_assoc()["name"] . "), ";

        }

        echo "</td>";
        
        echo "</tr>"; // End table row

    }

    echo "</table>"; // Table ending  
}

function insert_unit_type_form() {

    echo "<form method='post'>"; // Form beginning
        echo "<input type='text' name='Nome' placeholder='Unidade a inserir'>";
        echo "<input type='hidden' name='Estado' value='Inserir'>";
        echo "<button> Submeter </button>";
    echo "</form>"; // Form ending
    
}

print_r($_REQUEST);

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
