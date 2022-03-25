<?php
// ajaxCuentaCruzada.php
// recibe datos sobre que sistema necesito la caja y un rango de fechas

include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//Array ( [mes] => 31/12/2019 [rangoInicio] => 01/01/2018 ) 

//print_r($_POST);die;

setlocale(LC_MONETARY, 'es_AR');

if(isset($_POST['mes'])&&$_POST['mes']>date("Y")){
    // mensual
    $mes = substr($_POST['mes'],4,2);
    $anio = substr($_POST['mes'],0,4);
    $rango = " AND YEAR(fecha)=$anio AND MONTH(fecha)=$mes";
} else {
    // anual
    $rangoInicio = date("Y-01-01");
    $anio = substr($_POST['mes'],0,4);
    $rango = " AND YEAR(fecha)=$anio";
}


if(isset($_POST['rangoFin'])){
  $fin=explode("/", $_POST['rangoFin']);
  $rangoFinal = "$fin[2]-$fin[1]-$fin[0] 23:59:59";
} else {
  $rangoFinal = date("Y-m-d")." 23:59:59";
}

$orden = "DESC";
$ordenFecha = "ASC";

$cuentaContableSetup = 211110;
ChromePhp::log("Adelantos fleteros");
$filtroDescripcion = "";



$sqlSetup = "SELECT cantidad, [sqlcoop_dbimplemen].[dbo].[asiecont].asiento, [sqlcoop_dbimplemen].[dbo].[concasie].detalle, debe, haber, fecha, [sqlcoop_dbshared].[dbo].[plancuen].nombre, cuentacont, ordenamien, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[asiecont].idtranglob, [sqlcoop_dbimplemen].[dbo].[asiecont].cod_libro FROM [sqlcoop_dbimplemen].[dbo].[asiecont], [sqlcoop_dbimplemen].[dbo].[concasie], [sqlcoop_dbshared].[dbo].[plancuen] WHERE [sqlcoop_dbshared].[dbo].[plancuen].codigo=cuentacont AND [sqlcoop_dbimplemen].[dbo].[asiecont].cod_libro=[sqlcoop_dbimplemen].[dbo].[concasie].cod_libro AND [sqlcoop_dbimplemen].[dbo].[asiecont].asiento=[sqlcoop_dbimplemen].[dbo].[concasie].asiento AND [sqlcoop_dbimplemen].[dbo].[asiecont].transaccio=[sqlcoop_dbimplemen].[dbo].[concasie].transaccio AND cuentacont = $cuentaContableSetup $rango $filtroDescripcion UNION SELECT cantidad, [sqlcoop_dbimplemen].[dbo].[diario].asiento, [sqlcoop_dbimplemen].[dbo].[concasie].detalle, debe, haber, fecha, [sqlcoop_dbshared].[dbo].[plancuen].nombre, cuentacont, ordenamien, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[diario].idtranglob, [sqlcoop_dbimplemen].[dbo].[diario].cod_libro FROM [sqlcoop_dbimplemen].[dbo].[diario], [sqlcoop_dbimplemen].[dbo].[concasie], [sqlcoop_dbshared].[dbo].[plancuen] WHERE [sqlcoop_dbshared].[dbo].[plancuen].codigo=cuentacont AND [sqlcoop_dbimplemen].[dbo].[diario].cod_libro=[sqlcoop_dbimplemen].[dbo].[concasie].cod_libro AND [sqlcoop_dbimplemen].[dbo].[diario].asiento=[sqlcoop_dbimplemen].[dbo].[concasie].asiento AND [sqlcoop_dbimplemen].[dbo].[diario].transaccio=[sqlcoop_dbimplemen].[dbo].[concasie].transaccio AND cuentacont=$cuentaContableSetup $rango $filtroDescripcion ORDER BY fecha $ordenFecha";






