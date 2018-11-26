<?php
ini_set('mssql.charset', 'UTF-8');
/* Specify the server and connection string attributes. */
$serverName = "192.168.1.13";
$connectionOptions = array(
    "Database" => "CoopDeTrabajo.Net",
    "Uid" => "sa",
    "PWD" => "B8000ftq"
);
$connectionOptions2 = array(
    "Database" => "sqlcoop_dbimplemen",
    "Uid" => "sa",
    "PWD" => "B8000ftq"
);
$connectionOptions3 = array(
    "Database" => "sqlcoop_dbshared",
    "Uid" => "sa",
    "PWD" => "B8000ftq"
);

if (!function_exists('sqlsrv_connect')) {
    echo "sqlsrv_connect functions are not available.<br />\n";
}
$mssql = sqlsrv_connect($serverName, $connectionOptions);
print_r(sqlsrv_server_info($mssql));
if( $mssql === false ){
  echo "MSSQL - No pudo conectarse:</br>";
  die( print_r( sqlsrv_error().' - '.sqlsrv_errormsg(), true));
}

$mssql2 = sqlsrv_connect($serverName, $connectionOptions2);
if( $mssql2 === false ){
  echo "MSSQL2 - No pudo conectarse:</br>";
  die( print_r( sqlsrv_error().' - '.sqlsrv_errormsg(), true));
}

$mssql3 = sqlsrv_connect($serverName, $connectionOptions3);
if( $mssql3 === false ){
  echo "MSSQL3 - No pudo conectarse:</br>";
  die( print_r( sqlsrv_error().' - '.sqlsrv_errormsg(), true));
}



?>