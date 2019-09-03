<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
print_r($_POST);
//Array ( [mes] => 31/12/2019 [rangoInicio] => 01/01/2018 ) 

if(isset($_POST['periodo'])){
  $rangoInicio = substr($_POST['periodo'],0,4)."-".substr($_POST['periodo'],4,2)."-01";
  $rangoFin = date("Y-m-t", strtotime($rangoInicio));
  //$mes = substr($_POST['periodo']);
  //$anio = substr($_POST['periodo'],0,4);
}



$sqlZarpazo = "SELECT DISTINCT dbo.ordservi.sucursal_e AS sucos, dbo.ordservi.numero as numos, dbo.ordservi.fecha, dbo.ordservi.importe, dbo.ordservi.reten_ib, dbo.impupagf.sucursal_e, dbo.impupagf.pago, dbo.ordservi.observacio, dbo.ordservi.numeinter, dbo.pagos.fecha AS fechaorden, dbo.ordservi.proveedor, dbo.ordservi.fletero , dbo.pagos.detalle, SUBSTRING(dbo.pagos.detalle, CHARINDEX('-',dbo.pagos.detalle)+1, len(dbo.pagos.detalle)) as nombreFletero FROM dbo.ordservi, dbo.detaorse, dbo.impupagf, dbo.pagos where dbo.impupagf.pago=dbo.pagos.numero AND dbo.ordservi.numero=dbo.detaorse.ordenservi and dbo.ordservi.proveedor IN (1, 321) AND dbo.detaorse.tipoadelan in (2, 3, 25, 24) and dbo.ordservi.numero = dbo.impupagf.numero and dbo.ordservi.sucursal_e = dbo.impupagf.sucursal_e and dbo.detaorse.sucursal_e = dbo.impupagf.sucursal_e and dbo.pagos.sucursal = dbo.impupagf.sucursal_e AND dbo.impupagf.fletero = dbo.ordservi.fletero and dbo.pagos.fecha>='$rangoInicio' and dbo.pagos.fecha<='$rangoFin 23:59:59' AND totadelant=dbo.impupagf.importe ORDER BY nombreFletero ASC, detalle Desc, sucos, numos, fechaorden;";

ChromePhp::log($sqlZarpazo);

$stmt = odbc_exec2( $mssql2, $sqlZarpazo, __LINE__, __FILE__);

$tabla = "";$a=$q=$subtotal=0;
while($fila = sqlsrv_fetch_array($stmt)){
  if(!isset($fletero)||$fletero<>$fila['fletero']){
    if(isset($fletero)){
      // no es el primero
      $tabla .= "<tr class='comisionEncabezado info'><td></td><td></td><td></td><td><b>$detalle</b></td><td style='text-align:right'>$ ".sprintf("%01.2f", $subtotal)."</td><td style='text-align:right'></td></tr>";
    } else {
      
    }
    $detalle = explode('-', $fila['detalle']);
    $detalle = $detalle[1];
    $fletero=$fila['fletero'];
    $subtotal = 0;
  }
  $fechaEmision = $fila['fecha']->format('d/m/Y');
  $fechaOrden = $fila['fechaorden']->format('d/m/Y');
  $subtotal += $fila['importe'];
  $sumaOrden = $sumaOrden + $fila['importe'];
  
  $tabla .= "<tr class='viaje'><td>$fechaEmision</td><td>$fechaOrden</td><td>Nº $fila[sucos]-$fila[numos]</td><td>$fila[detalle]</td><td style='text-align:right'>$ ".sprintf("%01.2f", $fila['importe'])."</td><td style='text-align:right'>$ ".sprintf("%01.2f", $fila['reten_ib'])."</td></tr>";
  $a++;
}
$tabla .= "<tr class='comisionEncabezado info'><td></td><td></td><td></td><td><b>$detalle</b></td><td style='text-align:right'>$ ".sprintf("%01.2f", $subtotal)."</td><td style='text-align:right'></td></tr>";
//$sumaOrden = -1*$sumaOrden;
if(isset($sumaOrden)&&$sumaOrden>0&&$a>1){
  // termino orden anterior
  $tabla .= "<tr><td colspan='7' class='alert alert-danger' ><center><strong>Total órdenes descontadas \$ ".number_format($sumaOrden,2,',','.')."</strong></center></td></tr>";
} elseif($a==0) {
  $tabla = "<tr><td colspan='7' class='alert alert-danger'><center><strong>No existen órdenes descontadas en el período seleccionado</strong></center></td></tr>";
}
echo $tabla;
?>
