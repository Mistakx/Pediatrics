<?php
require_once("custom/php/common.php");

//código da capibility "Manage Records"
if (testar_permissão('manage_records')) {

	$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

    if(!isset($_POST['estado'])) {
        echo ' <h3>Dados de registo - introdução</h3> ';

        echo ' Introduza os dados pessoais da criança: ';

        echo '<form action="" method="POST">'; 	
            echo ' Nome Completo: ';
            echo '<input type="text" name="nome_crianca">';

            echo ' Data de nascimento: ';
            echo '<input type="text" name="data_nasc">';

            echo ' Nome completo do encarregado de educação: ';
            echo '<input type="text" name="nome_tutor">';

            echo ' Telefone do encarregado de educação: ';
            echo '<input type="text" name="tutor_telefone">';

            echo ' Endereço de e-mail do tutor: ';
            echo '<input type="text" name="tutor_email">';

            echo '<input type="hidden" name="estado" value="validar">';
            echo '<input type="submit" value="Submeter">';
        echo '</form>';
    }
    elseif($_POST['estado'] == 'validar') {
        echo ' <h3> Dados de registo - validação</h3> ';

        $ERROS_FORMULARIO = 0;

        echo '<br>';

//verificação do nome
        echo ' Nome Completo: ' ;
        if (verificar_string($_POST["nome_crianca"], '')) {
            echo $_POST["nome_crianca"];
        }
        else {
            $ERROS_FORMULARIO = 1;
            echo 'O nome que introduziu não é valido';
        }

        echo '<br>';

//verificação da data de nascimento
        echo ' Data de nascimento: ';
        if (verificar_string($_POST["data_nasc"], '')) {
            $date = explode("-", $_POST["data_nasc"]); 
            if (checkdate($date[1], $date[2], $date[0])) {
                echo $_POST["data_nasc"];
            }
            else {
                echo ' A data de nascimento deve ser válida, com o formato "AAAA-MM-DD". ';
                $ERROS_FORMULARIO = 1;
            }
        }
        else {
            $ERROS_FORMULARIO = 1;
        }

        echo '<br>';

//verificação do nome do ee
        echo ' Nome completo do encarregado de educação: ';
        if (verificar_string($_POST["nome_tutor"], '')) {
            echo $_POST["nome_tutor"];
        }
        else $ERROS_FORMULARIO = 1;
        echo 'O nome que introduziu não é valido';

        echo '<br>';

//verificação do telefone do ee
        echo ' Telefone do encarregado de educação: ';
        if (verificar_string($_POST["tutor_telefone"], '   ')) {
            if(preg_match("/[0-9]/", $_POST["tutor_telefone"])) {
                if(strlen($_POST["tutor_telefone"]) == 9) {
                    echo $_POST["tutor_telefone"];
                }
                else { 
                    echo ' O número de telefone deve conter obrigatoriamente 9 digitos. ';
                    $ERROS_FORMULARIO = 1;
                }
            }
            else { 
                echo ' O número de telefone deve conter apenas números ';
                $ERROS_FORMULARIO = 1;
            }
        }
        else $ERROS_FORMULARIO = 1;

        echo '<br>';

//verificação do email do tutor
        echo ' Endereço e-mail do tutor: ';
        if ($_POST["tutor_email"] != '') {
            if(preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $_POST["tutor_email"])) {
                echo $_POST["tutor_email"];
            }
            else {
                echo ' O endereço email deve ser um endereço válido. ';
                $ERROS_FORMULARIO = 1;
            }
        }
        else echo ' Não inseriu um endereço de email (Opcional) ';

        echo '<br>';

        if ($ERROS_FORMULARIO == 1) {
            echo ' Clique no <a href = "gestao-de-registos"> Voltar </a> para regressar. ';
        }
        else {
        echo 'Os dados estão prestes a ser inseridos na base de dados. Confirme que os dados estão correctos e pretende submeter?';
        echo '<form action="" method="POST">';
            echo '<input type="hidden" name="nome_crianca" value="'.$_POST["nome_crianca"].'">';
            echo '<input type="hidden" name="data_nasc" value="'.$_POST["data_nasc"].'">';
            echo '<input type="hidden" name="nome_tutor" value="'.$_POST["nome_tutor"].'">';
            echo '<input type="hidden" name="tutor_telefone" value="'.$_POST["tutor_telefone"].'">';
            echo '<input type="hidden" name="tutor_email" value="'.$_POST["tutor_email"].'">';
            echo '<input type="hidden" name="estado" value="inserir">';
            echo '<input type="submit" value="Submeter">';
        echo '</form>';
        }
    }
    elseif($_POST['estado'] == 'inserir') {
        echo "<h3> Dados de registos - inserção </h3>";

            $name             = $_POST["nome_crianca"];
            $data_nasc           = $_POST["data_nasc"];
            $nome_tutor         = $_POST["nome_tutor"];
            $tutor_telefone = $_POST["tutor_telefone"];
            $tutor_email       = $_POST["tutor_email"];

//forma mais compacta de inserir valores            
            // $query = "INSERT INTO child VALUES ('$name', '$data_nasc', '$nome_tutor', '$tutor_telefone', '$tutor_email')";
           $query = "INSERT INTO `child` (`id`, `name`, `data_nasc`, `nome_tutor`, `tutor_telefone`, `tutor_email`) VALUES (NULL, '$name', '$data_nasc', '$nome_tutor', '$tutor_telefone', '$tutor_email')";

//codigo para verificar se deu erro na execução da quer
            if( ! mysqli_query($link,$query)) {
                echo "Erro na execução da query: " . mysqli_error($link);
                die;
            }

            echo 	' Os dados de registo foram inseridos com sucesso.<br> ';
            echo	' Clique no <a href = "gestao-de-registos"> Continuar </a> para avançar. ';			
    }
}
//Se o utilizador não tem a sessão iniciada esta será a mensagem apresentada
else echo " Não tem autorização para aceder a esta página ";