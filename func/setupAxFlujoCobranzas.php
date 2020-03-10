<?php
header_remove('Set-Cookie');
// setupFlujoBanco.php
// Lista los viajes que no se han liquidados a una fecha determinada
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
// print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';
$d = substr($_POST['mes'],0,4).'-'.substr($_POST['mes'],4,2).'-01';


$desdeRecibos=date("Y-m", strtotime("-12 months")).'-01 00:00:00';
$hastaRecibos=date("Y-m", strtotime("+1 months")).'-01 00:00:00';
$desdeRecibos=date("Y-m", strtotime($d)).'-01 00:00:00';
$hastaRecibos=date("Y-m-t", strtotime($d)). ' 23:59:59';

$desdeFacturas=date("Y-m", strtotime("-24 months")).'-01 00:00:00';
$hastaFacturas=date("Y-m").'-01 00:00:00';
$hastaFacturas= $d .' 23:59:59';
$hastaFacturas=$hastaRecibos;

$desdeVencimiento=date("Y-m", strtotime("-24 months")).'-01 00:00:00';
$hastaVencimiento=date("Y-m", strtotime("+2 months")).'-01 00:00:00';

$idCliente = (!isset($_POST['idCliente']))?"histoven.cliente>=0 AND histoven.cliente<=999999":"histoven.cliente=$_POST[idCliente]";
 
 
// $sqlFlujoBanco = "SELECT histoven.emision as factura_emision, histoven.vencimien, histoven.comprobant as factura_comprobant, histoven.tipo, histoven.sucursal as factura_sucursal, histoven.numero as factura_numero, CONDICIO.detalle, clientes.codigo, clientes.nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre, histoven.importe as factura_importe, Recibos.emision as recibo_emision, Recibos.sucursal as recibo_sucursal, Recibos.numero as recibo_numero, Recibos.tipo, impuvent.importe as recibo_importe, DATEDIFF(d, histoven.emision, recibos.emision) AS qDias FROM (((((sqlcoop_dbimplemen.dbo.histoven histoven LEFT OUTER JOIN sqlcoop_dbimplemen.dbo.impuvent impuvent ON (((histoven.comprobant=impuvent.comprobant) AND (histoven.tipo=impuvent.tipo)) AND (histoven.sucursal=impuvent.sucursal)) AND (histoven.numero=impuvent.numero))) LEFT OUTER JOIN sqlcoop_dbimplemen.dbo.CONDICIO CONDICIO ON histoven.condicion=CONDICIO.codigo) LEFT OUTER JOIN sqlcoop_dbimplemen.dbo.clientes clientes ON histoven.cliente=clientes.codigo) LEFT OUTER JOIN sqlcoop_dbimplemen.dbo.acobrar acobrar ON (((histoven.comprobant=acobrar.comprobant) AND (histoven.tipo=acobrar.tipo)) AND (histoven.sucursal=acobrar.sucursal)) AND (histoven.numero=acobrar.numero))   LEFT OUTER JOIN sqlcoop_dbimplemen.dbo.histoven Recibos ON (((impuvent.comprobani=Recibos.comprobant) AND (impuvent.tipoi=Recibos.tipo)) AND (impuvent.sucursali=Recibos.sucursal)) AND (impuvent.numeroi=Recibos.numero) WHERE  histoven.comprobant IN ('FACTURA', 'NOTA DE CREDITO', 'NOTA DE DEBITO') AND ($idCliente) AND (histoven.emision>={ts '$desdeFacturas'} AND histoven.emision<{ts '$hastaFacturas'}) AND (histoven.vencimien>={ts '$desdeVencimiento'} AND histoven.vencimien<{ts '$hastaVencimiento'}) AND (Recibos.emision>={ts '$desdeRecibos'} AND Recibos.emision<={ts '$hastaRecibos'}) ORDER BY Recibos.emision DESC, histoven.cliente, histoven.emision";


