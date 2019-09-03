<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$andFecha=(isset($_REQUEST['rangoInicio']))?" AND femision>='$_REQUEST[rangoInicio]' AND femision<='$_REQUEST[rangoFin]'":'';
// if(isset($_REQUEST['mes'])&&$_REQUEST['mes']<>''){
//   $mesFin= date("Y-m-t", strtotime($_REQUEST['mes']));
//   $andFecha=" AND femision>='$_REQUEST[mes]' AND femision<='$mesFin'";
// }

$andConciliado = (isset($_POST['conciliado']))?" AND conciliado=1":"";
if(!isset($_POST['anio'])){
  $inicio = '2018-12-01';
  $fin = "2019-12-31";
} else {
  $inicio = $_POST['anio'].'-01-01';
  $fin = $_POST['anio']."-12-31";
}

$sqlCalden = "select m.IdMovimientoPro as id, Fecha, IdTipoMovimientoProveedor as Tipo, PuntoVenta as pv, Numero, Total, Total as PagoEfectivo, 0 as ReemplazaRechazado, 0 as Rechazado FROM dbo.movimientospro as m where idproveedor IN (4, 422) and m.fecha>='$inicio' and m.fecha<='$fin' UNION 

select IdOrdenPago as id, Fecha, 'OP' as Tipo, Prefijo as pv, Numero, TotalAPagar as Total, PagoEfectivo, 0 as ReemplazaRechazado, 0 as Rechazado FROM dbo.OrdenesPago where idproveedor IN (4, 422) and fecha>='$inicio' and fecha<='$fin' UNION 

select IdChequeTercero as id, o.Fecha, 'Cheque' as Tipo, IdBanco as pv, o.Numero, Importe as Total, Importe as PagoEfectivo, ReemplazaRechazado, Rechazado FROM dbo.ChequesTerceros as c, dbo.OrdenesPago as o WHERE o.idproveedor IN (4, 422) AND c.IdOrdenPago=o.IdOrdenPago AND o.Fecha>='$inicio' AND o.Fecha<='$fin' UNION 

select IdTransferenciaBancaria as id, t.Fecha, 'Banco' as Tipo, IdCuentaBancaria as pv, o.Numero, Importe as Total, Importe as PagoEfectivo, 0 as ReemplazaRechazado, 0 as Rechazado FROM dbo.TransferenciasBancarias as t, dbo.OrdenesPago as o where o.IdProveedor IN (4, 422) AND o.IdOrdenPago=t.IdOrdenPago AND o.Fecha>='$inicio' AND o.Fecha<='$fin' ORDER BY Fecha ASC;";

ChromePhp::log($sqlCalden);

$stmt = odbc_exec2( $mssql, $sqlCalden, __LINE__, __FILE__);


$tabla = "";$a=0;$q=0;
echo "<tbody>";
while($rowCalden = sqlsrv_fetch_array($stmt)){
  $rowCalden['Tipo']=trim($rowCalden['Tipo']);
  $rowCalden['MuestraTipo']=$rowCalden['Tipo'];
  if(trim($rowCalden['Tipo'])=='OP'&&$rowCalden['PagoEfectivo']==0){
    
    // no hago nada.
  } else {
    $clase = ($rowCalden['Tipo']=='OP'||$rowCalden['Tipo']=='Cheque'||$rowCalden['Tipo']=='Banco'||$rowCalden['Tipo']=='AJU'||$rowCalden['Tipo']=='AJU'||$rowCalden['Tipo']=='VP')?"success":'danger';
    
    $negativo = ($rowCalden['Tipo']=='OP'||$rowCalden['Tipo']=='Cheque'||$rowCalden['Tipo']=='Banco'||$rowCalden['Tipo']=='AJU')?'':'neg';
    if($rowCalden['Tipo']=='AJU'||$rowCalden['Tipo']=='AJN')$rowCalden['Tipo']='AV';
    if($rowCalden['Tipo']=='VP'){
      $rowCalden['Tipo']='RV';
    }/* elseif($rowCalden['Tipo']=='NDI'){
      $rowCalden['Tipo']='faa';
    }*/
    if($rowCalden['Tipo']=='Banco'){
      // muestro que banco es
      if($rowCalden['pv']==1||$rowCalden['pv']==5||$rowCalden['pv']==16){
        // epago
        $rowCalden['Tipo']='ePago';
      } elseif($rowCalden['pv']==13){
        // visa
        $rowCalden['Tipo']='Visa';
      } elseif($rowCalden['pv']==16){
        // visa
        $rowCalden['Tipo']='Banco';
      } 
    }
    if(trim($rowCalden['Tipo'])=='RV'){
      $rowCalden['pv'] = sprintf('%04d', $rowCalden['pv']);
      $rowCalden['Numero'] = sprintf('%06d', $rowCalden['Numero']);
    }
    // busco si está conciliado o no
    $sqlConciliacion = "SELECT * FROM conciliacion WHERE idCalden=$rowCalden[id] AND (tipoCalden in ('".strtolower($rowCalden['Tipo'])."','banco','visa') OR (tipoCalden='op' AND auto=0) OR (tipoCalden='av' AND auto=0) OR (tipoCalden='VP'))";
    if($rowCalden['id']==12831){
      ChromePhp::log($sqlConciliacion);
      ChromePhp::log($rowCalden);
      //print_r($rowCalden);
    }
    $result3 = $mysqli3->query($sqlConciliacion);
    $rowConciliado = $result3->fetch_assoc();
    //ChromePhp::log$rowConciliado);
    @$cuantos++;
    if($rowConciliado['idConciliado']==0){
      // aun no conciliado
      $classConciliado='noConciliado2';
      $clase=(isset($_POST['soloNoConciliado'])&&$rowConciliado['idConciliado']<>0)?'info':$clase;
      $tdConciliado = "<td class=''>$cuantos<input type='checkbox' name='idcalden[]' value='$rowCalden[id]' class='calden $negativo' rel='$rowCalden[PagoEfectivo]'/> $rowCalden[id]</td>";
    } else {
      $classConciliado="conciliado_$rowConciliado[idConciliado]";
      $tdConciliado = "<td class='m$rowConciliado[idConciliado]'><span class='label label-info mConciliado'>$rowConciliado[idConciliado]</label></td>";
    }
    if(isset($_POST['soloNoConciliado'])&&$rowConciliado['idConciliado']<>0){
      $cuantos--;
    } else {
      echo "<tr class='alert alert-$clase $classConciliado' id='calden_$rowCalden[id]'>$tdConciliado<td>".fecha($rowCalden['Fecha'])."</td><td>$rowCalden[MuestraTipo]</td><td>$rowCalden[pv] $rowCalden[Numero]</td><td id='i$rowCalden[id]'>$".number_format($rowCalden['PagoEfectivo'], 2, ",", ".")."</td><td>".(($rowCalden['Rechazado'])?'Rech':'')."</td></tr>";
    }            
  }
}
echo "</tbody>";
if(!isset($clase)){echo "<tbody><tr><td colspan='4'>No hay resultados</td></tr></tbody>";}
?>
