<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
//print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';

$desdeQueFechaBusco = date('Y-m-d', strtotime(date('Y-m-01').' -6 months'));
$desdeQueFechaBuscoOrdenes = date('Y-m-d', strtotime(date('Y-m-01').' -7 months'));
$desdeQueFechaBusco = '2018-01-01';
$desdeQueFechaBuscoOrdenes = '2017-12-01';


/* Genero array con histórico de precios de gasoil */
if(!isset($_SESSION['precioGasoil'])){
  $sqlPrecioGasoil = "select fecha, PrecioPublico from dbo.CambiosPrecio where idarticulo=2069 and Fecha>'$desdeQueFechaBusco' ORDER BY Fecha DESC;";
  $stmt = odbc_exec2($mssql, $sqlPrecioGasoil, __LINE__, __FILE__);
  while($fila = sqlsrv_fetch_array($stmt)){
    $_SESSION['precioGasoil'][$fila[0]->format('Y-m-d h:i:s')] = $fila[1];
    if(!isset($_SESSION['precioActual'])){$_SESSION['precioActual']=$fila[1];}
  }
  ChromePhp::log($_SESSION['precioGasoil']);
}





if(!isset($_SESSION['OrSeFact'])){
  unset($_SESSION['OrSeFact']);
  $sqlOrsefact = "SELECT Sucursal_E, OrdenServi FROM OrSeFact WHERE fecha>='$desdeQueFechaBuscoOrdenes'";
  $stmt = odbc_exec2($mssql2, $sqlOrsefact, __LINE__, __FILE__);
  while($fila = sqlsrv_fetch_array($stmt)){
    $_SESSION['OrSeFact'][] = trim($fila[0]).'-'.trim($fila[1]);
    asort($_SESSION['OrSeFact']);
  }
}
ChromePhp::log(count($_SESSION['OrSeFact']));

$soloExternos = (isset($_POST['soloExternos'])&&$_POST['soloExternos']<>0)?" AND tipoviaje=$_POST[soloExternos]":"";
if(isset($_POST['mes'])){
  $periodo = "";
  if(strlen($_POST['mes'])>4){
    $anio = substr($_POST['mes'], 0, 4);
    $mes = substr($_POST['mes'], 5, 2);
    $incluyeMes = "AND datepart(month, Partes.Salida)='$mes'";
  } else {
    $anio = substr($_POST['mes'], 0, 4);
    $incluyeMes ="";
  }

// TODO: Histograma con barritas que representen día por día el volumen total de adelantos y en otra escala vayan mostrando el acumulado en el mismo períoco


  
//   $sqlOrdenes = "SELECT ORDSERVI.sucursal_e, ordservi.numero, ordservi.fletero, ordservi.importe, ordservi.numeinter, FLETEROS.NOMBRE AS nomFletero, Choferes.Nombre as nomChofer, tipoadel.detalle as nomRubro, detaorse.tipoadelan, detaorse.cantidad as cantServ, detaorse.precio_uni,  detaorse.importe as importeDetalle FROM ORDSERVI INNER JOIN detaorse ON ORDSERVI.sucursal_e = detaorse.sucursal_e AND ORDSERVI.numero = detaorse.ordenservi LEFT JOIN Proveedo ON OrdServi.Proveedor = Proveedo.Codigo LEFT JOIN Fleteros ON OrdServi.Fletero   = Fleteros.Fletero LEFT JOIN Choferes ON OrdServi.Chofer    = Choferes.Codigo LEFT JOIN equipos as equipo ON equipo.equipo = ordservi.equipo LEFT JOIN equipos as acoplado ON acoplado.equipo = ordservi.Acoplado INNER JOIN tipoadel ON tipoadel.codigo = detaorse.tipoadelan WHERE ordservi.proveedor=321 AND ordservi.anulada = 0 AND STR(OrdServi.Sucursal_E,4) + STR(OrdServi.Numero,10) NOT IN ( SELECT STR(Sucursal_E,4) + STR(OrdenServi,10) FROM OrSeFact ) AND OrdServi.Fletero > 0 ORDER BY nomFletero ";
  
$sqlOrdenes = "SELECT ORDSERVI.sucursal_e, ordservi.numero, ordservi.fletero, ordservi.importe, ordservi.numeinter Collate SQL_Latin1_General_CP1253_CI_AI AS numerinter, FLETEROS.NOMBRE Collate SQL_Latin1_General_CP1253_CI_AI AS nomFletero,  Choferes.Nombre Collate SQL_Latin1_General_CP1253_CI_AI as nomChofer, tipoadel.detalle as nomRubro, detaorse.tipoadelan, detaorse.precio_uni,  detaorse.importe as importeDetalle, fecha FROM ORDSERVI INNER JOIN detaorse ON ORDSERVI.sucursal_e = detaorse.sucursal_e AND ORDSERVI.numero = detaorse.ordenservi LEFT JOIN Proveedo ON OrdServi.Proveedor = Proveedo.Codigo LEFT JOIN Fleteros ON OrdServi.Fletero   = Fleteros.Fletero LEFT JOIN Choferes ON OrdServi.Chofer    = Choferes.Codigo LEFT JOIN equipos as equipo ON equipo.equipo = ordservi.equipo LEFT JOIN equipos as acoplado ON acoplado.equipo = ordservi.Acoplado INNER JOIN tipoadel ON tipoadel.codigo = detaorse.tipoadelan WHERE ordservi.proveedor=321 AND ordservi.anulada = 0 AND OrdServi.Fletero > 0 AND Fecha>'$desdeQueFechaBusco' ORDER BY nomFletero, fecha asc ";
/* 
1:26 segundos para el query completo.
Tengo que probar si hago un query de SELECT STR(Sucursal_E,4) + STR(OrdenServi,10) FROM OrSeFact y lo paso a un array, luego hago uin query sin esa parte y por PHP elimino los renglones que estén en OrSeFact.

Probar un script que muestre agrupado por fletero cantidad y total de ordenes de servicio impagas, pudiendo verse el detalle.

Probar el mismo script de las dos maneras, tomando el query pesado o probando mi alternativa, decantarse por la mas veloz. Si el query de OrSeFact se pasa a una variable de sesion probablemente sea mas rápido

*/

} 
ChromePhp::log($sqlOrdenes);


