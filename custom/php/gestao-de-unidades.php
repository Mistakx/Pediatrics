<?php 

require_once("custom/php/common.php");

verifyLoginAndCapability("manage_unit_types");

//* User information
$currentUser = wp_get_current_user();
printCurrentUsername($currentUser);
printCurrentUserRoles($currentUser);

//* Database information
$databaseConnection = connectToDatabase();
$query = "SELECT * FROM subitem_unit_type";
// $query = "SELECT * FROM child";
$queryResult = $databaseConnection->query($query);

// echo "\nQuery result:\n";
// print_r($queryResult);
echo "\n";

//* If there are no unit types in the database
if ($queryResult->num_rows == 0) {
    
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
    foreach($queryResult->fetch_all() as $queryArray) {

        echo "<tr>";
        
        echo "<td>" . $queryArray[0] . "</td>"; // id
        echo "<td>" . $queryArray[1] . "</td>"; // name

        echo "</tr>";

    }

    // Table ending
    echo "</table>"; 


}



?> 
