<?php

$serverName = "192.168.1.13";
// // echo phpinfo();die;
$connectionOptions4 = array(
    "Database" => "coop",
    "Uid" => "sa",
    "PWD" => "B8000ftq"
);
$mssql4 = sqlsrv_connect($serverName, $connectionOptions4);
if( $mssql4 === false ){
  echo "MSSQL4 - No pudo conectarse:</br>";
  die( print_r( sqlsrv_error().' - '.sqlsrv_errormsg(), true));
}

date_default_timezone_set('America/Argentina/Buenos_Aires');
/* extraeInfoYPF.php
	- obtiene desde la web del BCRA la Tasa Nominal Anual del Banco Nación
*/
$url = "http://www.bcra.gov.ar/BCRAyVos/Plazos_fijos_online.asp";

$myFile = "/tmp/actualizaTNA.log";
$fh = fopen($myFile, 'a') or die("can't open file");
//print_r($_SERVER);
$desdeDonde=(isset($_SERVER['HTTP_HOST']))?' forzado por usuario':' automatica';
$stringData = "\n\n\n".date('d/M/Y H:i:s', time())." Inicia actualizacion$desdeDonde\n";
fwrite($fh, $stringData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_SSLVERSION, 3);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
$listadoOP = curl_exec($ch);
if($listadoOP === FALSE) {
    echo "Me muero.... ";
    die(curl_error($ch));
} else {
    echo "ésitos!";
}
$info = curl_getinfo($ch);
curl_close($ch);

//$listadoOP =  file_get_contents($url);
if(!$listadoOP){fwrite($fh, "Problemas de conectividad\n");}
// empiezo la limpieza
$listadoUtil = substr($listadoOP, stripos($listadoOP, "<table"));
$listadoUtil = substr($listadoUtil, 0, stripos($listadoUtil, "</table"));
// BANCO DE LA NACION ARGENTINA

$listadoUtil = substr($listadoUtil, stripos($listadoUtil, "BANCO DE LA NACION ARGENTINA"));
$listadoUtil = substr($listadoUtil, 0, stripos($listadoUtil, "</tr>"));
//$listadoUtil = str_replace ("</TR>", "<x>", $listadoUtil);

$listadoUtil = strip_tags($listadoUtil);
$listadoUtil = substr($listadoUtil, 0, stripos($listadoUtil, "%")+1);
$listadoUtil = trim(substr($listadoUtil, -10));
$listadoUtil = str_replace(",",".", $listadoUtil);
$tasa = preg_replace('/[^0-9.]/','',$listadoUtil);
echo "\nTNA $tasa%\n";
echo "Fin TNA\n";
// FIN actualizaTNA

// INICIO CER
$url = "http://www.bcra.gob.ar/PublicacionesEstadisticas/Principales_variables_i.asp";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_SSLVERSION, 3);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
$listadoOP = curl_exec($ch);
if($listadoOP === FALSE) {
    echo "Me muero.... ";
    die(curl_error($ch));
} else {
    echo "ésitos!";
}
$info = curl_getinfo($ch);
curl_close($ch);

//$listadoOP =  file_get_contents($url);
if(!$listadoOP){fwrite($fh, "Problemas de conectividad\n");}
// empiezo la limpieza
$listadoUtil = substr($listadoOP, stripos($listadoOP, "<table"));
$listadoUtil = substr($listadoUtil, 0, stripos($listadoUtil, "</table"));
// BANCO DE LA NACION ARGENTINA

$listadoUtil = substr($listadoUtil, stripos($listadoUtil, "(CER)"));
$listadoUtil = substr($listadoUtil, 0, stripos($listadoUtil, "</tr>"));
$listadoUtil = str_replace ("</td>", "|", $listadoUtil);
$array = explode('|', $listadoUtil);
$listadoUtil = strip_tags($array[2]);
// $listadoUtil = substr($listadoUtil, 0, stripos($listadoUtil, "</tr>")+1);
// $listadoUtil = trim(substr($listadoUtil, -10));
$listadoUtil = str_replace(",",".", $listadoUtil);
$cer = preg_replace('/[^0-9.]/','',$listadoUtil);
// echo $listadoUtil;
echo "\nCER $cer%\n";
fclose($fh);
echo "Fin CER\n";






// Grabo datos obtenidos
$sql = "begin tran
   update tasasInteres with (serializable) set tna='$tasa', cer='$cer'
   where fecha='".date('Y-m-d')."'
   if @@rowcount = 0
   begin
      insert into tasasInteres (fecha, tna, cer) values ('".date('Y-m-d')."', '$tasa', '$cer')
   end
commit tran";
echo $sql."\n";
$stmt = odbc_exec2($mssql4, $sql, __LINE__, __FILE__);

/*
$sql = "UPDATE dbo.tasasInteres SET tna='$tasa', cer='1$cer' WHERE fecha='".date("Y-m-d")."';";
//$sql = "UPDATE dbo.tasasInteres SET tna=$tasa WHERE fecha='2020-02-20';";
echo $sql."\n";
$stmt = odbc_exec2($mssql4, $sql, __LINE__, __FILE__);

echo '-'.($stmt).'-';

if(sqlsrv_rows_affected($stmt)>0){
  echo "Actualizó";
} else {
  echo "No :(, inserto entonces\n";
  $sql = "INSERT INTO dbo.tasasInteres (fecha, tna, cer) VALUES ('".date("Y-m-d")."', '$tasa', '$cer');";
  echo $sql."\n";
  //$stmt = odbc_exec2($mssql4, $sql, __LINE__, __FILE__);
}
*/

function odbc_exec2($db, $sql, $linea=__LINE__, $script=__FILE__){
  // realiza el query y muestra error unificado en caso de falla
  $params = array();
  $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
  $stmt = sqlsrv_query($db, $sql, $params, $options);
  if( $stmt === false ){
    $errorSQL = sqlsrv_errors();
    // en formato viejo se usaba la funcion odbc_error()
    if(sqlsrv_errors()[0][0]==23000){
    } elseif(sqlsrv_errors()[0][0]==37000){
      echo "<span class='alert alert-danger'>Error SQL - 37000</span><br/>$sql";
      die();
    } else {
      echo "Error SQL, en $script, linea $linea:<br/><br/>$sql<br/><br/>";print_r(sqlsrv_errors());
      echo "<span class='alert alert-danger'>Error SQL</span>";
      die();
    }
  }
  echo var_dump(sqlsrv_errors());
  echo var_dump(sqlsrv_rows_affected($stmt));
  return sqlsrv_rows_affected($stmt);
}
?>
