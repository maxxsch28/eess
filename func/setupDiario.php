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
    $rango = " AND YEAR(a.fecha)=$anio AND MONTH(a.fecha)=$mes";
} else {
    // anual
    $rangoInicio = date("Y-01-01");
    $anio = substr($_POST['mes'],0,4);
    $rango = " AND YEAR(a.fecha)=$anio";
}

//$rango = " AND YEAR(a.fecha)='2020'";

$orden = "DESC";
$ordenFecha = "ASC";

ChromePhp::log("Diario Setup");
$filtroDescripcion = "";

/*
cod_libro	libro	sucursal	asiento	item	fecha	cuentacont	cuentatota	ordenamien	volcado	debe	haber	signo	cantidad	cotizacion	transaccio	exportacio	moneda	sucutranu	numetranu	suctranglo	idtranglob	idempresa	cuentaaux	impumanual	observacio	tipo_impu	cod_libro	libro	sucursal	asiento	fecha_asie	concepto	detalle	exportacio	suctranglo	idtranglob	idempresa	ejercicio	tipoorigen	fecha_asbk	transaccio	invertir	libro_orig	suc_orig	asie_orig
5	IMPUTACIONES	10	390	1	2021-04-01 00:00:00.000	340019	214000	620003	0	0.00	99999.05	+	99999.05	1.0000	2237	 	1	0	0	10	2559	1	0	0	NULL	2	5	IMPUTACIONES	10	390	2021-04-01 00:00:00.000	54	Extracción   10-     242 de la Cuenta EE SS  - Concepto: x lote cheques 	 	10	2559	1	12	0	1900-01-01 00:00:00.000	2237	0	0	0	0*/

$sqlSetup = "SELECT a.asiento, a.fecha, a.libro, a.sucursal, item, cuentacont, debe, haber, a.idtranglob, detalle Collate SQL_Latin1_General_CP1253_CI_AI as detalle, nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre, b.concepto FROM dbo.asiecont a, concasie b, plancuen c WHERE a.asiento=b.asiento AND a.idtranglob=b.idtranglob AND a.cuentacont=c.codigo $rango ORDER BY a.fecha $ordenFecha, a.asiento, debe DESC, haber DESC;";


if(!isset($_POST['caja'])){
    die;
}
if($_POST['caja']=='Setup'){
    // muestro contenido mayor de contabilidad de Setup

    ChromePhp::log($sqlSetup);


    $stmt = odbc_exec2( $mssql2, $sqlSetup, __LINE__, __FILE__);

    $tabla = "";$a=$q=0;
    $renglon = array();
    $asiento = array();
    $desbalanceo = array();
    while($fila = sqlsrv_fetch_array($stmt)){
        $asiento[$fila['asiento']][$fila['item']] = $fila;
        $desbalanceo[$fila['asiento']] += ($fila['debe']-$fila['haber']);
        //echo "<tr><td>$fila[asiento]</td><td>$fila[debe]</td><td>$fila[haber]</td><td>{$desbalanceo[$fila['asiento']]}</td></tr>";
    }//die;

    
    foreach($asiento as $key => $asiento2){
        foreach($asiento2 as $key => $fila){
            if((abs($desbalanceo[$fila['asiento']])>0.01&&$_GET['satus']=='desb')||!isset($_GET['status'])){
                $desbalanceado = "";
                if(abs($desbalanceo[$fila['asiento']])>0.01){
                    $desbalanceado="<span class='label label-danger'>$ {$desbalanceo[$fila['asiento']]}</span>";
                } 
                if(!isset($encabeza)||$encabeza<>$fila['asiento']){
                    if(isset($encabeza)){
                        $tabla .=  "<tr class='fila'><td class='cuentaH'>&nbsp;</td><td class='debe cierre x'>".number_format(str_replace(',', '.', sprintf("%.2f",$totDebe)), 2, ',', '.')."</td><td class='haber cierre x'>".number_format(str_replace(',', '.', sprintf("%.2f",$totHaber)), 2, ',', '.')."</td></tr></tbody>";	
                    }
                    $tabla .= "<tbody class='asientoTransporte' id='$fila[idtranglob]_$fila[concepto]'><tr class='encabezaAsiento encabezado2' style='line-height:12em;' title='".$fila['concepto']."-{$_SESSION['concepto'][$fila['concepto']]}'><td align='left' rowspan='".((isset($fila['nombre']))?'1':'2')."'>$desbalanceado $fila[detalle]</td><td colspan='2'>(".$fila['fecha']->format('d/m/Y').") Nº $fila[asiento]</td></tr>
                    ";
                    $encabeza = $fila['asiento'];
                    $totDebe = $totHaber = 0;
                }
                
                $debe = sprintf("%.2f",$fila['debe']);
                $haber = sprintf("%.2f",$fila['haber']);
                if($fila['debe']>0)
                    $tabla .= "<tr class='fila'><td class='cuentaD'>($fila[cuentacont]) $fila[nombre]</td><td class='debe x'>$debe</td><td class='haber'>&nbsp;</td></tr>";

                if($fila['haber']>0)
                    $tabla .= "<tr class='fila'><td class='cuentaH'>($fila[cuentacont]) $fila[nombre]</td><td class='debe'>&nbsp;</td><td class='haber x'>$haber</td></tr>";
                
                $totDebe+=$fila['debe'];
                $totHaber+=$fila['haber'];
    

                
            }

            

            $respuesta[] = array('idtranglob' => $fila['idtranglob'],'fecha'=> "$fechaEmision", 'asiento' => $fila['asiento'], 'detalle'=>"$fila[detalle] - $nombre", 'debe' => $fila['debe'], 'haber' => $fila['haber'], 'concepto'=>$_SESSION['concepto'][$fila['concepto']]);
        }
    }
    $tabla .=  "<tr class='fila'><td class='cuentaH'>&nbsp;</td><td class='debe cierre x'>".number_format(str_replace(',', '.', sprintf("%.2f",$totDebe)), 2, ',', '.')."</td><td class='haber cierre x'>".number_format(str_replace(',', '.', sprintf("%.2f",$totHaber)), 2, ',', '.')."</td></tr></tbody>";	

    //echo "<tr><td>$a resultados</td></tr>";

    echo $tabla;
    //echo json_encode($respuesta);

} else {
    die;
}
?>