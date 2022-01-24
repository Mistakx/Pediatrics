<?php

//! Author: Sérgio Oliveira

require_once("custom/php/common.php");
wp_enqueue_style('ag', get_bloginfo( 'wpurl' ) . '/custom/css/ag.css',false,'1.1','all');

//código da capibility "Manage_subitens"

print_r("DEBUG\n");

function handle_request($databaseConnection) {

    if (array_key_exists("Estado", $_REQUEST) and $_REQUEST['Estado'] == "Inserir") {


    }

    else { //* If the user entered the page as usual, without inserting any subitem

        $items = $databaseConnection->query("SELECT id, name FROM item");

        if ($items->num_rows == 0) { //* If there are no items in the database

            echo "<strong> Não há tipos de unidades.</strong>";

        } else { //* If there are items in the database
            
            subitems_table($databaseConnection); 

        }

    }

}




function subitems_table($databaseConnection) {

    echo "<table>"; //* Table beginning
    
    //* Table header
    echo "<tr> 

            <th> item </th>
            <th> id </th>
            <th> subitem </th>
            <th> tipo de valor </th>
            <th> nome do campo no formulário </th>
            <th> tipo do campo no formulário </th>
            <th> tipo de unidade </th>
            <th> ordem do campo no formulário</th>
            <th> obrigatório </th>
            <th> estado </th>
            <th> ação </th>
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
        $subitems = $databaseConnection->query("SELECT * FROM subitem WHERE subitem.item_id = $item[id]");
        foreach($subitems as $subitem) {

            $subitemRow = "";
            $subitemRowSpan = 0; // Number of allowed values the subitem has

            // Subitem ID
            $subitemRow.="<td rowspan=mistakxSubitemSpan> $subitem[id] </td>";

            // Subitem name
            $subitemRow.="<td rowspan=mistakxSubitemSpan> <a href='?Estado=Introducao&Subitem=$subitem[id]'>$subitem[name]</a> </td>";

            // Value type
            $subitemRow.="<td rowspan=mistakxSubitemSpan> $subitem[value_type] </td>";

            // Form field name
            $subitemRow.="<td rowspan=mistakxSubitemSpan> $subitem[form_field_name] </td>";

            // Form field type
            $subitemRow.="<td rowspan=mistakxSubitemSpan> $subitem[form_field_type] </td>";

            // Unit type ID
            $subitemRow.="<td rowspan=mistakxSubitemSpan> $subitem[unit_type_id] </td>";
    
            // Form field order
            $subitemRow.="<td rowspan=mistakxSubitemSpan> $subitem[form_field_order] </td>";

            // Mandatory
            $subitemRow.="<td rowspan=mistakxSubitemSpan> $subitem[mandatory] </td>";

            // State
            $subitemRow.="<td rowspan=mistakxSubitemSpan> $subitem[state] </td>";

            // Action
            $subitemRow.="<td>";
            $editPageLink = get_bloginfo( 'wpurl' ) . "/edicao-de-dados" . "?Estado=Editar&Tipo=gestao-de-subitens&ID=$subitem[id]";
            $subitemRow.="<a href=" . $editPageLink . ">[editar]</a> <br>";
            if ($subitem["state"] == "active") {
                $deactivatePageLink = get_bloginfo( 'wpurl' ) . "/edicao-de-dados" . "?Estado=Desativar&Tipo=gestao-de-subitens&ID=$subitem[id]";
                $subitemRow.="<a href=" . $deactivatePageLink . ">[desativar]</a> <br>";
            } else {
                $activatePageLink = get_bloginfo( 'wpurl' ) . "/edicao-de-dados" . "?Estado=Ativar&Tipo=gestao-de-subitens&ID=$subitem[id]";
                $subitemRow.="<a href=" . $activatePageLink . ">[ativar]</a> <br>";
            }
            $subitemRow.="</td>";
            

            $subitemRowSpan++;
            $subitemRow.="</tr>";               
            $itemRowSpan = $itemRowSpan + $subitemRowSpan;
            $itemRow.= str_replace("mistakxSubitemSpan", strval($subitemRowSpan),$subitemRow);

        }
    

        
        if ($itemRowSpan == 0) { // If item doesn't have any subitems
            $itemRow.="<td colspan=10>Não há valores predefinidos (item)</td>"; // TODO: Make this more dynamic
            $itemRowSpan++; // The item row span can't be 0 because it bugs itself, so make it 1
        } 
        $itemRow.="</tr>"; 
        echo str_replace("mistakxItemSpan", strval($itemRowSpan),$itemRow);

    }

    echo "</table>"; // Table ending  


}






// function form(){
//     global $current_page;
//     echo "<h3>Gestão de subitens - introdução</h3>";
//     echo "<form method='post' action={$current_page}>
//         <label for='name'>Nome:</label><span style=color:red>*campo obrigatório</span><br>
//         <input type='text' id='itemName' name='itemName' ><br><br>";
//     $subItem="SELECT DISTINCT id,name FROM item_type";
//     $subItemResult = mysqli_query($link, $subItem);
//     echo "
//         <label for='tipo'>Tipo:</label><span style=color:red>*campo obrigatório</span><br>";
//     while($subItemTuples=mysqli_fetch_assoc($subItemResult)){
//          echo"<input type='radio' id={$subItemTuples["id"]} name='itemType' value={$subItemTuples["name"]}>{$subItemTuples["name"]}<br>";
//     }
//     echo"<br>
//         <label>Estado:</label><span style=color:red>*campo obrigatório</span><br>
//         <input type='radio' id='active' name='state' value='active'>ativo<br>
//         <input type='radio' id='inactive' name='state' value='inactive'>inativo<br>
//         <br>
//         <input type = 'hidden' name = 'estado' value = 'inserir' />
//         <input type = 'submit' name = 'submit' value = 'Inserir item' />
//         </form> ";
// }

/*function insert(){

}*/


//* Verify if the user is logged in, and if it has the manage_subitems capability
verifyLoginAndCapability("manage_subitems");

//* User information
$currentUser = wp_get_current_user();
printCurrentUsername($currentUser);
printCurrentUserRoles($currentUser);

echo "<br>";

//* Database information
$databaseConnection = connectToDatabase();
handle_request($databaseConnection);



?>