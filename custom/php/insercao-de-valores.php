<?php
require_once("custom/php/common.php");
//global $link,$current_page;
$current_page = get_site_url().'/'.basename(get_permalink());
$link = connectToDatabase();

if(is_user_logged_in() && current_user_can('search')){
    if(!isset($_REQUEST['estado'])){
        echo"<h3>Inserção de valores - criança - procurar</h3>";
        formChild();

    }
    else {
        switch ($_REQUEST['estado']) {
            case 'escolher_crianca':
                form_validationChild();
                break;
            case 'escolher_item':
                $_SESSION['child_id'] =$_REQUEST['crianca'];
                $child_id =$_SESSION['child_id'];

                choose_item();
                break;
            case 'escolher_subitem':
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
                    choose_subitem($item_id);

                }
                break;
            case 'introducao':
                //Store in a session variable called item_id ,item.name and item.item_type_id
    //            $_SESSION['itemid']=$_REQUEST['item'];
                $item_id  =$_SESSION['itemid'];
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

    //                echo "<br>item_id {$item_id}<br>item_name {$item_name}<br>item_type_id{$item_type_id}";//esta funcionar
    //                echo "<br>TEST";
                    if(isset($_REQUEST['subitem_chosen'])){
                        $_SESSION['subitem_chosen']=$_REQUEST['subitem_chosen'];
                        $subitem_chosen  =$_SESSION['subitem_chosen'];
    //                    echo "<br>subitem_chosen {$subitem_chosen}<br>";
                        form_intro($item_id,$item_name,$item_type_id,$subitem_chosen);
                    } else{
                        echo "Não escolheu um subitem para o item {$item_name}<br>";
                        BackButton();
                    }
                }
                break;
            case 'validar':
                $_SESSION['itemid']=$_REQUEST['item'];
                $item_id  =$_SESSION['itemid'];
                $item_name =$_SESSION['itemname'];
                $item_type_id =$_SESSION['itemtypeid'];
                $subitem_chosen =$_SESSION['subitem_chosen'];
                formValidationIntro($item_id,$item_name,$item_type_id,$subitem_chosen);

                break;
            case 'inserir':
                $child_id =$_SESSION['child_id'];
                $item_id  =$_SESSION['itemid'];
                $item_name =$_SESSION['itemname'];
                $item_type_id =$_SESSION['itemtypeid'];
                $subitem_chosen =$_SESSION['subitem_chosen'];
                $sub_descrip=$_REQUEST['subitem_descrip'];
                $date=$_REQUEST['date'];
                $time=$_REQUEST['time'];
                insert($item_name,$child_id,$item_id,$subitem_chosen, $sub_descrip,$date,$time);
                break;
        }
    }
}else{
    echo"Não tem autorização para aceder a esta página";
}
function formChild(){//no state shows form to seachr for child
    global $link,$current_page;
    $current_page = get_site_url().'/'.basename(get_permalink());
    echo"Introduza um dos nomes da criança a encontrar e/ou a data de nascimento dela";
    echo "<form method='post' action={$current_page}>
        <label for='name'>Nome:<br>
        <input type='text' name='nameChild' ><br><br> 
        <input type='text' name='bday'  placeholder=' AAAA-MM-DD'>
        <input type = 'hidden' name = 'estado' value = 'escolher_crianca'>
        <input type = 'submit' name = 'submit' value = 'Inserir item' >
        </form> ";

}
function form_validationChild(){//validates input such as date and name
    $error=array();
    $nome=$_REQUEST['nameChild'];
    $bday=$_REQUEST['bday'];
//    let's hope its correctly or else it gives a warning and allow the user to go back and fix mistake
    if(empty($nome) && empty($bday)){//case 1
        searchChild($_REQUEST['nameChild'],$_REQUEST['bday'],1);
    }
    else {
        if(empty($nome) && !empty($bday)){
//            echo"n tem nome, E data";
            list($yyyy,$mm,$dd) = explode('-',$bday);
            if (!checkdate($mm,$dd,$yyyy)) {
                array_push($error, "Formato da Data Incorreto<br> Formato tem de estar na seguinte forma: AAAA-MM-DD");
            }
            if(validateInputs($error)){
//                echo"searchChild {$nome}, {$bday}";

                searchChild($nome,$bday,2);
            }
        }
        else if(empty($bday) && !empty($nome)){
//            echo"tem nome, n  data";
            if(preg_match('/^[^A-Z]+|[^a-zA-ZáàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ\s\-\_]+/',"{$nome}")){// ^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ -]+$ comhifen
                array_push($error, "Só letras do alfabeto, espaços brancos e acentos, para o nome da criança");
            }
            if(validateInputs($error)){
//                echo"searchChild {$nome}, {$bday}";
                searchChild($_REQUEST['nameChild'],$_REQUEST['bday'],3);
            }
        }
        else {//case 4
            echo "cheio";
            if(preg_match('/^[^A-Z]+|[^a-zA-ZáàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ\s\-\_]+/',"{$nome}")){// ^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ -]+$ comhifen
                array_push($error, "Só letras do alfabeto, espaços brancos e acentos, para o nome da criança");
            }
            list($yyyy,$mm,$dd) = explode('-',$bday);
            if (!checkdate($mm,$dd,$yyyy)) { //echo"verdadeiro<br>{$dd}<br>{$mm}<br>{$yyyy}";
                array_push($error, "Formato da Data Incorreto<br> Formato tem de estar na seguinte forma: AAAA-MM-DD");
            }
            if(validateInputs($error)){
                searchChild($nome,$bday,4);
            }
        }
    }
}
function searchChild($childName,$childBday,$case){ //uses the input given by the user to seach child/if not shows every child
    $link = connectToDatabase();

    switch ($case) {
        case 1:
            //Case user didnt insert anything in the form shows all kids, that kinda invasion of privacy
            $queryEmpty="SELECT `id`,`name`,`birth_date` FROM `child` ORDER BY child.name";
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
            //user only remembered childs bday
            $query2 = "SELECT `id`,`name`,`birth_date` FROM `child` WHERE `birth_date`='{$yyyy}-{$mm}-{$dd}' ORDER BY child.name";
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
            //user only remembered childs name , if correctly shows page else thers no child
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
            //user knows both name and bday
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
                echo"Escolha a criança que pretende indicar : <br>";
                echo "<ul>";
                while ($childTuples4 = mysqli_fetch_assoc($queryResult4)) {
                    echo "<li><a href=insercao-de-valores?estado=escolher_item&crianca={$childTuples4["id"]}>[{$childTuples4["name"]}]</a> ({$childTuples4["birth_date"]})</li>";
                }
                echo "</ul>";
            }
            break;
    }
}
function choose_item(){ //chooses item using a Hyperlink
    $link = connectToDatabase();
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
                    echo"<li><a href=insercao-de-valores?estado=escolher_subitem&item={$itemTuples['id']}>[{$itemTuples['name']}] </a></li>";
                }
                echo"</ul>";
            }
        }
        echo"</ul>";
    }
}
function choose_subitem($item_id){//chooses subitem in a radion form
//    global $link,$current_page;
    $link = connectToDatabase();
    $current_page = get_site_url().'/'.basename(get_permalink());
//    $actio="?estado=validar&item={$item_id}";
//    echo "<form method='post' action={$actio} ";
    echo "<form method='post' action={$current_page}>";
    $item="SELECT item.* FROM item WHERE item.id={$item_id} ";
    $itemResult = mysqli_query($link, $item);
    if(!$itemResult){
        die('Não fez a query:' .mysqli_error() );
    }
    $itemNRows= mysqli_num_rows($itemResult);
    if ($itemNRows==0){
        echo "O item  não tem subitens,logo não aparece formulario<br>";
        BackButton();
    }
    else {
        $itemTuples = mysqli_fetch_assoc($itemResult);
        $subitem="SELECT DISTINCT subitem.* FROM subitem,item WHERE subitem.item_id=item.id AND item.id={$item_id} AND subitem.state='active' ORDER BY form_field_order";
        $subitemResult = mysqli_query($link, $subitem);
        if(!$subitemResult){
            die('Não fez a query:' .mysqli_error() );
        }
        $subitemNRows= mysqli_num_rows($subitemResult);
        if ($subitemNRows==0){
            echo "O item {$itemTuples["name"]}, não tem subitens,logo não aparece formulario<br>";
            BackButton();
        }
        else {
            echo"<ul>";

            echo "<li> {$itemTuples["name"]}</li><ul>";

            while ($subitemTuples = mysqli_fetch_assoc($subitemResult)) {
//                echo "<li><a href=insercao-de-valores?estado=introducao&subitem={$subitemTuples["id"]}>[{$subitemTuples["name"]}]</a> </li>";
                echo "<input type='radio' name='subitem_chosen' value='{$subitemTuples["id"]}'> {$subitemTuples["name"]}<br>";
            }echo"</ul>";
            echo "<br>
                <input type = 'hidden' name = 'estado' value = 'introducao' />
                <input type = 'submit' name = 'submit' value = 'Submeter' />
                </form> ";

        }
    }
}
function form_intro($item_id,$item_name,$item_type_id,$subitem_chosen){ //presents form of subitem chosen
    $link = connectToDatabase();
    echo"<h3>Inserção de valores - {$item_name}</h3>";
    $actio="?estado=validar&item={$item_id}";
    echo "<form method='post' action={$actio} name='item_type_{$item_type_id}_item_{$item_id}'><br>";
    //Makes the query that gets all sub-items (active) associated with the selected item, ordered by the value of the form_field_order attribute
    $subitem="SELECT DISTINCT subitem.* FROM subitem,item WHERE subitem.item_id=item.id AND item.id={$item_id} AND subitem.id={$subitem_chosen} AND subitem.state='active' ORDER BY form_field_order";
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
            else{
                echo"<br>";
            }

            $subitem_all_val = "SELECT DISTINCT subitem_allowed_value.* FROM subitem_allowed_value  WHERE subitem_id={$subitemTuples['id']} ORDER BY value ASC";
            $subitem_all_valResult = mysqli_query($link, $subitem_all_val);
            if(!$subitem_all_valResult){ //shows form if type is such
                die('Não fez a query/não existem subitens com valores permitidos :' .mysqli_error() );
            }
            //$subitem_all_valNRows= mysqli_num_rows($subitem_all_valResult);

            switch ($valueType) {
                case 'text':
                case 'int':
                case 'double':
                    echo "<input type='text' name='{$subitemTuples['form_field_name']}' > <br>";
                    break;
                case 'textbox':
                    echo "<input type='text' size='50' name='{$subitemTuples['form_field_name']}' > <br>";
                    break;
                case 'bool':case 'radio':
                    $subitem_all_valNRows= mysqli_num_rows($subitem_all_valResult);
                    if ($subitem_all_valNRows==0){//  if there's no tuples shows message and sugest the user to go back
                        echo"Nao existem valores permitidos relacionados com {$subitemTuples['name']}<br>";
                        BackButton();
                    }
                    else{
                        while ($subitem_all_valTuple=mysqli_fetch_assoc($subitem_all_valResult)) {
                            echo "<input type='radio' name='{$subitemTuples['form_field_name']}' value='{$subitem_all_valTuple['value']}'> {$subitem_all_valTuple['value']}<br>";
                        }
                    }
                    break;
                case 'checkbox':
                    $subitem_all_valNRows= mysqli_num_rows($subitem_all_valResult);
                    if ($subitem_all_valNRows==0){//  if there's no tuples shows message and sugest the user to go back
                        echo"Nao existem valores permitidos relacionados com {$subitemTuples['name']}<br>";
                        BackButton();
                    }else{
                        while ($subitem_all_valTuple=mysqli_fetch_assoc($subitem_all_valResult)) {
                            echo "<input type='checkbox' name='{$subitemTuples['form_field_name']}'value={$subitem_all_valTuple['value']}>  {$subitem_all_valTuple['value']}<br>";
                        }
                        echo" <br>";
                    }
                    break;
                case 'selectbox':
                    $subitem_all_valNRows= mysqli_num_rows($subitem_all_valResult);
                    if ($subitem_all_valNRows==0){//  if there's no tuples shows message and sugest the user to go back
                        echo"Nao existem valores permitidos relacionados com {$subitemTuples['name']}<br>";
                        BackButton();
                    }else{
                        echo "<select name={$subitemTuples['form_field_name']} >";
                        echo"<option hidden disabled selected value> -- Choose subitem  -- </option>";
                        while ($subitem_all_valTuple=mysqli_fetch_assoc($subitem_all_valResult)) {
                            echo "<option value='{$subitem_all_valTuple['value']}'> {$subitem_all_valTuple['value']}</option><br>";
                        }
                        echo "</select> ";
                        echo" <br>";
                    }
                    break;
            }
        }
        echo "<br>
                <input type = 'hidden' name = 'estado' value = 'validar' />
                <input type = 'submit' name = 'submit' value = 'Submeter' />
                </form> ";
    }
}
function formValidationIntro($item_id,$item_name,$item_type_id,$subitem_chosen){ //validates form from the introduction test
    $link = connectToDatabase();
    echo "<h3>Inserção de valores - {$item_name} - validar</h3>";
//    echo "<h4>Teste funçao- validar</h4>";
    $error_val=array();

    $subitem_val="SELECT subitem.* FROM subitem,item WHERE subitem.item_id=item.id AND item.id={$item_id} AND subitem.id={$subitem_chosen} AND subitem.state='active' ORDER BY form_field_order";
    $subitem_valResult = mysqli_query($link, $subitem_val);
    if(!$subitem_valResult){
        die('Não fez a query:' .mysqli_error() );
    }
    else {
        while ($subitem_valTuples = mysqli_fetch_assoc($subitem_valResult)) {
            //Find a way to get form field name
            echo "<br>";
            $form=mysqli_real_escape_string($link,$_REQUEST[$subitem_valTuples['form_field_name']]);
            if($form==null  && $subitem_valTuples['mandatory']==1){
                //error empty field
                array_push($error_val, "Campo do {$subitem_valTuples['name']} obrigatorio");
            }
            if(($form!=null )){
                switch ($subitem_valTuples['value_type']) {
                    case 'text':
                        if(preg_match('/^[^a-z]+|[^a-zA-ZáàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ\s\-\_]+/',"{$form}")){
                            array_push($error_val, "Só letras do alfabeto,minusculas, espaços brancos e acentos, para o nome do item");
                        }
                        break;
                    case 'int':
                    case 'double':
                        if(preg_match('/\D/',"{$form}")){// \D '/^[1-9][0-9]{0,5}$/',
                            array_push($error_val, "Só numeros");
                        }
                        break;
                    case 'enum':
                        if($subitem_valTuples['form_field_type']=='text' ||$subitem_valTuples['form_field_type']=='textbox' ){
                            if(preg_match('/^[^a-z]+|[^a-zA-ZáàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ\s\-\_]+/',"{$form}")){
                                array_push($error_val, "Só letras do alfabeto,minusculas, espaços brancos e acentos, para o nome do item");
                            }
                        }
                        break;
                }
            }
            /*if(!empty($_REQUEST['enum'])){
                $checkbox="(";
                foreach($_REQUEST['obter_sub'] as $value){
                    $checkbox=append_string ($checkbox, "{$value},");
                }
            }else{
                array_push($error_val, "Vazio");
            }*/


        }
        if(validateInputs($error_val)){
// sends to common function an array of mistakes, and it treats such as: if theres no errors goes to the check data function,else prints the error and displays a back button
            checkData($item_id,$item_name,$subitem_chosen);

        }
    }
}
function checkData($item_id,$item_name,$subitem_chosen){ //shows list of the data chosen e inserts in form
    $link = connectToDatabase();
    echo"Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?<br>";
    echo "<ul>";
    if(isset($_SESSION['child_id'])) {

        echo "<li>Criança: </li>";

        $child="SELECT * FROM child WHERE id={$_SESSION['child_id']}";
        $childResult = mysqli_query($link, $child);
        if(!$childResult){
            die('Não fez a query para obter nome e data de nascimento:' .mysqli_error() );
        }
        else {
            $childTuples = mysqli_fetch_assoc($childResult);
            echo "<ul><li>{$childTuples['name']}</li>";
            echo "<li>{$childTuples['birth_date']}</li></ul>";
            //echo "<li>Your session is running {$_SESSION['child_id']} </li>";
            echo "<li>{$item_name}: </li>";
            $subitem="SELECT subitem.* FROM subitem,item WHERE subitem.item_id=item.id AND item.id={$item_id} AND subitem.id={$subitem_chosen} AND subitem.state='active' ORDER BY form_field_order";
            $subitemResult = mysqli_query($link, $subitem);
            if(!$subitemResult){
                die('Não fez a query:' .mysqli_error() );
            }
            else {
                while($subitemTuples = mysqli_fetch_assoc($subitemResult)) {
                    echo "<ul><li>{$subitemTuples['name']}: </li>";
                    $form=mysqli_real_escape_string($link,$_REQUEST[$subitemTuples['form_field_name']]);
                    echo "<ul><li>{$form}  ";
                    if($subitemTuples['unit_type_id']!=null){
                        $unitName = "SELECT name FROM subitem_unit_type WHERE subitem_unit_type.id={$subitemTuples['unit_type_id']}";
                        $unitNameResult = mysqli_query($link, $unitName);
                        if(!$unitNameResult){//$subitem_all_valResult
                            die('Não fez a query:' .mysqli_error() );
                        }
                        else{
                            $unitTuple=mysqli_fetch_assoc($unitNameResult);
                            echo" {$unitTuple['name']}</li></ul></ul>";
                        }
                    }
                    else{
                        echo" </li></ul></ul>";
                    }
                }
            }
        }
    }echo "</ul>";
    echo "</ul>";
    $actio="?estado=inserir&item={$item_id}";
    echo "<form method='post' action={$actio} <br>";
    $date=date("Y-m-d"); //used to get date
    $time=date("H:i:sa");//used to get time
    echo "<br>
        <input type = 'hidden' name = 'estado' value = 'inserir' />
        <input type='hidden' name='subitem_descrip' value={$form}>
        <input type='hidden' name='date' value={$date}>
        <input type='hidden' name='time' value={$time}>
        <input type = 'submit' name = 'submit' value = 'Submeter' >
        </form> ";//<input type = 'hidden' name = 'estado' value = 'validar' />

}

