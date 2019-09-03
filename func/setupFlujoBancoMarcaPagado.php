<?php
// setupFlujoBancoMarcaPagado.php
// Recibe un número de cheque 
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

if(isset($_POST['cheque'])&&is_numeric($_POST['cheque'])){
  $sql = "INSERT INTO [coop].[dbo].[flujoBanco] (cheque, conciliado) VALUES ('$_POST[cheque]', '".DATE('Y-m-d')."');";
} elseif(isset($_POST['deposito'])&&is_numeric($_POST['deposito'])){
  $sql = "INSERT INTO [coop].[dbo].[flujoBanco] (deposito, conciliado) VALUES ('$_POST[deposito]', '".DATE('Y-m-d')."');";
} else {
  ChromePhp::log('me muero');die;
}


$stmt = odbc_exec2($mssql2, $sql, __LINE__, __FILE__);
if($stmt){
  echo 'yes';
} else {

  echo "no";
}

?>
