<?php
// calculaPromedios.php
include_once('../include/inicia.php');

$limit=11;
$offset=0;



$andFecha=(isset($_REQUEST['rangoInicio']))?" AND femision>='$_REQUEST[rangoInicio]' AND femision<='$_REQUEST[rangoFin]'":'';
// if(isset($_REQUEST['mes'])&&$_REQUEST['mes']<>''){
//   $mesFin= date("Y-m-t", strtotime($_REQUEST['mes']));
//   $andFecha=" AND femision>='$_REQUEST[mes]' AND femision<='$mesFin'";
// }

$andConciliado = (isset($_POST['conciliado']))?" AND conciliado=1":"";


$sqlCalden = "select m.IdMovimientoPro as id, Fecha, IdTipoMovimientoProveedor as Tipo, PuntoVenta as pv, Numero, Total, Total as PagoEfectivo, 0 as ReemplazaRechazado, 0 as Rechazado from dbo.movimientospro as m where IdProveedor=4 and m.fecha>='2016-01-01' and m.fecha<='2016-12-31' UNION select IdOrdenPago as id, Fecha, 'OP' as Tipo, Prefijo as pv, Numero, TotalAPagar as Total, PagoEfectivo, 0 as ReemplazaRechazado, 0 as Rechazado from dbo.OrdenesPago where idproveedor=4 and fecha>='2016-01-01' and fecha<='2016-12-31' UNION select IdChequeTercero as id, o.Fecha, 'Cheque' as Tipo, IdBanco as pv, o.Numero, Importe as Total, Importe as PagoEfectivo, ReemplazaRechazado, Rechazado from dbo.ChequesTerceros as c, dbo.OrdenesPago as o WHERE o.IdProveedor=4 AND c.IdOrdenPago=o.IdOrdenPago AND o.Fecha>='2016-01-01' AND o.Fecha<='2016-12-31' UNION select IdTransferenciaBancaria as id, o.Fecha, 'Banco' as Tipo, IdCuentaBancaria as pv, o.Numero, Importe as Total, Importe as PagoEfectivo, 0 as ReemplazaRechazado, 0 as Rechazado from dbo.TransferenciasBancarias as t, dbo.OrdenesPago as o where o.IdProveedor=4 AND o.IdOrdenPago=t.IdOrdenPago AND o.Fecha>='2016-01-01' AND o.Fecha<='2016-12-31' ORDER BY Fecha ASC;";

//fb($sqlCalden);

$stmt = sqlsrv_query( $mssql, $sqlCalden);


$tabla = "";$a=0;$q=0;
echo "<tbody>";
while($rowCalden = sqlsrv_fetch_array($stmt)){
  if(trim($rowCalden['Tipo'])=='OP'&&$rowCalden['PagoEfectivo']==0){
    
    // no hago nada.
  } else {
    $clase = ($rowCalden['Tipo']=='OP'||$rowCalden['Tipo']=='Cheque'||$rowCalden['Tipo']=='Banco')?"success":'danger';
    if($rowCalden['Tipo']=='Banco'){
      // muestro que banco es
      if($rowCalden['pv']==1){
        // epago
        $rowCalden['Tipo']='ePago';
      } elseif($rowCalden['pv']==13){
        // visa
        $rowCalden['Tipo']='Visa';
      } 
    }
    if(trim($rowCalden['Tipo'])=='RV'){
      $rowCalden['pv'] = sprintf('%04d', $rowCalden['pv']);
      $rowCalden['Numero'] = sprintf('%06d', $rowCalden['Numero']);
    }
    // busco si está conciliado o no
    $sqlConciliacion = "SELECT * FROM conciliacion WHERE idCalden=$rowCalden[id] AND tipoCalden='".strtolower($rowCalden['Tipo'])."'";
    
    $result3 = $mysqli3->query($sqlConciliacion);
    $rowConciliado = $result3->fetch_assoc();
    //fb($rowConciliado);
    if($rowConciliado['idConciliado']==0){
      // aun no conciliado
      $classConciliado='noConciliado2';
      $clase='info';
      $tdConciliado = "<td class=''><input type='checkbox' name='idcalden[]' value='$rowCalden[id]' class='calden' rel='$rowCalden[PagoEfectivo]'/></td>";
    } else {
      $classConciliado="conciliado_$rowConciliado[idConciliado]";
      $tdConciliado = "<td class='mConciliado n$rowConciliado[idConciliado]'><span class='label label-info'>$rowConciliado[idConciliado]</label></td>";
    }
    if(isset($_POST['soloNoConciliado'])&&$rowConciliado['idConciliado']<>0){

    } else {
      echo "<tr class='alert alert-$clase $classConciliado' id='calden_$rowCalden[id]'>$tdConciliado<td>".date_format($rowCalden['Fecha'], 'd/m/Y')."</td><td>$rowCalden[Tipo]</td><td>$rowCalden[pv] $rowCalden[Numero]</td><td id='i$rowCalden[id]'>$".number_format($rowCalden['PagoEfectivo'], 2, ",", ".")."</td><td>$rowCalden[Rechazado]</td></tr>";
    }            
  }
}
echo "</tbody>";
if(!isset($clase)){echo "<tbody><tr><td colspan='4'>No hay resultados</td></tr></tbody>";}
?>
