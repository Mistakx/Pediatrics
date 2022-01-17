<?php
require_once("custom/php/common.php");
global $link,$current_page;
//$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
//$current_page = get_site_url().'/'.basename(get_permalink());

if(!isset($_REQUEST['estado'])){
    echo"<h3>Inserção de valores - criança - procurar</h3>";
    form();

}
else {
    switch ($_REQUEST['estado']) {
        case 'escolher_crianca':
            form_validationChild();
            break;
        case 'escolher_item':
            echo "escolher_item";
            $child_id =$_REQUEST['crianca'];
            echo "{$child_id}";
            choose_item();
            break;
        case 'introducao':
            echo "introducao";
            //Store in a session variable called item_id ,item.name and item.item_type_id
            $_SESSION['itemid']=$_REQUEST['item'];
            $item_id  =$_SESSION['itemid'];

//            echo "<br>item_id {$item_id}<br>"; //esta funcionar
            $itemnameid="SELECT item.* FROM item WHERE item.id={$item_id}";
            $itemnameidResult = mysqli_query($link, $itemnameid);
            if(!$itemnameidResult){
                die('Não fez a query:Não é possivel obter o nome do item nem o item_type_id' .mysqli_error() );
            }
            else{
                $itemnameidTuples=mysqli_fetch_assoc($itemnameidResult);
                $_SESSION['itemname']=$itemnameidTuples['name'];
                $item_name =$_SESSION['itemname'];
                $_SESSION['itemtypeid']=$itemnameidTuples['item_type_id'];
                $item_type_id =$_SESSION['itemtypeid'];

                echo "<br>item_id {$item_id}<br>item_name {$item_name}<br>item_type_id{$item_type_id}";//esta funcionar
                echo "<br>TEST";
                form_intro($item_id,$item_name,$item_type_id);
            }
            break;
        case 'validar':
            echo "validar";
            $_SESSION['itemid']=$_REQUEST['item'];
            $item_id  =$_SESSION['itemid'];
            $item_name =$_SESSION['itemname'];
            $item_type_id =$_SESSION['itemtypeid'];
//            $form="item_type_{$item_type_id}_id_item_{$item_id}";
//            $formName=$_REQUEST[$form];
//            echo"$formName";

            echo "<h3>Inserção de valores - {$item_name} - validar</h3>";

//            $error=array();

                //echo "<br>item_id- {$item_id}<br>item_name- {$item_name}<br>mandatory-{$mandatory}";
            $subitem_val="SELECT subitem.* FROM subitem,item WHERE subitem.item_id=item.id AND item.id={$item_id} AND subitem.state='active' ORDER BY form_field_order";
            $subitem_valResult = mysqli_query($link, $subitem_val);
            if(!$subitem_valResult){
                die('Não fez a query:' .mysqli_error() );
            }
            else {
                while ($subitem_valTuples = mysqli_fetch_assoc($subitem_valResult)) {
                    //Find a way to get form field name
                    echo "<br>";
                }
            }
            echo "<br>item_id {$item_id}<br>";
            break;
        case 'inserir':
            echo "inserir";

            break;
    }
}
function form(){
    global $link,$current_page;
    echo"Introduza um dos nomes da criança a encontrar e/ou a data de nascimento dela";
    echo "<form method='post' action={$current_page}>
        <label for='name'>Nome:<br>
        <input type='text' id='name' name='nameChild' ><br><br> 
        <input type='text' name='bday'  placeholder=' AAAA-MM-DD'>
        <input type = 'hidden' name = 'estado' value = 'escolher_crianca'>
        <input type = 'submit' name = 'submit' value = 'Inserir item' >
        </form> ";

}
//form_validation()
function form_validationChild(){
    $error=array();
    $nome=$_REQUEST['nameChild'];
    $bday=$_REQUEST['bday'];
    echo"{$nome}<br>{$bday}<br>";
    if(empty($nome) && empty($bday)){//case 1
        searchChild($_REQUEST['name'],$_REQUEST['bday'],1);
        echo"vazio";
    }
    else {
        if(empty($nome) && !empty($bday)){
            echo"n tem nome, E data";
            list($yyyy,$mm,$dd) = explode('-',$bday);
            if (!checkdate($mm,$dd,$yyyy)) { //echo"verdadeiro<br>{$dd}<br>{$mm}<br>{$yyyy}";
                array_push($error, "Formato da Data Incorreto<br> Formato tem de estar na seguinte forma: AAAA-MM-DD");
            }
            if(validateInputs($error)){
                echo"searchChild {$nome}, {$bday}";

                searchChild($nome,$bday,2);
            }
        }
        else if(empty($bday) && !empty($nome)){
            echo"tem nome, n  data";
            if(preg_match('/^[^A-Z]+|[^a-zA-ZáàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ\s\-\_]+/',"{$nome}")){// ^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ -]+$ comhifen
                array_push($error, "Só letras do alfabeto, espaços brancos e acentos, para o nome do item");
            }
            if(validateInputs($error)){
                echo"searchChild {$nome}, {$bday}";
                searchChild($_REQUEST['nameChild'],$_REQUEST['bday'],3);
            }
        }
        else {//case 4
            echo "cheio";
            if(preg_match('/^[^A-Z]+|[^a-zA-ZáàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ\s\-\_]+/',"{$nome}")){// ^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ -]+$ comhifen
                array_push($error, "Só letras do alfabeto, espaços brancos e acentos, para o nome do item");
            }
            list($yyyy,$mm,$dd) = explode('-',$bday);
            if (!checkdate($mm,$dd,$yyyy)) { //echo"verdadeiro<br>{$dd}<br>{$mm}<br>{$yyyy}";
                array_push($error, "Formato da Data Incorreto<br> Formato tem de estar na seguinte forma: AAAA-MM-DD");
            }
            if(validateInputs($error)){
                echo"searchChild {$nome}, {$bday}";

                searchChild($nome,$bday,4);
            }
        }
    }



}
function searchChild($childName,$childBday,$case){
    global $link,$current_page;

    echo "{$childName}<br>,{$childBday}<br>,{$case}";
    switch ($case) {
        case 1:
            echo "1";
            $queryEmpty="SELECT `id`,`name`,`birth_date` FROM `child`";
            $queryEmptyResult = mysqli_query($link, $queryEmpty);
            if(!$queryEmptyResult){
                die('Não fez a query:' .mysqli_error() );
            }
            else{
                echo "<ul>";
                while($childEmptyTuples=mysqli_fetch_assoc($queryEmptyResult)){
                    echo "<li><a href=insercao-de-valores?estado=escolher_item&crianca={$childEmptyTuples["id"]}>[{$childEmptyTuples["name"]}]</a> ({$childEmptyTuples["birth_date"]})</li>";
                }
                echo "</ul>";// NOTWORKING RETIREI {$current_page}";
            }
            break;

        case 2:
            echo "2 <br>";
            list($yyyy, $mm, $dd) = explode('-', $childBday);
            echo"{$yyyy}, {$mm},{$dd} ";
            $query2 = "SELECT `id`,`name`,`birth_date` FROM `child` WHERE `birth_date`='{$yyyy}-{$mm}-{$dd}'";
            $queryResult2 = mysqli_query($link, $query2);
            if (!$queryResult2) {
                die('Não fez a query:' . mysqli_error());
            }
            $query2Rows= mysqli_num_rows($queryResult2);
            if ($query2Rows==0) {
                echo "Nao existem crianças com essa data de nascimento. <br>";
                BackButton();
            }
            else {
                echo "<ul>";
                while ($childTuples = mysqli_fetch_assoc($queryResult2)) {
                    echo "<li><a href=insercao-de-valores?estado=escolher_item&crianca={$childTuples["id"]}>[{$childTuples["name"]}]</a> ({$childTuples["birth_date"]})</li>";
                }
                echo "</ul>";
            }
            break;
        case 3:
            echo "3";
            $query3 = "SELECT `id`,`name`,`birth_date` FROM `child` WHERE `name` LIKE '{$childName}' ";
            $queryResult3 = mysqli_query($link, $query3);
            if (!$queryResult3) {
                die('Não fez a query:' . mysqli_error());
            }
            $query3Rows= mysqli_num_rows($queryResult3);
            if ($query3Rows==0) {
                echo "Nao existem crianças com esse nome. <br>";
                BackButton();
            }
            else {
                echo "<ul>";
                while ($childTuples3 = mysqli_fetch_assoc($queryResult3)) {
                    echo "<li><a href=insercao-de-valores?estado=escolher_item&crianca={$childTuples3["id"]}>[{$childTuples3["name"]}]</a> ({$childTuples3["birth_date"]})</li>";
                }
                echo "</ul>";
            }
            break;
        case 4:
            echo "4";
            list($yyyy, $mm, $dd) = explode('-', $childBday);
            $query4 = "SELECT `id`,`name`,`birth_date` FROM `child` WHERE `name` LIKE '{$childName}' AND `birth_date`='{$yyyy}-{$mm}-{$dd}'";
            $queryResult4 = mysqli_query($link, $query4);
            if (!$queryResult4) {
                die('Não fez a query:' . mysqli_error());
            }
            $query4Rows= mysqli_num_rows($queryResult4);
            if ($query4Rows==0) {
                echo "Nao existem a criança com esses dados. <br>";
                BackButton();
            }
            else {
                echo "<ul>";
                while ($childTuples4 = mysqli_fetch_assoc($queryResult4)) {
                    echo "<li><a href=insercao-de-valores?estado=escolher_item&crianca={$childTuples4["id"]}>[{$childTuples4["name"]}]</a> ({$childTuples4["birth_date"]})</li>";
                }
                echo "</ul>";
            }
            break;
    }
}
function choose_item(){
    global $link;
    $itemType="SELECT id,name FROM item_type";
    $itemTypeResult = mysqli_query($link, $itemType);

    if(!$itemTypeResult){
        die('Não fez a query:' .mysqli_error() );
    }
    $itemTypeNRows= mysqli_num_rows($itemTypeResult);
    if ($itemTypeNRows==0){
        echo "Não existem tipos de item<br>";
    }
    else{
        echo"<ul>";
        while ($itemTypeTuples=mysqli_fetch_assoc($itemTypeResult)){
            echo"<li>{$itemTypeTuples['name']} </li>";
            $item="SELECT item.* FROM item,item_type WHERE item.item_type_id=item_type.id AND item_type.id={$itemTypeTuples["id"]} ORDER BY item.name ASC";
            $itemResult = mysqli_query($link, $item);
            if(!$itemResult){
                die('Não fez a query:' .mysqli_error() );
            }
            $itemNRows= mysqli_num_rows($itemResult);
            if($itemNRows==0){
                echo "Não existem itens para este tipo de item<br>";
            }
            else{
                echo"<ul>";
                while($itemTuples=mysqli_fetch_assoc($itemResult)){
//                    $tuples="{$itemTypeTuples['id']},{$itemTuples['name']},{$itemTuples['id']}";//{$tuples}
                    //itemtype={$itemTypeTuples['id']}&itemname={$itemTuples['name']}&item={$itemTuples['id']}
                    echo"<li><a href=insercao-de-valores?estado=introducao&item={$itemTuples['id']}>{$itemTuples['name']} </a></li>";
                }
                echo"</ul>";
            }
        }
        echo"</ul>";
    }
}

