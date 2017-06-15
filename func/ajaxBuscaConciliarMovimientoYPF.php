<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
////fb($_REQUEST);

if(isset($_POST['id'])){
  // AUTOMATICO
  $id = substr($_POST['id'],4);
  $sqlYPF = "SELECT * FROM ctacte WHERE id=$id";
  ////fb($sqlYPF);die;
  $result = $mysqli3->query($sqlYPF);
  $rowYPF = $result->fetch_assoc();
  $mes = substr($rowYPF['femision'],3,2);
  $dia = substr($rowYPF['femision'],0,2);
  $ano = substr($rowYPF['femision'],6,4);
  $limiteInferior = (is_decimal(abs($rowYPF['Importe'])))?6:2;
  $limiteSuperior = (is_decimal(abs($rowYPF['Importe'])))?4:1;
  //fb($rowYPF['clase']);
  switch($rowYPF['clase']){
    case 'RV':
    $sqlCalden = "select * from dbo.movimientospro where IdTipoMovimientoProveedor IN ('RV') AND total=".abs($rowYPF['Importe'])." AND datepart(MONTH, Fecha)=$mes AND datepart(YEAR, Fecha)=$ano;";
    //fb($sqlCalden);
    $stmt = odbc_exec2($mssql, $sqlCalden, __LINE__, __FILE__);
    $rowCalden = sqlsrv_fetch_array($stmt);
    if(is_array($rowCalden)){
      $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($rowCalden[IdMovimientoPro], $id, 'rv', 1)";
    }
    
    break;
    case 'VP':
    $sqlCalden = "select * from dbo.movimientospro where IdTipoMovimientoProveedor IN ('VP') AND total=".abs($rowYPF['Importe'])." AND datepart(MONTH, Fecha)=$mes AND datepart(YEAR, Fecha)=$ano;";
    //fb($sqlCalden);
    $stmt = odbc_exec2($mssql, $sqlCalden, __LINE__, __FILE__);
    $rowCalden = sqlsrv_fetch_array($stmt);
    if(is_array($rowCalden)){
      $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($rowCalden[IdMovimientoPro], $id, 'rv', 1)";
    }
    
    break;
    case 'AR':
    // cheque rebotado
    $sqlCalden = "select * from dbo.movimientospro where IdTipoMovimientoProveedor='NDI' AND total=".abs($rowYPF['Importe'])." AND datepart(MONTH, Fecha)=$mes AND datepart(YEAR, Fecha)=$ano;";
    //fb($sqlCalden);
    $stmt = odbc_exec2($mssql, $sqlCalden, __LINE__, __FILE__);
    $rowCalden = sqlsrv_fetch_array($stmt);
    if(is_array($rowCalden)){
      $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($rowCalden[IdMovimientoPro], $id, 'ndi', 1)";
    }
    break;
    case 'LR':
    case 'DD':
    case 'FR':
    // Facturas lubricantes y facturas por seguro RC
    // saco numero de documento
    $numero = explode('A', $rowYPF['Referencia']);
    $sqlCalden = "select * from dbo.movimientospro where IdTipoMovimientoProveedor IN ('FAA', 'NDA') AND Numero=$numero[1] AND total=".abs($rowYPF['Importe'])." AND datepart(MONTH, Fecha)=$mes AND datepart(YEAR, Fecha)=$ano;";
    //fb($sqlCalden);
    $stmt = odbc_exec2($mssql, $sqlCalden, __LINE__, __FILE__);
    $rowCalden = sqlsrv_fetch_array($stmt);
    if(is_array($rowCalden)){
      $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($rowCalden[IdMovimientoPro], $id, '".strtolower($rowCalden[IdTipoMovimientoProveedor])."', 1)";
    }
    break;
    case 'CD':
    case 'LC':
    // Notas de credito
    // saco numero de documento
    $numero = explode('A', $rowYPF['Referencia']);
    $sqlCalden = "select * from dbo.movimientospro where IdTipoMovimientoProveedor IN ('NCA') AND Numero=$numero[1] AND total=".abs($rowYPF['Importe'])." AND datepart(MONTH, Fecha)=$mes AND datepart(YEAR, Fecha)=$ano;";
    //fb($sqlCalden);
    $stmt = odbc_exec2($mssql, $sqlCalden, __LINE__, __FILE__);
    $rowCalden = sqlsrv_fetch_array($stmt);
    if(is_array($rowCalden)){
      $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($rowCalden[IdMovimientoPro], $id, '".strtolower($rowCalden[IdTipoMovimientoProveedor])."', 1)";
    }
    break;
    case 'AV':
    // cheque rebotado
    $sqlCalden = "select * from dbo.movimientospro where IdTipoMovimientoProveedor='AJU' AND total=".abs($rowYPF['Importe'])." AND datepart(MONTH, Fecha)=$mes AND datepart(YEAR, Fecha)=$ano;";
    //fb($sqlCalden);
    $stmt = odbc_exec2($mssql, $sqlCalden, __LINE__, __FILE__);
    $rowCalden = sqlsrv_fetch_array($stmt);
    if(is_array($rowCalden)){
      $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($rowCalden[IdMovimientoPro], $id, 'av', 1)";
    }
    break;
    case 'DP':
    case 'RC':
    if(strlen($rowYPF['Referencia'])==15){
      // Visa
      $rowYPF['clase']='Visa';
      $sqlCalden = "select IdTransferenciaBancaria FROM dbo.TransferenciasBancarias as t, dbo.OrdenesPago as o where o.IdProveedor=4 AND o.IdOrdenPago=t.IdOrdenPago AND t.Fecha>=dateadd(day, -$limiteInferior, '$ano-$mes-$dia') AND t.Fecha<=dateadd(day, +$limiteSuperior, '$ano-$mes-$dia') AND Importe=".abs($rowYPF['Importe']).";";
      //fb($sqlCalden);
      $stmt = odbc_exec2($mssql, $sqlCalden, __LINE__, __FILE__);
      $rowCalden = sqlsrv_fetch_array($stmt);
      if(is_array($rowCalden)){
        $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($rowCalden[IdTransferenciaBancaria], $id, 'visa', 1)";
      }
    } else {
      // Otro recibo
      // busco primero pagos en efectivo
      $sqlCalden = "select TOP 1 IdOrdenPago, TotalAPagar, PagoEfectivo FROM dbo.OrdenesPago as o WHERE o.IdProveedor=4 AND  o.Fecha>=dateadd(day, -2, '$ano-$mes-$dia') AND o.Fecha<dateadd(day, +$limiteSuperior, '$ano-$mes-$dia')  AND PagoEfectivo=".abs($rowYPF['Importe'])." ORDER BY o.Fecha DESC;";
      ////fb($sqlCalden);
      $stmt = odbc_exec2($mssql, $sqlCalden, __LINE__, __FILE__);
      $rowCalden = sqlsrv_fetch_array($stmt);
      if(is_array($rowCalden)){
        $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($rowCalden[IdOrdenPago], $id, 'op', 1)";
      }
      if(!isset($rowCalden)||($rowCalden['Total']<>$rowCalden['PagoEfectivo'])){
        // Busco cheques de terceros
        //$sqlCalden = "select IdChequeTercero FROM dbo.OrdenesPago as o, dbo.ChequesTerceros as c WHERE o.IdProveedor=4 AND c.IdOrdenPago=o.IdOrdenPago AND  (datepart(MONTH, o.Fecha)=$mes AND datepart(YEAR, o.Fecha)=$ano   ) AND Importe=".abs($rowYPF['Importe']).";";
        $sqlCalden = "select IdChequeTercero FROM dbo.OrdenesPago as o, dbo.ChequesTerceros as c WHERE o.IdProveedor=4 AND c.IdOrdenPago=o.IdOrdenPago AND o.Fecha>=dateadd(day, -$limiteInferior, '$ano-$mes-$dia') AND o.Fecha<=dateadd(day, +$limiteSuperior, '$ano-$mes-$dia') AND Importe=".abs($rowYPF['Importe']).";";
        
        
        ////fb($sqlCalden);
        $stmt = odbc_exec2($mssql, $sqlCalden, __LINE__, __FILE__);
        $rowCalden = sqlsrv_fetch_array($stmt);
        if(is_array($rowCalden)){
          $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($rowCalden[IdChequeTercero], $id, 'cheque', 1)";
        } else {
          // no fue cheque, busco epago
          $sqlCalden = "select IdTransferenciaBancaria FROM dbo.TransferenciasBancarias as t, dbo.OrdenesPago as o where o.IdProveedor=4 AND o.IdOrdenPago=t.IdOrdenPago AND t.Fecha>=dateadd(day, -2, '$ano-$mes-$dia') AND t.Fecha<dateadd(day, +$limiteSuperior, '$ano-$mes-$dia')  AND Importe=".abs($rowYPF['Importe']).";";
          ////fb($sqlCalden);
          $stmt = odbc_exec2($mssql, $sqlCalden, __LINE__, __FILE__);
          $rowCalden = sqlsrv_fetch_array($stmt);
          if(is_array($rowCalden)){
            $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($rowCalden[IdTransferenciaBancaria], $id, 'epago', 1)";
          }
        }
      }
    }
    break;
  }
  if(isset($insert)){
    //fb($insert);
    $result2 = $mysqli3->query($insert);
    $idConciliado = $mysqli3->insert_id;
    //fb($idConciliado);
    $update = "UPDATE ctacte SET idConciliado=$idConciliado WHERE id=$id";
    //fb($update);
    $result = $mysqli3->query($update);
    $update2 = "UPDATE conciliacion SET idConciliado=$idConciliado WHERE id=$idConciliado";
    //fb($update2);
    $result = $mysqli3->query($update2);
  }
} else {
  //fb($_POST);
  if(count($_POST['idypf'])>1){
    // varios ypf a varios idCalden
    foreach($_POST['idypf'] as $r => $id){
      // busco tipo de documento para vincularlo a idCalden
      $sqlYPF = "SELECT * FROM ctacte WHERE id=$id";
      $result = $mysqli3->query($sqlYPF);
      $rowYPF = $result->fetch_assoc();
      switch($rowYPF['clase']){
        case 'RV':
        case 'VP':
        foreach($_POST['idcalden'] as $r => $idCalden){
          $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($idCalden, $id, 'rv', '0');";
          //fb($insert);
          $result2 = $mysqli3->query($insert);
          
          if(!isset($idConciliado))$idConciliado = $mysqli3->insert_id;
          $ultimoId=$mysqli3->insert_id;
          $update = "UPDATE ctacte SET idConciliado=$idConciliado WHERE id=$id";
          //fb($update);
          
          $result = $mysqli3->query($update);
          $update2 = "UPDATE conciliacion SET idConciliado=$idConciliado WHERE id=$ultimoId";
          //fb($update2);
          $result = $mysqli3->query($update2);
        }
        break;
        case 'DP':
        case 'RC':
        foreach($_POST['idcalden'] as $r => $idCalden){
          $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($idCalden, $id, 'epago', '0');";
          //fb($insert);
          $result2 = $mysqli3->query($insert);
          
          if(!isset($idConciliado))$idConciliado = $mysqli3->insert_id;
          $ultimoId=$mysqli3->insert_id;
          $update = "UPDATE ctacte SET idConciliado=$idConciliado WHERE id=$id";
          //fb($update);
          
          $result = $mysqli3->query($update);
          $update2 = "UPDATE conciliacion SET idConciliado=$idConciliado WHERE id=$ultimoId";
          //fb($update2);
          $result = $mysqli3->query($update2);
        }
        break;
      }
    }
  } else {
    // 1 ypf a varios calden
    $id = $_POST['idypf'][0];
    /*array(
      ['idypf'] =>    array(
        [0] =>      9
      )
      ['idcalden'] =>    array(
        [0] =>      5488
        [1] =>      5495
      )
    )*/
    // busco que es el tipo de documento para vincularlo a idCalden
    $sqlYPF = "SELECT * FROM ctacte WHERE id=$id";
    $result = $mysqli3->query($sqlYPF);
    $rowYPF = $result->fetch_assoc();
    //fb($rowYPF['clase']);
    switch($rowYPF['clase']){
      case 'RV':
      case 'VP':
      foreach($_POST['idcalden'] as $r => $idCalden){
        $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($idCalden, $id, 'rv', '0');";
        //fb($insert);
        $result2 = $mysqli3->query($insert);
        
        if(!isset($idConciliado))$idConciliado = $mysqli3->insert_id;
        $ultimoId=$mysqli3->insert_id;
        $update = "UPDATE ctacte SET idConciliado=$idConciliado WHERE id=$id";
        //fb($update);
        
        $result = $mysqli3->query($update);
        $update2 = "UPDATE conciliacion SET idConciliado=$idConciliado WHERE id=$ultimoId";
        //fb($update2);
        $result = $mysqli3->query($update2);
      }
      break;
      case 'AR':
      break;
      case 'LC':
      foreach($_POST['idcalden'] as $r => $idCalden){
        $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($idCalden, $id, 'nca', '0');";
        //fb($insert);
        $result2 = $mysqli3->query($insert);
        
        if(!isset($idConciliado))$idConciliado = $mysqli3->insert_id;
        $ultimoId=$mysqli3->insert_id;
        $update = "UPDATE ctacte SET idConciliado=$idConciliado WHERE id=$id";
        //fb($update);
        
        $result = $mysqli3->query($update);
        $update2 = "UPDATE conciliacion SET idConciliado=$idConciliado WHERE id=$ultimoId";
        //fb($update2);
        $result = $mysqli3->query($update2);
      }
      break;
      case 'PU':
      foreach($_POST['idcalden'] as $r => $idCalden){
        $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($idCalden, $id, 'nda', '0');";
        //fb($insert);
        $result2 = $mysqli3->query($insert);
        
        if(!isset($idConciliado))$idConciliado = $mysqli3->insert_id;
        $ultimoId=$mysqli3->insert_id;
        $update = "UPDATE ctacte SET idConciliado=$idConciliado WHERE id=$id";
        //fb($update);
        
        $result = $mysqli3->query($update);
        $update2 = "UPDATE conciliacion SET idConciliado=$idConciliado WHERE id=$ultimoId";
        //fb($update2);
        $result = $mysqli3->query($update2);
      }
      break;
      case 'LR':
      case 'FR':
      case 'DD':
      foreach($_POST['idcalden'] as $r => $idCalden){
        $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($idCalden, $id, 'faa', '0');";
        //fb($insert);
        $result2 = $mysqli3->query($insert);
        
        if(!isset($idConciliado))$idConciliado = $mysqli3->insert_id;
        $ultimoId=$mysqli3->insert_id;
        $update = "UPDATE ctacte SET idConciliado=$idConciliado WHERE id=$id";
        //fb($update);
        
        $result = $mysqli3->query($update);
        $update2 = "UPDATE conciliacion SET idConciliado=$idConciliado WHERE id=$ultimoId";
        //fb($update2);
        $result = $mysqli3->query($update2);
      }
      break;
      case 'CF':
      foreach($_POST['idcalden'] as $r => $idCalden){
        $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($idCalden, $id, 'nca', '0');";
        //fb($insert);
        $result2 = $mysqli3->query($insert);
        
        if(!isset($idConciliado))$idConciliado = $mysqli3->insert_id;
        $ultimoId=$mysqli3->insert_id;
        $update = "UPDATE ctacte SET idConciliado=$idConciliado WHERE id=$id";
        //fb($update);
        
        $result = $mysqli3->query($update);
        $update2 = "UPDATE conciliacion SET idConciliado=$idConciliado WHERE id=$ultimoId";
        //fb($update2);
        $result = $mysqli3->query($update2);
      }
      break;
      case 'AV':
      foreach($_POST['idcalden'] as $r => $idCalden){
        $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($idCalden, $id, 'av', '0');";
        //fb($insert);
        $result2 = $mysqli3->query($insert);
        
        if(!isset($idConciliado))$idConciliado = $mysqli3->insert_id;
        $ultimoId=$mysqli3->insert_id;
        $update = "UPDATE ctacte SET idConciliado=$idConciliado WHERE id=$id";
        //fb($update);
        
        $result = $mysqli3->query($update);
        $update2 = "UPDATE conciliacion SET idConciliado=$idConciliado WHERE id=$ultimoId";
        //fb($update2);
        $result = $mysqli3->query($update2);
      }
      break;
      case 'DP':
      case 'RC':
      foreach($_POST['idcalden'] as $r => $idCalden){
        $insert = "INSERT INTO conciliacion (idCalden, idYPF, tipoCalden, auto) VALUES ($idCalden, $id, 'op', '0');";
        //fb($insert);
        $result2 = $mysqli3->query($insert);
        
        if(!isset($idConciliado))$idConciliado = $mysqli3->insert_id;
        $ultimoId=$mysqli3->insert_id;
        $update = "UPDATE ctacte SET idConciliado=$idConciliado WHERE id=$id";
        //fb($update);
        
        $result = $mysqli3->query($update);
        $update2 = "UPDATE conciliacion SET idConciliado=$idConciliado WHERE id=$ultimoId";
        //fb($update2);
        $result = $mysqli3->query($update2);
      }
      break;
    }
  }
  echo json_encode(array('status' => 'yes','idConciliado'=> $idConciliado));
} 
?>
