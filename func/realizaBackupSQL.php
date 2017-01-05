<?php
// calculaPromedios.php
#include_once("e:\htdocs\ypf\include\inicia.php");

include('../include/inicia.php');

//Database Information
$dbtype = "mysqli"; 
$db_host = "localhost";
$db_user = "coopetrans";
$db_pass = "vGCP6eZ6dqUFZ2pB";
$db_name = "pedidosypf";
$db_port = "3306";
$db_table_prefix = "users_";

//Dbal Support - Thanks phpBB ; )
require_once("e:\htdocs\ypf\classes\mysqli.php");

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    die('MySQL - Error de Conexión ('.$mysqli->connect_errno.') '.$mysqli->connect_error);
}



$sql = file_get_contents('BackupCaldenOil.sql');
//echo $sql;
$stmt = sqlsrv_query( $mssql, $sql);
if( $stmt === false ){
     echo "Error in executing query.</br>";
     ( print_r( sqlsrv_errors(), true));
} else {
    echo "Se hizo el query SQL";
}
set_time_limit(0);
$output = shell_exec("sqlcmd -S Server01\SQLAoniken -Usa -PB8000ftq -iBackupCaldenOil.sql -obackup.txt");
echo $output;


$sqlTurnos = "INSERT INTO ultimo_backup_sql SET fecha=NOW();";
//echo $sqlTurnos;
$result = $mysqli->query($sqlTurnos);
?>