if(!isset($_POST['caja'])){
    die;
}
if($_POST['caja']=='Setup'){
    // muestro contenido mayor de contabilidad de Setup

    //ChromePhp::log($sqlSetup);


    $stmt = odbc_exec2( $mssql2, $sqlSetup, __LINE__, __FILE__);

    $tabla = "";$a=$q=0;
    $renglon = array();
    while($fila = sqlsrv_fetch_array($stmt)){
        if(array_key_exists("$fila[idtranglob]$fila[asiento]",$renglon)){
            unset($renglon["$fila[idtranglob]$fila[asiento]"]);
        } else {
            $renglon["$fila[idtranglob]$fila[asiento]"] = $fila;
        }
    }
    foreach($renglon as $key => $fila){
        $fechaEmision = $fila['fecha']->format('d/m/y');
        
        if(substr(strtoupper($fila['detalle']), 0,19)=="COMPROBANTE DE CAJA"){
            $fila['detalle'] = trim(substr($fila['detalle'], 19));
        }
        if(substr(strtoupper($fila['detalle']), 0,11)=="COMPROBANTE"){
            $fila['detalle'] = trim(substr($fila['detalle'], 11));
        }
        if(substr(trim(strtoupper($fila['detalle'])), 0,5) == "SERIE"){
            $fila['detalle'] = trim(substr($fila['detalle'], 7));
        }
        // Limpia textos en Setup
        if(strpos($fila['detalle'], "Concepto")){
            $fila['detalle'] = trim(substr($fila['detalle'], strpos($fila['detalle'], "Concepto")+10));
        } elseif(strpos($fila['detalle'], "Observación")){
            $fila['detalle'] = trim(substr($fila['detalle'], strpos($fila['detalle'], "Observación")+13));
        } elseif(strpos($fila['detalle'], "Nombre")){
            $fila['detalle'] = "Pago a ".trim(substr($fila['detalle'], strpos($fila['detalle'], "Nombre")+8));
        } elseif(strpos($fila['detalle'], "Deudor")){
            $fila['detalle'] = "Cobranza de ".trim(substr($fila['detalle'], strpos($fila['detalle'], "Deudor")+8));
        } elseif(strpos($fila['detalle'], "DESDE VIAJE")){
            $fila['detalle'] = trim(substr($fila['detalle'], 0,strpos($fila['detalle'], "DESDE VIAJE")));
        }
        if(stripos($fila['detalle'], ". DE LA CTA. CTE. 5")){
            $fila['detalle'] = str_ireplace(". DE LA CTA. CTE. 5", '', $fila['detalle']);
        }
        
        if(!isset($_SESSION['concepto'][$fila['concepto']])){
            $stmt3 = odbc_exec2($mssql2, "SELECT concepto  Collate SQL_Latin1_General_CP1253_CI_AI as concepto FROM [sqlcoop_dbimplemen].[dbo].[CONCECON] WHERE codigo='$fila[concepto]';", __LINE__, __FILE__);
            //ChromePHP::log("SELECT concepto FROM [sqlcoop_dbimplemen].[dbo].[CONCECON] WHERE codigo='$fila[concepto]';");
            $tmp = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC);
            $tmp = substr($tmp['concepto'], 5);
            $_SESSION['concepto'][$fila['concepto']] = ucfirst(strtolower(trim($tmp)));
        }



        switch(intval($fila['concepto'])) {
            case 30:
              $sqlFletero = "SELECT nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre FROM [sqlcoop_dbimplemen].[dbo].ordservi, [sqlcoop_dbimplemen].[dbo].fleteros WHERE [sqlcoop_dbimplemen].[dbo].ordservi.idtranglob=$fila[idtranglob] AND [sqlcoop_dbimplemen].[dbo].ordservi.fletero=[sqlcoop_dbimplemen].[dbo].fleteros.fletero";
              //ChromePhp::log($sqlFletero, $fila['asiento']);
              $stmt3 = odbc_exec2($mssql2, $sqlFletero, __LINE__, __FILE__);
              $rowFletero = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC);
              if($rowFletero)
                $nombre = $rowFletero['nombre'];
              else
                $nombre ="SIN DATOS";
              break;
            case 1039:
              $sqlFletero = "SELECT nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre FROM [sqlcoop_dbimplemen].[dbo].histccfl, [sqlcoop_dbimplemen].[dbo].fleteros WHERE [sqlcoop_dbimplemen].[dbo].histccfl.idtranglob=$fila[idtranglob] AND [sqlcoop_dbimplemen].[dbo].histccfl.fletero=[sqlcoop_dbimplemen].[dbo].fleteros.fletero";
              //ChromePhp::log($sqlFletero, $fila['asiento']);
              $stmt3 = odbc_exec2( $mssql2, $sqlFletero, __LINE__, __FILE__);
              $arrayDetalle=explode("DESDE VI", utf8_encode($fila['detalle']));
              $detalle=$arrayDetalle[0];
              $rowFletero = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC);
              if($rowFletero)
                $nombre = $rowFletero['nombre'];
              else
                $nombre = "SIN DATOS";
              break;
            case 1057:
              // ajuste negativo cliente
              $arrayDetalle=explode("Cliente:", utf8_encode($fila['detalle']));
              $detalle=$arrayDetalle[0];
              $nombre="".$arrayDetalle[1];
              break;
            case 1024:
              // deposito bancario
              $arrayDetalle=explode("Observación:", ($fila['detalle']));
              $detalle=$arrayDetalle[0];
              $nombre=$arrayDetalle[1];
              break;
            case 1029:
            case 1030:
              // deposito bancario
              $arrayDetalle=explode("Nombre:", utf8_encode($fila['detalle']));
              $detalle=$arrayDetalle[0];
              $nombre="Proveedor ".$arrayDetalle[1];
              break;
            case 1031:
              // deposito bancario
              $arrayDetalle=explode("Nombre:", utf8_encode($fila['detalle']));
              $detalle=$arrayDetalle[0];
              @$nombre="".$arrayDetalle[1];
              break;
            case 1002:
              // anulacion contable
              // Anulación de Asiento : COMPRAS : 0007 - 0000000482 
              $arrayDetalle=explode("Anulación de Asiento :", utf8_encode($fila['detalle']));
              $detalle="Anulación de Asiento";
              //$nombre="".$arrayDetalle[1];
              break;
            case 1025:
              // FACTURA A 0001-00000315 Fletero: Fogel Daniel Albino 
              if(strpos ( $fila['detalle'], 'Proveedor:')){
                $arrayDetalle=explode("Proveedor:", utf8_encode($fila['detalle']));
                @$nombre="Proveedor ".$arrayDetalle[1];
              }elseif(strpos ( $fila['detalle'], 'Fletero:')){
                $arrayDetalle=explode("Fletero:", utf8_encode($fila['detalle']));
                @$nombre="Fletero ".$arrayDetalle[1];
              }
              $detalle=$arrayDetalle[0];
              break;
              
            case 1033:
              // pago vario
              //COMPROBANTE DE CAJA 0007-00000086 por FLETES Y EXTRACTORA MORGADO // 38
              $detalle=trim(substr(utf8_encode($fila['detalle']),0,34));
              $nombre=trim(substr(utf8_encode($fila['detalle']),38));
              break;
            case 1034:
              // recibo vario
              // COMPROBANTE DE CAJA 0007-00000097 por saldo ddjj IVA octubre 2015. 
              $arrayDetalle=explode("Deudor:", utf8_encode($fila['detalle']));
              $arrayDetalle=explode(" por ", utf8_encode($fila['detalle']));
              $detalle=$arrayDetalle[0];
              $nombre=$arrayDetalle[1];
              break;
            case 24:
              // liquidacion de fletero
              // FACTURA A 0002-0000000009 al fletero Mainini Miguel Angel 
              $arrayDetalle=explode("al fletero", utf8_encode($fila['detalle']));
              $detalle=$arrayDetalle[0];
              $nombre="Fletero ".$arrayDetalle[1];
              break;
            case 6:
            case 11:
            case 1035:
            case 4:
            case 8:
            case 9:
            case 1018:
              // facturas simples y recibos a clientes
              $arrayDetalle=explode("Cliente:", utf8_encode($fila['detalle']));
              $detalle=$arrayDetalle[0];
              $nombre=$arrayDetalle[1];
              break;
            case 22:
              $nombre="Asiento Manual";
          }


        $importe = ($fila['debe']>0)?$fila['debe']:$fila['haber'];
        $neg = ($fila['haber']>0)?"neg":"";
        
        // revisa si el movimiento está conciliado
        if(!isset($rowConciliadoSetup[$fila['idtranglob']])){
            // aun no conciliado
            //$clase=(isset($_POST['soloNoConciliado'])&&$rowConciliado['idConciliado']<>0)?'info':$clase;
            $tdConciliado = "<td class='noConciliado' id='$fila[idtranglob]'><input type='checkbox' name='idsetup[]' value='".strtotime($fila['fecha']->format('Y-m-d'))."_$fila[idtranglob]' class='setup $neg' rel='$importe'/></td>";
        } else {
            $tdConciliado = "<td class='m{$rowConciliadoSetup[$fila['idtranglob']]}'><span class='label label-info mConciliado'>{$rowConciliadoSetup[$fila['idtranglob']]}</span></td>";
        }


        $tabla .= "<tr id='g$fila[idtranglob]' class='S'><td><span data-toggle='tooltip' data-original-title='$fila[asiento]'>$fechaEmision</span></td><td>$fila[asiento]</td><td>".strtoupper($fila['detalle'])." - $nombre</td>".(($fila['haber']>0)?"<td>$0,00</td>":"")."<td style='text-align:right'  id='c_$fila[idtranglob]_$fila[concepto]' class='".(($fila['haber']>0)?" neg":"")."'>".money_format('%.2n', $importe)."</td>".(($fila['haber']>0)?"":"<td>$0,00</td>")."<td>".$_SESSION['concepto'][$fila['concepto']]."</td></tr>";

        $respuesta[] = array('idtranglob' => $fila['idtranglob'],'fecha'=> "$fechaEmision", 'asiento' => $fila['asiento'], 'detalle'=>"$fila[detalle] - $nombre", 'debe' => $fila['debe'], 'haber' => $fila['haber'], 'concepto'=>$_SESSION['concepto'][$fila['concepto']]);
    }
    //echo "<tr><td>$a resultados</td></tr>";

    //echo $tabla;
    echo json_encode($respuesta);

} else {
    die;
}
?>