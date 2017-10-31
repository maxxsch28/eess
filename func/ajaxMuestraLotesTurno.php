<?php
// listaStockTanques.php
// muestra y lleva el control de stocks de combustibles.
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_GET);
 // $array=array();
//$_POST['mes']='201411';
if(isset($_GET['idTurno'])&&is_numeric($_GET['idTurno'])){
  // esta revisado?
  $stmprevisado = odbc_exec2($mssql, "SELECT lotesRevisados FROM dbo.Table_1 WHERE IdCierreTurno=$_GET[idTurno]", __LINE__, __FILE__);
  $revisado = sqlsrv_fetch_array($stmprevisado);
  

  // Obtengo lotes cargados en el turno requerido
  $sqlLotesTurno = "select IdLoteTarjetasCredito, dbo.TarjetasCredito.IdTarjeta, Nombre, Loteprefijo, LoteNumero, Importe, IdCuentaContable_presentacion, idAsiento, TipoAcreditacion from dbo.LotesTarjetasCredito, dbo.TarjetasCredito where dbo.LotesTarjetasCredito.IdTarjeta=dbo.TarjetasCredito.IdTarjeta AND Importe<>0 AND IdCierreTurno=$_GET[idTurno] order by LotePrefijo, LoteNumero";

  $stmt = odbc_exec2( $mssql, $sqlLotesTurno, __LINE__, __FILE__);
  
  echo "<form name='modificacionLotesTarjetas' id='modificacionLotesTarjetas'><table class='table table-striped table-bordered IdCierreTurno' id='$_GET[idTurno]'><thead><tr><th width='15%'>idLote / Asiento</th><th>Tarjeta</th><th colspan=2 width='18%'>Prefijo y Lote</th><th>Importe</th><th><span id='marcarRevisado' class='btn btn-xs btn-".(($revisado[0]==1)?"success'>REVISADO":"danger'>NO REVISADO")."</span></th></tr></thead><tbody>";
  $sumaLote = 0;
  while($rowLotesIngresados = sqlsrv_fetch_array($stmt)){
      //print_r($rowLotesIngresados);
      //Array ( [0] => 129889 [IdLoteTarjetasCredito] => 129889 [1] => VISA DEBITO [Nombre] => VISA DEBITO [2] => 2 [Loteprefijo] => 2 [3] => 257 [LoteNumero] => 257 [4] => 1193.5000 [Importe] => 1193.5000 [5] => 714 [idCuentaContable_presentacion] => 714 [6] => 616219 [idAsiento] => 616219 ) 
      if(!isset($lote)){
        $lote = $rowLotesIngresados['LoteNumero'];
        $sumaLote = 0;
      } elseif($lote<>$rowLotesIngresados['LoteNumero']){
        echo "<tr><td colspan='4'>Total lote:</td><td colspan='2' class='text-bg'><b>$ ".sprintf("%.2f",$sumaLote)."</b></td></tr>";
        $sumaLote = 0;
        $lote = $rowLotesIngresados['LoteNumero'];
      }
      $sumaLote += $rowLotesIngresados['Importe'];
      
      if($rowLotesIngresados['TipoAcreditacion']<>0){
        echo "<tr><td>$rowLotesIngresados[IdLoteTarjetasCredito]</td><td>$rowLotesIngresados[Nombre]</td><td>$rowLotesIngresados[Loteprefijo]</td><td>$rowLotesIngresados[LoteNumero]</td><td>".sprintf("%.2f",$rowLotesIngresados['Importe'])."</td><td>$rowLotesIngresados[idAsiento] ($rowLotesIngresados[IdCuentaContable_presentacion])</td><td><span class='label label-success'>Acreditado</span></td></tr>";
      } else {
        $selectorTarjeta = "<select name='selectorTarjeta' id='selectorTarjeta_$rowLotesIngresados[IdLoteTarjetasCredito]'>";
        foreach($_SESSION['tarjetasCredito'] as $idTarjeta => $nombre){
          $tarjetaSantander = (substr($nombre, 0, 2)=='S ')?" class='bg-danger text-danger'":'';
          $selectorTarjeta .= "<option value='$idTarjeta'$tarjetaSantander".(($rowLotesIngresados['IdTarjeta']==$idTarjeta)?' selected':'').">$nombre</option>";
        }
        $selectorTarjeta .= "</select>";
        
        echo "<tr><td>$rowLotesIngresados[IdLoteTarjetasCredito] ($rowLotesIngresados[IdCuentaContable_presentacion])<input type='hidden' name='IdCuentaContable_presentacion' id='IdCuentaContable_presentacion_$rowLotesIngresados[IdLoteTarjetasCredito]' value='$rowLotesIngresados[IdCuentaContable_presentacion]'/><br/>$rowLotesIngresados[idAsiento]</td><td>$selectorTarjeta</td><td>$rowLotesIngresados[Loteprefijo]</td><td><input type='text' name='IdLote' id='LoteNumero_$rowLotesIngresados[IdLoteTarjetasCredito]' value='$rowLotesIngresados[LoteNumero]' class='col-xs-11' pattern='[0-9]*'/></td><td>$ ".sprintf("%.2f",$rowLotesIngresados['Importe'])."</td><td><span class='btn btn-default btn-xs graba' id='actualizaLote_$rowLotesIngresados[IdLoteTarjetasCredito]'>Graba</span></td></tr>";
      }
  }
  if($sumaLote>0){
    echo "<tr><td colspan='4'>Total lote:</td><td><b>$ ".sprintf("%.2f",$sumaLote)."</b></td><td></td></tr>";
  }
  echo "</table>";
} else {
  echo "<table class='table'><tr><td colspan='5'>Nada para ver.</td></tr></table></form>";
}

//echo $tabla;
?>
