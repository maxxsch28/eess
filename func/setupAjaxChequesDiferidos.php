<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_POST);
//Array ( [mes] => 31/12/2019 [rangoInicio] => 01/01/2018 ) 

if(isset($_POST['rangoInicio'])){
  $fin=explode("/", $_POST['rangoInicio']);
  $rangoInicio = "$fin[2]-$fin[1]-$fin[0]";
} else {
  $rangoInicio = date("Y"-01-01);
}
if(isset($_POST['rangoFin'])){
  $fin=explode("/", $_POST['rangoFin']);
  $fechaPago = "$fin[2]-$fin[1]-$fin[0]";
} else {
  $fechaPago = date("Y"-01-01);
}


$sqlCheques = "select salida, vencimien, numeche, detalle_s Collate SQL_Latin1_General_CP1253_CI_AI as detalle_s, importe from dbo.histcomp where ingreso>='$rangoInicio' AND ingreso<='$fechaPago' and vencimien>'$fechaPago' and cuenta=1 ORDER BY salida ASC;";

ChromePhp::log($sqlCheques);


$stmt = odbc_exec2( $mssql2, $sqlCheques, __LINE__, __FILE__);

$tabla = "";$a=$q=0;
while($fila = sqlsrv_fetch_array($stmt)){
  echo "hla";
  ChromePhp::log($fila);
  $fechaEmision = $fila['salida']->format('d/m/Y');
  $fechaPago = $fila['vencimien']->format('d/m/Y');

  $sumaOrden = $sumaOrden + $fila['importe'];
  $tabla .= "<tr id='g$fila[numeche]'><td>$fechaEmision</td><td>$fechaPago</td><td>Nº $fila[numeche]</td><td>$fila[detalle_s]</td><td style='text-align:right'>$ ".sprintf("%01.2f", -1*$fila['importe'])."</td></tr>";
  $a++;
}
$sumaOrden = -1*$sumaOrden;
if(isset($sumaOrden)&&$sumaOrden>0&&$a>1){
  // termino orden anterior
  $tabla .= "<tr><td colspan='5' class='alert alert-danger' ><center><strong>Total cheques diferidos \$ ".number_format($sumaOrden,2,',','.')."</strong></center></td></tr>";
} elseif($a==0) {
  $tabla = "<tr><td colspan='5' class='alert alert-danger'><center><strong>No existen cheques diferidos del rango de fecha seleccionado</strong></center></td></tr>";
}
echo $tabla;
?>
