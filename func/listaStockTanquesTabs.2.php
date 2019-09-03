<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
 // print_r($_POST);
 // $array=array();
 
 
if(isset($_POST['mes'])&&is_numeric($_POST['mes'])&&strlen($_POST['mes'])==6){
  $mes=substr($_POST['mes'],4,2);
  $anio=substr($_POST['mes'],0,4);
} 

$picos = picos();
$tanques = tanques();

if($_POST['mes']=='30d'){
  // ultimos 30 días
  $sqlAforadores = "SELECT distinct aforadores.fechaCierre, aforadores.*, turno, tanques.tq1, tanques.tq2, tanques.tq3, tanques.tq4, tanques.tq5, tanques.tq6, sum(recepcion.tq1) as r1, sum(recepcion.tq2) as r2, sum(recepcion.tq3) as r3, sum(recepcion.tq4) as r4, sum(recepcion.tq5) as r5, sum(recepcion.tq6) as r6, sum(recepcion.tq6) as r2069, sum(recepcion.tq4+recepcion.tq1) as r2068, sum(recepcion.tq3+recepcion.tq5) as r2078, sum(recepcion.tq2) as r2076, (tanques.tq1+tanques.tq4) as tq2068, (tanques.tq6) as tq2069, (tanques.tq3+tanques.tq5) as tq2078, tanques.tq2 as tq2076 FROM cierres_$_POST[origen]_aforadores aforadores INNER JOIN cierres_cem_tanques tanques ON aforadores.fechaCierre = tanques.fechaCierre LEFT JOIN recepcioncombustibles recepcion ON recepcion.fecha = date(aforadores.fechaCierre) WHERE aforadores.fechaCierre>=date_add(CURDATE(), INTERVAL -1 MONTH) GROUP BY aforadores.fechaCierre ORDER BY aforadores.fechaCierre ASC";
} elseif($_POST['mes']=='365d'){
  // anual
  $sqlAforadores = "SELECT distinct aforadores.fechaCierre, aforadores.*, turno, tanques.tq1, tanques.tq2, tanques.tq3, tanques.tq4, tanques.tq5, tanques.tq6, sum(recepcion.tq1) as r1, sum(recepcion.tq2) as r2, sum(recepcion.tq3) as r3, sum(recepcion.tq4) as r4, sum(recepcion.tq5) as r5, sum(recepcion.tq6) as r6, sum(recepcion.tq6) as r2069, sum(recepcion.tq4+recepcion.tq1) as r2068, sum(recepcion.tq3+recepcion.tq5) as r2078, sum(recepcion.tq2) as r2076, (tanques.tq1+tanques.tq4) as tq2068, (tanques.tq6) as tq2069, (tanques.tq3+tanques.tq5) as tq2078, tanques.tq2 as tq2076 FROM cierres_$_POST[origen]_aforadores aforadores INNER JOIN cierres_cem_tanques tanques ON aforadores.fechaCierre = tanques.fechaCierre LEFT JOIN recepcioncombustibles recepcion ON recepcion.fecha = date(aforadores.fechaCierre) WHERE aforadores.fechaCierre>=date_add(CURDATE(), INTERVAL -1 YEAR) GROUP BY aforadores.fechaCierre ORDER BY aforadores.fechaCierre ASC";
} elseif(strlen($_POST['mes'])==4){
  // mes en curso
  $sqlAforadores = "SELECT distinct aforadores.fechaCierre, aforadores.*, turno, tanques.tq1, tanques.tq2, tanques.tq3, tanques.tq4, tanques.tq5, tanques.tq6, sum(recepcion.tq1) as r1, sum(recepcion.tq2) as r2, sum(recepcion.tq3) as r3, sum(recepcion.tq4) as r4, sum(recepcion.tq5) as r5, sum(recepcion.tq6) as r6, sum(recepcion.tq6) as r2069, sum(recepcion.tq4+recepcion.tq1) as r2068, sum(recepcion.tq3+recepcion.tq5) as r2078, sum(recepcion.tq2) as r2076, (tanques.tq1+tanques.tq4) as tq2068, (tanques.tq6) as tq2069, (tanques.tq3+tanques.tq5) as tq2078, tanques.tq2 as tq2076 FROM cierres_$_POST[origen]_aforadores aforadores INNER JOIN cierres_cem_tanques tanques ON aforadores.fechaCierre = tanques.fechaCierre LEFT JOIN recepcioncombustibles recepcion ON recepcion.fecha = date(aforadores.fechaCierre) WHERE (YEAR(aforadores.fechaCierre) = $_POST[mes] OR (DATE(aforadores.fechaCierre) = '".($_POST['mes']-1)."-12-31')) GROUP BY aforadores.fechaCierre ORDER BY aforadores.fechaCierre ASC";
} else {
  $sqlAforadores = "SELECT distinct aforadores.fechaCierre, aforadores.*, turno, tanques.tq1, tanques.tq2, tanques.tq3, tanques.tq4, tanques.tq5, tanques.tq6, sum(recepcion.tq1) as r1, sum(recepcion.tq2) as r2, sum(recepcion.tq3) as r3, sum(recepcion.tq4) as r4, sum(recepcion.tq5) as r5, sum(recepcion.tq6) as r6, sum(recepcion.tq6) as r2069, sum(recepcion.tq4+recepcion.tq1) as r2068, sum(recepcion.tq3+recepcion.tq5) as r2078, sum(recepcion.tq2) as r2076, (tanques.tq1+tanques.tq4) as tq2068, (tanques.tq6) as tq2069, (tanques.tq3+tanques.tq5) as tq2078, tanques.tq2 as tq2076 FROM cierres_$_POST[origen]_aforadores aforadores INNER JOIN cierres_cem_tanques tanques ON aforadores.fechaCierre = tanques.fechaCierre LEFT JOIN recepcioncombustibles recepcion ON recepcion.fecha = DATE( aforadores.fechaCierre ) WHERE (YEAR(aforadores.fechaCierre) = $anio AND MONTH(aforadores.fechaCierre)= $mes) OR (DATE(aforadores.fechaCierre) = LAST_DAY('$anio-$mes-01' - INTERVAL 1 MONTH)) GROUP BY aforadores.fechaCierre ORDER BY aforadores.fechaCierre ASC";

    //echo $sqlAforadores;
}
ChromePhp::log($sqlAforadores);


