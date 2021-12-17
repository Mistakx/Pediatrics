
<?php 
// kU3o7LHl8HKq

require_once("custom/php/common.php");

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

    //* Query all item names and IDs
    $itemsNamesAndIDs = $databaseConnection->query("SELECT 
    item.name as itemName, 
    item.id as itemID
    FROM item");
    foreach ($itemsNamesAndIDs as $itemNameAndID) {
        
        //! Item table information corresponding to this loop iteration's item
        $itemRowSpan = 0;
        $itemRow = "";

        $itemRow.="<tr>";

        // Item name
        $itemRow.="<td rowspan=mistakxItemSpan> $itemNameAndID[itemName] </td>";

        // Item ID
        $itemRow.="<td rowspan=mistakxItemSpan> $itemNameAndID[itemID] </td>";

        //* Query all associated subitem names and IDs
        $subitemsNamesAndIDs = $databaseConnection->query("SELECT 
        subitem.name as subitemName,
        subitem.id as subitemID
        FROM subitem
        WHERE subitem.item_id = $itemNameAndID[itemID]");
        $subitemI = 0;
        print_r("SUBITEMS OF $itemNameAndID[itemName]:\n");
        print_r($subitemsNamesAndIDs);
        foreach($subitemsNamesAndIDs as $subitemNameAndID) {

            //! Subitem table information corresponding to this loop iteration's subitem
            $subitemRow = "";
            $subitemRowSpan = 0;

            if ($subitemI != 0) {$subitemRow.="<tr>";}

            // Subitem name
            $subitemRow.="<td rowspan=mistakxSubitemSpan> $subitemNameAndID[subitemName] </td>";
    
            // Subitem ID
            $subitemRow.="<td rowspan=mistakxSubitemSpan> $subitemNameAndID[subitemID] </td>";

            //* Query all associated allowed values and IDs
            $allowedValuesAndIDs = $databaseConnection->query("SELECT 
            subitem_allowed_value.value as allowedValue, 
            subitem_allowed_value.state as allowedValueState
            FROM subitem_allowed_value 
            WHERE subitem_allowed_value.subitem_id = $subitemNameAndID[subitemID]");
            $allowedValueI = 0;
            // print_r("ALLOWED VALUES:\n");
            // print_r($allowedValuesAndIDs);
            foreach($allowedValuesAndIDs as $allowedValueAndID) {
                $subitemRowSpan++;

                //! Subitem table information corresponding to this loop iteration's subitem
                $allowedValueRow = "";

                if ($allowedValueI != 0) {$allowedValueRow.="<tr>";}                

                // Allowed value
                $allowedValueRow.="<td> $allowedValueAndID[allowedValue] </td>";
        
                // Allowed value state
                $allowedValueRow.="<td> $allowedValueAndID[allowedValueState] </td>";

                // Action
                $allowedValueRow.="<td> Edit </td>";



                if ($allowedValueI != 0) {$allowedValueRow.="</tr>";}
                $subitemRow.=$allowedValueRow;
                $allowedValueI++;
                

            }

            if ($subitemI != 0) {$subitemRow.="</tr>";}
            if ($subitemRowSpan == 0) { $subitemRowSpan++; }
            $itemRowSpan = $itemRowSpan + $subitemRowSpan;
            $itemRow.= str_replace("mistakxSubitemSpan", strval($subitemRowSpan),$subitemRow);
            $subitemRowSpan = 0;
            $subitemRow = "";

        }

        
        $itemRow.="</tr>";
        // str_replace("mistakxItemSpan",$itemRowSpan,$itemRow);
        // echo $itemRow;
        if ($itemRowSpan == 0) { $itemRowSpan++; }
        echo str_replace("mistakxItemSpan", strval($itemRowSpan),$itemRow);
        // print_r($itemRow);
        $itemRowSpan = 0;
        $itemRow = "";

        break;

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