function getClosest($search, $arr) {
  // funcion para encontrar la fecha de cambio de precio mas cercana a la fecha original del remito. No es perfecta, pero aproxima.
  $closest = null;
  foreach ($arr as $item => $precio) {
      if ($closest === null || abs(strtotime($search) - strtotime($closest)) > abs(strtotime($item) - strtotime($search))) {
        
        $closest = $item;
      }
  }
  return $closest;
}


$stmt = odbc_exec2($mssql2, $sqlOrdenes, __LINE__, __FILE__);
$tabla = "";
$a = $totalB = $totalA = $cantidadFacturas = $cantidadClientes = $totalPendiente = $salen= 0;
$totalAComisionar = array();
$numeroResultados = sqlsrv_num_rows($stmt);
while($fila = sqlsrv_fetch_array($stmt)){
  $orsefact = trim($fila['sucursal_e']).'-'.trim($fila['numero']);
  if(!in_array($orsefact, $_SESSION['OrSeFact'])){
    if(!isset($idSocio)){
      $idSocio = $fila['fletero'];
      $socio = $fila['nomFletero'];
      $cantidadClientes++;
    }
    if($fila['fletero']<>$idSocio){
      $tablaEncabezado = "<tr class='info comisionEncabezado'><td>$idSocio - <b>".strtoupper(trim(utf8_encode($socio)))."</b></td><td colspan='2' style='text-align:right'><b>TOTAL ADELANTOS FLETERO: $".number_format($totalAFacturar,2,',','.')."</b></td><td></td></tr>";

      $idSocio = $fila['fletero'];
      $socio = $fila['nomFletero'];
      $cantidadClientes++;
      $totalAFacturar=0;
    }
    if(isset($tablaEncabezado)){
      $tabla.=$tablaEncabezado;
      unset($tablaEncabezado);
    }
    //Salida	SalidaHora	Sucursal_E	Parte	Tramo	Origen	nom_Origen	Loc_Origen	ProvOrigen	Destino	Nom_Destin	Loc_Desti	fletero	nomfletero	Kilometros	TipoViaje	TpV_Nombre	APagar_Fle	Pagado_Fle	LiquidarCh	Cumplido	Rendido	Anulado	NomOrigen	NomDestino	ImpVta	Cliente

    $importe = ($fila['importeDetalle']>0)?$fila['importeDetalle']:(($fila['LiquidarCh']>0)?$fila['LiquidarCh']:$fila['ImpVta']);
    

    // calculo de perdida por cambios de precios
    $precioOriginal = getClosest($fila['fecha']->format('Y-m-d h:i:s'), $_SESSION['precioGasoil']);
    $diferenciaPorPrecios = $importe/$_SESSION['precioGasoil'][$precioOriginal]*$_SESSION['precioActual'];
    $diferenciasAcumuladas += $diferenciaPorPrecios-$importe;
    // fin calculo de perdida por cambios de precios
    
    $tabla.= "<tr class='viaje'><td class='no'> $fila[nomChofer]</td><td>".$fila['fecha']->format('d/m/Y')." - $fila[sucursal_e] - $fila[numero], $fila[nomRubro]</td><td style='text-align:right'>$ ".number_format($importe, 2, ',', '.')."</td><td style='text-align:right ' class='no'>".((intval($diferenciaPorPrecios-$importe)<>0)?"$ ".number_format($diferenciaPorPrecios-$importe, 2, ',', '.'):'')."</td></tr>";
    
    $cantidadFacturas++;
    $totalAFacturar += $importe;
    $totalPendiente += $importe;
    $salen++;
  } else {
    //$tabla.= "<tr class='viaje'><td class='no'>PAGADO! $fila[nomChofer]</td><td>$fila[sucursal_e] - $fila[numero], $fila[nomRubro]</td><td style='text-align:right'>$ ".number_format($importe, 2, ',', '.')."</td></tr>";
    $noSalen++;
  }
}
if($tabla==""){
  $tabla="<tr><td colspan='6' class='label-info center'>NO HAY FACTURAS CARGADAS EN ESTE PERIODO</td></tr>";
} elseif(!isset($limit)) {
  @$tablaEncabezado = "<tr class='info comisionEncabezado'><td>$idSocio - <b>".strtoupper(utf8_encode($socio))."</b></td><td colspan='2' style='text-align:right'><b>TOTAL ADELANTOS FLETERO: $".number_format($totalAFacturar, 2, ',', '.')."</b></td><td></td></tr>";
  $tabla.="$tablaEncabezado<tr class='warning'><td colspan='1'>$cantidadClientes fleteros, $cantidadFacturas adelantos</td><td><u>$numeroResultados adelantos entregados, $salen pendientes.</td><td style='text-align:right'><b >TOTAL PENDIENTE: $".number_format($totalPendiente, 2, ',', '.')."</b></u></td><td><b>$".number_format($diferenciasAcumuladas, 2, ',', '.')."</b></td></tr>";

}
echo $tabla;


?>
