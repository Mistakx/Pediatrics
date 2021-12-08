<?php 

require_once("custom/php/common.php");

verifyLoginAndCapability("manage_unit_types");

//* User information
$currentUser = wp_get_current_user();
printCurrentUsername($currentUser);
printCurrentUserRoles($currentUser);

//* Database information
$databaseConnection = connectToDatabase();
$subitemTypes = $databaseConnection->query("SELECT * FROM subitem_unit_type");

// echo "\nQuery result:\n";
// print_r($subitemTypes);
echo "\n";

//* If there are no unit types in the database
if ($subitemTypes->num_rows == 0) {
    
    echo "<strong> There are no unit types.</strong>";

} else { //* If there are unit types in the database

    // Table beginning
    echo "<table>";

    // Table header
    echo "<tr>
            <th>id</th>
            <th>unidade</th>
            <th>subitem</th>
        </tr>";

    // For each subitem type table row: kg, cm
    // Subitem type: id, name 
    foreach($subitemTypes->fetch_all() as $subitemType) {

        echo "<tr>"; // Begin table row

        echo "<td>" . $subitemType[0] . "</td>"; // First column: id
        echo "<td>" . $subitemType[1] . "</td>"; // Second column: name

        echo "<td>"; // Third column: nomes subitens que têm respetivo tipo de unidade, aparecendo dentro de parêntesis o nome do item a que pertence esse subitem

        // Query the subitems that have the same subitem type of this foreach loop instance subitem type
        $subitems = $databaseConnection->query("SELECT * FROM subitem WHERE subitem.unit_type_id = " . $subitemType[0]);
        // print_r($subitems);

        // For each subitem table row that has the same subitem type of this foreach loop instance subitem type
        foreach($subitems as $subitem) {
            print_r($subitem);
            echo $subitem["name"] . ", "; // name
        }

        echo "</td>";
        
        echo "</tr>"; // End table row

    }

    // Table ending
    echo "</table>"; 


}



?> 
