<?php
// calculaPromedios.php
include_once('../include/inicia.php');
print_r($_REQUEST);
if(isset($_POST['id'])){
  $id = substr($_POST['id'],4);
  $sqlYPF = "SELECT * FROM 2016_ctacte WHERE id=$id";
  $result = $mysqli3->query($sqlYPF);
  $rowYPF = $result->fetch_assoc();
  $mes = substr($rowYPF['femision'],3,2);
  $dia = substr($rowYPF['femision'],0,2);
  $ano = substr($rowYPF['femision'],6,4);
  switch($rowYPF['clase']){
    case 'RV':
    $sqlCalden = "select * from dbo.movimientospro where IdTipoMovimientoProveedor='RV' AND total=".abs($rowYPF['Importe'])." AND datepart(MONTH, Fecha)=$mes AND datepart(YEAR, Fecha)=$ano;";
    fb($sqlCalden);
    $stmt = sqlsrv_query( $mssql, $sqlCalden);
    $rowCalden = sqlsrv_fetch_array($stmt);
    if(is_array($rowCalden)){
      $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden) VALUES ($rowCalden[IdMovimientoPro], $id, 'rv')";
      fb($insert);
      $result2 = $mysqli3->query($insert);
      $idConciliado = $mysqli3->insert_id;
      fb($idConciliado);
      $update = "UPDATE 2016_ctacte SET idConciliado=$idConciliado WHERE id=$id";
      fb($update);
      $result = $mysqli3->query($update);
    }
    
    break;
    case 'AR':
    // cheque rebotado
    $sqlCalden = "select * from dbo.movimientospro where IdTipoMovimientoProveedor='NDI' AND total=".abs($rowYPF['Importe'])." AND datepart(MONTH, Fecha)=$mes AND datepart(YEAR, Fecha)=$ano;";
    fb($sqlCalden);
    $stmt = sqlsrv_query( $mssql, $sqlCalden);
    $rowCalden = sqlsrv_fetch_array($stmt);
    if(is_array($rowCalden)){
      $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden) VALUES ($rowCalden[IdMovimientoPro], $id, 'ndi')";
      fb($insert);
      $result2 = $mysqli3->query($insert);
      $idConciliado = $mysqli3->insert_id;
      fb($idConciliado);
      $update = "UPDATE 2016_ctacte SET idConciliado=$idConciliado WHERE id=$id";
      fb($update);
      $result = $mysqli3->query($update);
    }
    break;
    case 'LR':
    case 'DD':
    // Facturas lubricantes y facturas por seguro RC
    // saco numero de documento
    $numero = explode('A', $rowYPF['Referencia']);
    $sqlCalden = "select * from dbo.movimientospro where IdTipoMovimientoProveedor IN ('FAA', 'NDA') AND Numero=$numero[1] AND total=".abs($rowYPF['Importe'])." AND datepart(MONTH, Fecha)=$mes AND datepart(YEAR, Fecha)=$ano;";
    fb($sqlCalden);
    $stmt = sqlsrv_query( $mssql, $sqlCalden);
    $rowCalden = sqlsrv_fetch_array($stmt);
    if(is_array($rowCalden)){
      $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden) VALUES ($rowCalden[IdMovimientoPro], $id, '".strtolower($rowCalden[IdTipoMovimientoProveedor])."')";
      fb($insert);
      $result2 = $mysqli3->query($insert);
      $idConciliado = $mysqli3->insert_id;
      fb($idConciliado);  
      $update = "UPDATE 2016_ctacte SET idConciliado=$idConciliado WHERE id=$id";
      fb($update);
      $result = $mysqli3->query($update);
    }
    break;
    case 'AV':
    // cheque rebotado
    $sqlCalden = "select * from dbo.movimientospro where IdTipoMovimientoProveedor='AJU' AND total=".abs($rowYPF['Importe'])." AND datepart(MONTH, Fecha)=$mes AND datepart(YEAR, Fecha)=$ano;";
    fb($sqlCalden);
    $stmt = sqlsrv_query( $mssql, $sqlCalden);
    $rowCalden = sqlsrv_fetch_array($stmt);
    if(is_array($rowCalden)){
      $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden) VALUES ($rowCalden[IdMovimientoPro], $id, 'av')";
      fb($insert);
      $result2 = $mysqli3->query($insert);
      $idConciliado = $mysqli3->insert_id;
      fb($idConciliado);
      $update = "UPDATE 2016_ctacte SET idConciliado=$idConciliado WHERE id=$id";
      fb($update);
      $result = $mysqli3->query($update);
    }
    break;
    case 'DP':
    case 'RC':
    if(strlen($rowYPF['Referencia'])==15){
      // Visa
      $rowYPF['clase']='Visa';
      $sqlCalden = "select IdTransferenciaBancaria FROM dbo.TransferenciasBancarias as t, dbo.OrdenesPago as o where o.IdProveedor=4 AND o.IdOrdenPago=t.IdOrdenPago AND datepart(MONTH, o.Fecha)=$mes AND datepart(YEAR, o.Fecha)=$ano AND Importe=".abs($rowYPF['Importe']).";";
      fb($sqlCalden);
      $stmt = sqlsrv_query( $mssql, $sqlCalden);
      $rowCalden = sqlsrv_fetch_array($stmt);
      if(is_array($rowCalden)){
        $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden) VALUES ($rowCalden[IdTransferenciaBancaria], $id, 'visa')";
        fb($insert);
        $result2 = $mysqli3->query($insert);
        $idConciliado = $mysqli3->insert_id;
        fb($idConciliado);
        $update = "UPDATE 2016_ctacte SET idConciliado=$idConciliado WHERE id=$id";
        fb($update);
        $result = $mysqli3->query($update);
      }
    } else {
      // Otro recibo
      // busco primero pagos en efectivo
      $sqlCalden = "select IdOrdenPago, TotalAPagar, PagoEfectivo FROM dbo.OrdenesPago as o WHERE o.IdProveedor=4 AND datepart(MONTH, o.Fecha)=$mes AND datepart(YEAR, o.Fecha)=$ano   AND PagoEfectivo=".abs($rowYPF['Importe']).";";
      fb($sqlCalden);
      $stmt = sqlsrv_query( $mssql, $sqlCalden);
      $rowCalden = sqlsrv_fetch_array($stmt);
      if(is_array($rowCalden)){
        $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden) VALUES ($rowCalden[IdOrdenPago], $id, 'op')";
        fb($insert);
        $result2 = $mysqli3->query($insert);
        $idConciliado = $mysqli3->insert_id;
        fb($idConciliado);
        $update = "UPDATE 2016_ctacte SET idConciliado=$idConciliado WHERE id=$id";
        fb($update);
        $result = $mysqli3->query($update);
      }
      if(!isset($rowCalden)||($rowCalden['Total']<>$rowCalden['PagoEfectivo'])){
        // Busco cheques de terceros
        $sqlCalden = "select IdChequeTercero FROM dbo.OrdenesPago as o, dbo.ChequesTerceros as c WHERE o.IdProveedor=4 AND c.IdOrdenPago=o.IdOrdenPago AND (datepart(MONTH, o.Fecha)=$mes AND datepart(YEAR, o.Fecha)=$ano OR o.Fecha >= DATEADD(MONTH, -3, '$ano-$mes-01')  ) AND Importe=".abs($rowYPF['Importe']).";";
        fb($sqlCalden);
        $stmt = sqlsrv_query( $mssql, $sqlCalden);
        $rowCalden = sqlsrv_fetch_array($stmt);
        if(is_array($rowCalden)){
          $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden) VALUES ($rowCalden[IdChequeTercero], $id, 'cheque')";
          fb($insert);
          $result2 = $mysqli3->query($insert);
          $idConciliado = $mysqli3->insert_id;
          fb($idConciliado);
          $update = "UPDATE 2016_ctacte SET idConciliado=$idConciliado WHERE id=$id";
          fb($update);
          $result = $mysqli3->query($update);
        } else {
          // no fue cheque, busco epago
          $sqlCalden = "select IdTransferenciaBancaria FROM dbo.TransferenciasBancarias as t, dbo.OrdenesPago as o where o.IdProveedor=4 AND o.IdOrdenPago=t.IdOrdenPago AND datepart(MONTH, t.Fecha)=$mes AND datepart(YEAR, t.Fecha)=$ano AND Importe=".abs($rowYPF['Importe']).";";
          fb($sqlCalden);
          $stmt = sqlsrv_query( $mssql, $sqlCalden);
          $rowCalden = sqlsrv_fetch_array($stmt);
          if(is_array($rowCalden)){
            $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden) VALUES ($rowCalden[IdTransferenciaBancaria], $id, 'epago')";
            fb($insert);
            $result2 = $mysqli3->query($insert);
            $idConciliado = $mysqli3->insert_id;
            fb($idConciliado);
            $update = "UPDATE 2016_ctacte SET idConciliado=$idConciliado WHERE id=$id";
            fb($update);
            $result = $mysqli3->query($update);
          }
        }
      }
    }
    break;
  }
} else {
  die;
}

?>
