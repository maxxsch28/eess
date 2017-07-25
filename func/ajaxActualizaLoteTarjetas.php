<?php
// ajaxActualizaLoteTarjetas.php
// recibe IdLoteTarjetasCredito con nuevo tipo de tarjeta y número de lote. Chequea si cambio, si lo hizo modifica en la tabla de lotes
// 
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));


// tengo que asegurarme que el lote no esté acreditado. No podría ni listar los lotes acreditados o mostarlos grisados.
// compruebo datos recibidos
// IdLoteTarjetasCredito	120632 
// IdTarjeta	10 
// LoteNumero	101  
// IdCuentaContable_presentacion  
// IdCierreTurno

//ChromePhp::log($_GET);
if(isset($_GET['IdLoteTarjetasCredito'])&&isset($_GET['IdTarjeta'])&&isset($_GET['LoteNumero'])&&isset($_GET['IdCuentaContable_presentacion'])&&is_numeric($_GET['IdLoteTarjetasCredito'])&&is_numeric($_GET['IdTarjeta'])&&is_numeric($_GET['LoteNumero'])&&is_numeric($_GET['IdCuentaContable_presentacion'])){
  $sqlLoteOriginal = "SELECT * FROM dbo.LotesTarjetasCredito WHERE IdLoteTarjetasCredito=$_GET[IdLoteTarjetasCredito]";
  $stmt = odbc_exec2($mssql, $sqlLoteOriginal, __LINE__, __FILE__);
  $loteOriginal = sqlsrv_fetch_array($stmt);
  if(is_array($loteOriginal)){
    $sqlAsientos = array();
    if($loteOriginal['IdTarjeta']<>$_GET['IdTarjeta']){
      // cambio la tarjeta
      $sqlAsientos[] = "UPDATE dbo.asientos SET concepto='PRESENTACION {$_SESSION['tarjetasCredito'][$_GET['IdTarjeta']]}' WHERE IdAsiento=$loteOriginal[IdAsiento]";
      $sqlAsientos[] = "UPDATE dbo.asientosDetalle SET detalle='PRESENTACION {$_SESSION['tarjetasCredito'][$_GET['IdTarjeta']]}' WHERE IdAsiento=$loteOriginal[IdAsiento]";
      $sqlAsientos[] = "UPDATE dbo.LotesTarjetasCredito SET IdTarjeta=$_GET[IdTarjeta] WHERE IdLoteTarjetasCredito=$_GET[IdLoteTarjetasCredito]";
    }
    if($_SESSION['tarjetasCreditoCuenta'][$loteOriginal['IdTarjeta']]<>$_SESSION['tarjetasCreditoCuenta'][$_GET['IdTarjeta']]){
      // cambio la cuenta Contable de presentacion
      $sqlAsientos[] = "UPDATE dbo.asientosDetalle SET IdCuentaContable={$_SESSION['tarjetasCreditoCuenta'][$_GET['IdTarjeta']]} WHERE IdAsiento=$loteOriginal[IdAsiento] AND IdCuentaContable=$_GET[IdCuentaContable_presentacion]";
      $sqlAsientos[] = "UPDATE dbo.LotesTarjetasCredito SET IdCuentaContable_presentacion={$_SESSION['tarjetasCreditoCuenta'][$_GET['IdTarjeta']]} WHERE IdLoteTarjetasCredito=$_GET[IdLoteTarjetasCredito] AND IdCuentaContable_presentacion=$_GET[IdCuentaContable_presentacion]";
    } else {
      echo "original: {$_SESSION['tarjetasCreditoCuenta'][$loteOriginal['IdTarjeta']]} cambiar a $_GET[IdCuentaContable_presentacion]";
    }
    if($loteOriginal['LoteNumero']<>$_GET['LoteNumero']){
      // cambio numero de lote
      $sqlAsientos[] = "UPDATE dbo.LotesTarjetasCredito SET LoteNumero=$_GET[LoteNumero] WHERE IdLoteTarjetasCredito=$_GET[IdLoteTarjetasCredito]";
    }
    if(count($sqlAsientos)>0){
      ChromePhp::log($sqlAsientos);
      foreach($sqlAsientos as $sql){
        $stmt = odbc_exec2($mssql, $sql, __LINE__, __FILE__);
        $stmt = odbc_exec2($mssql, "UPDATE dbo.LotesTarjetasCredito SET SinCierreDeLote=1 WHERE IdCierreTurno=$_GET[IdCierreTurno] AND Importe=0 and IdAsiento is NULL AND SinCierreDeLote=0", __LINE__, __FILE__);
        //echo "UPDATE dbo.LotesTarjetasCredito SET SinCierreDeLote=1 WHERE IdCierreTurno=$_GET[IdCierreTurno] AND Importe=0 and IdAsiento is NULL AND SinCierreDeLote=0";
      }
      echo "1";
    } else {
      echo "0";
      die;
    }
  } else {
    // Por alguna razón no existe el lote buscado
    echo "1";
    die;
  }
} else {
  echo "1";
   die;
}




