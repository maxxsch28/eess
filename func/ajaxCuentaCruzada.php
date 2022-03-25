<?php
// ajaxCuentaCruzada.php
// recibe datos sobre que sistema necesito la caja y un rango de fechas

include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//Array ( [mes] => 31/12/2019 [rangoInicio] => 01/01/2018 ) 

//print_r($_POST);

setlocale(LC_MONETARY, 'es_AR');

if(isset($_POST['rangoInicio'])){
  $fin=explode("/", $_POST['rangoInicio']);
  $rangoInicio = "$fin[2]-$fin[1]-$fin[0]";
} else {
  $rangoInicio = date("Y-01-01");
  $rangoInicio = date("2021-01-01");
}
if(isset($_POST['rangoFin'])){
  $fin=explode("/", $_POST['rangoFin']);
  $rangoFinal = "$fin[2]-$fin[1]-$fin[0] 23:59:59";
} else {
  $rangoFinal = date("Y-m-d")." 23:59:59";
}

$orden = "DESC";
$ordenFecha = "DESC";

if(isset($_POST['go'])&&$_POST['go']==1){
    // Adelantos en gasoil
    $cuentaContableCalden = 529;
    $cuentaContableSetup = 211101;
    $proveedor = 321;
    ChromePhp::log("CAJAS ADELANTOS GASOIL");
    $filtroDescripcion = " AND ([sqlcoop_dbimplemen].[dbo].[concasie].detalle like ('PAGO A COOP PROV CONS Y CRED TRANS AUT%') OR [sqlcoop_dbimplemen].[dbo].[concasie].detalle like ('%GASOIL SOCIOS COOP TRANSPORTE%'))";

} else {
    // lo que ya está hecho
    $cuentaContableCalden = 742;
    $cuentaContableSetup = 340019;
    ChromePhp::log("CAJAS CRUZADAS");
}



// levanta datos conciliación
$sqlConciliados = "SELECT * FROM  [coop].[dbo].[cajasCruzadas] WHERE fechaCalden>='$rangoInicio' OR fechaSetup>='$rangoInicio'";
// idConciliacion, fechaCalden, fechaSetup, idAsiento, idTranglob, auto

$stmt = odbc_exec2( $mssql4, $sqlConciliados, __LINE__, __FILE__);

$rowConciliadoSetup = $rowConciliadoCalden = array();
while($fila = sqlsrv_fetch_array($stmt)){
    $rowConciliadoCalden[$fila['idAsiento']] = ($fila['idConciliacionOriginal'] === NULL)?$fila['idConciliacion']:$fila['idConciliacionOriginal'];
    $rowConciliadoSetup[$fila['idTranglob']] = ($fila['idConciliacionOriginal'] === NULL)?$fila['idConciliacion']:$fila['idConciliacionOriginal'];
}




