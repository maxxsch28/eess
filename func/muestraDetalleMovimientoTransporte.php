<?php
// muestraDetalleMovimientoTransporte.php
include_once('../include/inicia.php');


$limit=11;
$offset=0;

$parametros = explode('_', $_GET['idAsiento']);
$idTranglob = $parametros[0];
$tipo = $parametros[1];

fb($tipo);
switch($tipo){
  case '1003':
  // pago a fletero
    $sqlMovimiento = "select p.sucursal as pagoSucursal, p.numero as pagoNumero, fecha, detalle, p.importe as pagoImporte, reten_ib, banco, nombre, abs(d.numero) as detalleNumero, d.importe as detalleImporte, d.vencimien from dbo.pagos as p, dbo.detapago as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob;";
    $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
    while($rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$rowMovimiento[detalle]</b> (".date_format($rowMovimiento['fecha'], "d/m/Y").")</br>Total: $rowMovimiento[pagoImporte]<br/>IIBB: $$rowMovimiento[reten_ib]<br>";
      }
      echo "$rowMovimiento[nombre] Nº$rowMovimiento[detalleNumero], $$rowMovimiento[detalleImporte], ".date_format($rowMovimiento['vencimien'], "d/m/Y")."<br/>";
    }
    break;  
  case '1031':
  // pago a fletero
    $sqlMovimiento = "select p.sucursal as pagoSucursal, p.numero as pagoNumero, fecha, detalle, p.importe as pagoImporte, reten_ib, banco, nombre, abs(d.numero) as detalleNumero, d.importe as detalleImporte, d.vencimien from dbo.pagos as p, dbo.detapago as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob;";
    $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
    while($rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$rowMovimiento[detalle]</b> (".date_format($rowMovimiento['fecha'], "d/m/Y").")</br>Total: $$rowMovimiento[pagoImporte]<br/>IIBB: $$rowMovimiento[reten_ib]<br>";
      }
      echo "$rowMovimiento[nombre] Nº $rowMovimiento[detalleNumero], $$rowMovimiento[detalleImporte], ".date_format($rowMovimiento['vencimien'], "d/m/Y")."<br/>";
    }
    break;   
  case '1039':
  // adelanto a fletero
    $sqlMovimiento = " select p.numero, fecha, detalle, p.importe, banco, nombre_ban, abs(d.numero_che) as numero_che, d.importe as detalleImporte, fecha_che from dbo.moctacte as p, dbo.detaadfl as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob AND abs(p.numero)=abs(d.numero_che);";
    $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
    while($rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$rowMovimiento[detalle]</b> (".date_format($rowMovimiento['fecha'], "d/m/Y").")</br>";
      }
      echo "$rowMovimiento[nombre_ban] Nº $rowMovimiento[numero_che], $".round($rowMovimiento['detalleImporte'],2).", ".date_format($rowMovimiento['fecha_che'], "d/m/Y")."<br/>";
    }
    break;  
  case '1030':
  // pago a proveedor
    $sqlMovimiento = "select p.numero, p.fecha, detalle, p.importe, d.nombre, abs(d.numero) as numeroCheque, d.importe as detalleImporte, d.vencimien, reten_ib from dbo.pagos as p, dbo.detapago as d where p.idtranglob=17915 AND p.idtranglob=d.idtranglob;";
    $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
    while($rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$rowMovimiento[detalle]</b> (".date_format($rowMovimiento['fecha'], "d/m/Y").")</br>Total: $$rowMovimiento[importe]<br/>IIBB: $$rowMovimiento[reten_ib]<br>";
      }
      echo "$rowMovimiento[nombre] Nº $rowMovimiento[numeroCheque], $".round($rowMovimiento['detalleImporte'],2).", ".date_format($rowMovimiento['vencimien'], "d/m/Y")."<br/>";
    }
    break; 
  case "VENTAS":
    echo "$rowAsiento[Descripcion]";
    if($rowAsiento['Descripcion']=='ND VARIAS EN CUENTA CORRIENTE'){
      $sqlMovimiento = '1';
    } else {
      $sqlMovimiento = "SELECT * FROM dbo.MovimientosFac WHERE IdAsiento=$_GET[idAsiento]";
      $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
      $rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
      echo ", $rowMovimiento[IdTipoMovimiento] $rowMovimiento[PuntoVenta]-$rowMovimiento[Numero], emitida el ".date_format($rowMovimiento['FechaEmision'], "d/m/Y H:i:s");
      echo "<br/><br/>$rowMovimiento[RazonSocial] ($rowMovimiento[IdCliente]), CUIT $rowMovimiento[NumeroDocumento]<br/><br/>";
  //     echo "<span class='pull-right'>";
      if($rowMovimiento['NetoCombustibles']>0)echo "<span class='".((($rowMovimiento['NetoCombustibles']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['NetoCombustibles']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['NetoCombustibles']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Combustibles: $$rowMovimiento[NetoCombustibles]</span><br/>";

      if($rowMovimiento['NetoNoGravado']>0)echo "<span class='".((($rowMovimiento['NetoNoGravado']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['NetoNoGravado']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['NetoNoGravado']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>No gravado: $$rowMovimiento[NetoNoGravado]</span><br/>";
      
      if($rowMovimiento['NetoMercaderias']>0)echo "<span class='".((($rowMovimiento['NetoMercaderias']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['NetoMercaderias']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['NetoMercaderias']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Mercaderías: $$rowMovimiento[NetoMercaderias]</span><br/>";
      
      if($rowMovimiento['NetoLubricantes']>0)echo "<span class='".((($rowMovimiento['NetoLubricantes']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['NetoLubricantes']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['NetoLubricantes']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Lubricantes: $$rowMovimiento[NetoLubricantes]</span><br/>";
      
      if($rowMovimiento['NetoCigarrillos']>0)echo "<span class='".((($rowMovimiento['NetoCigarrillos']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['NetoCigarrillos']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['NetoCigarrillos']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Cigarrillos: $$rowMovimiento[NetoCigarrillos]</span><br/>";
      
      if($rowMovimiento['NetoConceptosFinancieros']>0)echo "<span class='".((($rowMovimiento['NetoConceptosFinancieros']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['NetoConceptosFinancieros']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['NetoConceptosFinancieros']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Financieros: $$rowMovimiento[NetoConceptosFinancieros]</span><br/>";
      
      if($rowMovimiento['IVA']>0)echo "<span class='".((($rowMovimiento['IVA']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['IVA']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['IVA']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>IVA: $$rowMovimiento[IVA]</span><br/>";
      
      if($rowMovimiento['ImpuestoInterno']>0)echo "<span class='".((($rowMovimiento['ImpuestoInterno']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['ImpuestoInterno']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['ImpuestoInterno']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Impuesto Interno: $$rowMovimiento[ImpuestoInterno]</span><br/>";
      
      if($rowMovimiento['Tasas']>0)echo "<span class='".((($rowMovimiento['Tasas']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['Tasas']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['Tasas']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Tasas: $$rowMovimiento[Tasas]</span><br/>";
      
      if($rowMovimiento['PercepcionIIBB']>0)echo "<span class='".((($rowMovimiento['PercepcionIIBB']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['PercepcionIIBB']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['PercepcionIIBB']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Percepcion IIBB: $$rowMovimiento[PercepcionIIBB]</span><br/>";
      
      if($rowMovimiento['Total']>0)echo "<b><span class='".((($rowMovimiento['Total']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['Total']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['Total']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Total: $$rowMovimiento[Total]</b></span><br/>";
  //     echo "</span>";
      if($rowMovimiento['DocumentoCancelado']>0)echo "<b>DOCUMENTO CANCELADO</b><br/>";
    }
    break;
  case "SUELDOS":
    break;
  case "TARJETAS":
    // separar subconceptos, presentacion de cupones de acreditaciones.
    echo "$rowAsiento[Descripcion]";
    if(trim($rowAsiento['Nombre'])=='TARJETAS01'){
      // ACREDITACIONES:
      $sqlMovimiento = "select * from dbo.AcreditacionesTarjetasCredito where IdAsiento=$_GET[idAsiento]";
      $sqlMovimiento = "select dbo.Bancos.Nombre, NumeroCuenta, Numero, FechaAcreditacion, AcreditacionNeta, Comisiones,   RetencionIIB, IVA,  TotalRechazadas, AnioImputacionIVACompras, MesImputacionIVACompras, ImporteAcreditado, dbo.AcreditacionesTarjetasCredito.IdTarjeta, LotePrefijo,dbo.AcreditacionesTarjetasCredito.RetencionGanancias, dbo.AcreditacionesTarjetasCredito.RetencionIIB, dbo.AcreditacionesTarjetasCredito.RetencionIVA, dbo.LotesTarjetasCredito.Fecha, dbo.LotesTarjetasCredito.Importe, LoteNumero FROM dbo.bancos, dbo.CuentasBancarias, dbo.AcreditacionesTarjetasCredito, dbo.LotesAcreditados, dbo.LotesTarjetasCredito where dbo.AcreditacionesTarjetasCredito.IdAsiento=$_GET[idAsiento] AND dbo.LotesAcreditados.IdAcreditacionTarjetasCredito = dbo.AcreditacionesTarjetasCredito.IdAcreditacionTarjetasCredito AND  dbo.LotesAcreditados.IdLoteTarjetasCredito=dbo.LotesTarjetasCredito.IdLoteTarjetasCredito AND dbo.bancos.IdBanco=dbo.CuentasBancarias.IdBanco AND dbo.CuentasBancarias.IdCuentaBancaria=dbo.AcreditacionesTarjetasCredito.IdCuentaBancaria";
      //echo $sqlMovimiento;
      $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
      // hacer loop;
      while($rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
        if(!isset($encabezado)){
          $encabezado=1;
          echo "<br/>Número $rowMovimiento[Numero], acreditada el ".date_format($rowMovimiento['FechaAcreditacion'], "d/m/Y H:i:s");
          echo "<br/><br/>Acreditación NETA: ".sprintf("%.2f",$rowMovimiento['AcreditacionNeta'])."<br/>";
          if($rowMovimiento['Comisiones']>0)echo "Comisiones: $".sprintf("%.2f",$rowMovimiento['Comisiones'])."<br/>";
          if($rowMovimiento['RetencionIIB']>0)echo "Retencion IIBB: $".sprintf("%.2f",$rowMovimiento['RetencionIIB'])."<br/>";
          if($rowMovimiento['IVA']>0)echo "IVA: $".sprintf("%.2f",$rowMovimiento['IVA'])." ($rowMovimiento[MesImputacionIVACompras]/$rowMovimiento[AnioImputacionIVACompras])<br/>";
          if($rowMovimiento['RetencionIVA']>0)echo "Retencion IVA: $".sprintf("%.2f",$rowMovimiento['RetencionIVA'])."<br/>";
          if($rowMovimiento['RetencionGanancias']>0)echo "Retencion Ganancias: $".sprintf("%.2f",$rowMovimiento['RetencionGanancias'])."<br/>";
          if($rowMovimiento['TotalRechazadas']>0)echo "Total Rechazadas: $".sprintf("%.2f",$rowMovimiento['TotalRechazadas'])."<br/>";
          echo "<br>";
        }
        echo "$rowMovimiento[LotePrefijo]-$rowMovimiento[LoteNumero], $".sprintf("%.2f",$rowMovimiento['Importe'])." (Presentado ".date_format($rowMovimiento['Fecha'], "d/m/Y H:i:s").")<br/>";
      }
    } elseif(trim($rowAsiento['Nombre'])=='TARJETAS03'){
    // ACREDITACIONES CON CHEQUES
      $sqlMovimiento = "select dbo.AcreditacionesTarjetasCredito.Numero, FechaAcreditacion, AcreditacionNeta, Comisiones, RetencionIIB, IVA, TotalRechazadas, AnioImputacionIVACompras, MesImputacionIVACompras, ImporteAcreditado, dbo.AcreditacionesTarjetasCredito.IdTarjeta, LotePrefijo,dbo.AcreditacionesTarjetasCredito.RetencionGanancias, dbo.AcreditacionesTarjetasCredito.RetencionIIB, dbo.AcreditacionesTarjetasCredito.RetencionIVA, dbo.LotesTarjetasCredito.Fecha, dbo.LotesTarjetasCredito.Importe, LoteNumero, dbo.ChequesTerceros.Numero as NumeroCheque, dbo.ChequesTerceros.IdBanco, Localidad, dbo.ChequesTerceros.Fecha, FechaSalida, TipoSalida, IdOrdenPago, Nombre FROM dbo.AcreditacionesTarjetasCredito, dbo.LotesAcreditados, dbo.LotesTarjetasCredito, dbo.ChequesTerceros, dbo.Bancos WHERE dbo.Bancos.idBanco=dbo.ChequesTerceros.IdBanco AND dbo.AcreditacionesTarjetasCredito.IdAsiento=$_GET[idAsiento] AND dbo.LotesAcreditados.IdAcreditacionTarjetasCredito = dbo.AcreditacionesTarjetasCredito.IdAcreditacionTarjetasCredito AND dbo.LotesAcreditados.IdLoteTarjetasCredito=dbo.LotesTarjetasCredito.IdLoteTarjetasCredito AND dbo.AcreditacionesTarjetasCredito.IdAcreditacionTarjetasCredito=dbo.ChequesTerceros.IdAcreditacionTarjetasCredito";
      //echo $sqlMovimiento;
      $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
      // hacer loop;
      while($rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
        if(!isset($encabezado)){
          $encabezado=1;
          echo "<br/>Número $rowMovimiento[Numero], acreditada el ".date_format($rowMovimiento['FechaAcreditacion'], "d/m/Y H:i:s");
          echo "<br/><br/>Acreditación NETA: ".sprintf("%.2f",$rowMovimiento['AcreditacionNeta'])."<br/>";
          if($rowMovimiento['NumeroCheque']>0)echo "Cheque Nº $rowMovimiento[NumeroCheque], $rowMovimiento[Nombre] (CP $rowMovimiento[Localidad])<br/>";
          if($rowMovimiento['Comisiones']>0)echo "Comisiones: $".sprintf("%.2f",$rowMovimiento['Comisiones'])."<br/>";
          if($rowMovimiento['RetencionIIB']>0)echo "Retencion IIBB: $".sprintf("%.2f",$rowMovimiento['RetencionIIB'])."<br/>";
          if($rowMovimiento['IVA']>0)echo "IVA: $".sprintf("%.2f",$rowMovimiento['IVA'])." ($rowMovimiento[MesImputacionIVACompras]/$rowMovimiento[AnioImputacionIVACompras])<br/>";
          if($rowMovimiento['RetencionIVA']>0)echo "Retencion IVA: $".sprintf("%.2f",$rowMovimiento['RetencionIVA'])."<br/>";
          if($rowMovimiento['RetencionGanancias']>0)echo "Retencion Ganancias: $".sprintf("%.2f",$rowMovimiento['RetencionGanancias'])."<br/>";
          if($rowMovimiento['TotalRechazadas']>0)echo "Total Rechazadas: $".sprintf("%.2f",$rowMovimiento['TotalRechazadas'])."<br/>";
          echo "<br>";
        }
        echo "$rowMovimiento[LotePrefijo]-$rowMovimiento[LoteNumero], $".sprintf("%.2f",$rowMovimiento['Importe'])." (Presentado ".date_format($rowMovimiento['Fecha'], "d/m/Y H:i:s").")<br/>";
      }
    } else {
      // PRESENTACIONES:
      if(!isset($_SESSION['TipoManejoTarjetasCredito'])||$_SESSION['TipoManejoTarjetasCredito']==0){
        $sqlMovimiento = "select LotePrefijo, LoteNumero, dbo.LotesTarjetasCredito.Fecha, dbo.CierresTurno.Fecha as fechaTurno, dbo.CierresTurno.IdCaja, Importe, Nombre from dbo.LotesTarjetasCredito, dbo.CierresTurno, dbo.TarjetasCredito where  dbo.LotesTarjetasCredito.IdCierreTurno=dbo.CierresTurno.IdCierreTurno AND  dbo.LotesTarjetasCredito.IdTarjeta=dbo.TarjetasCredito.IdTarjeta AND  dbo.LotesTarjetasCredito.IdAsiento=$_GET[idAsiento]";
        $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
        $rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
        
        echo "<br/>$rowMovimiento[Nombre] Lote $rowMovimiento[LotePrefijo]-$rowMovimiento[LoteNumero], ingresado en turno ".(($rowMovimiento['IdCaja']==1)?'PLAYA':'SHOP')." ". date_format($rowMovimiento['fechaTurno'], "d/m/Y H:i:s")." por $$rowMovimiento[Importe]";
        // select * from dbo.LotesAcreditados where IdLoteTarjetasCredito=135755
//         $sqlAcreditacion = 
        
        
        
      } elseif($_SESSION['TipoManejoTarjetasCredito']==1) {
        $sqlMovimiento = "select * from dbo.CuponesTarjetasCredito where IdAsiento=$_GET[idAsiento]";
        $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
        $rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
        echo "<br/>Número $rowMovimiento[NumeroCupon], ingresa en";

        if($rowMovimiento['idCliente']>0){
          $sql3 = "select * from dbo.Recibos where IdRecibo=$rowMovimiento[IdRecibo]";
          $stmt3 = sqlsrv_query( $mssql2, $sqlMovimiento);
          $row3 = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
          echo " recibo a <b>$rowMovimiento[RazonSocial]</b><br/>"; 
        } else {
          echo " turno <b>$rowMovimiento[RazonSocial]</b><br/>"; 
        }
      }
    }
    
    break;
  case "COBRANZAS":
    //select * from dbo.Recibos where IdAsiento
    echo "$rowAsiento[Descripcion], $rowAsiento[Concepto]";
    if(trim($rowAsiento['Nombre'])=='COBRANZAS01'){
      $sqlMovimiento = "select * from dbo.Recibos where IdAsiento=$_GET[idAsiento]";
      $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
      $rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
      /* print_r($rowMovimiento); */
      echo " ($rowMovimiento[IdCliente])<br/><br/>Recibo $rowMovimiento[PuntoVenta]-$rowMovimiento[Numero], cobrado el ".date_format($rowMovimiento['Fecha'], "d/m/Y H:i:s");
      
      if($rowMovimiento['Efectivo']>0)echo "<br/><br/><span class='".(($rowMovimiento['Efectivo']==$_GET['monto'])?'montoBuscado':'')."'>Efectivo: $$rowMovimiento[Efectivo]</span><br/>";
      
      $sqlCheque = "select * from dbo.ChequesTerceros where IdRecibo=$rowMovimiento[IdRecibo]";
      $stmt3 = sqlsrv_query( $mssql2, $sqlCheque);
      while($rowCheque = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)){
        if(!isset($tituloChequesPropios)){
          $tituloChequesPropios=true;
          echo "<br/><br/>Cheques de terceros:<br/>";
        }
        echo "<span class='".((($rowCheque['Importe']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowCheque['Importe']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowCheque['Importe']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Nº $rowCheque[Numero], $$rowCheque[Importe], ".date_format($rowCheque['Fecha'], "d/m/Y H:i:s").", CP $rowCheque[Localidad]</span><br/>";
        //print_r($rowCheque);

        // $rowCheque[IdCuentaBancaria] => 1
        // $rowCheque[CorrienteDiferido] => 0
      }
      $sqlCheque = "select * from dbo.CuponesTarjetasCredito where IdRecibo=$rowMovimiento[IdRecibo]";
      $stmt3 = sqlsrv_query( $mssql2, $sqlCheque);
      while($rowCheque = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)){
        if(!isset($tituloTarjetas)){
          $tituloTarjetas=true;
          echo "<br/><br/>Cupones de tarjetas:<br/>";
        }
        print_r($rowCheque);
        echo "<span class='".((($rowCheque['Importe']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowCheque['Importe']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowCheque['Importe']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Nº $rowCheque[NumeroCupon], $$rowCheque[Importe]</span><br/>";
      }			
    } else {
      // Cobranzas contado con tarjeta o cheques:
      $sqlMovimiento = "select *, dbo.CuponesTarjetasCredito.IdCierreTurno as cierre, dbo.CuponesTarjetasCredito.Importe as Importe2 from dbo.CuponesTarjetasCredito,  dbo.LotesTarjetasCredito where dbo.LotesTarjetasCredito.IdLoteTarjetasCredito=dbo.CuponesTarjetasCredito.IdLoteTarjetasCredito AND dbo.CuponesTarjetasCredito.IdAsiento=$_GET[idAsiento]";
      //$sqlMovimiento = "select *, dbo.CuponesTarjetasCredito.IdCierreTurno as cierre from dbo.CuponesTarjetasCredito where dbo.CuponesTarjetasCredito.IdAsiento=$_GET[idAsiento]";
      $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
      $rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
      if($rowMovimiento){
        //print_r2($rowMovimiento);
        echo "<br/><br/>Ingresa en";
        if(is_int($rowMovimiento["IdCliente"]) && $rowMovimiento["IdCliente"]>0){
          $sql3 = "select * from dbo.Recibos where IdRecibo=$rowMovimiento[IdRecibo]";
          $stmt3 = sqlsrv_query( $mssql2, $sql3);
          $row3 = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC);
          echo " recibo a <b>$rowMovimiento[RazonSocial]</b><br/>";
        } else {
          $sql3 = "select * from dbo.cierresTurno where IdCierreTurno=$rowMovimiento[cierre]";
          $stmt3 = sqlsrv_query( $mssql2, $sql3);
          $row3 = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC);
          echo " turno <b>".date_format($row3['Fecha'], "d/m/Y H:i:s").",</b> (".(($row3['IdEmpleado2']>0)?'+'.$_SESSION['empleados'][$row3['IdEmpleado2']]:'')." ".(($row3['IdEmpleado3']>0)?'+'.$_SESSION['empleados'][$row3['IdEmpleado3']]:'').")<br/>"; 
        }
        echo "<br/><span class='".((($rowMovimiento['Importe2']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['Importe2']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['Importe2']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Cupón Nº$rowMovimiento[NumeroCupon]: $$rowMovimiento[Importe2]</span><br/>";
        echo "Lote: $rowMovimiento[LotePrefijo]-$rowMovimiento[LoteNumero], total lote $ $rowMovimiento[Importe]";
      }
      $sqlCheque = "select * from dbo.ChequesTerceros where IdAsiento=$_GET[idAsiento]";
      $stmt2 = sqlsrv_query( $mssql2, $sqlCheque);
      $rowCheque = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
      if($rowCheque){
        echo "<br/><br/>Ingresa en";
        if($rowCheque['IdCliente']>0){
          $sql3 = "select * from dbo.Recibos where IdRecibo=$rowCheque[IdRecibo]";
          $stmt3 = sqlsrv_query( $mssql2, $sql3);
          $row3 = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC);
          echo " recibo a <b>$rowMovimiento[RazonSocial]</b><br/>";
        } else {
          $sql3 = "select * from dbo.cierresTurno where IdCierreTurno=$rowCheque[IdCierreTurno]";
          $stmt3 = sqlsrv_query( $mssql2, $sql3);
          $row3 = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC);
          echo " turno <b>".date_format($row3['Fecha'], "d/m/Y H:i:s").",</b> (".(($row3['IdEmpleado2']>0)?'+'.$_SESSION['empleados'][$row3['IdEmpleado2']]:'')." ".(($row3['IdEmpleado3']>0)?'+'.$_SESSION['empleados'][$row3['IdEmpleado3']]:'').")<br/>"; 
        }
        echo "<br/><span class='".((($rowCheque['Importe']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowCheque['Importe']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowCheque['Importe']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Nº $rowCheque[Numero], $$rowCheque[Importe], ".date_format($rowCheque['Fecha'], "d/m/Y H:i:s").", CP $rowCheque[Localidad]</span><br/>";
      }
    }
    break;
  case "VALES":
    echo "$rowAsiento[Descripcion], $rowAsiento[Concepto]";
    // CANCELA

    // EMITE
    
    break;
  case "BANCOS":
    echo $rowAsiento['Descripcion'].', '.$rowAsiento['Concepto'];
    $sqlMovimiento = "select IdMovimientoBancario, IdCuentaBancaria, FechaContable, TiposMovimientoBancario.Descripcion, Detalle, NumeroComprobante, Importe, dbo.CierresCajaTesoreria.Numero, dbo.CierresCajaTesoreria.FechaCierre from dbo.MovimientosBancarios, TiposMovimientoBancario, dbo.CierresCajaTesoreria where dbo.MovimientosBancarios.IdCierreCajaTesoreria=dbo.CierresCajaTesoreria.IdCierreCajaTesoreria AND dbo.MovimientosBancarios.IdTipoMovimientoBancario=dbo.TiposMovimientoBancario.IdTipoMovimientoBancario AND IdAsiento=$_GET[idAsiento]";
    $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
    $rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
    echo "<br/>$rowMovimiento[Detalle]<br/>";
    if($rowMovimiento['FechaContable'])
        {echo "<br/>Cargado el ".date_format($rowMovimiento['FechaContable'], "d/m/Y H:i:s").'<br/>';}
    else 
        {echo "<br/>Cargado manualmente, no tiene fecha (REVISAR)<br/>";}
    echo "<br/>Movimiento: $rowMovimiento[Descripcion], en Caja Nº $rowMovimiento[Numero] del ".date_format($rowMovimiento['FechaCierre'], "d/m/Y H:i:s").'<br/>';
    $sqlCheque = "select * from dbo.ChequesTerceros, dbo.Bancos where IdMovimientoBancario=$rowMovimiento[IdMovimientoBancario] AND dbo.ChequesTerceros.IdBanco=dbo.Bancos.IdBanco";
    $stmt3 = sqlsrv_query( $mssql2, $sqlCheque);
    $totalCheques=0;
    while($rowCheque = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloChequesPropios)){
        $tituloChequesPropios=true;
        echo "<br/>Cheques de terceros:<br/><table style='width:100%'>";
      }
      $totalCheques+=$rowCheque['Importe'];
      echo "<tr class='".((($rowCheque['Importe']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowCheque['Importe']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowCheque['Importe']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'><td>$rowCheque[Nombre]</td><td>Nº $rowCheque[Numero]</td><td align=right><b>$".sprintf("%01.2f", $rowCheque['Importe'])."</b></td><td align=center>".date_format($rowCheque['Fecha'], "d/m/Y")."</td><td>($rowCheque[Localidad])</td></tr>";
      //print_r($rowCheque);

      // $rowCheque[IdCuentaBancaria] => 1
      // $rowCheque[CorrienteDiferido] => 0
    }
    if(isset($tituloChequesPropios)&&$tituloChequesPropios)
      {echo"</table>";}
    if($rowMovimiento['Importe']>$totalCheques)
      {echo "<br/>Efectivo: ".($rowMovimiento['Importe']-$totalCheques);}
  break;
  case "COMPRAS":
    echo "$rowAsiento[Descripcion], $rowAsiento[Concepto]";
    $sqlMovimiento = "select * from dbo.MovimientosPro where IdAsiento=$_GET[idAsiento]";
//     //echo $sqlMovimiento;
    $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
    $rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
    if(is_array($rowMovimiento)){
      echo ", $rowMovimiento[IdTipoMovimientoProveedor] <b>$rowMovimiento[PuntoVenta]-$rowMovimiento[Numero]</b>, cargada el ".date_format($rowMovimiento['Fecha'], "d/m/Y H:i:s");
      echo "<br/><br/>$rowMovimiento[RazonSocial] ($rowMovimiento[IdProveedor]), CUIT $rowMovimiento[NumeroDocumento]<br/><br/>";
      if($rowMovimiento['NetoNoGravado']>0)echo "<span class='".((($rowMovimiento['NetoNoGravado']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['NetoNoGravado']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['NetoNoGravado']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>No gravado: $$rowMovimiento[NetoNoGravado]</span><br/>";
      
      if($rowMovimiento['NetoMercaderias']>0)echo "<span class='".((($rowMovimiento['NetoMercaderias']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['NetoMercaderias']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['NetoMercaderias']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Mercaderías: $$rowMovimiento[NetoMercaderias]</span><br/>";
      
      if($rowMovimiento['NetoCombustibles']>0)echo "<span class='".((($rowMovimiento['NetoCombustibles']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['NetoCombustibles']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['NetoCombustibles']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>NetoCombustibles: $$rowMovimiento[NetoCombustibles]</span><br/>";
      
      if($rowMovimiento['NetoLubricantes']>0)echo "<span class='".((($rowMovimiento['NetoLubricantes']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['NetoLubricantes']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['NetoLubricantes']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Lubricantes: $$rowMovimiento[NetoLubricantes]</span><br/>";
      
      if($rowMovimiento['NetoFinanciacion']>0)echo "<span class='".((($rowMovimiento['NetoFinanciacion']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['NetoFinanciacion']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['NetoFinanciacion']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Neto Financiacion: $$rowMovimiento[NetoFinanciacion]</span><br/>";
      
      if($rowMovimiento['NetoFletes']>0)echo "<span class='".((($rowMovimiento['NetoFletes']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['NetoFletes']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['NetoFletes']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Neto Fletes: $$rowMovimiento[NetoFletes]</span><br/>";
      
      if($rowMovimiento['NetoGastos']>0)echo "<span class='".((($rowMovimiento['NetoGastos']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['NetoGastos']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['NetoGastos']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Neto Gastos: $$rowMovimiento[NetoGastos]</span><br/>";
      
      if($rowMovimiento['IVA3']>0)echo "<span class='".((($rowMovimiento['IVA3']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['IVA3']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['IVA3']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>IVA 3: $$rowMovimiento[IVA3] ($rowMovimiento[Mes]/$rowMovimiento[Anio])</span><br/>";
      
      if($rowMovimiento['IVA1']>0)echo "<span class='".((($rowMovimiento['IVA1']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['IVA1']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['IVA1']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>IVA 21%: $$rowMovimiento[IVA1] ($rowMovimiento[Mes]/$rowMovimiento[Anio])</span><br/>";
      
      if($rowMovimiento['IVA2']>0)echo "<span class='".((($rowMovimiento['IVA2']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['IVA2']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['IVA2']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>IVA 2: $$rowMovimiento[IVA2] ($rowMovimiento[Mes]/$rowMovimiento[Anio])</span><br/>";
      
      if($rowMovimiento['ImpuestoInterno']>0)echo "<span class='".((($rowMovimiento['ImpuestoInterno']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['ImpuestoInterno']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['ImpuestoInterno']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Impuesto Interno: $$rowMovimiento[ImpuestoInterno]</span><br/>";
      
      if($rowMovimiento['Tasas']>0)echo "<span class='".((($rowMovimiento['Tasas']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['Tasas']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['Tasas']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Tasas: $$rowMovimiento[Tasas]</span><br/>";
      
      if($rowMovimiento['PercepcionIIBB']>0)echo "<span class='".((($rowMovimiento['PercepcionIIBB']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['PercepcionIIBB']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['PercepcionIIBB']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Percepcion IIBB: $$rowMovimiento[PercepcionIIBB]</span><br/>";
      
      if($rowMovimiento['PercepcionIVA']>0)echo "<span class='".((($rowMovimiento['PercepcionIVA']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['PercepcionIVA']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['PercepcionIVA']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Percepcion IVA: $$rowMovimiento[PercepcionIVA]</span><br/>";
      
      if($rowMovimiento['PercepcionOtras']>0)echo "<span class='".((($rowMovimiento['PercepcionOtras']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['PercepcionOtras']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['PercepcionOtras']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>PercepcionOtras: $$rowMovimiento[PercepcionOtras]</span><br/>";
      
      if($rowMovimiento['RetencionIVA']>0)echo "<span class='".((($rowMovimiento['RetencionIVA']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['RetencionIVA']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['RetencionIVA']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>RetencionIVA: $$rowMovimiento[RetencionIVA]</span><br/>";
      
      if($rowMovimiento['RetencionGanancias']>0)echo "<span class='".((($rowMovimiento['RetencionGanancias']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['RetencionGanancias']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['RetencionGanancias']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>RetencionGanancias: $$rowMovimiento[RetencionGanancias]</span><br/>";
      
      if($rowMovimiento['RetencionOtras']>0)echo "<span class='".((($rowMovimiento['RetencionOtras']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['RetencionOtras']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['RetencionOtras']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>RetencionOtras: $$rowMovimiento[RetencionOtras]</span><br/>";
      
      if($rowMovimiento['RetencionIIBB']>0)echo "<span class='".((($rowMovimiento['RetencionIIBB']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['RetencionIIBB']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['RetencionIIBB']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>RetencionIIBB: $$rowMovimiento[RetencionIIBB]</span><br/>";
      
      if($rowMovimiento['Total']>0)echo "<span class='".(($rowMovimiento['Total']==$_GET['monto'])?'montoBuscado':'')."'><b>Total: $$rowMovimiento[Total]</b></span><br/>";
      
      if($rowMovimiento['PagoEfectivo']>0)echo "<span class='".((($rowMovimiento['PagoEfectivo']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['PagoEfectivo']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['PagoEfectivo']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'><b>Pagado en efectivo: $$rowMovimiento[PagoEfectivo]</b></span><br/>";
      $sqlMovimiento2 = "select * from dbo.MovimientosDetallePro where IdMovimientoPro=$rowMovimiento[IdMovimientoPro]";
      //echo $sqlMovimiento2;
      $stmt3 = sqlsrv_query( $mssql2, $sqlMovimiento2);
      while($rowMovimiento2 = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)){
        echo "<br>Descripcion: <b>$rowMovimiento2[Descripcion]</b><br/>";if(strlen($rowMovimiento2['Descripcion'])<4)echo "<i>(Te mataste con el detalle)</i>";
      }
      
    } else {
      // está anulado
      $sqlAnulado = "SELECT IdAsiento FROM dbo.asientos WHERE IdAsientoAnulado=$_GET[idAsiento]";
      $stmt3 = sqlsrv_query( $mssql2, $sqlAnulado);
      $rowAnulado = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC);
      echo "<br/><b>ANULADO CON ASIENTO $rowAnulado[IdAsiento]</b>";
    }
    break;
  case "IMPUESTOS":
    echo "$tipo";
    break;
  case "PAGOS":
    echo "$rowAsiento[Descripcion], $rowAsiento[Concepto]";
    $sqlMovimiento = "select * from dbo.OrdenesPago where IdAsiento=$_GET[idAsiento]";echo "<br>$sqlMovimiento<br>";
    $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
    $rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
    echo " ($rowMovimiento[IdProveedor])<br/><br/>";
    echo "Orden de pago Nº $rowMovimiento[Numero], emitida ".date_format($rowMovimiento['Fecha'], "d/m/Y H:i:s")."<br>";
    if($rowMovimiento['TotalRetencionIIBB']>0)echo "Retencion IIBB: $$rowMovimiento[TotalRetencionIIBB]<br/>";
    if($rowMovimiento['TotalAPagar']>0)echo "<span class='".(($rowMovimiento['TotalAPagar']==$_GET['monto'])?'montoBuscado':'')."'>Total a pagar: $$rowMovimiento[TotalAPagar]</span><br/>";
    
    if($rowMovimiento['PagoEfectivo']>0){
            echo "<br/><span class='".((($rowMovimiento['PagoEfectivo']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['PagoEfectivo']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['PagoEfectivo']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Efectivo de caja: $$rowMovimiento[PagoEfectivo]</span><br/>";
    }
    $sqlCheque = "select * from dbo.ChequesPropios where IdOrdenPago=$rowMovimiento[IdOrdenPago]";
    $stmt3 = sqlsrv_query( $mssql2, $sqlCheque);
    while($rowCheque = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)){
            if(!isset($tituloChequesPropios)){
                    $tituloChequesPropios=true;
                    echo "<br/>Cheques propios:<br/>";
            }
            echo "<span class='".((($rowCheque['Importe']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowCheque['Importe']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowCheque['Importe']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Nº $rowCheque[Numero], $$rowCheque[Importe], ".date_format($rowCheque['FechaEmision'], "d/m/Y H:i:s")."</span><br/>";
            //print_r($rowCheque);

            // $rowCheque[IdCuentaBancaria] => 1
            // $rowCheque[CorrienteDiferido] => 0
    }
    $sqlCheque = "select * from dbo.ChequesTerceros where IdOrdenPago=$rowMovimiento[IdOrdenPago]";
    $stmt3 = sqlsrv_query( $mssql2, $sqlCheque);
    while($rowCheque = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)){
            if(!isset($tituloChequesPropios)){
                    $tituloChequesPropios=true;
                    echo "<br/>Cheques de terceros:<br/>";
            }
            echo "<span class='".((($rowCheque['Importe']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowCheque['Importe']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowCheque['Importe']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Nº $rowCheque[Numero], $$rowCheque[Importe], ".date_format($rowCheque['Fecha'], "d/m/Y H:i:s").", CP $rowCheque[Localidad]</span><br/>";
            //print_r($rowCheque);

            // $rowCheque[IdCuentaBancaria] => 1
            // $rowCheque[CorrienteDiferido] => 0
    }
    //$rowMovimiento[PagoEfectivo] => .0000
    //$rowMovimiento[PagoEfectivoTipo] => 0
    //$rowMovimiento[PagoTarjeta] => .0000
    //$rowMovimiento[IdTarjeta] => 
    break;
  case "STOCK":
    echo "$tipo<br/>";
    echo "Descripcion: $rowAsiento[Descripcion]<br/>Concepto: $rowAsiento[Concepto]";

    $sqlMovimiento = "select * from dbo.OtrosMovimientosStock, dbo.MotivosStock where MotivosStock.IdMotivoStock=OtrosMovimientosStock.IdMotivo AND IdAsiento=$_GET[idAsiento];";
    $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
            $rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
            
    echo "<br/>Movimiento: $rowMovimiento[Descripcion], Número $rowMovimiento[IdTipoMovimiento]-$rowMovimiento[Numero]<br/>";
    break;
  case "TESORERIA":
          echo "$rowAsiento[Descripcion], $rowAsiento[Concepto]";
          $sqlMovimiento = "select dbo.OtrosMovimientosCajaTesoreria.IdOtroMovimientoCajaTesoreria, dbo.OtrosMovimientosCajaTesoreria.Fecha, Detalle, Importe, Efectivo, Descripcion, dbo.CierresCajaTesoreria.Numero, dbo.CierresCajaTesoreria.FechaCierre from dbo.OtrosMovimientosCajaTesoreria, dbo.GruposOtrosMovimientosCajaTesoreria, dbo.CierresCajaTesoreria where dbo.GruposOtrosMovimientosCajaTesoreria.IdGrupoOtrosMovimientosCajaTesoreria=dbo.OtrosMovimientosCajaTesoreria.IdGrupoOtrosMovimientosCajaTesoreria AND dbo.CierresCajaTesoreria.IdCierreCajaTesoreria=dbo.OtrosMovimientosCajaTesoreria.IdCierreCajaTesoreria AND IdAsiento=$_GET[idAsiento]";
          $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
          $rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
          echo "<br/>$rowMovimiento[Detalle]<br/>";
          echo "<br/>Cargado el ".date_format($rowMovimiento['Fecha'], "d/m/Y H:i:s").'<br/>';
          echo "<br/>Movimiento: $rowMovimiento[Descripcion], en Caja Nº $rowMovimiento[Numero] del ".date_format($rowMovimiento['FechaCierre'], "d/m/Y H:i:s").'<br/>';
          $sqlCheque = "select * from dbo.ChequesPropios where IdOtroMovimientoCajaTesoreriaSalida=$rowMovimiento[IdOtroMovimientoCajaTesoreria]";
          $stmt3 = sqlsrv_query( $mssql2, $sqlCheque);
          while($rowCheque = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)){
                  if(!isset($tituloChequesPropios)){
                          $tituloChequesPropios=true;
                          echo "<br/>Cheques propios:<br/>";
                  }
                  echo "<span class='".((($rowCheque['Importe']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowCheque['Importe']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowCheque['Importe']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Nº $rowCheque[Numero], $$rowCheque[Importe], ".date_format($rowCheque['FechaEmision'], "d/m/Y H:i:s")."</span><br/>";
                  //print_r($rowCheque);

                  // $rowCheque[IdCuentaBancaria] => 1
                  // $rowCheque[CorrienteDiferido] => 0
          }
          $sqlCheque = "select * from dbo.ChequesTerceros where IdOtroMovimientoCajaTesoreriaEntrada=$rowMovimiento[IdOtroMovimientoCajaTesoreria] OR IdOtroMovimientoCajaTesoreriaSalida=$rowMovimiento[IdOtroMovimientoCajaTesoreria]";
          $stmt3 = sqlsrv_query( $mssql2, $sqlCheque);
          while($rowCheque = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)){
                  if(!isset($tituloChequesTerceros)){
                          $tituloChequesTerceros=true;
                          echo "<br/>Cheques de terceros:<br/>";
                  }
                  echo "<span class='".((($rowCheque['Importe']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowCheque['Importe']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowCheque['Importe']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Nº $rowCheque[Numero], $$rowCheque[Importe], ".date_format($rowCheque['Fecha'], "d/m/Y H:i:s").", CP $rowCheque[Localidad]</span><br/>";
                  }
          if(!isset($tituloChequesPropios)&&!isset($tituloChequesTerceros)){
                  echo "<br/><span class='".((($rowMovimiento['Importe']==$_GET['monto'])||($_REQUEST['fuzzy']&&$rowMovimiento['Importe']>=floor($_GET['monto']-$_REQUEST['fuzziness'])&&$rowMovimiento['Importe']<=ceil($_GET['monto']+$_REQUEST['fuzziness'])))?'montoBuscado':'')."'>Efectivo de caja $$rowMovimiento[Importe]</span><br/>";
          }
          break;
  case "CARGARV":
          echo "$rowAsiento[Descripcion], $rowAsiento[Concepto]";
          $sqlMovimiento = "select * from dbo.OtrosMovimientosCajaTesoreria where IdAsiento=$_GET[idAsiento]";
          $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
          $rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
          print_r($rowMovimiento);
          echo "$tipo";
          break;
  case "VARIOS":
          echo "$rowAsiento[Descripcion], $rowAsiento[Concepto]";
          $sqlMovimiento = "select * from dbo.OtrosMovimientosCajaTesoreria where IdAsiento=$_GET[idAsiento]";
          $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
          $rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
          print_r($rowMovimiento);
          echo "$tipo";
          break;
  case "SUELDOS":
          echo "$rowAsiento[Descripcion], $rowAsiento[Concepto]";
          $sqlMovimiento = "select * from dbo.OtrosMovimientosCajaTesoreria where IdAsiento=$_GET[idAsiento]";
          $stmt2 = sqlsrv_query( $mssql2, $sqlMovimiento);
          $rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
          print_r($rowMovimiento);
          echo "$tipo";
          break;
  case "MANUAL":
          echo "Asiento manual / sin modelo de asiento";
          
          break;
  default:
          echo "$tipo";
          break;
		
}
fb($sqlMovimiento);


?>
