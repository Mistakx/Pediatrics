<?php
require_once("custom/php/common.php");
global $link,$current_page;
$insert=false;

$current_page = get_site_url().'/'.basename(get_permalink());
$link = connectToDatabase();

// print_r("DEBUG2");

//if(verifyLoginAndCapability('manage items')){ //problema na capability perguntar docente
if(!isset($_REQUEST['estado'])){
    $item="SELECT * FROM item";
    $itemResult = mysqli_query($link, $item);
    if(!$itemResult){
        die('Não fez a query:' .mysqli_error() );
    }
    else{
        showTable();
        form();
    }
}
else{
    switch ($_REQUEST['estado']){
        case 'inserir':
            form_validation();
            break;

        case 'editar':
//            echo "editar";
            $itemID=$_REQUEST['item'];
//            echo"{$itemID}";
            editar($itemID);
            break;

        case 'ativar':
            $itemID=$_REQUEST['item'];
            $queryActivate="UPDATE item SET state='active' WHERE item.id={$itemID}";
            $activateStateResult = mysqli_query($link, $queryActivate);
            if(!$activateStateResult){
                die('Não fez a query ativar estado:' .mysqli_error() );
            }
            else{
                echo"Atualizou o estado do item com sucesso.<br>";
                echo"Clique em <a href={$current_page}>Continuar</a> para avançar";
            }
            break;

        case 'desativar':
            $itemID=$_REQUEST['item'];
            $queryDeactivate="UPDATE item SET state='inactive' WHERE item.id={$itemID}";
            $deactivateStateResult = mysqli_query($link, $queryDeactivate);
            if(!$deactivateStateResult){
                die('Não fez a query desativar estado:' .mysqli_error() );
            }
            else{
                echo"Atualizou o estado do item com sucesso.<br>";
                echo"Clique em <a href={$current_page}>Continuar</a> para avançar";
            }
            break;
        case 'editUpdate':
            $updateitemNameEdit=$_REQUEST['itemNameEdit'];
            $updateItemTypeName=$_REQUEST['itemTypeName'];
            $updateStateEdit=$_REQUEST['stateEdit'];
            $updateItemID=$_REQUEST['itemEditID'];

            echo"{$updateitemNameEdit}=updateitemNameEdit <br>";
            echo"{$updateItemTypeName}=updateItemTypeName <br>";
            echo"{$updateStateEdit}=updateStateEdit <br>";
            echo"{$updateItemID}=updateItemID <br>";
            echo"editUpdate <br>";


            break;
    }
}
/* if($_REQUEST['estado']=='inserir'){
    form_validation();
}*/

//}
/*else{
    echo "Não tem autorização para aceder a esta página";
    BackButton();
}*/
function showTable(){
    global $link,$current_page;
    $itemType="SELECT id,name FROM item_type";
    $itemTypeResult = mysqli_query($link, $itemType);

    if(!$itemTypeResult){
        die('Não fez a query:' .mysqli_error() );
    }
    $itemTypeNRows= mysqli_num_rows($itemTypeResult);
    if ($itemTypeNRows>0){
        //table cellspacing="2" cellpadding="2" border="1" width="100%"
        echo "
        <table>
            <thead>
                <tr>
                    <td><b>tipo de item</b></td>
                    <td><b>id</b></td>
                    <td><b>nome do item</b></td>
                    <td><b>estado</b></td>
                    <td><b>ação</b></td>
                </tr>
            </thead>
            <tbody>";
        while ($itemTypeTuples=mysqli_fetch_assoc($itemTypeResult)){
            $item="SELECT item.* FROM item,item_type 
                   where item.item_type_id=item_type.id AND item_type.id={$itemTypeTuples["id"]} 
                   ORDER BY item.name ASC";
            $itemResult = mysqli_query($link, $item);
            if(!$itemResult){
                die('Não fez a query:' .mysqli_error() );
            }
            else{
                $itemNRows= mysqli_num_rows($itemResult);
                echo "<tr><td rowspan={$itemNRows}> {$itemTypeTuples["name"]}</td>";
                while($itemTuples=mysqli_fetch_assoc($itemResult)){
                    echo"
                        <td>{$itemTuples["id"]}</td>
                        <td>{$itemTuples["name"]}</td>
                        <td>{$itemTuples["state"]}</td>";
                        
                    if ($itemTuples["state"]=='active'){

                        echo "<td>";

                        $editPageLink = get_bloginfo( 'wpurl' ) . "/edicao-de-dados" . "?Estado=Editar&Tipo=gestao-de-itens&ID=$itemTuples[id]";
                        echo "<a href=" . $editPageLink . ">[editar]</a> <br>";

                        $deactivatePageLink = get_bloginfo( 'wpurl' ) . "/edicao-de-dados" . "?Estado=Desativar&Tipo=gestao-de-itens&ID=$itemTuples[id]";
                        echo "<a href=" . $deactivatePageLink . ">[desativar]</a> <br>";

                        echo "</td></tr>";

                        // echo "<td><a href={$current_page}?Estado=Editar&Tipo=gestao-de-itens&ID={$itemTuples['id']}>[editar]</a>";
                        // echo"<a href={$current_page}?Estado=Desativar&ID={$itemTuples['id']}>[desativar]</a></td></tr>";
                    }
                    else{

                        echo "<td>";


                        $editPageLink = get_bloginfo( 'wpurl' ) . "/edicao-de-dados" . "?Estado=Editar&Tipo=gestao-de-itens&ID=$itemTuples[id]";
                        echo "<a href=" . $editPageLink . ">[editar]</a> <br>";

                        $activatePageLink = get_bloginfo( 'wpurl' ) . "/edicao-de-dados" . "?Estado=Ativar&Tipo=gestao-de-itens&ID=$itemTuples[id]";
                        echo "<a href=" . $activatePageLink . ">[ativar]</a> <br>";

                        echo "</td></tr>";


                        // echo "<td><a href={$current_page}?Estado=Editar&Tipo=gestao-de-itens&ID={$itemTuples['id']}>[editar]</a>";
                        // echo"<a href={$current_page}?Estado=Ativar&ID={$itemTuples['id']}>[ativar]</a></td></tr>";
                    }

                }
            }
        }
        echo"</tbody></table>";
    }
}
function form(){
    global $link,$current_page;
    echo "<h3>Gestão de itens - introdução</h3>";
    echo "<span style=color:red>*campo obrigatório</span><br>";
    echo "<form method='post' action={$current_page}>
        <label for='name'>Nome:</label><span style=color:red>*</span><br>
        <input type='text' id='itemName' name='itemName' ><br><br>";
    $itemType="SELECT DISTINCT id,name FROM item_type";
    $itemTypeResult = mysqli_query($link, $itemType);
    echo "
        <label for='tipo'>Tipo:</label><span style=color:red>*</span><br>";
    while($itemTypeTuples=mysqli_fetch_assoc($itemTypeResult)){
         echo"<input type='radio' id={$itemTypeTuples["id"]} name='itemType' value={$itemTypeTuples["id"]}>{$itemTypeTuples["name"]}<br>";
    }
    echo"<br>
        <label>Estado:</label><span style=color:red>*</span><br>
        <input type='radio' id='active' name='state' value='active'>ativo<br>
        <input type='radio' id='inactive' name='state' value='inactive'>inativo<br>
        <br>
        <input type = 'hidden' name = 'estado' value = 'inserir' />
        <input type = 'submit' name = 'submit' value = 'Inserir item' />
        </form> ";
}

