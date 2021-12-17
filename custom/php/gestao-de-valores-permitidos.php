<?php 
// kU3o7LHl8HKq

require_once("custom/php/common.php");

function allowed_values_table($databaseConnection) {

    print_r("DEBUG13");

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
    $itemsNamesAndIDs = $databaseConnection->query("SELECT 
    item.name as itemName, 
    item.id as itemID
    FROM item");
    foreach ($itemsNamesAndIDs as $itemNameAndID) {
        
        $itemRowSpan = 0;
        $itemRow = "";

        $itemRow.="<tr>";

        // Item name
        $itemRow.="<td rowspan=mistakxItemSpan> $itemNameAndID[itemName] </td>";

        //! Query all associated subitem names and IDs
        $subitemsNamesAndIDs = $databaseConnection->query("SELECT 
        subitem.name as subitemName,
        subitem.id as subitemID
        FROM subitem
        WHERE subitem.item_id = $itemNameAndID[itemID]");
        // print_r("SUBITEMS OF $itemNameAndID[itemName]:\n");
        // print_r($subitemsNamesAndIDs);
        foreach($subitemsNamesAndIDs as $subitemNameAndID) {

            $subitemRow = "";
            $subitemRowSpan = 0; // Number of allowed values the subitem has

            if ($subitemRowSpan != 0) {$subitemRow.="<tr>";} // First element continues in the same line, after that it's a new row               

            // Subitem ID
            $subitemRow.="<td rowspan=mistakxSubitemSpan> $subitemNameAndID[subitemID] </td>";

            // Subitem name
            $subitemRow.="<td rowspan=mistakxSubitemSpan> $subitemNameAndID[subitemName] </td>";

            //! Query all associated allowed values and IDs
            $allowedValuesAndIDs = $databaseConnection->query("SELECT 
            subitem_allowed_value.id as allowedValueID, 
            subitem_allowed_value.value as allowedValue, 
            subitem_allowed_value.state as allowedValueState
            FROM subitem_allowed_value 
            WHERE subitem_allowed_value.subitem_id = $subitemNameAndID[subitemID]");
            // print_r("ALLOWED VALUES:\n");
            // print_r($allowedValuesAndIDs);
            foreach($allowedValuesAndIDs as $allowedValueAndID) {

                $allowedValueRow = "";

                if ($subitemRowSpan != 0) {$allowedValueRow.="<tr>";} // First element continues in the same line, after that it's a new row               

                // Allowed value ID
                $allowedValueRow.="<td> $allowedValueAndID[allowedValueID] </td>";

                // Allowed value
                $allowedValueRow.="<td> $allowedValueAndID[allowedValue] </td>";
        
                // Allowed value state
                $allowedValueRow.="<td> $allowedValueAndID[allowedValueState] </td>";

                // Action
                $allowedValueRow.="<td> Edit </td>";
                
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


//* Verify if the user is logged in, and if it has the manage_unit_types capability
verifyLoginAndCapability("manage_allowed_values");

//* User information
$currentUser = wp_get_current_user();
printCurrentUsername($currentUser);
printCurrentUserRoles($currentUser);

echo "<br>";

//* Database information
$databaseConnection = connectToDatabase();
$items = $databaseConnection->query("SELECT * FROM item");

if ($items->num_rows == 0) { //* If there are no subitems in the database
     
    echo "<strong> Não há subitens especificados.</strong>";
    
} else { //* If there are subitems in the database

    allowed_values_table($databaseConnection);
}


?> 
