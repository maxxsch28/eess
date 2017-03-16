<?php
// ajaxListaRemitosCliente.php
// muestra los remitos del cliente seleccionado.
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_POST);

//Array ( [rangoInicio] => 01/01/2016 [rangoFin] => 12/31/2016 [idCliente] => 1909 [clientes] => internos [idTipoMovimiento] => REM ) 


if(isset($_POST['idCliente'])&&is_numeric($_POST['idCliente'])){
  if($_POST['idTipoMovimiento']=='REM'){
    $filtroDocumento="'REM'";
  } elseif($_POST['idTipoMovimiento']=='REM'){
    $filtroDocumento="'FAB', 'FAA'";
  } else {
    $filtroDocumento="'FAB', 'FAA', 'REM'";
  }
  // esta revisado?
  
  
  $sqlRemitos = "SELECT IdMovimientoFac, IdTipoMovimiento, PuntoVenta, Numero, Fecha, IdMovimientoCancelado, DocumentoCancelado FROM dbo.MovimientosFac WHERE Fecha>='".fecha($_POST['rangoInicio'], 'sql')."' AND Fecha<='".fecha($_POST['rangoFin'], 'sql')."' AND IdCliente='$_POST[idCliente]' AND IdTipoMovimiento IN ($filtroDocumento) ORDER BY Fecha DESC, IdTipoMovimiento,  PuntoVenta, Numero";
  $stmt = odbc_exec2($mssql, $sqlRemitos, __LINE__, __FILE__);
  
  if($stmt){
    echo "<form name='reasignaRemitos' id='reasignaRemitos' class='reasignaRemitos'><table class='table table-striped table-bordered'><thead><tr><th colspan='4'><select name='nuevoCliente' id='nuevoCliente' title='Mover comprobantes'><option value='' disabled selected>Mover comprobantes al cliente</option>".(($_POST['clientes']=='internos'||isset($_POST['seleccionaTodos']))?($_SESSION['clientesRemitosInternos'].$_SESSION['clientesRemitos']):$_SESSION['clientesRemitos'])."</select></th><th><button type='submit' id='reasigna' name='reasigna'>>></button></th></tr><tr><th width='15%'>Documento</th><th>Numero</th><th>Fecha</th><th></th><th></th></tr></thead><tbody>";
    $sumaLote = 0;
    while($rowRemito = sqlsrv_fetch_array($stmt)){
      //print_r($rowRemito);
      //Array ( [0] => FAA [IdTipoMovimiento] => FAA [1] => 8 [PuntoVenta] => 8 [2] => 73019 [Numero] => 73019 [3] => DateTime Object ( [date] => 2016-01-22 11:27:34 [timezone_type] => 3 [timezone] => America/Argentina/Buenos_Aires ) [Fecha] => DateTime Object ( [date] => 2016-01-22 11:27:34 [timezone_type] => 3 [timezone] => America/Argentina/Buenos_Aires ) [4] => [IdMovimientoCancelado] => [5] => 0 [DocumentoCancelado] => 0 ) 
      $tipoDocumento = ($rowRemito['IdTipoMovimiento']=='FAA'||$rowRemito['IdTipoMovimiento']=='FAB')?"info":"warning";
      if($rowRemito['DocumentoCancelado']<>0){
        // Cancelado, tengo que mostrar este documento y el que lo cancela.
        $rowRemitoCancelado = sqlsrv_fetch_array(odbc_exec2($mssql, "SELECT IdMovimientoFac, IdTipoMovimiento, PuntoVenta, Numero, Fecha, IdMovimientoCancelado, DocumentoCancelado, IdCliente FROM dbo.MovimientosFac WHERE IdMovimientoCancelado='$rowRemito[IdMovimientoFac]' AND IdTipoMovimiento IN ('RDV', 'NCA', 'NCB') ORDER BY IdTipoMovimiento, Fecha, PuntoVenta, Numero"), __LINE__, __FILE__);
        // verifico que el cliente de la anulación coincida con el cliente del documento original
        $errorClienteInconsistente=($rowRemitoCancelado['IdCliente']<>$_POST['idCliente'])?"<span class='bg-danger'>CLIENTE INCONSISTENTE</span>":'';
        
        echo "<tr class='bg-warning'><td class='label-$tipoDocumento'>$rowRemito[IdTipoMovimiento]</td><td><b>$rowRemito[PuntoVenta]-$rowRemito[Numero]</b></td><td>".date_format($rowRemito['Fecha'], "d/m/Y")."</td><td rowspan=2>CANCELADO$errorClienteInconsistente</td><td rowspan='2'><input type='checkbox' value='$rowRemito[IdMovimientoFac]' name='idMovimientoFac[]' class='idMovimientoFac'></td></tr>"
          ."<tr class='bg-warning'><td>$rowRemitoCancelado[IdTipoMovimiento]</td><td><b>$rowRemitoCancelado[PuntoVenta]-$rowRemitoCancelado[Numero]</b></td><td>".date_format($rowRemitoCancelado['Fecha'], "d/m/Y")."</td></tr>";
      } else {
        echo "<tr class='trSelect' id='tr_$rowRemito[IdMovimientoFac]'><td class='label-$tipoDocumento'>$rowRemito[IdTipoMovimiento]</td><td><b>$rowRemito[PuntoVenta]-$rowRemito[Numero]</b></td><td>".date_format($rowRemito['Fecha'], "d/m/Y")."</td><td></td><td><input type='checkbox' id='id_$rowRemito[IdMovimientoFac]' value='$rowRemito[IdMovimientoFac]' name='idMovimientoFac[]' class='idMovimientoFac'></td></tr>";
      }
    }
    echo "</tbody></table></form>";
  } else {
    echo "<table class='table'><tr><td colspan='4' class='bg-success'>No hay remitos con este criterio de búsqueda.</td></tr></table>";
  }
} else {
  echo "<table class='table'><tr><td colspan='4'>Nada para ver.</td></tr></table>";
}

//echo $tabla;
?>