$sqlFlujoBanco = "SELECT histoven.emision as factura_emision, histoven.vencimien as factura_vencimiento, histoven.comprobant as factura_comprobant, histoven.tipo, histoven.sucursal as factura_sucursal, histoven.numero as factura_numero, CONDICIO.detalle, clientes.codigo, clientes.nombre  as nombre, histoven.importe as factura_importe, Recibos.emision as recibo_emision, Recibos.sucursal as recibo_sucursal, Recibos.numero as recibo_numero, Recibos.tipo, importei as recibo_importe, DATEDIFF(d, histoven.emision, recibos.emision) AS qDias FROM (((((sqlcoop_dbimplemen.dbo.histoven histoven LEFT OUTER JOIN sqlcoop_dbimplemen.dbo.impuvent impuvent ON (((histoven.comprobant=impuvent.comprobant) AND (histoven.tipo=impuvent.tipo)) AND (histoven.sucursal=impuvent.sucursal)) AND (histoven.numero=impuvent.numero))) LEFT OUTER JOIN sqlcoop_dbimplemen.dbo.CONDICIO CONDICIO ON histoven.condicion=CONDICIO.codigo) LEFT OUTER JOIN sqlcoop_dbimplemen.dbo.clientes clientes ON histoven.cliente=clientes.codigo) LEFT OUTER JOIN sqlcoop_dbimplemen.dbo.acobrar acobrar ON (((histoven.comprobant=acobrar.comprobant) AND (histoven.tipo=acobrar.tipo)) AND (histoven.sucursal=acobrar.sucursal)) AND (histoven.numero=acobrar.numero))   LEFT OUTER JOIN sqlcoop_dbimplemen.dbo.histoven Recibos ON (((impuvent.comprobani=Recibos.comprobant) AND (impuvent.tipoi=Recibos.tipo)) AND (impuvent.sucursali=Recibos.sucursal)) AND (impuvent.numeroi=Recibos.numero) WHERE  histoven.comprobant IN ('FACTURA', 'NOTA DE CREDITO', 'NOTA DE DEBITO') AND ($idCliente) AND (histoven.emision>={ts '$desdeFacturas'} AND histoven.emision<{ts '$hastaFacturas'}) AND (histoven.vencimien>={ts '$desdeVencimiento'} AND histoven.vencimien<{ts '$hastaVencimiento'}) AND (Recibos.emision>={ts '$desdeRecibos'} AND Recibos.emision<={ts '$hastaRecibos'}) ORDER BY Recibos.emision DESC, histoven.cliente, histoven.emision";

 ChromePhp::log($sqlFlujoBanco);

$stmt = odbc_exec2($mssql2, $sqlFlujoBanco, __LINE__, __FILE__);
$tabla = "";$a=0;
$obligaciones = array();
$depositos = array();
$recibo = array();

