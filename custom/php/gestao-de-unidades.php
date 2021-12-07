<?php 

require_once("custom/php/common.php");

verifyLoginAndCapability("manage_unit_types");

//* User information
$currentUser = wp_get_current_user();
printCurrentUsername($currentUser);
printCurrentUserRoles($currentUser);

//* Database information
$queryTipos = "SELECT * FROM child";
$mySQL = connectToDatabase();
$tabelaTipos = mysqli_query($mySQL, $queryTipos);


//SE HÁ TIPOS DE ITEM NA BASE DE DADOS:
if (mysqli_num_rows($tabelaTipos) > 0) {
    //CABEÇALHO DA TABELA:
    echo "<table class='tabela'>";
    echo "<tr class='row'><th class='textoTabela cell'>tipo de item</th><th class='textoTabela cell'>id</th><th class='textoTabela cell'>nome do item</th><th class='textoTabela cell'>estado</th><th class='textoTabela cell'>ação</th></tr>";
}

?> 