//insercao-de-valores?estado=introducao&item=i
    //        echo "<li><a href=insercao-de-valores?estado=escolher_item&crianca={$childEmptyTuples["id"]}>[{$childEmptyTuples["name"]}]</a> ({$childEmptyTuples["birth_date"]})</li>";
function form_intro($item_id,$item_name,$item_type_id){
    global $link;//?estado=introducao&item=4
    echo"<h3>Inserção de valores - {$item_name}</h3>";
    $actio="?estado=validar&item={$item_id}";
    echo "<form method='post' action={$actio} name='item_type_{$item_type_id}_item_{$item_id}'><br>";
    //Makes the query that gets all sub-items (active) associated with the selected item, ordered by the value of the form_field_order attribute
    $subitem="SELECT subitem.* FROM subitem,item WHERE subitem.item_id=item.id AND item.id={$item_id} AND subitem.state='active' ORDER BY form_field_order";
    $subitemResult = mysqli_query($link, $subitem);
    if(!$subitemResult){
        die('Não fez a query:' .mysqli_error() );
    }
    $subitemNRows= mysqli_num_rows($subitemResult);
    if ($subitemNRows==0){
        echo "O item não tem subitens,logo não aparece formulario<br>";
        BackButton();
    }
    else {

        while ($subitemTuples = mysqli_fetch_assoc($subitemResult)) {
            //if mandatory ,appears a red * ,in front of subitem name, so that the user know its mandatory.This is not a validation
            if($subitemTuples['mandatory']){
                echo "<label for='item_type_{$item_type_id}_item_{$item_id}'>{$subitemTuples['name']}:</label><span style=color:red>*</span>";
            }else{
                echo "<label for='item_type_{$item_type_id}_item_{$item_id}'>{$subitemTuples['name']}:</label>";
            }
            $valueType=$subitemTuples['value_type'];
            if($subitemTuples['value_type']=='enum'){
                $valueType=$subitemTuples['form_field_type'];
            }
//            echo "<label for='item_type_{$item_type_id}_item_{$item_id}'>Tipo de valor:</label>";
            echo "{$subitemTuples['unit_type_id']}";
            if($subitemTuples['unit_type_id']>0) {
                $unit_type_name = "SELECT subitem_unit_type.name FROM subitem_unit_type WHERE subitem_unit_type.id={$subitemTuples['unit_type_id']}";
                $unit_type_nameResult = mysqli_query($link, $unit_type_name);
                if(!$unit_type_nameResult){//$subitem_all_valResult
                    die('Não fez a query:' .mysqli_error() );
                }
                $unit_type_nameNRows= mysqli_num_rows($unit_type_nameResult);
                if($unit_type_nameNRows>0){
                    $unit_type_nameTuple=mysqli_fetch_assoc($unit_type_nameResult);//$subitem_all_valTuple
                    echo" Tipo de unidade: {$unit_type_nameTuple['name']}<br>";
                }
            }
            echo" <br>";
            $subitem_all_val = "SELECT DISTINCT subitem_allowed_value.* FROM subitem_allowed_value  WHERE subitem_id={$subitemTuples['id']} ORDER BY value ASC";
            $subitem_all_valResult = mysqli_query($link, $subitem_all_val);
            if(!$subitem_all_valResult){
                die('Não fez a query/não existem subitens com valores permitidos :' .mysqli_error() );
            }
            $subitem_all_valNRows= mysqli_num_rows($subitem_all_valResult);
            if($subitem_all_valNRows>0){
                switch ($valueType) {
                    case 'text':
                        echo "<input type='text' name={$subitemTuples['form_field_name']}> <br><br> ";
                        break;
                    case 'textbox':
                        echo "<textarea name={$subitemTuples['form_field_name']} cols='60' rows='10'></textarea><br><br> ";
                        break;
                    case 'int':case 'double':
                        echo "<input type='text' name={$subitemTuples['form_field_name']}><br><br> ";
                        break;
                    case 'bool':case 'radio':
                        while ($subitem_all_valTuple=mysqli_fetch_assoc($subitem_all_valResult)) {
                            echo "<input type='radio' name='{$subitemTuples['form_field_name']}' value='{$subitem_all_valTuple['value']}'> {$subitem_all_valTuple['value']}<br>";
                        }
                        echo" <br>";
                        break;
                    case 'checkbox':
                        while ($subitem_all_valTuple=mysqli_fetch_assoc($subitem_all_valResult)) {
                            echo "<input type='checkbox' name='{$subitemTuples['form_field_name']}'value={$subitem_all_valTuple['value']}>  {$subitem_all_valTuple['value']}<br>";
                        }
                        echo" <br>";
                        break;
                    case 'selectbox':
                        echo "<select name={$subitemTuples['form_field_name']} >";

                        while ($subitem_all_valTuple=mysqli_fetch_assoc($subitem_all_valResult)) {
                            echo "<option value='{$subitem_all_valTuple['value']}'> {$subitem_all_valTuple['value']}</option><br>";
                        }
                        echo "</select> ";
                        echo" <br>";
                        break;
                }
            }
            //echo "<label for='form_field_name'>Nome do campo no formulário:</label> {$subitemTuples['form_field_name']} <br>";
            //echo"<input type='text' name='form_field_name' value={$subitemTuples['form_field_name']} >{$subitemTuples['form_field_name']}<br><br>";
        }
        echo "<br>
                <input type = 'hidden' name = 'estado' value = 'validar' />
                <input type = 'submit' name = 'submit' value = 'Submeter' />
                </form> ";
    }
}
?>