if(isset($_REQUEST['status'])&&$_REQUEST['status']=='nc'){
    // solo No conciliados
    $sqlCalden = "SELECT a.idasiento, a.fecha, Concepto, IdModeloContable, DebitoCredito, Importe FROM dbo.asientos a, dbo.AsientosDetalle b WHERE IdCuentaContable=$cuentaContableCalden AND a.IdAsiento=b.IdAsiento AND a.fecha>='$rangoInicio' AND a.fecha<='$rangoFinal' AND a.idasiento NOT IN (SELECT idasiento FROM [coop].[dbo].[cajasCruzadas]) ORDER BY a.fecha $ordenFecha;";

    $sqlSetup = "SELECT cantidad, [sqlcoop_dbimplemen].[dbo].[asiecont].asiento, [sqlcoop_dbimplemen].[dbo].[concasie].detalle, debe, haber, fecha, [sqlcoop_dbshared].[dbo].[plancuen].nombre, cuentacont, ordenamien, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[asiecont].idtranglob, [sqlcoop_dbimplemen].[dbo].[asiecont].cod_libro FROM [sqlcoop_dbimplemen].[dbo].[asiecont], [sqlcoop_dbimplemen].[dbo].[concasie], [sqlcoop_dbshared].[dbo].[plancuen] WHERE [sqlcoop_dbshared].[dbo].[plancuen].codigo=cuentacont AND [sqlcoop_dbimplemen].[dbo].[asiecont].cod_libro=[sqlcoop_dbimplemen].[dbo].[concasie].cod_libro AND [sqlcoop_dbimplemen].[dbo].[asiecont].asiento=[sqlcoop_dbimplemen].[dbo].[concasie].asiento AND [sqlcoop_dbimplemen].[dbo].[asiecont].transaccio=[sqlcoop_dbimplemen].[dbo].[concasie].transaccio AND cuentacont = $cuentaContableSetup AND fecha>='$rangoInicio' AND fecha<='$rangoFinal' AND [sqlcoop_dbimplemen].[dbo].[asiecont].idtranglob NOT IN (SELECT idTranglob FROM [coop].[dbo].[cajasCruzadas]) $filtroDescripcion UNION SELECT cantidad, [sqlcoop_dbimplemen].[dbo].[diario].asiento, [sqlcoop_dbimplemen].[dbo].[concasie].detalle, debe, haber, fecha, [sqlcoop_dbshared].[dbo].[plancuen].nombre, cuentacont, ordenamien, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[diario].idtranglob, [sqlcoop_dbimplemen].[dbo].[diario].cod_libro FROM [sqlcoop_dbimplemen].[dbo].[diario], [sqlcoop_dbimplemen].[dbo].[concasie], [sqlcoop_dbshared].[dbo].[plancuen] WHERE [sqlcoop_dbshared].[dbo].[plancuen].codigo=cuentacont AND [sqlcoop_dbimplemen].[dbo].[diario].cod_libro=[sqlcoop_dbimplemen].[dbo].[concasie].cod_libro AND [sqlcoop_dbimplemen].[dbo].[diario].asiento=[sqlcoop_dbimplemen].[dbo].[concasie].asiento AND [sqlcoop_dbimplemen].[dbo].[diario].transaccio=[sqlcoop_dbimplemen].[dbo].[concasie].transaccio AND cuentacont=$cuentaContableSetup AND fecha>='$rangoInicio' AND fecha<='$rangoFinal' AND [sqlcoop_dbimplemen].[dbo].[diario].idtranglob NOT IN (SELECT idTranglob FROM [coop].[dbo].[cajasCruzadas]) $filtroDescripcion ORDER BY fecha $ordenFecha";

} elseif(isset($_REQUEST['status'])&&$_REQUEST['status']=='c'){
    // solo conciliados, ordenados por número de conciliación
    $sqlCalden = "SELECT a.idasiento, a.fecha, Concepto, IdModeloContable, DebitoCredito, Importe, c.idConciliacion FROM dbo.asientos a, dbo.AsientosDetalle b, [coop].[dbo].[cajasCruzadas] c WHERE IdCuentaContable=$cuentaContableCalden AND a.IdAsiento=b.IdAsiento AND a.fecha>='$rangoInicio' AND a.fecha<='$rangoFinal' AND a.idasiento=c.idasiento ORDER BY a.fecha $ordenFecha, c.idConciliacion $orden;";

    $sqlSetup = "SELECT cantidad, [sqlcoop_dbimplemen].[dbo].[asiecont].asiento, [sqlcoop_dbimplemen].[dbo].[concasie].detalle, debe, haber, fecha, [sqlcoop_dbshared].[dbo].[plancuen].nombre, cuentacont, ordenamien, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[asiecont].idtranglob, [sqlcoop_dbimplemen].[dbo].[asiecont].cod_libro, c.idConciliacion FROM [sqlcoop_dbimplemen].[dbo].[asiecont], [sqlcoop_dbimplemen].[dbo].[concasie], [sqlcoop_dbshared].[dbo].[plancuen], [coop].[dbo].[cajasCruzadas] c  WHERE c.idTranglob=[sqlcoop_dbimplemen].[dbo].[asiecont].idtranglob AND [sqlcoop_dbshared].[dbo].[plancuen].codigo=cuentacont AND [sqlcoop_dbimplemen].[dbo].[asiecont].cod_libro=[sqlcoop_dbimplemen].[dbo].[concasie].cod_libro AND [sqlcoop_dbimplemen].[dbo].[asiecont].asiento=[sqlcoop_dbimplemen].[dbo].[concasie].asiento AND [sqlcoop_dbimplemen].[dbo].[asiecont].transaccio=[sqlcoop_dbimplemen].[dbo].[concasie].transaccio AND cuentacont = $cuentaContableSetup AND fecha>='$rangoInicio' AND fecha<='$rangoFinal' $filtroDescripcion UNION SELECT cantidad, [sqlcoop_dbimplemen].[dbo].[diario].asiento, [sqlcoop_dbimplemen].[dbo].[concasie].detalle, debe, haber, fecha, [sqlcoop_dbshared].[dbo].[plancuen].nombre, cuentacont, ordenamien, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[diario].idtranglob, [sqlcoop_dbimplemen].[dbo].[diario].cod_libro, c.idConciliacion FROM [sqlcoop_dbimplemen].[dbo].[diario], [sqlcoop_dbimplemen].[dbo].[concasie], [sqlcoop_dbshared].[dbo].[plancuen], [coop].[dbo].[cajasCruzadas] c WHERE c.idTranglob=[sqlcoop_dbimplemen].[dbo].[diario].idtranglob AND [sqlcoop_dbshared].[dbo].[plancuen].codigo=cuentacont AND [sqlcoop_dbimplemen].[dbo].[diario].cod_libro=[sqlcoop_dbimplemen].[dbo].[concasie].cod_libro AND [sqlcoop_dbimplemen].[dbo].[diario].asiento=[sqlcoop_dbimplemen].[dbo].[concasie].asiento AND [sqlcoop_dbimplemen].[dbo].[diario].transaccio=[sqlcoop_dbimplemen].[dbo].[concasie].transaccio AND cuentacont=$cuentaContableSetup AND fecha>='$rangoInicio' AND fecha<='$rangoFinal' $filtroDescripcion ORDER BY fecha $ordenFecha, c.idConciliacion $orden";

} else {
    // resto
    $sqlCalden = "SELECT a.idasiento, a.fecha, Concepto, IdModeloContable, DebitoCredito, Importe FROM dbo.asientos a, dbo.AsientosDetalle b WHERE IdCuentaContable=$cuentaContableCalden AND a.IdAsiento=b.IdAsiento AND a.fecha>='$rangoInicio' AND a.fecha<='$rangoFinal' ORDER BY a.fecha $ordenFecha;";

    $sqlSetup = "SELECT cantidad, [sqlcoop_dbimplemen].[dbo].[asiecont].asiento, [sqlcoop_dbimplemen].[dbo].[concasie].detalle, debe, haber, fecha, [sqlcoop_dbshared].[dbo].[plancuen].nombre, cuentacont, ordenamien, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[asiecont].idtranglob, [sqlcoop_dbimplemen].[dbo].[asiecont].cod_libro FROM [sqlcoop_dbimplemen].[dbo].[asiecont], [sqlcoop_dbimplemen].[dbo].[concasie], [sqlcoop_dbshared].[dbo].[plancuen] WHERE [sqlcoop_dbshared].[dbo].[plancuen].codigo=cuentacont AND [sqlcoop_dbimplemen].[dbo].[asiecont].cod_libro=[sqlcoop_dbimplemen].[dbo].[concasie].cod_libro AND [sqlcoop_dbimplemen].[dbo].[asiecont].asiento=[sqlcoop_dbimplemen].[dbo].[concasie].asiento AND [sqlcoop_dbimplemen].[dbo].[asiecont].transaccio=[sqlcoop_dbimplemen].[dbo].[concasie].transaccio AND cuentacont = $cuentaContableSetup AND fecha>='$rangoInicio' AND fecha<='$rangoFinal' $filtroDescripcion UNION SELECT cantidad, [sqlcoop_dbimplemen].[dbo].[diario].asiento, [sqlcoop_dbimplemen].[dbo].[concasie].detalle, debe, haber, fecha, [sqlcoop_dbshared].[dbo].[plancuen].nombre, cuentacont, ordenamien, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[diario].idtranglob, [sqlcoop_dbimplemen].[dbo].[diario].cod_libro FROM [sqlcoop_dbimplemen].[dbo].[diario], [sqlcoop_dbimplemen].[dbo].[concasie], [sqlcoop_dbshared].[dbo].[plancuen] WHERE [sqlcoop_dbshared].[dbo].[plancuen].codigo=cuentacont AND [sqlcoop_dbimplemen].[dbo].[diario].cod_libro=[sqlcoop_dbimplemen].[dbo].[concasie].cod_libro AND [sqlcoop_dbimplemen].[dbo].[diario].asiento=[sqlcoop_dbimplemen].[dbo].[concasie].asiento AND [sqlcoop_dbimplemen].[dbo].[diario].transaccio=[sqlcoop_dbimplemen].[dbo].[concasie].transaccio AND cuentacont=$cuentaContableSetup AND fecha>='$rangoInicio' AND fecha<='$rangoFinal' $filtroDescripcion ORDER BY fecha $ordenFecha";
}





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


        $tabla .= "<tr id='g$fila[idtranglob]' class='S'><td><span data-toggle='tooltip' data-original-title='$fila[asiento]'>$fechaEmision</span></td><td>".strtoupper($fila['detalle'])."</td><td style='text-align:right'  id='c_$fila[idtranglob]_$fila[concepto]' class='mAsS".(($fila['haber']>0)?" neg":"")."'>".money_format('%.2n', $importe)."</td>$tdConciliado</tr>";
        $a++;
    }
    $encabezado = "<tr><th>Fecha</th><th>Detalle</th><th width='22%' colspan='2'>Importe</th></tr>";
    echo $encabezado.$tabla;

} else if ($_POST['caja']=='Calden'){
    // muestro contenido mayor de contabilidad de Calden
    
    //ChromePhp::log($sqlCalden);
    $stmt = odbc_exec2( $mssql, $sqlCalden, __LINE__, __FILE__);

    $tabla = "";$a=$q=0;
    while($fila = sqlsrv_fetch_array($stmt)){
        // revisa si el movimiento está conciliado
        if(!isset($rowConciliadoCalden[$fila['idasiento']])){
            // aun no conciliado
            $classConciliado='noConciliado2';
            //$clase=(isset($_POST['soloNoConciliado'])&&$rowConciliado['idConciliado']<>0)?'info':$clase;
            $neg = ($fila['DebitoCredito']>0)?"neg":"";
            $tdConciliado = "<td class=''><input type='checkbox' name='idcalden[]' value='".strtotime($fila['fecha']->format('Y-m-d'))."_$fila[idasiento]' class='calden $neg' rel='$fila[Importe]'/></td>";
        } else {
            $classConciliado="conciliado_{$rowConciliadoCalden[$fila['idasiento']]}";
            $tdConciliado = "<td class='m{$rowConciliadoCalden[$fila['idasiento']]}'><span class='label label-info mConciliado'>{$rowConciliadoCalden[$fila['idasiento']]}</span></td>";
        }

        $fechaEmision = $fila['fecha']->format('d/m/y');
        
        if(!isset($orientacion)){
            $tabla .= "<tr id='g$fila[idasiento]'>$tdConciliado<td style='text-align:right' id='c_$fila[idasiento]' class='mAsC".(($fila['DebitoCredito']>0)?" neg":"")."'>".money_format('%.2n', $fila['Importe'])."</td><td>$fila[Concepto]</td><td>$fechaEmision</td><td alt='$fila[IdModeloContable]'>$fila[idasiento]</td></tr>";
        } else {
            $tabla .= "<tr id='g$fila[idasiento]' class='C'>$tdConciliado<td alt='$fila[IdModeloContable]'>$fila[idasiento]</td></td><td>$fechaEmision</td><td>$fila[Concepto]</td><td style='text-align:right' ".(($fila['DebitoCredito']>0)?" class='neg'":"").">".money_format('%.2n', $fila['Importe'])."</td></tr>";    
        }
        $a++;
    }   
    $encabezado = "<tr><th width='25%' colspan='2'>Importe</th><th>Detalle</th><th colspan=2>Fecha y Asiento</th></tr>";
    echo $encabezado.$tabla;
} else {
    die;
}
?>