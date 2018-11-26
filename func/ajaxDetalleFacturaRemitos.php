<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

ChromePhp::log($_POST);





/* Para Lolen */
$sqlDetalle = "select IdTipoMovimiento, PuntoVenta, numero, Fecha, c.descripcion, cantidad, precio, b.iva as IVA_unitario, b.iva*cantidad as IVA_Renglon, b.ImpuestoInterno as IMP_INT_Unitario, b.ImpuestoInterno*cantidad as ImpuestosInternos_Renglon, b.Tasas as Tasa_unitario, b.Tasas*Cantidad as Tasas_Renglon from dbo.movimientosfac a , dbo.MovimientosDetalleFac b, dbo.articulos c where a.IdMovimientoFac=b.IdMovimientoFac and b.idarticulo=c.idarticulo and a.numero=43609 and a.PuntoVenta=12 and a.IdTipoMovimiento='FAA';";

if(isset($_POST['viejos'])&&is_numeric($_POST['viejos'])){
  $maximoMesesAtras = 8;
} else {
  $maximoMesesAtras = 2;
}

//$maximoMesesAtras2 = 5;
if(isset($_POST['ticket'])&&is_numeric($_POST['ticket'])){
  $sqlTicket = "select DISTINCT PuntoVenta, Numero, FechaEmision, IdArticulo, Total, Cantidad, dbo.movimientosfac.IdMovimientoFac, IdTipoMovimiento, IdCierreTurno from dbo.movimientosfac, dbo.MovimientosDetalleFac WHERE dbo.movimientosfac.IdMovimientoFac=dbo.MovimientosDetalleFac.IdMovimientoFac AND idArticulo IN (2076, 2068) AND Numero=$_POST[ticket] AND Total>=570 AND dbo.movimientosfac.fecha>DATEADD(month, -$maximoMesesAtras, GETDATE()) AND dbo.movimientosfac.fecha>DATEADD(month, -$maximoMesesAtras2, GETDATE()) AND IdCierreTurno IS NOT NULL;";
  $sqlTicket = "select DISTINCT PuntoVenta, Numero, FechaEmision, IdArticulo, Total, Cantidad, dbo.movimientosfac.IdMovimientoFac, IdTipoMovimiento, IdCierreTurno from dbo.movimientosfac, dbo.MovimientosDetalleFac WHERE dbo.movimientosfac.IdMovimientoFac=dbo.MovimientosDetalleFac.IdMovimientoFac AND idArticulo IN (2076, 2068) AND Numero=$_POST[ticket] AND Total>=570 AND dbo.movimientosfac.fecha>DATEADD(month, -$maximoMesesAtras, GETDATE()) AND IdCierreTurno IS NOT NULL;";
  $empleados = '';
  ChromePhp::log($sqlTicket);
  $stmt = odbc_exec2( $mssql, $sqlTicket, __LINE__, __FILE__);
  $tmp=array();
  //ChromePhp::log(sqlsrv_num_rows($stmt));
  if(sqlsrv_has_rows($stmt)){
    // multiples resultados
    while($rowTicket = sqlsrv_fetch_array($stmt)){
      $tmp[]=$rowTicket;
    }
    if(count($tmp)>1){
      // multiple
      $devuelve = '';
      foreach($tmp as $rowTicket){
        if($_POST['idEmpleado']==21){
          // Cubre Vacaciones, obtengo datos del turno
          $sqlTurno = "SELECT IdEmpleado1, IdEmpleado2, IdEmpleado3, IdEmpleado4 FROM dbo.cierresTurno WHERE IdCierreTurno='$rowTicket[8]';";
          $stmt3 = odbc_exec2( $mssql, $sqlTurno, __LINE__, __FILE__);
          $rowTurno = sqlsrv_fetch_array($stmt3);
          if($rowTurno[0]=='21'||$rowTurno[1]=='21'||$rowTurno[2]=='21'||$rowTurno[3]=='21'){
            // turno con Cubre Vacaciones real
          } else {
            $empleados = "";
          $empleados .= ($rowTurno[0]>0)?$vendedor[$rowTurno[0]].' ':'';
          $empleados .= ($rowTurno[1]>0)?$vendedor[$rowTurno[1]].' ':'';
          $empleados .= ($rowTurno[2]>0)?$vendedor[$rowTurno[2]].' ':'';
          $empleados .= ($rowTurno[3]>0)?$vendedor[$rowTurno[3]].' ':'';
          }
        }
        $sqlYaAsignado = "SELECT IdMovimientoFac, IdEmpleado, fechaCanje from coop.dbo.promoDesayunos WHERE IdMovimientoFac=$rowTicket[6];";
        $stmt2 = odbc_exec2( $mssql, $sqlYaAsignado, __LINE__, __FILE__);
        if($stmt2 && sqlsrv_has_rows($stmt2)){
          // ya está tomado ese documento, mínimamente aviso
          $devuelve.="<input type='radio' name='multi' class='multi' value='$rowTicket[6]' disabled='disabled'><span class='text-danger'> $rowTicket[7] $rowTicket[0]-$rowTicket[1], $rowTicket[5] lts de {$articulo[$rowTicket[3]]}, $$rowTicket[4] (".$rowTicket[2]->format('d/m/Y').")</span><br/>";
        } else {
          $devuelve.="<input type='radio' name='multi' class='multi' value='$rowTicket[6]'> $rowTicket[7] $rowTicket[0]-$rowTicket[1], $rowTicket[5] lts de {$articulo[$rowTicket[3]]}, $$rowTicket[4]<br/>(".$rowTicket[2]->format('d/m/Y').") $empleados<br/>";
        }
      }
      echo json_encode(array('status' => 'multiple','message'=> $devuelve, 'fecha' => '', 'pv'=> ''));
    } else {
      // single
      $rowTicket=$tmp[0];
      if($_POST['idEmpleado']=='21'){
        // Cubre Vacaciones, obtengo datos del turno
        $sqlTurno = "SELECT IdEmpleado1, IdEmpleado2, IdEmpleado3, IdEmpleado4 FROM dbo.cierresTurno WHERE IdCierreTurno='$rowTicket[8]';";
        ChromePhp::log($sqlTurno);
        $stmt3 = odbc_exec2( $mssql, $sqlTurno, __LINE__, __FILE__);
        $rowTurno = sqlsrv_fetch_array($stmt3);
        if($rowTurno[0]=='21'||$rowTurno[1]=='21'||$rowTurno[2]=='21'||$rowTurno[3]=='21'){
          // turno con Cubre Vacaciones real
        } else {
          $empleados = "";
          $empleados .= ($rowTurno[0]>0)?$vendedor[$rowTurno[0]].' ':'';
          $empleados .= ($rowTurno[1]>0)?$vendedor[$rowTurno[1]].' ':'';
          $empleados .= ($rowTurno[2]>0)?$vendedor[$rowTurno[2]].' ':'';
          $empleados .= ($rowTurno[3]>0)?$vendedor[$rowTurno[3]].' ':'';
        }
      }
      $_SESSION['esNafta'] = (($rowTicket['IdArticulo']==2076)?1:0);
      $fCanje = (isset($_SESSION['fCanje']))?$_SESSION['fCanje']:false;
      $resalta = "";
      if($fCanje){
        //ChromePhp::log("fCanje");
        // si la fecha de canje que tengo cargada es anterior a la fecha del ticket la pone en rojo
        //$resaltaFecha = ();
        //ChromePhp::log(($rowTicket[2]->format('d/m/Y')));
        $fCanje2 = explode("/", $fCanje);
        $fechaCanje = new DateTime("$fCanje2[1]/$fCanje2[0]/$fCanje2[2] 23:59:59");
        if($fechaCanje<$rowTicket[2]){
          ChromePhp::log("fecha imposible");
          $resalta = "label label-danger";
        }
      }
      $sqlYaAsignado = "SELECT IdMovimientoFac, IdEmpleado, fechaCanje from coop.dbo.promoDesayunos WHERE IdMovimientoFac=$rowTicket[6];";
      $stmt2 = odbc_exec2( $mssql, $sqlYaAsignado, __LINE__, __FILE__);
      if($stmt2 && sqlsrv_has_rows($stmt2)){
        $ticketCanjeado = sqlsrv_fetch_array($stmt2);
        $devuelve = "<span class='text-danger'>$rowTicket[0]-$rowTicket[1], $rowTicket[5]</span> lts de {$articulo[$rowTicket[3]]}, $$rowTicket[4] (<span class='$resalta'>".$rowTicket[2]->format('d/m/Y')."</span>) - <span class='text-danger'>Asignado a {$empleado[1][$ticketCanjeado[1]]} el ".$ticketCanjeado[2]->format('d/m/Y')."</span>";
        echo json_encode(array('status' => 'single','message'=> $devuelve, 'fecha'=>$rowTicket[2]->format('d/m'), 'pv'=> $rowTicket[0], 'IdMovimientoFac'=> $rowTicket[6], 'FechaTicket'=>$rowTicket[2]->format('d/m/Y'), 'fCanje'=>$fCanje));
      } else {
        $devuelve = "$rowTicket[0]-$rowTicket[1], $rowTicket[5] lts de {$articulo[$rowTicket[3]]}, $$rowTicket[4] (<span class='$resalta'>".$rowTicket[2]->format('d/m/Y')."</span>) $empleados";
        echo json_encode(array('status' => 'single','message'=> $devuelve, 'fecha'=>$rowTicket[2]->format('d/m'), 'pv'=> $rowTicket[0], 'IdMovimientoFac'=> $rowTicket[6], 'FechaTicket'=>$rowTicket[2]->format('d/m/Y'), 'fCanje'=>$fCanje));
      }
    }
  } else {
    echo json_encode(array('status' => 'error','message'=> 'Por favor complete los datos solicitados', 'fecha'=> '', 'pv'=> ''));
  }
} elseif(is_numeric($_POST['IdMovimientoFac'])){
  $sqlTicket = "select DISTINCT PuntoVenta, Numero, FechaEmision, IdArticulo, Total, Cantidad, dbo.movimientosfac.IdMovimientoFac, IdTipoMovimiento from dbo.movimientosfac, dbo.MovimientosDetalleFac WHERE dbo.movimientosfac.IdMovimientoFac=dbo.MovimientosDetalleFac.IdMovimientoFac AND idArticulo IN (2076, 2068) AND dbo.MovimientosDetalleFac.IdMovimientoFac=$_POST[IdMovimientoFac] AND Total>=570 AND dbo.movimientosfac.fecha>DATEADD(month, -$maximoMesesAtras, GETDATE()) AND IdCierreTurno IS NOT NULL;";

  //ChromePhp::log($sqlTicket);
  $stmt = odbc_exec2( $mssql, $sqlTicket, __LINE__, __FILE__);
  if(sqlsrv_has_rows($stmt)){
    // multiples resultados
    $rowTicket = sqlsrv_fetch_array($stmt);
    $_SESSION['esNafta'] = (($rowTicket['IdArticulo']==2076)?1:0);
    $devuelve = "$rowTicket[0]-$rowTicket[1], $rowTicket[5] lts de {$articulo[$rowTicket[3]]}, $$rowTicket[4] (".$rowTicket[2]->format('d/m/Y').")";
    $fCanje = (isset($_SESSION['fCanje']))?$_SESSION['fCanje']:false;
    echo json_encode(array('status' => 'single','message'=> $devuelve, 'fecha'=>$rowTicket[2]->format('d/m'), 'pv'=> $rowTicket[0], 'IdMovimientoFac'=> $rowTicket[6], 'FechaTicket'=>$rowTicket[2]->format('d/m/Y'), 'fCanje'=>$fCanje));
  }
}
?>
