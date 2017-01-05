<?php
// calculaPromedios.php
include_once('../include/inicia.php');
//fb($_POST);
if(isset($_POST['ticket'])&&is_numeric($_POST['ticket'])){
  $sqlTicket = "select PuntoVenta, Numero, FechaEmision, IdArticulo, Total, Cantidad, dbo.movimientosfac.IdMovimientoFac from dbo.movimientosfac, dbo.MovimientosDetalleFac WHERE dbo.movimientosfac.IdMovimientoFac=dbo.MovimientosDetalleFac.IdMovimientoFac AND idArticulo IN (2076, 2068) AND Numero=$_POST[ticket] AND Total>=570 AND dbo.movimientosfac.fecha>DATEADD(month, -3, GETDATE());";

 //fb($sqlTicket);
  $stmt = sqlsrv_query( $mssql, $sqlTicket);
  if( $stmt === false ){
      echo "1. Error in executing query.</br>$sqlTicket<br/>";
      die( print_r( sqlsrv_errors(), true));
  }
  $tmp=array();
  //fb(sqlsrv_num_rows($stmt));
  if(sqlsrv_has_rows($stmt)){
    // multiples resultados
    while($rowTicket = sqlsrv_fetch_array($stmt)){
      $tmp[]=$rowTicket;
    }
    if(count($tmp)>1){
      // multiple
      $devuelve = '';
      foreach($tmp as $rowTicket){
        $devuelve.="<input type='radio' name='multi' class='multi' value='$rowTicket[6]'> $rowTicket[0]-$rowTicket[1], $rowTicket[5] lts de {$articulo[$rowTicket[3]]}, $$rowTicket[4] (".$rowTicket[2]->format('d/m/Y').")<br/>";
      }
      echo json_encode(array('status' => 'multiple','message'=> $devuelve, 'fecha' => '', 'pv'=> ''));
    } else {
      // single
      $rowTicket=$tmp[0];
      $_SESSION['esNafta'] = (($rowTicket['IdArticulo']==2076)?1:0);
      $devuelve = "$rowTicket[0]-$rowTicket[1], $rowTicket[5] lts de {$articulo[$rowTicket[3]]}, $$rowTicket[4] (".$rowTicket[2]->format('d/m/Y').")";
      echo json_encode(array('status' => 'single','message'=> $devuelve, 'fecha'=>$rowTicket[2]->format('d/m'), 'pv'=> $rowTicket[0], 'IdMovimientoFac'=> $rowTicket[6], 'FechaTicket'=>$rowTicket[2]->format('d/m/Y')));
    }
  } else {
    echo json_encode(array('status' => 'error','message'=> 'Por favor complete los datos solicitados', 'fecha'=> '', 'pv'=> ''));
  }
} elseif(is_numeric($_POST['IdMovimientoFac'])){
  $sqlTicket = "select PuntoVenta, Numero, FechaEmision, IdArticulo, Total, Cantidad, dbo.movimientosfac.IdMovimientoFac from dbo.movimientosfac, dbo.MovimientosDetalleFac WHERE dbo.movimientosfac.IdMovimientoFac=dbo.MovimientosDetalleFac.IdMovimientoFac AND idArticulo IN (2076, 2068) AND dbo.MovimientosDetalleFac.IdMovimientoFac=$_POST[IdMovimientoFac] AND Total>=570 AND dbo.movimientosfac.fecha>DATEADD(month, -3, GETDATE());";

  //fb($sqlTicket);
  $stmt = sqlsrv_query( $mssql, $sqlTicket);
  if( $stmt === false ){
      echo "1. Error in executing query.</br>$sqlTicket<br/>";
      die( print_r( sqlsrv_errors(), true));
  }
  if(sqlsrv_has_rows($stmt)){
    // multiples resultados
    $rowTicket = sqlsrv_fetch_array($stmt);
    $_SESSION['esNafta'] = (($rowTicket['IdArticulo']==2076)?1:0);
    $devuelve = "$rowTicket[0]-$rowTicket[1], $rowTicket[5] lts de {$articulo[$rowTicket[3]]}, $$rowTicket[4] (".$rowTicket[2]->format('d/m/Y').")";
    echo json_encode(array('status' => 'single','message'=> $devuelve, 'fecha'=>$rowTicket[2]->format('d/m'), 'pv'=> $rowTicket[0], 'IdMovimientoFac'=> $rowTicket[6], 'FechaTicket'=>$rowTicket[2]->format('d/m/Y')));
  }
}
?>
