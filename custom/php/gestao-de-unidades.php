<?php 
// kU3o7LHl8HKq

require_once("custom/php/common.php");

function handle_request($databaseConnection) {

    if ( array_key_exists("Estado", $_REQUEST) and $_REQUEST['Estado'] == "Inserir") { //* If the user has inserted some unit type in the database

        if ( array_key_exists("Nome", $_REQUEST) and $_REQUEST['Nome'] != "") { //* Valid unit type name

            $unitInserted = $_REQUEST['Nome'];
            $unitNamesInDatabase = $databaseConnection->query("SELECT name FROM subitem_unit_type");
            $unitNameAlreadyExists = FALSE;
            foreach ($unitNamesInDatabase as $unitNameInDatabase) { // Check if unit already exists in the database                
                if ($unitInserted == $unitNameInDatabase["name"]) {
                    $unitNameAlreadyExists = TRUE;
                    break;
                }
            }
            
            if ($unitNameAlreadyExists) {
                echo "<script>alert('O valor $unitInserted já existe na base de dados.')</script>";
            } else {
                $databaseConnection->query("INSERT INTO subitem_unit_type (name) VALUES ('$unitInserted')");
                // echo "<script>alert('Foi inserido o valor $unitInserted')</script>";
            }



        } else { //* Invalid unit type name
            echo "<script>alert('O nome da unidade enviada foi inválido.')</script>";
            echo "<br><br>";
        }
    
    } else { //* If the user entered the page as usual, without sending any unit type to the database
        return;
    }

}

function unit_types_table($databaseConnection) {

    $subitemTypes = $databaseConnection->query("SELECT * FROM subitem_unit_type");


    echo "<table>"; // Table beginning

    // Table header
    echo "<tr>
            <th>id</th>
            <th>unidade</th>
            <th>subitem</th>
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

    echo "<h3>Gestão de unidades - introdução</hr>"; 

    echo "<form method='post'>"; // Form beginning
        echo "<input type='text' name='Nome' >";
        echo "<input type='hidden' name='Estado' value='Inserir'>";
        echo "<button> Submeter </button>";
    echo "</form>"; // Form ending
    
}

//* Verify if the user is logged in, and if it has the manage_unit_types capability
verifyLoginAndCapability("manage_unit_types");

//* User information
$currentUser = wp_get_current_user();
printCurrentUsername($currentUser);
printCurrentUserRoles($currentUser);

echo "<br>";

//* Database information
$databaseConnection = connectToDatabase();
$subitemTypes = $databaseConnection->query("SELECT * FROM subitem_unit_type");

handle_request($databaseConnection);

if ($subitemTypes->num_rows == 0) { //* If there are no unit types in the database
     
    echo "<strong> There are no unit types.</strong>";
    
} else { //* If there are unit types in the database

    unit_types_table($databaseConnection, $subitemTypes);
    insert_unit_type_form();

}


?> 