function insert($item_name,$child_id,$item_id,$subitem_chosen, $sub_descrip,$date,$time){ //inserts and gives 2 options
//    global $link,$current_page;
    $link = connectToDatabase();
    $current_page = get_site_url().'/'.basename(get_permalink());
    echo"<h3>Inserção de valores - {$item_name} - inserção</h3>";
    $currentUser = wp_get_current_user();
    $currentUserUsername = $currentUser->user_login;//used to get user name
    if(preg_match('/am/',"{$time}")){
        list($hours)=(explode("am",$time));
    }elseif (preg_match('/pm/',"{$time}")){
        list($hours)=(explode("pm",$time));
    }
    $insertQuery="INSERT INTO `value` (`child_id`, `subitem_id`, `value`, `date`, `time`, `producer`) VALUES ( '{$child_id}', '{$subitem_chosen}', '{$sub_descrip}', '{$date}', '{$hours}', '{$currentUserUsername}')";
//    echo $insertQuery;
    $insertResult = mysqli_query($link, $insertQuery);
    if(!$insertResult){
        die('Não inseriu o(s) valor(es): insucesso.' .mysqli_error() );
    }
    else{
        echo"Inseriu o(s) valor(es) com sucesso.<br>";
        echo"Clique em <a href={$current_page}> Voltar </a> para voltar ao início da inserção de valores ou em <a href=insercao-de-valores?estado=escolher_item&crianca={$child_id}> Escolher item </a> se quiser continuar a inserir valores associados a esta criança";
    }

}
?>