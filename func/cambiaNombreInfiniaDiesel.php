<?php
// calculaPromedios.php
#include_once("e:\htdocs\ypf\include\inicia.php");

include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));


$sql = "UPDATE dbo.articulos SET descripcion='INFINIA DIESEL' WHERE idarticulo=2068;";
//echo $sql;
$stmt = odbc_exec( $mssql, $sql);
if( $stmt === false ){
     echo "Error in executing query.</br>";
     ( print_r( sqlsrv_errors(), true));
} else {
    echo "Se hizo el query SQL";
}
?>
