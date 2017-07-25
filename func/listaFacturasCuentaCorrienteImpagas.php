<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
 // print_r($_POST);
 // $array=array();
if(isset($_POST['idcliente'])&&is_numeric($_POST['idcliente'])){
  if($_POST['solocomb']=='comb'){$whereSoloComb=" AND dbo.Articulos.Consignado=1";}
  elseif ($_POST['solocomb']=='nocomb') {$whereSoloComb=" AND dbo.Articulos.Consignado=0";}
  else {$whereSoloComb='';}
  
  $fInicio = explode('/', $_POST['rangoInicio']);
  $fFin = explode('/', $_POST['rangoFin']);
  
  $orden = (isset($_POST['ordenArticulo']))?" dbo.MovimientosDetalleFac.IdArticulo":"dbo.MovimientosFac.Fecha";
  
  // borro todos los registros de este cliente
  $sqlClientes = "SELECT dbo.MovimientosFac.IdMovimientoFac, dbo.MovimientosFac.Fecha, dbo.MovimientosFac.IdTipoMovimiento, dbo.MovimientosFac.PuntoVenta, dbo.MovimientosFac.Numero, dbo.movimientosdetalleFac.IdArticulo, Cantidad, Precio, dbo.Articulos.PrecioPublico, (Cantidad*(PrecioPublico-Precio)) as Ajuste, dbo.Articulos.Descripcion FROM dbo.MovimientosDetalleFac, dbo.MovimientosFac, MovimientosCta, dbo.Articulos WHERE dbo.Articulos.idArticulo=dbo.movimientosdetallefac.idArticulo AND dbo.movimientosFac.IdCondicionVenta=2 AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND dbo.MovimientosCta.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND dbo.MovimientosFac.IdCliente=$_POST[idcliente] AND dbo.MovimientosFac.Fecha >= '$fInicio[2]-$fInicio[1]-$fInicio[0]' AND dbo.MovimientosFac.Fecha <= '$fFin[2]-$fFin[1]-$fFin[0]'  AND dbo.movimientoscta.IdMovimientoCta NOT IN (SELECT IdMovimientoImputado FROM dbo.MovimientosCta WHERE IdCliente=$_POST[idcliente] AND IdTipoMovimiento='REC' AND IdMovimientoImputado>0)$whereSoloComb ORDER BY $orden ASC;";
  //ChromePhp::log($sqlClientes);
  $stmt = odbc_exec2( $mssql, $sqlClientes, __LINE__, __FILE__);
  $a=0;$q=0;
  
  //  [0] => Array ( [0] => 498 [IdCliente] => 498 [1] => ADM.DE CAMPOS LA COLINA SA [RazonSocial] => ADM.DE CAMPOS LA COLINA SA [2] => [Identificador] => ) 
  //  [498] => Array ( [0] => 498 [IdCliente] => 498 [1] => ADM.DE CAMPOS LA COLINA SA [RazonSocial] => ADM.DE CAMPOS LA COLINA SA [2] => [Identificador] => )
  //ChromePhp::log($_SESSION['clientesCuentaCorriente'][$_POST['idcliente']]);
  $tabla ="<legend>{$_SESSION['clientesCuentaCorriente'][$_POST['idcliente']]['RazonSocial']}</legend><div style='height:80%;'><table class='table' id='ultimasFacturas'><thead><tr><th>Fecha</th><th>Documento</th><th>Artículos</th><th>Cantidad</th><th>Precio original</th><th>Precio actual</th><th>Ajuste</th><tbody>";
  if($stmt){
  while($fila = sqlsrv_fetch_array($stmt)){
    if(!isset($_SESSION['precioAnterior'][$fila['IdArticulo']])){
      $sqlPrecioAnterior = "SELECT TOP(1) PrecioPublico FROM dbo.CambiosPrecio WHERE IdArticulo=$fila[IdArticulo] AND PrecioPublico<>$fila[PrecioPublico] ORDER BY Fecha DESC";
      $stmt2 = odbc_exec2($mssql, $sqlPrecioAnterior, __LINE__, __FILE__);
      $row2 = sqlsrv_fetch_array($stmt2);
      $_SESSION['precioAnterior'][$fila['IdArticulo']]=$row2['PrecioPublico'];
    }
    $signo = ($fila['IdTipoMovimiento']=='FAA'||$fila['IdTipoMovimiento']=='FAB')?1:-1;
    $precioAnterior = (isset($_POST['precioAnterior']))?$_SESSION['precioAnterior'][$fila['IdArticulo']]:$fila['Precio'];
    
    $ajuste = (isset($_POST['precioAnterior']))?$fila['Cantidad']*($fila['PrecioPublico']-$_SESSION['precioAnterior'][$fila['IdArticulo']]):$fila['Ajuste'];
    $fecha = $fila['Fecha']->format('d/m/Y');
    $tabla .= "<tr id='f$fila[IdMovimientoFac]'".(($signo<0)?" class='bg-danger'":'')."><td>$fecha</td><td>$fila[IdTipoMovimiento] $fila[PuntoVenta]-$fila[Numero]</td><td>$fila[Descripcion]</td><td>".sprintf("%01.2f", $fila['Cantidad'])." lts</td>
    <td>$".sprintf("%01.2f", $precioAnterior)."</td><td>$".sprintf("%01.2f", $fila['PrecioPublico'])."</td><td>$".sprintf("%01.2f", $signo*$ajuste)."</td></tr>";
    $q=$q+$signo*$ajuste;
    $a=$a+$signo*$fila['Cantidad']*$precioAnterior;
  }
  $tabla .= "<tr><td colspan='4'></td><td><b>$".sprintf("%01.2f", $a)."</b></td><td><b>".sprintf("%01.2f", $q/$a*100)."%</b></td><td><b>$".sprintf("%01.2f", $q)."</b></td></tr>";
  } else {
    $tabla ="<tr><td colspan='7' class='bg-success'><b>Este cliente no posee facturas vencidas</td></tr>";
  }
  echo $tabla."</tbody></table></div>";
} else {
  echo $tabla."<tr><td colspan='7' class='bg-danger'><b>Seleccione cliente</td></tr></tbody></table></div>";
}
?>
