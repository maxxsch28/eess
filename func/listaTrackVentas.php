<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
 // print_r($_POST);
 // $array=array();
 //fb($_POST);
if(!isset($_POST['producto'])&&!isset($_POST['resumen'])){
  if(isset($_POST['mes'])){
    $mes=substr($_POST['mes'],4,2);
    $anio=substr($_POST['mes'],0,4);
  }
  if($mes==''){
    // anual
    $sqlAforadores = "SELECT distinct aforadores.fechaCierre, ed1, ns1, ni1, ed2, ns2, ni2, ed2, ud3, ed4, ud5, ud6, ed7, turno, tanques.tq1, tanques.tq2, tanques.tq3, tanques.tq4, tanques.tq5, tanques.tq6, sum(recepcion.tq1) as r1, sum(recepcion.tq2) as r2, sum(recepcion.tq3) as r3, sum(recepcion.tq4) as r4, sum(recepcion.tq5) as r5, sum(recepcion.tq6) as r6, sum(recepcion.tq6+recepcion.tq2) as r2069, sum(recepcion.tq4+recepcion.tq1) as r2068, sum(recepcion.tq3) as r2078, sum(recepcion.tq5) as r2076, (tanques.tq1+tanques.tq4) as tq2068, (tanques.tq2+tanques.tq6) as tq2069, tanques.tq3 as tq2078, tanques.tq5 as tq2076 FROM cierres_cem_aforadores aforadores INNER JOIN cierres_cem_tanques tanques ON aforadores.fechaCierre = tanques.fechaCierre LEFT JOIN recepcioncombustibles recepcion ON recepcion.fecha = DATE( aforadores.fechaCierre ) WHERE YEAR(aforadores.fechaCierre) = $anio GROUP BY aforadores.fechaCierre ORDER BY aforadores.fechaCierre ASC";
  } else {
    $sqlAforadores = "SELECT distinct aforadores.fechaCierre, ed1, ns1, ni1, ed2, ns2, ni2, ed2, ud3, ed4, ud5, ud6, ed7, turno, tanques.tq1, tanques.tq2, tanques.tq3, tanques.tq4, tanques.tq5, tanques.tq6, sum(recepcion.tq1) as r1, sum(recepcion.tq2) as r2, sum(recepcion.tq3) as r3, sum(recepcion.tq4) as r4, sum(recepcion.tq5) as r5, sum(recepcion.tq6) as r6, sum(recepcion.tq6+recepcion.tq2) as r2069, sum(recepcion.tq4+recepcion.tq1) as r2068, sum(recepcion.tq3) as r2078, sum(recepcion.tq5) as r2076, (tanques.tq1+tanques.tq4) as tq2068, (tanques.tq2+tanques.tq6) as tq2069, tanques.tq3 as tq2078, tanques.tq5 as tq2076 FROM cierres_cem_aforadores aforadores INNER JOIN cierres_cem_tanques tanques ON aforadores.fechaCierre = tanques.fechaCierre LEFT JOIN recepcioncombustibles recepcion ON recepcion.fecha = DATE( aforadores.fechaCierre ) WHERE (YEAR(aforadores.fechaCierre) = $anio AND MONTH(aforadores.fechaCierre)= $mes) OR (DATE(aforadores.fechaCierre) = LAST_DAY('$anio-$mes-01' - INTERVAL 1 MONTH)) GROUP BY aforadores.fechaCierre ORDER BY aforadores.fechaCierre ASC";
    //echo $sqlAforadores;
  }
  $result = $mysqli->query($sqlAforadores);
  $a=0;$q=0;
  $tableTanques="<table class='table'><thead><tr><th>Fecha</th><th>ED1</th><th>NS1</th><th>NI1</th><th>ED2</th><th>NS2</th><th>NI2</th><th>UD3</th><th>ED4</th><th>UD5</th><th>UD6</th><th>ED7</th></tr></thead><tbody>";
  while($filaCEM = $result->fetch_assoc()){
    if(!isset($anterior)){
      // obtiene resultados ultimo día período anterior
      $inicial = $filaCEM;
      $anterior = $stockInicial = $filaCEM; // define stock inicial de tanques al último día del mes anterior.
      asort($arrayPicosTanques);
      $totalDiferenciaPico = array();
      $totalPico = array();
      /*foreach($stockInicialProducto as $idProducto => $despachado){
        // defino tabla para este tanque
        //$tablaResumen[$idProducto] = "<div class='panel panel-{$classArticulo[$idProducto]}'><div class='panel-heading'><h3 class='panel-title'>$articulo[$idProducto]</h3></div><div class='panel-body'>Existencia Inicial: <b>".($filaCEM['tq'.$idProducto]}</b><br/>";
        $tablaResumen[$idProducto] = "<div class='col-xs-6 small'><ul class='list-group'><li class='list-group-item list-group-item-$classArticulo[$idProducto]'>$articulo[$idProducto]</li><li class='list-group-item'>Existencia Inicial: <span class='pull-right'><b>{$inicial['tq'.$idProducto]}</b></span><br/>";
      }*/
    } else {
      $rowCEM = "<tr><td rowspan=3>".substr($filaCEM['fechaCierre'],0,-9)."</td><td>".round(($filaCEM['ed1']-$anterior['ed1']),2)."</td><td>".round(($filaCEM['ns1']-$anterior['ns1']),2)."</td><td>".round(($filaCEM['ni1']-$anterior['ni1']),2)."</td><td>".round(($filaCEM['ed2']-$anterior['ed2']),2)."</td><td>".round(($filaCEM['ns2']-$anterior['ns2']),2)."</td><td>".round(($filaCEM['ni2']-$anterior['ni2']),2)."</td><td>".round(($filaCEM['ud3']-$anterior['ud3']),2)."</td><td>".round(($filaCEM['ed4']-$anterior['ed4']),2)."</td><td>".round(($filaCEM['ud5']-$anterior['ud5']),2)."</td><td>".round(($filaCEM['ud6']-$anterior['ud6']),2)."</td><td>".round(($filaCEM['ed7']-$anterior['ed7']),2)."</td></tr>";
       // saca de SQL Server despachos por pico leídos por CaldenOil
      $sqlDespachos = "select idManguera, sum(cantidad) from dbo.despachos where fecha>='$anterior[fechaCierre]' and fecha<='$filaCEM[fechaCierre]' group by Idmanguera order BY IdManguera;";
      //fb($sqlDespachos);
      $stmt = odbc_exec( $mssql, $sqlDespachos);
      unset($pico, $tableTanques2, $diferenciaPico);
      $rowCalden = "<tr>";
      $rowDiferencia = "</tr><tr class='diferencias'>";
      while($filaCalden = odbc_fetch_array($stmt)){
        $tableTanques2[$filaCalden[0]] = "<td>".round($filaCalden[1],2)."</td>";
        $pico[$filaCalden[0]] = $filaCalden[1];
      }
      for($i=1;$i<=11;$i++){
        @$totalPico[$i] = $totalPico[$i] + round(($filaCEM[$arrayPicosNumeros[$i]]-$anterior[$arrayPicosNumeros[$i]]),2);
        if(isset($tableTanques2[$i])){
          $rowCalden .= $tableTanques2[$i];
          $diferenciaPico[$i] = round(($filaCEM[$arrayPicosNumeros[$i]]-$anterior[$arrayPicosNumeros[$i]]-$pico[$i]),2);
        } else {
          $rowCalden .= "<td>0</td>";
          $diferenciaPico[$i] = round(
          (isset($filaCEM[$arrayPicosNumeros[$i]])?$filaCEM[$arrayPicosNumeros[$i]]:0)-
          (isset($anterior[$arrayPicosNumeros[$i]])?$anterior[$arrayPicosNumeros[$i]]:0)-
          (isset($pico[$i])?$pico[$i]:0)
          ,2);
        }
        @$totalDiferenciaPico[$i] = $totalDiferenciaPico[$i] + $diferenciaPico[$i];
        $rowDiferencia .= "<td>$diferenciaPico[$i]</td>";
      }
      $rowCalden .= "</tr>";
      
      
     $rowDiferencia.="</tr>";
      
      $tableTanques .= $rowCEM.$rowCalden.$rowDiferencia;
      $anterior=$filaCEM;
      $stockFinal = $filaCEM;
      $combustibleDespachadoTanque=array();
      $combustibleDespachadoProducto=array();
    }
  }
  $tableTanques .= "</tbody><tfooter><tr><td><b>Diferencia</b></td>";
  for($i=1;$i<=11;$i++){
    $tableTanques .= "<td>$totalDiferenciaPico[$i]<br/>$totalPico[$i]<br/>".round(($totalDiferenciaPico[$i]/$totalPico[$i]*100),2)."%</td>";
  }
  $tableTanques .= "</tr></tfooter></table>";
  //fb($tableTanques);
  echo $tableTanques;
  //$_SESSION['tablaResumen']=$tablaResumen;
  $_SESSION['tablaTanques']=$tableTanques;
} elseif(isset($_POST['producto'])&&!isset($_POST['resumen'])) {
    // tabla de productos
    echo $_SESSION['tablaProductos'];
} else {
  // resumen
  // Inicial
  // Recepcion
  // Ventas medidas
  // Existencia teórica
  // Medicion real
  // Diferencia lts (%)
  $i=0;
  foreach($articulo as $idProducto => $producto){
    $i++;
    echo $_SESSION['tablaResumen'][$idProducto];
  }
}

?>