switch($_POST['que']){
  case 'productos':
    echo $_SESSION['tablaProductos'];
    break;
  case 'resumen':
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
    break;
  case 'tanques':
    $result = $mysqli->query($sqlAforadores);
    $tabla = "";$a=0;$q=0;
    while($fila = $result->fetch_assoc()){
      if(!isset($anterior)){
        $inicial = $fila;
        $anterior = $stockInicial = $fila; // define stock inicial de tanques al último día del mes anterior.
       
        //ChromePhp::log($picos);
        
        $tableTanques = array();
        foreach($picos as $pico => $detallePico){
          if(!isset($tableTanques[$detallePico['tanque']])){
            // defino tabla para este tanque
            @$i++;
            $tableTanques[$detallePico['tanque']]="<div role='tabpanel' class='tab-pane' id='tq$detallePico[tanque]'><table class='table table-hover table-condensed'><colgroup><col><col span='2' class='alert alert-warning'><col><col class='alert alert-warning'></colgroup><thead><tr><th class='nombre' width='15%'>Fecha</th><th align=center width='15%'>Ingresos</th><th width='15%'>Ventas</th><th width='15%'>Teórico</th><th width='15%'>Medido</th><th colspan=2 width='25%'>&#916;</th></tr></thead><tbody><tr><td>Inicial</td><td></td><td></td><td></td><td>{$fila['tq'.$detallePico['tanque']]}</td><td></td></tr>";
          }
          @$stockInicialTanque[$detallePico['tanque']] += $fila['tq'.$detallePico['tanque']];
          @$stockInicialProducto[$detallePico['idArticulo']] += $fila['tq'.$detallePico['tanque']];
        }
        //echo "$ i: $i;";print_r($picos);
        $tableProductos = "<table class='table table-hover'><colgroup><col>";
        ksort($stockInicialProducto);
        foreach($stockInicialProducto as $idProducto => $despachado){
            $tableProductos .= "<col span='5' class='alert-$classArticulo[$idProducto]'>";
        }
        $tableProductos .= "</colgroup><thead><tr><th class='nombre'>Fecha</th>";
        $tabla .= "<td></td>";
        foreach($stockInicialProducto as $idProducto => $despachado){
          // defino tabla para este tanque
          $tableProductos.="<th>Ing.</th><th>Vtas.</th><th>{$fila['tq'.$idProducto]}</th><th>Medido</th><th>&#916;</th>";
          //$tablaResumen[$idProducto] = "<div class='panel panel-{$classArticulo[$idProducto]}'><div class='panel-heading'><h3 class='panel-title'>$articulo[$idProducto]</h3></div><div class='panel-body'>Existencia Inicial: <b>{$fila['tq'.$idProducto]}</b><br/>";
          $tablaResumen[$idProducto] = "<div class='col-xs-6 small'><ul class='list-group'><li class='list-group-item list-group-item-$classArticulo[$idProducto]'>{$articulo[$idProducto]['descripcion']}</li><li class='list-group-item'>Existencia Inicial: <span class='pull-right'><b>{$inicial['tq'.$idProducto]}</b></span><br/>";
        }
        $tableProductos .= "</tr></thead><tbody>";
      } else {
        // tabla con venta por tanque mas columnas de venta por producto
        asort($arrayPicosTanques);
        $totalDespachoProductoPorAforadores=array();
        if(!isset($sumaDiferencias))$sumaDiferencias=array(0,0,0,0,0,0,0);
        if(!isset($totalRecibidoTanque))$totalRecibidoTanque=array(0,0,0,0,0,0,0);
        
        foreach($picos as $pico => $detallePico){
          // En $fila los picos tienen el nombre según CIO_CaldenOil, tendría que hacer un explode a todos los resultados de $fila y chequear si la segunda parte coincide con el pico según CaldenOil
          //print_r($detallePico);
          foreach($fila as $key => $value){
            $explode = explode('_', $key);
            if($explode[1]==$pico){
              //ChromePhp::log("Pico: $pico, ".$fila[$pico]);
              @$combustibleDespachadoTanque[$detallePico['tanque']] += $fila[$key]-$anterior[$key];
              @$totalDespachoTanque[$detallePico['tanque']] += $fila[$key]-$anterior[$key];
              @$combustibleDespachadoProducto[$detallePico['idArticulo']] += $fila[$key]-$anterior[$key];
              @$totalDespachoProducto[$detallePico['idArticulo']] += $fila[$key]-$anterior[$key];
              
              @$totalDespachoProductoPorAforadores[$detallePico['idArticulo']] += $fila[$key]-$stockInicial[$key];
              @$totalRecibidoPorAforadores[$detallePico['idArticulo']] += $fila['r'.$detallePico['tanque']];
              
              
              @$totalFamilia[$articulo[$detallePico['idArticulo']]['familia']] += $fila[$key]-$anterior[$key];
              if($articulo[$detallePico['idArticulo']]['premium']){
                $litrosPremium[$articulo[$detallePico['idArticulo']]['familia']] += $fila[$key]-$anterior[$key];
              }
              break;
            }
          }
        }
        //print_r($combustibleDespachadoProducto);
        foreach($tanques as $tanque => $idProducto){
          $totalRecibidoTanque[$tanque] += $fila['r'.$tanque];
          @$totalRecibidoProducto[$tanques[$tanque]] += $fila['r'.$tanque];
        }
        $medido = array();
        foreach($combustibleDespachadoTanque as $tanque => $despachado){
          foreach($tanques as $tanque2 => $idProducto2){
            if($tanque2 == $tanque){
              //echo "idproduct2 $idProducto2 en tanque $tanque<br>";
              @$medido[$idProducto2['idArticulo']] = $medido[$idProducto2['idArticulo']] + $fila['tq'.$tanque];
            }
          }
          $tableTanques[$tanque].="<tr><td><b>".substr($fila['fechaCierre'],0,-9)."</b></td>";
          $teorico = intval($anterior['tq'.$tanque] + $fila['r'.$tanque] - $despachado);
          $diferenciaMedidoTeorico = $fila['tq'.$tanque]-$teorico;
          $arrayDiferencias['tq'.$tanque][]=$diferenciaMedidoTeorico;
          $sumaDiferencias[$tanque] = $sumaDiferencias[$tanque] + $diferenciaMedidoTeorico;
          $porcentajeDiferenciaSobreVentas = ($despachado<>0)?round(100 * $diferenciaMedidoTeorico / $despachado, 2):0;
          $desvio = sqrt(pow($diferenciaMedidoTeorico,2));
          if(round($porcentajeDiferenciaSobreVentas,1)<-.5){
            $fueraDeTolerancia = "neg $porcentajeDiferenciaSobreVentas";
          } else if(round($porcentajeDiferenciaSobreVentas,1)>.5){
            $fueraDeTolerancia = "pos $porcentajeDiferenciaSobreVentas";
          } else {
            $fueraDeTolerancia = '';
          }
          
          $tableTanques[$tanque] .= "<td>".(($fila['r'.$tanque]<>(NULL||0))?$fila['r'.$tanque]:'')."</td><td>".sprintf("%.2f", $despachado)."</td><td>$teorico</td><td>{$fila['tq'.$tanque]}</td><td >$diferenciaMedidoTeorico lts</td><td class='$fueraDeTolerancia'>$porcentajeDiferenciaSobreVentas%</td></tr>";
        }

        ksort($combustibleDespachadoProducto);
        $tableProductos.="<tr><td><b>".substr($fila['fechaCierre'],8,-9)."</b></td>";
        //print_r($combustibleDespachadoProducto);

        foreach($combustibleDespachadoProducto as $idProducto => $despachado){
          $teorico = intval($anterior['tq'.$idProducto] + $fila['r'.$idProducto] - $despachado);
          $diferenciaMedidoTeorico = $fila['tq'.$idProducto]-$teorico;
          @$sumaDiferencias[$idProducto] = $sumaDiferencias[$idProducto] + $diferenciaMedidoTeorico;
          @$sumaDespachado[$idProducto] = $sumaDespachado[$idProducto] + $despachado;
          $porcentajeDiferenciaSobreVentas = ($despachado<>0)?round(100 * $diferenciaMedidoTeorico / $despachado, 2):0;
          $tableProductos.="<td>".(($fila['r'.$idProducto]>0)?$fila['r'.$idProducto]:'')."</td>"
          . "<td>".sprintf("%.2f", $despachado)."</td>"
          . "<td>$teorico</td>"
          /// TODO
          . "<td>{$medido[$idProducto]}</td>"
          . "<td class='".((($diferenciaMedidoTeorico)<-$toleranciaTanques)?'neg':((($diferenciaMedidoTeorico)>$toleranciaTanques)?'pos':''))."'>$diferenciaMedidoTeorico</td>";
        }
        $tableProductos .= "</tr>";
        $anterior=$fila;
        $stockFinal = $fila;
        $combustibleDespachadoTanque=array();
        $combustibleDespachadoProducto=array();
      }
    }

    foreach($totalDespachoTanque as $tanque => $despachado){  
      $promedio = array_sum($arrayDiferencias['tq'.$tanque])/count($arrayDiferencias['tq'.$tanque]);
      $tableTanques[$tanque] .= "<tr><td>Final</td><td>".sprintf("%.2f", $totalRecibidoTanque[$tanque])."</td><td>".sprintf("%.2f", $despachado)."</td><td></td><td></td><td class='".((($sumaDiferencias[$tanque])<-$toleranciaTanques)?'neg':((($sumaDiferencias[$tanque])>$toleranciaTanques)?'pos':''))."'>$sumaDiferencias[$tanque] lts<br/>x&#772;".round($promedio).' &sigma;'.round(stats_standard_deviation($arrayDiferencias['tq'.$tanque]),1)."</td><td>".sprintf("%.2f", $sumaDiferencias[$tanque]/$despachado*100)."%</td></tr>";
    }
    ksort($totalDespachoProducto);
    $tableProductos .= "<tr><td>Final</td>";
    $i=$totalDespachadoMensual=0;
    foreach($totalDespachoProducto as $idProducto => $despachado){
      $i++;
      $tableProductos .= "<td>".sprintf("%.2f", $totalRecibidoProducto[$idProducto])."</td>"
        . "<td>".sprintf("%.2f", $despachado)."</td>"
        . "<td colspan='3' class='".((($sumaDiferencias[$idProducto])<-100)?'neg':((($sumaDiferencias[$idProducto])>100)?'pos':''))."'>("
        . sprintf("%.2f", $sumaDiferencias[$idProducto]/$sumaDespachado[$idProducto]*100)."%)     $sumaDiferencias[$idProducto] lts</td>";
        
        $dif = round($medido[$idProducto] - ($inicial['tq'.$idProducto]+$totalRecibidoProducto[$idProducto]-$totalDespachoProducto[$idProducto]),0);
        //print_R($articulo);
      if($articulo[$idProducto]['familia']=='DIESEL'){
        $maxTolerable=0.5;
      }else{
        $maxTolerable=0.3;
      }
      $tablaResumen[$idProducto] .= "Recepción: <span class='pull-right' style='clear:both'><b>+".round($totalRecibidoProducto[$idProducto],0)."</b></span><br/>"
        . "Ventas: <span class='pull-right' style='border-bottom:1px solid #000; clear:both'><b>-".round($totalDespachoProducto[$idProducto],0)."</b></span><br/><br/>"
        . "Existencia teórica: <span class='pull-right' style='clear:both'><b>".round($inicial['tq'.$idProducto]+$totalRecibidoProducto[$idProducto]-$totalDespachoProducto[$idProducto],0)."</b></span><br/>"
        . "Medición real: <span class='pull-right' style='border-bottom:1px solid #000; clear:both'><b>$medido[$idProducto]</b></span><br/><br/>"
        . "&nbsp;<span class='pull-right ".((($dif/$sumaDespachado[$idProducto]*100)<=-$maxTolerable)?'neg':((round($dif/$sumaDespachado[$idProducto]*100,1
        )>=$maxTolerable)?'pos':'0'))."'>(".round($dif/$sumaDespachado[$idProducto]*100,1)."%) <b>$dif</b></span></li>"
        . "</ul></div>";
      if($i==2){
        // diesel
        $porcentajeFamilia[$articulo[$idProducto]['familia']] = round(100*$litrosPremium[$articulo[$idProducto]['familia']]/$totalFamilia[$articulo[$idProducto]['familia']],1);
        
        // revisa para que familia tengo que hacer la barra
        $tablaResumen[$idProducto] .= "<div class='col-md-12'><div class='progress'>";
        $t100 = 100;
        foreach($articulo as $idArticulo => $detalleArticulo) {
          
          if($detalleArticulo['familia']==$articulo[$idProducto]['familia']){
            //echo"$idProducto - ";print_r($porcentajeFamilia);
            $tablaResumen[$idProducto] .= "<div class='fam_{$articulo[$idProducto]['familia']} progress-bar progress-bar-{$classArticulo[$detalleArticulo[idArticulo]]}' style='width: ".($t100-$porcentajeFamilia[$articulo[$idProducto]['familia']])."%'>"
            .($t100-$porcentajeFamilia[$articulo[$idProducto]['familia']])."% ".$articulo[$detalleArticulo['idArticulo']]['descripcion']."
            </div>";
            $t100 = $t100 - $porcentajeFamilia[$articulo[$idProducto]['familia']];
          }
        }
        

        $tablaResumen[$idProducto] .= "</div></div>";
      }elseif($i==4){
        // naftas
        $porcentajeSuper = round(($totalDespachoProducto[2078]/($totalDespachoProducto[2078]+$totalDespachoProducto[2076]))*100,1);
        $tablaResumen[$idProducto] .= "<div class='col-md-12'><div class='progress'>
          <div class='progress-bar progress-bar-info' style='width: ".(100-$porcentajeSuper)."%'>
            ".(100-$porcentajeSuper)."% Infinia
          </div>
          <div class='progress-bar progress-bar-info2' style='width: $porcentajeSuper%'>
            $porcentajeSuper% Super
          </div>
        </div></div>";
        $tablaResumen[$idProducto] .= "<div class='col-md-12'><div class='progress'>";
        foreach($articulo as $idProducto3=>$producto){
          $totalDespachadoMensual = $totalDespachadoMensual + $totalDespachoProducto[$idProducto3];
        }
        foreach($articulo as $idProducto3=>$producto){
          $porcentaje[$idProducto3] = round(100*($totalDespachoProducto[$idProducto3]/$totalDespachadoMensual),0);
          if(!isset($acumulado)){$acumulado = $porcentaje[$idProducto3];}
          elseif(($acumulado+$porcentaje[$idProducto3])>100){$porcentaje[$idProducto3]=100-$acumulado;}
          else {$acumulado += $porcentaje[$idProducto3];}
          $tablaResumen[$idProducto] .= "<div class='progress-bar progress-bar-$classArticulo[$idProducto3]' style='width: {$porcentaje[$idProducto3]}%'>$porcentaje[$idProducto3]% $producto</div>";
        }
        $tablaResumen[$idProducto] .= "</div></div>";
      }
    }
    $tableProductos .= "</tr><tr><td></td><td colspan=10><b>".sprintf("%.2f", ($totalDespachoProducto[2068]/($totalDespachoProducto[2068]+$totalDespachoProducto[2069]))*100)."% Euro/Gasoil</b></td><td colspan=10><b>".sprintf("%.2f", ($totalDespachoProducto[2076]/($totalDespachoProducto[2076]+$totalDespachoProducto[2078]))*100)."% Infinia/Naftas</b></td></tr></tbody></table>";

    foreach($arrayPicosTanques as $pico => $tanque){
      echo $tableTanques[$tanque]."</tbody></table></div>";
    }
    $_SESSION['tablaProductos']=$tableProductos;
    $_SESSION['tablaResumen']=$tablaResumen;
    break;
}
?>