ChromePhp::log($_GET);
die;
//print_r($_GET);
 // $array=array();
//$_POST['mes']='201411';
if(isset($_GET['idTurno'])&&is_numeric($_GET['idTurno'])){
  // Obtengo lotes cargados en el turno requerido
  $sqlLotesTurno = "select IdLoteTarjetasCredito, dbo.TarjetasCredito.IdTarjeta, Nombre, Loteprefijo, LoteNumero, Importe, idCuentaContable_presentacion, idAsiento from dbo.LotesTarjetasCredito, dbo.TarjetasCredito where dbo.LotesTarjetasCredito.IdTarjeta=dbo.TarjetasCredito.IdTarjeta AND Importe<>0 AND IdCierreTurno=$_GET[idTurno] order by LotePrefijo, LoteNumero";

  $stmt = odbc_exec( $mssql, $sqlLotesTurno);
  if( $stmt === false ){
      echo "1. Error in executing query.</br>$sqlLotesTurno<br/>";
      die( print_r( sqlsrv_errors(), true));
  }

  echo "<form name='modificacionLotesTarjetas' id='modificacionLotesTarjetas'><table class='table table-striped table-bordered'><thead><tr><th width='5%'>idLote</th><th>Tarjeta</th><th colspan=2>Prefijo y Lote</th><th>Importe</th><th>Asiento</th><th></th></tr></thead><tbody><tr>";
  
  while($rowLotesIngresados = sqlsrv_fetch_array($stmt)){
      //print_r($rowLotesIngresados);
      //Array ( [0] => 129889 [IdLoteTarjetasCredito] => 129889 [1] => VISA DEBITO [Nombre] => VISA DEBITO [2] => 2 [Loteprefijo] => 2 [3] => 257 [LoteNumero] => 257 [4] => 1193.5000 [Importe] => 1193.5000 [5] => 714 [idCuentaContable_presentacion] => 714 [6] => 616219 [idAsiento] => 616219 ) 
      $selectorTarjeta = "<select name='selectorTarjeta' id='selectorTarjeta_$rowLotesIngresados[IdLoteTarjetasCredito]'>";
      foreach($_SESSION['tarjetasCredito'] as $idTarjeta => $nombre){
        $selectorTarjeta .= "<option value='$idTarjeta'".(($rowLotesIngresados['IdTarjeta']==$idTarjeta)?' selected':'').">$nombre</option>";
      }
      $selectorTarjeta .= "</select>";
      
      echo "<tr><td>$rowLotesIngresados[IdLoteTarjetasCredito]</td><td>$selectorTarjeta</td><td>$rowLotesIngresados[Loteprefijo]</td><td><input type='text' name='IdLote' id='LoteNumero_$rowLotesIngresados[IdLoteTarjetasCredito]' value='$rowLotesIngresados[LoteNumero]' class='col-xs-8' pattern='[0-9]*'/></td><td>".sprintf("%.2f",$rowLotesIngresados['Importe'])."</td><td>$rowLotesIngresados[idAsiento] ($rowLotesIngresados[idCuentaContable_presentacion])</td><td><span class='btn btn-default btn-xs graba' id='actualizaLote_$rowLotesIngresados[IdLoteTarjetasCredito]'>Graba</span></td></tr>";
  }
  echo "</table>";
} else {
  echo "<table class='table'><tr><td colspan='5'>Nada para ver.</td></tr></table></form>";
}

//echo $tabla;
?>
