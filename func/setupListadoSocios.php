<?php
// setupListadoSocios.php
// lista la tabla asociados, cruza los que puede contra la base de Setup.
// extrae de las bases de datos de Setup aquellos que son socios y muestra la información pertinente
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');



// carga inicial
/*
$sql = "select titular  Collate SQL_Latin1_General_CP1253_CI_AI as titular, nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre, fletero, direccion Collate SQL_Latin1_General_CP1253_CI_AI as direccion, ingrefecha, inhabfecha, telefono, cuit, tipo_resp from fleteros where pidecta=1 order by nombre;";

$stmt = odbc_exec2($mssql2, $sql, __LINE__, __FILE__);
$tabla = "";$a=0;
$sqlInsertaSocio="";
while($fila = sqlsrv_fetch_array($stmt)){
  
  $tm = "INSERT INTO [coop].[dbo].[socios.socios] (razonsocial, nombre, idFletero, domicilio, fechaIngreso, fechaEgreso, celular, cuit, iva, activo) VALUES ('".trim($fila['titular'])."', '".trim($fila['nombre'])."', '".trim($fila['fletero'])."', '".trim($fila['direccion'])."', '".$fila['ingrefecha']->format('Y-m-d')."', '".$fila['inhabfecha']->format('Y-m-d')."', '".trim($fila['telefono'])."', '$fila[cuit]', '$fila[tipo_resp]', 1);";
  $sqlInsertaSocio .= $tm;
  echo "<tr><td colspan=4>$tm</td></tr>";


}

  $stmt2 = odbc_exec2($mssql4, $sqlInsertaSocio, __LINE__, __FILE__);
die;*/







$sqlClientes = "SELECT [idSocio], [razonsocial] ,[nombre]  ,[idFletero]  ,[domicilio]  ,[fechaIngreso]  ,[activo]  ,[fechaEgreso]  ,[celular]  ,[email]  ,[domicilio2]  ,[cuit]  ,[iva] FROM [coop].[dbo].[socios.socios]";
$stmt = odbc_exec2($mssql4, $sqlClientes, __LINE__, __FILE__);
$tabla = "";$a=0;
while($fila = sqlsrv_fetch_array($stmt)){

  $tabla .= "<tr class='".((pow(-1,$a)>0)?'info':'')." comisionEncabezado'><td>$fila[idSocio]</td><td><b><a href='/sociosABM.php?idSocio=$fila[idSocio]'>".strtoupper(trim(utf8_encode($fila['razonsocial'])))."</a></b></td><td>$fila[celular]</td><td>".$fila['fechaIngreso']->format('d/m/Y')."</td><td>".((($fila['fechaEgreso']<>null&&$fila['fechaEgreso']>$fila['fechaIngreso'])||$fila['activo']==0)?"Baja: ".$fila['fechaEgreso']->format('d/m/Y'):'Activo')."</td></tr>";
  $a++;
      
}
if($tabla==""){
  $tabla="<tr><td colspan='5' class='label-info center'>NO HwwqwAY FACTURAS CARGADAS EN ESTE PERIODO</td></tr>";
} elseif(!isset($limit)) {
  @$tablaEncabezado = "<tr class='info comisionEncabezado'><td>$codigosocio - <b>".strtoupper(utf8_encode($socio))."</b></td><td colspan='4' style='text-align:right'><b>";
  $a=0;$totalAFacturar = 0;
  foreach($comision[$idSocio] as $alicuota => $monto){
    $a++;
    $totalAFacturar += $monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100;
    if($a==1){
      $tablaEncabezado .= " {$_SESSION['transporte_tipos_comisiones'][$alicuota]}: $".number_format($monto, 2, ',', '.')." ||  Facturar $".number_format($monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100, 2, ',', '.')."</b>";
      if($alicuota==1||$alicuota==5){
        $tablaEncabezado .= "<br/><b>1% A capitalizar $".number_format($monto*.01, 2, ',', '.')."</b>";
        $totalACapitalizar = $totalACapitalizar + $monto*.01;
      }
      $tablaEncabezado .= "</td></tr>";
    }  else {
      $tablaEncabezado .= "<tr class='info comisionEncabezado'><td></td><td colspan='4' style='text-align:right'><b> {$_SESSION['transporte_tipos_comisiones'][$alicuota]}: $".number_format($monto, 2, ',', '.')." || Facturar $".number_format($monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100, 2, ',', '.')."</b></td></tr>";
    }
    
  }
  if($a>1){
    // totaliza comisiones por fletero
    $tablaEncabezado .= "<tr class='info comisionEncabezado'><td></td><td colspan='4' style='text-align:right'><b> TOTAL A FACTURAR SOCIO: $".number_format($totalAFacturar, 2, ',', '.')."</b></td></tr>";
  }
  $tabla.="$tablaEncabezado<tr class='warning'><td colspan='1'>$cantidadClientes Fleteros, $cantidadFacturas Viajes</td><td colspan='4'><u>$numeroResultados - Total comisiones mensuales</u>:</td></tr>";
  foreach($totalAComisionar as $alicuota => $monto){
    $tabla .= "<tr class='warning'><td colspan='1'></td><td colspan='4'>{$_SESSION['transporte_tipos_comisiones'][$alicuota]} \$".number_format($monto, 2, ',', '.')." <b>\$".number_format($monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100, 2, ',', '.')."</b></td></tr>";
    if($alicuota==1||$alicuota==5){
      $tabla .= "<tr class='warning'><td colspan='1'></td><td colspan='4'>Aportes a capitalizar, sobre \$".number_format($monto, 2, ',', '.')." <b>\$".number_format($totalACapitalizar, 2, ',', '.')." <b></b></td></tr>";
    }
  }
}
echo $tabla;
?>