function form_validation(){
    global $itemName,$itemType,$itemState,$insert;
    $error=array();

    if(empty($_REQUEST['itemName'])){
        array_push($error, "O nome é obrigatório");
        /*echo"O nome é obrigatório <br>";
        BackButton();*/
    }
    else{
        $itemName=$_REQUEST['itemName'];
        if(!preg_match("/^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ ]+$/",$itemName)){// /^[A-Za-záàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ ]+$/ ou ^[a-zA-z]*$/ check for hyphen
            array_push($error, "Só letras do alfabeto, espaços brancos e acentos, para o nome do item");
            /*echo"Só letras do alfabeto, espaços brancos e acentos <br>";
            BackButton();*/
        }
    }
    if(empty($_REQUEST['itemType'])){
        array_push($error, "Tipo de item é obrigatório");

        /*echo"Tipo de item é obrigatório <br>";
        BackButton();*/
    }
    else{
        $itemType=$_REQUEST['itemType'];
    }
    if(empty($_REQUEST['state'])){
        array_push($error, "Estado é obrigatório");

        /*echo"Estado é obrigatório <br>";
        BackButton();*/
    }
    else{
        $itemState=$_REQUEST['state'];
    }
    if(validateInputs($error)){
        insert($itemName,$itemType,$itemState);
    }

}

function insert($itemName,$itemType,$itemState){
    global $link,$current_page;
    $insertQuery="INSERT INTO item (name, item_type_id, state) VALUES ('{$itemName}', '{$itemType}', '{$itemState}')";
    $insertResult = mysqli_query($link, $insertQuery);
    if(!$insertResult){
        die('Não fez a query insert:' .mysqli_error() );
    }
    else{
        echo"Inseriu os dados de novo item com sucesso.<br>";
        echo"Clique em <a href={$current_page}>Continuar</a> para avançar";
    }
}

function editar($itemID){
    global $link,$current_page;
    echo "<h3>Gestão de itens - editar item</h3>";
    echo"Dados atuais do item que quer editar:";
    echo "
        <table>
            <thead>
                <tr>
                    <td><b>tipo de item</b></td>
                    <td><b>id</b></td>
                    <td><b>nome do item</b></td>
                    <td><b>estado</b></td>
                </tr>
            </thead>
            <tbody>";

    $queryEdit="SELECT item.*, item_type.name AS itemType FROM item,item_type WHERE item.id='{$itemID}' AND item.item_type_id=item_type.id";
    $editResult = mysqli_query($link, $queryEdit);
    if(!$editResult){
        die('Não fez a query para obter o item original:' .mysqli_error() );
    }
    else{
        $editTuples=mysqli_fetch_assoc($editResult);
        echo "<tr><td>{$editTuples["itemType"]} </td>
                <td>{$editTuples["id"]}</td>
                <td>{$editTuples["name"]}</td>
                <td>{$editTuples["state"]}</td></tr>
            </tbody>
        </table>";
        echo"Escolha o que quer editar:<br>";
        echo "<form method='post' action={$current_page}>
        <input type='checkbox'  name='itemNameEdit' value='itemNameEdit'> Nome do item	<br>
        <input type='checkbox'  name='itemTypeName' value='itemTypeName'>Tipo de item<br>
        <input type='checkbox'  name='stateEdit' value='stateEdit'>Estado<br>
        <input type = 'hidden' name = 'itemEditID' value = {$itemID} />
        <input type = 'hidden' name = 'estado' value = 'editUpdate' />
        <input type = 'submit' name = 'submit' value = 'Editar item' />
        </form> ";
    }
}
?>