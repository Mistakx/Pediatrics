<?php
require_once("custom/php/common.php");

global $link,$current_page;
$current_page = get_site_url().'/'.basename(get_permalink());
$link = connectToDatabase();
//$current_page = get_site_url().'/'.basename(get_permalink());
//$link = connectToDatabase();
//$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
//$current_page = get_site_url().'/'.basename(get_permalink());
if(is_user_logged_in() && current_user_can('search')){
//    echo "inside!";
    if(!isset($_REQUEST['estado'])){
        choose_item();
    }
    else {
        switch ($_REQUEST['estado']) {
            case 'escolha':
                //Store in a session variable called item_id ,item.name and item.item_type_id
                echo"<h3>Pesquisa - escolha</h3>";
                $_SESSION['itemid']=$_REQUEST['item'];
                $item_id =$_SESSION['itemid'];
//                echo $item_id;
                $itemnameid="SELECT item.* FROM item WHERE item.id={$item_id}";
                $itemnameidResult = mysqli_query($link, $itemnameid);
                if(!$itemnameidResult){
                    die('Não fez a query:Não é possivel obter o nome do item ' .mysqli_error() );
                }
                else {
                    $itemnameidTuples = mysqli_fetch_assoc($itemnameidResult);
                    $_SESSION['itemname'] = $itemnameidTuples['name'];
                    $item_name = $_SESSION['itemname'];
                    choose($item_id,$item_name);
                }
              break;
            case 'escolher_filtros':

                echo"escolher_filtros";
                $item_id =$_SESSION['itemid'];
                $item_name = $_SESSION['itemname'];
//                $obter=$_REQUEST['obter'];
//                $filtro=$_REQUEST['filtrp'];
                $item_name = $_SESSION['itemname'];
                choose_filter($item_id,$item_name);
                break;
            case 'execucao':

                echo"execucao";
                $item_id =$_SESSION['itemid'];
                $item_name = $_SESSION['itemname'];
//                $obter=$_REQUEST['obter'];
//                $filtro=$_REQUEST['filtrp'];
                $item_name = $_SESSION['itemname'];
                choose_filter($item_id,$item_name);
                break;
        }
    }
}else{
    echo"Não tem autorização para aceder a esta página";
}
function choose_item(){
    global $link;
    echo"<h3>Pesquisa - escolher item</h3>";
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
                    echo"<li><a href=pesquisa?estado=escolha&item={$itemTuples['id']}>[{$itemTuples['name']}]</a></li>";
                }
                echo"</ul>";
            }
        }
        echo"</ul>";
    }
}
function choose($item_id,$item_name){
    global $link,$current_page;
//    echo"<br>chose ";
    echo "<form method='post' action={$current_page}>";
    $querycolname="SELECT GROUP_CONCAT(column_name ORDER BY ordinal_position SEPARATOR ', ') AS columns FROM information_schema.columns WHERE table_name = 'child'";
    $querycolnameResult = mysqli_query($link, $querycolname);
    if(!$querycolnameResult){
        die('Não fez a query:Não é possivel obter o nome colunas' .mysqli_error() );
    }
    else {
        echo "<table>.<tr>
            <th>Atributo</th>
            <th>Obter</th>
            <th>Filtro</th>
            </tr>";

        $columnTuples = mysqli_fetch_assoc($querycolnameResult);
        $columnName=explode(',',$columnTuples['columns']);
        foreach ($columnName as $value) {
            echo "<tr>"; // Begin table row
            echo "<td>{$value}</td>";
            echo "<td><input type='checkbox' name='obter[]' value='{$value}' /></td>";// name=filtros[''] value=$value
            echo "<td><input type='checkbox' name='filtro[]' value='{$value}' /></td></tr>";
        }
        echo "</table>";

    }
    $querysubitens="SELECT DISTINCT subitem.* FROM subitem,item WHERE subitem.item_id=item.id AND item.id={$item_id} AND subitem.state='active' ORDER BY form_field_order";
    $querysubitensResult = mysqli_query($link, $querysubitens);
    if(!$querysubitensResult){
        die('Não fez a query:Não é possivel obter o nome colunas' .mysqli_error() );
    }
    else {
        echo "<table>.<tr>
            <th>Subitem</th>
            <th>Obter</th>
            <th>Filtro</th>
        </tr>";

        while( $subitemTuples = mysqli_fetch_assoc($querysubitensResult)){
            echo "<tr>"; // Begin table row
            echo "<td>{$subitemTuples['name']}</td>";
            echo "<td><input type='checkbox' name='obter_sub[]' value='{$subitemTuples['id']}' /></td>";
            echo "<td><input type='checkbox' name='filtro_sub[]' value='{$subitemTuples['id']}' /></td></tr>";
        }echo "</table>";
    }
    echo "<input type = 'hidden' name = 'estado' value = 'escolher_filtros'>
    <input type = 'submit' name = 'submit' value = 'Inserir' >
    </form> ";


}
function choose_filter($item_id,$item_name){
    global $link,$current_page;
    echo"<br>chose filter afsrhrfm";
    echo"<h3>Pesquisa - escolha</h3>"; //filtro  obter_sub  filtro_sub
    echo "<form method='post' action={$current_page}>";
    echo "<span style=color:red>*campo obrigatório</span><br>";
    echo "<p style=color:DodgerBlue><b>Irá ser realizada uma pesquisa que irá obter, como resultado, uma listagem de, para cada criança com os seguintes dados pessoais escolhidos</b></p><br>";
    echo"<table>";

    $querycolname="SELECT GROUP_CONCAT(column_name ORDER BY ordinal_position SEPARATOR ', ') AS columns FROM information_schema.columns WHERE table_name = 'child'";
    $querycolnameResult = mysqli_query($link, $querycolname);
    if(!$querycolnameResult){
        die('Não fez a query:Não é possivel obter o nome colunas' .mysqli_error() );
    }
    else {
        $columnTuples = mysqli_fetch_assoc($querycolnameResult);
        $columnName=explode(',',$columnTuples['columns']);
        foreach ($columnName as $colname) {
            if(!empty($_REQUEST['obter']) && empty($_REQUEST['filtro'])){
                foreach($_REQUEST['obter'] as $value){
                    if($value==$colname){//case theres 1 in the same row checked
//                        echo "value : {$value}<br/>";
                        echo"<tr><ul><td><li><input type = 'hidden' name = 'obterAtrQ[]' value = '{$value}'>{$value} </li></td>";
                        echo "<td> </td>";
                        echo "<td> </td>";
                    }
                }
            }
            elseif (empty($_REQUEST['obter']) && !empty($_REQUEST['filtro'])){//case theres 1 in the same row checked
                foreach($_REQUEST['filtro'] as $value_filter){
                    if($value_filter==$colname){
                        echo"<tr><ul><td><li><input type = 'hidden' name = 'filterAtrQ[]' value = '{$value_filter}'>{$value_filter} </li></td>";
                        echo "<td> <label>Operador: <span style=color:red>*</span></label> 
                                <select name=operador[]>
                                <option hidden disabled selected value> -- Choose operator  -- </option>
                                <option value=>></option>
                                <option value=>=></option>
                                <option value='='>=</option>
                                <option value='<'><</option>
                                <option value='<='><=</option>
                                <option value='!='>!=</option>
                                <option value='LIKE '>LIKE </option>
                            </select></td>";
                        echo "<td> <label for='filter_child[]'>{$value_filter}:<span style=color:red>*</span></label><br> ";
                        echo "<input type='text' name='filtroval[]' >  </td>";
                    }
                }
            }
            elseif (!empty($_REQUEST['obter']) && !empty($_REQUEST['filtro'])){
                foreach($_REQUEST['obter'] as $value){
                    foreach($_REQUEST['filtro'] as $value_filter){
                        echo"<tr><ul>";
                        if($value==$value_filter && $value==$colname){ //case theres 2 in the same row checked
                            echo"<td><li><input type = 'hidden' name = 'filterAtrQ[]' value = '{$colname}'>{$colname} </li></td>";
                             echo "<td> <label for='ope_child[]'>Operador: <span style=color:red>*</span></label> 
                                <select name=operador[]>
                                <option hidden disabled selected value> -- Choose operator  -- </option>
                                <option value=>></option>
                                <option value=>=></option>
                                <option value='='>=</option>
                                <option value='<'><</option>
                                <option value='<='><=</option>
                                <option value='!='>!=</option>
                                <option value='LIKE '>LIKE </option>
                            </select></td>";
                             echo "<td> <label for='filter_child[]'>{$value_filter}:<span style=color:red>*</span></label><br>";
                             echo "<input type='text' name='filtroval[]' >  </td>";
                         }
                    }

                }
            }
        }
    }

    echo"<tr>";
    echo"</ul></table>";
    echo "<p style=color:DodgerBlue><b>e do item: *{$item_name}* uma listagem dos valores dos subitens</b></p><br>";
    echo"<table>";
    if(!empty($_REQUEST['obter_sub']) && empty($_REQUEST['filtro_sub'])){
        foreach($_REQUEST['obter_sub'] as $obter_sub){
            $querysubitens="SELECT DISTINCT subitem.* FROM subitem,item WHERE subitem.item_id=item.id AND item.id={$item_id} AND subitem.id={$obter_sub} AND subitem.state='active' ORDER BY form_field_order";
            $querysubitensResult = mysqli_query($link, $querysubitens);
            if(!$querysubitensResult){
                die('Não fez a query:Não é possivel obter o nome colunas' .mysqli_error() );
            }
            else {
                $subitemTuples = mysqli_fetch_assoc($querysubitensResult);
            echo"<tr><ul><td><li><input type = 'hidden' name = 'obtersubQ[]' value = '{$subitemTuples['id']}'>{$subitemTuples['name']} </li></td>";
            }
        }
    }
    elseif(empty($_REQUEST['obter_sub']) && !empty($_REQUEST['filtro_sub'])){

        foreach($_REQUEST['filtro_sub'] as $filtro_sub){
            $querysubitens="SELECT DISTINCT subitem.* FROM subitem,item WHERE subitem.item_id=item.id AND item.id={$item_id} AND subitem.id={$filtro_sub} AND subitem.state='active' ORDER BY form_field_order";
            $querysubitensResult = mysqli_query($link, $querysubitens);
            if(!$querysubitensResult){
                die('Não fez a query:Não é possivel obter o nome colunas' .mysqli_error() );
            }
            else {
                $subitemTuples = mysqli_fetch_assoc($querysubitensResult);
                echo"<tr><ul><td><li>{$subitemTuples['name']} </li></td>";
                echo "<td> <label for='filter_child[]'>{$subitemTuples['name']}:<span style=color:red>*</span></label><br> ";
                echo "<input type='text' name='filtroval[]' >  </td>";

            }
        }
    }
    elseif (!empty($_REQUEST['obter_sub']) && !empty($_REQUEST['filtro_sub'])){
        foreach($_REQUEST['obter_sub'] as $obter_sub){
            foreach($_REQUEST['filtro_sub'] as $filtro_sub){
                if($obter_sub==$filtro_sub){
                    $querysubitens="SELECT DISTINCT subitem.* FROM subitem,item WHERE subitem.item_id=item.id AND item.id={$item_id} AND subitem.id={$filtro_sub} AND subitem.state='active' ORDER BY form_field_order";
                    $querysubitensResult = mysqli_query($link, $querysubitens);
                    if(!$querysubitensResult){
                        die('Não fez a query:Não é possivel obter o nome colunas' .mysqli_error() );
                    }
                    else {
                        $subitemTuples = mysqli_fetch_assoc($querysubitensResult);
                        echo"<tr><ul><td><li>{$subitemTuples['name']} </li></td>";
                        echo "<td> <label for='ope_child[]'>Operador:</label>
                             <span style=color:red>*</span><br>
                            <select name=operador_sub[] >
                            <option hidden disabled selected value> -- Choose operator  -- </option>
                            <option value=>></option>
                            <option value=>=></option>
                            <option value='='>=</option>
                            <option value='<'><</option>
                            <option value='<='><=</option>
                            <option value='!='>!=</option>
                            <option value='LIKE '>LIKE </option>
                        </select></td>";
                        echo "<td> <label >{$subitemTuples['name']}:<span style=color:red>*</span></label><br> ";
                        echo "<input type='text' name='filtroval[]' >  </td>";

                    }
                }
            }
        }
    }
    echo"<tr>";
    echo"</ul></table>";
    echo "<input type = 'hidden' name = 'estado' value = 'execucao'>
    <input type = 'submit' name = 'submit' value = 'Inserir' >
    </form> ";
}
?>
