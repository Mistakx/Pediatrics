<?php
require_once("custom/php/common.php");
global $link,$current_page;


$current_page = get_site_url().'/'.basename(get_permalink());
$link = connectToDatabase();

//código da capibility "Manage Records"
if (is_user_logged_in() && current_user_can('manage_records')) {

	$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

    if(!isset($_REQUEST['estado'])) {
        $child = "SELECT * FROM child ORDER BY child.name ASC";
        $childResult = mysqli_query($link, $child);
        if (!$childResult) {
            die('Não fez a query:' . mysqli_error());
        } $childNRows= mysqli_num_rows($childResult);
        if ($childNRows>0){
            echo "
        <table>
            <thead>
                <tr>
                    <td><strong>Nome</strong></td>
                    <td><strong>Data de nascimento</strong></td>
                    <td><strong>Enc. de educação</strong></td>
                    <td><strong>Telefone do Enc.</strong></td>
                    <td><strong>e-mail</strong></td>
                    <td><strong>registos</strong></td>
                </tr>
            </thead>
            <tbody>";
            while ($childTuples=mysqli_fetch_assoc($childResult)){ //query for item, we search for the item type of the item using item_type.id, rest of the columns of the table
                $value="SELECT item.name AS itemName,subitem.name AS subitemName,CONCAT('(',value,');') as reg FROM value 
                        JOIN subitem ON subitem.id=value.subitem_id AND child_id={$childTuples['id']} AND value!='' 
                        JOIN item ON subitem.item_id=item.id ORDER BY item.name";
                $valueResult = mysqli_query($link, $value);
                if (!$valueResult) {
                    die('Não fez a query:' . mysqli_error());
                } $valueNRows= mysqli_num_rows($valueResult);
                if ($valueNRows>0){
//                    $valueTuples1=mysqli_fetch_assoc($valueResult);
//                    $itemName=strtoupper($valueTuples1['itemName']);
                    echo"
                                            <tr><td>{$childTuples["name"]}</td>
                                            <td>{$childTuples["birth_date"]}</td>
                                            <td>{$childTuples["tutor_name"]}</td>
                                            <td>{$childTuples["tutor_phone"]}</td>
                                            <td>{$childTuples["tutor_email"]}</td>
                                            <td>";
                    while ($valueTuples=mysqli_fetch_assoc($valueResult)){

                        $itemName=strtoupper($valueTuples['itemName']);
                        echo"{$itemName}:<strong>{$valueTuples['subitemName']}</strong> {$valueTuples['reg']} ";
                    }echo"</td>";
                }
                elseif ($valueNRows==0){
                    echo"
                        <tr><td>{$childTuples["name"]}</td>
                        <td>{$childTuples["birth_date"]}</td>
                        <td>{$childTuples["tutor_name"]}</td>
                        <td>{$childTuples["tutor_phone"]}</td>
                        <td>{$childTuples["tutor_email"]}</td>
                        <td>Não tem valores ainda</td>";
                }echo"</tr>";
            }
        }
        else{
            echo"Não há crianças <br>";
        }echo"</tbody></table>";
        echo " <h3>Dados de registo - introdução</h3> ";

        echo " Introduza os dados pessoais da criança: ";
        echo "<span style=color:red>*campo obrigatório</span><br>";
        echo "<form method='post' action={$current_page}>";
            echo " Nome Completo: <span style=color:red>*</span>";
            echo "<input type='text' name='nome_crianca'>";

            echo " Data de nascimento: <span style=color:red>*</span>";
            echo "<input type='text' name='data_nasc' placeholder=' AAAA-MM-DD'>";

            echo " Nome completo do encarregado de educação: <span style=color:red>*</span>";
            echo "<input type='text' name='nome_tutor'>";

            echo " Telefone do encarregado de educação: <span style=color:red>*</span>";
            echo "<input type='text' name='tutor_telefone'>";

            echo " Endereço de e-mail do tutor: <b><span style=color:darkslategrey>(opcional)</span></b>";
            echo "<input type='text' name='tutor_email'><br><br>";

            echo "<input type='hidden' name='estado' value='validar'>";
            echo "<input type='submit' value='Submeter'>";
        echo "</form>";
    }
    elseif($_REQUEST['estado'] == 'validar') {
        echo ' <h3> Dados de registo - validação</h3> ';
        $error=array();

        echo '<br>';

//verificação do nome

        if (empty($_REQUEST["nome_crianca"])) {
            array_push($error, "O nome da criança é obrigatório");
        }
        else {
            if(preg_match('/^[^A-Z]+|[^a-zA-ZáàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ\s\-\_]+/',"{$_REQUEST['nome_crianca']}")){// ^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ -]+$ comhifen
                array_push($error, "Só letras do alfabeto, espaços brancos e acentos, para o nome da criança");
            }
        }

//verificação da data de nascimento

        if (empty($_REQUEST["data_nasc"])) {
            array_push($error, "O data de nascimento da criança é obrigatório");
        }
        else {
            $bday=$_REQUEST["data_nasc"];
            list($yyyy,$mm,$dd) = explode('-',$bday);
            if (!checkdate($mm,$dd,$yyyy)) {
                array_push($error, "Formato da Data Incorreto:<br> Formato tem de estar na seguinte forma: AAAA-MM-DD");
            }
        }

//verificação do nome do ee

        if (empty($_REQUEST["nome_tutor"])) {
            array_push($error, "O nome do encarregado de educação é obrigatório");
        }
        else {
            if(preg_match('/^[^A-Z]+|[^a-zA-ZáàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ\s\-\_]+/',"{$_REQUEST['nome_tutor']}")){
                array_push($error, "Só letras do alfabeto, espaços brancos e acentos, para o nome do encarregado de educação");
            }
        }

//verificação do telefone do ee

        if (empty($_REQUEST["tutor_telefone"])) {
            array_push($error, "O nª telefone do encarregado de educação é obrigatório");
        }
        else {
            if(!preg_match('/^[0-9]{9}$/',"{$_REQUEST['tutor_telefone']}")){
                array_push($error, "Só 9 digitos para o nª telefone do encarregado de educação");
            }
        }

//verificação do email do tutor
        if (!empty($_REQUEST["tutor_email"])) {
//            $email = test_input($_REQUEST["tutor_email"]);
            $data = trim($_REQUEST["tutor_email"]);// in case there's any whitespace
            $data = stripslashes($data);
            $email = htmlspecialchars($data);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($error, "Formato do email incorreto");
            }
        }

        if(validateInputs($error)){
            echo "Os dados estão prestes a ser inseridos na base de dados. Confirme que os dados estão correctos e pretende submeter?";
            echo"<ul>
                  <li>Nome completo da criança:</li>
                        <ul><li>{$_REQUEST["nome_crianca"]}</li></ul>  
                  <li>Data de nascimento</li>
                        <ul><li>{$_REQUEST["data_nasc"]}</li></ul>
                  <li>Nome completo do encarregado de educação</li>
                        <ul><li>{$_REQUEST["nome_tutor"]}</li></ul>
                  <li>Telefone do encarregado de educação</li><ul><li>{$_REQUEST["tutor_telefone"]}</li></ul>";
                    if (!empty($_REQUEST["tutor_email"])) {
                        echo"<li>Endereço de e-mail do tutor (opcional)</li>
                            <ul><li>{$_REQUEST["tutor_email"]}</li></ul>";
                    }
                echo"</ul> ";
            echo "<form method='post' action={$current_page}>";
            echo "<input type='hidden' name='nome_crianca' value='{$_REQUEST["nome_crianca"]}'>";
            echo "<input type='hidden' name='data_nasc' value={$_REQUEST["data_nasc"]}>";
            echo "<input type='hidden' name='nome_tutor' value={$_REQUEST["nome_tutor"]}>";
            echo "<input type='hidden' name='tutor_telefone' value={$_REQUEST["tutor_telefone"]}>";
            if (!empty($_REQUEST["tutor_email"])) {
                echo "<input type='hidden' name='tutor_email' value='{$_REQUEST["tutor_email"]}>";
            }
            echo "<input type='hidden' name='estado' value='inserir'>";
            echo "<br><input type='submit' value='Submeter'>";

            echo "</form>";

        }

    }
    elseif($_REQUEST['estado'] == 'inserir') {
        echo "<h3> Dados de registos - inserção </h3>";
        $link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
        $name             = $_REQUEST["nome_crianca"];
        $data_nasc           = $_REQUEST["data_nasc"];
        $nome_tutor         = $_REQUEST["nome_tutor"];
        $tutor_telefone = $_REQUEST["tutor_telefone"];
        if (!empty($_REQUEST["tutor_email"])) {
            $tutor_email       = $_REQUEST["tutor_email"];
            $insert = " INSERT INTO `child` (`name`, `birth_date`, `tutor_name`, `tutor_phone`, `tutor_email`) VALUES ('{$name}', '{$data_nasc}', '{$nome_tutor}', '{$tutor_telefone}', '{$tutor_email}')";
        }
        else{
            $insert = " INSERT INTO `child` (`name`, `birth_date`, `tutor_name`, `tutor_phone`) VALUES ('{$name}', '{$data_nasc}', '{$nome_tutor}', '{$tutor_telefone}')";
        }
        $insertResult = mysqli_query($link, $insert);
        //codigo para verificar se deu erro na execução da quer
        if( !$insertResult) {
            echo "Erro na execução da query: " . mysqli_error($link);
            die;
        }else{
            echo"Inseriu os dados de registo com sucesso.<br>";
            echo"Clique em <a href={$current_page}>Continuar</a> para avançar";
        }
    }
}
//Se o utilizador não tem a sessão iniciada esta será a mensagem apresentada
else{
    echo " Não tem autorização para aceder a esta página ";
}