while($fila = sqlsrv_fetch_array($stmt)){
  // primero voy seperando por mes
  if(!isset($mes)||$mes<>$fila['recibo_emision']->format('Ym')){
    $mes = $fila['recibo_emision']->format('Ym');
  }
  $recibo_actual = $fila['recibo_numero'];
  if(!isset($recibo[$recibo_actual])){
    $recibo[$recibo_actual]['head'] = "<div id='rec_$recibo_actual' class='xxx panel panel-default'><div class='encabezado panel-heading'><b>".trim($fila['nombre'])."</b> || Recibo $fila[recibo_sucursal]-$recibo_actual, ".$fila['recibo_emision']->format('d/m/y').", \$$fila[recibo_importe]yyy</div><div class='detRecibo panel-body' id='det_$recibo_actual'>";
     $recibo[$recibo_actual]['body2'] = "<div class='cheques col-md-6' id='ch_$recibo_actual'>";
     $recibo[$recibo_actual]['body'] = "<div class='facturas col-md-6' id='fc_$recibo_actual'>";
    
    // saco el CER del día del recibo
    if(!isset($cer[$fila['recibo_emision']->format('Ymd')])){
      $sqlCer = "SELECT cer, tna FROM [coop].[dbo].[tasasInteres] WHERE fecha='".$fila['recibo_emision']->format('Y-m-d')."';";
      $stmt2 = odbc_exec2($mssql4, $sqlCer, __LINE__, __FILE__);
      $filaCER = sqlsrv_fetch_array($stmt2);
      $cer[$fila['recibo_emision']->format('Ymd')] = $filaCER['cer'];
      $tna[$fila['recibo_emision']->format('Ymd')] = $filaCER['tna'];
    }
    $recibo[$recibo_actual]['cer'] = $cer[$fila['recibo_emision']->format('Ymd')];
    $recibo[$recibo_actual]['tna'] = $tna[$fila['recibo_emision']->format('Ymd')];
    
    // tengo que obtener los datos de con que pagaron el recibo, si es con cheque analizo cada caso.
    // compruebo si ese recibo tiene asociados cheques
    $sqlCheques = "select ingreso, vencimien, DATEDIFF(d, ingreso, vencimien) AS qdias, nombre, numero_val, importe from dbo.histvalo where comprobant='RECIBO' AND banco>0 AND sucursal='$fila[recibo_sucursal]' AND numero='$recibo_actual' AND numero_val<>'' ORDER BY qdias DESC ;";
    //ChromePhp::log($sqlCheques);
    $stmt2 = odbc_exec2($mssql2, $sqlCheques, __LINE__, __FILE__);
    while($filaCheques = sqlsrv_fetch_array($stmt2)){
      // calculo para cada cheque si es diferido la pérdida por CER si la fecha del cheque ya está cargada o por descuento según TNA si es a futuro de la consulta
      if($filaCheques['vencimien']->format('Ymd')>date('Ymd')){
        // fecha del cheque posterior a hoy, descuento
        if(!isset($cer[date('Ymd', time() - 60 * 60 * 24)])){
          $sqlCer = "SELECT cer, tna FROM [coop].[dbo].[tasasInteres] WHERE fecha='".date('Y-m-d', time() - 60 * 60 * 24)."';";
          $stmt2 = odbc_exec2($mssql4, $sqlCer, __LINE__, __FILE__);
          $filaCER = sqlsrv_fetch_array($stmt2);
          $cer[date('Ymd', time() - 60 * 60 * 24)] = $filaCER['cer'];
          $tna[date('Ymd', time() - 60 * 60 * 24)] = $filaCER['tna'];
        } 
        $tasaDescuento = $filaCheques['qdias']/360*$tna[date('Ymd', time() - 60 * 60 * 24)];
        
        $valorDescontado = round($filaCheques['importe']-  $filaCheques['importe']*(1-$tasaDescuento/100),2);
        
        $recibo[$recibo_actual]['body2'] .= "Cheque Nº$filaCheques[numero_val], \$$filaCheques[importe], ".$filaCheques['vencimien']->format('d/m/Y').". Descuento \$$valorDescontado</br/>";
        $recibo[$recibo_actual]['perdida2'] += $valorDescontado;
        @$perdidaMensual2[$mes] += $valorDescontado;
        $recibo[$recibo_actual]['cheques']=true;
      }
    }
    
    
    
    $recibo[$recibo_actual]['foot'] = "</div></div>";
    // cambio el header para agregarle la class "con deuda"
    if(isset($recibo[$recibo_anterior]['vencido'])){
      $recibo[$recibo_anterior]['head'] = str_replace("xxx", "conDeuda", $recibo[$recibo_anterior]['head']);
      $recibo[$recibo_anterior]['head'] = str_replace("panel-default", "panel-danger", $recibo[$recibo_anterior]['head']);
      $recibo[$recibo_anterior]['head'] = str_replace("yyy", "<br/>Pérdida por mora <b>\$".$recibo[$recibo_anterior]['perdida']."</b>".(($recibo[$recibo_anterior]['cheques'])?"<br/>Pérdida por cheques diferidos <b>\$".$recibo[$recibo_anterior]['perdida2']."</b>":''), $recibo[$recibo_anterior]['head']);
//       ChromePhp::log($recibo[$recibo_anterior]);die;
      
    } else {
      $recibo[$recibo_anterior]['head'] = str_replace("xxx", "sinD", $recibo[$recibo_anterior]['head']);
      $recibo[$recibo_anterior]['head'] = str_replace("yyy", "", $recibo[$recibo_anterior]['head']);
    }
    $recibos[$mes] .= $recibo[$recibo_anterior]['head'] . $recibo[$recibo_anterior]['body'] . '</div>' . $recibo[$recibo_anterior]['body2'] . '</div>' . $recibo[$recibo_anterior]['foot'];
    $recibo_anterior = $recibo_actual;
  }
  
  
  
  
  
  
  
  
  
  //detalle de facturas involucradas
  // saco la fecha y chequeo que exista CER
  if(!isset($cer[$fila['factura_vencimiento']->format('Ymd')])){
    $sqlCer = "SELECT cer, tna FROM [coop].[dbo].[tasasInteres] WHERE fecha='".$fila['factura_vencimiento']->format('Y-m-d')."';";
    $stmt2 = odbc_exec2($mssql4, $sqlCer, __LINE__, __FILE__);
    $filaCER = sqlsrv_fetch_array($stmt2);
    $cer[$fila['factura_vencimiento']->format('Ymd')] = $filaCER['cer'];
    $tna[$fila['factura_vencimiento']->format('Ymd')] = $filaCER['tna'];
  }
  

  
  //$perdida = 0;
  $cierre ="";
  $diasFactura = (int) filter_var($fila['detalle'], FILTER_SANITIZE_NUMBER_INT);
  if($fila['qDias']>$diasFactura){
    $diasVencida = $fila['qDias']-$diasFactura;
    // calculo ratio cer recibo / cer factura
    $ratio =  ($recibo[$recibo_actual]['cer'] / $cer[$fila['factura_vencimiento']->format('Ymd')]) - 1;
    $perdida = round($ratio * floatval($fila['factura_importe']) , 2);
    $recibo[$recibo_actual]['perdida'] += $perdida;
    @$perdidaMensual[$mes] += $perdida;
//     ChromePhp::log("Perdida $perdida  = ratio $ratio * fila[factura_importe] $fila[factura_importe]"); 
//     ChromePhp::log("({$recibo[$recibo_actual]['cer']} / {$cer[$fila['factura_vencimiento']->format('Ymd')]}) {$fila['factura_vencimiento']->format('Ymd')}");
// ChromePhp::log($recibo['recibo_numero']['perdida']);
    $recibo[$recibo_actual]['body'] .= "<span class='text-danger'>";
    $cierre = "<span class='perdida'>\$ $perdida</span></span>";
    $recibo[$recibo_actual]['vencido'] = 1;
  }
  
  $recibo[$recibo_actual]['body'] .= ucfirst(strtolower($fila['factura_comprobant']))." $fila[factura_sucursal]-$fila[factura_numero], \$$fila[factura_importe], ".$fila['factura_emision']->format('d/m/y')." ($fila[qDias] días) $cierre<br/>";
  
}

 if(isset($recibo[$recibo_anterior]['vencido'])){
  $recibo[$recibo_anterior]['head'] = str_replace("xxx", "conDeuda", $recibo[$recibo_anterior]['head']);
  $recibo[$recibo_anterior]['head'] = str_replace("alert-info", "alert-danger", $recibo[$recibo_anterior]['head']);
  $recibo[$recibo_anterior]['head'] = str_replace("yyy", "<br/>Pérdida por mora <b>\$".$recibo[$recibo_anterior]['perdida']."</b>", $recibo[$recibo_anterior]['head']);
//       ChromePhp::log($recibo[$recibo_anterior]);die;
  
} else {
  $recibo[$recibo_anterior]['head'] = str_replace("xxx", "sinD", $recibo[$recibo_anterior]['head']);
  $recibo[$recibo_anterior]['head'] = str_replace("yyy", "", $recibo[$recibo_anterior]['head']);
}
$recibos[$mes] .= $recibo[$recibo_anterior]['head'] . $recibo[$recibo_anterior]['body'] . '</div>' . $recibo[$recibo_anterior]['body2'] . '</div>' . $recibo[$recibo_anterior]['foot'];


$a=0;
/*
for($i=0;$i<=$cantidadMeses;$i++){
  $tr.="<td>".$recibos[date('Ym', strtotime("-$i months"))]."</td>";
  $a++;
}
for($i=0;$i<=$cantidadMeses;$i++){
  $tr2 .= "<td class='text-danger'><b>".number_format(-1*$perdidaMensual[date('Ym', strtotime("-$i months"))],2,',','.')."</b></td>";
}

$tr .= '</tr>';
$tr2 .= '</tr>';

echo $tr.$tr2;
*/
if($perdidaMensual[$mes]>0){
  $totalizaMes = "<div class='rec'><ul><li>Mora en pago de facturas \$ ".$perdidaMensual[$mes]."</li><li>Descuento cheques diferidos \$ ".$perdidaMensual2[$mes]."</li><li>Pérdida financiera <b>\$ ".($perdidaMensual2[$mes]+$perdidaMensual[$mes])."</b></li></ul></div>";
}
echo $totalizaMes."<div class='col-md-10 sds'>".$recibos[$mes];
?>
