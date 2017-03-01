<?php
// listaFacturasOrden.php
// revisa todos los clientes "orden" de caldenoil, saca sus respectivas facturas en cta cte impagas y revisa para cada uno si existe un adelanto en Setup por el mismo importe.





// version 2 revisa todos los adelantos en gasoil en setup correspondientes a coop de transporte y busca que el equivalente en calden pertenezca a una cuenta "orden" y esté en cuenta corriente.



include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';
$soloExternos = (isset($_POST['soloExternos']))?" AND tipoviaje=$_POST[soloExternos]":"";
if(isset($_POST['mes'])){
	$periodo = "";
    $anio = substr($_POST['mes'], 0, 4);
    $mes = substr($_POST['mes'], 5, 2);
	$sqlClientes = "SELECT parttram.Salida, parttram.SalidaHora, Partes.Sucursal_E, Partes.Parte, Parttram.Tramo,parttram.Origen,parttram.nom_Origen, parttram.Loc_Origen, parttram.ProvOrigen, Parttram.Destino, Parttram.Nom_Destin, Parttram.Loc_Desti, Parttram.Chofer, Choferes.Nombre as Cho_Nombre, parttram.Fletero, Fleteros.Nombre as Fle_Nombre, parttram.RegimenGan, AcomPart.Chofer as Acompanante, Acompa.Nombre as Aco_Nombre, parttram.Equipo, Equipos.Patente as Equ_Patent, Partes.Tara, Partes.Tara_Llega, Equipos.Patente2 as Equ_Paten2, parttram.Acoplado, Acoplado.Patente as Aco_Patent, parttram.tipo_Equi as TipoEquipo, Partes.Tipo_Servi, TipoServ.Detalle As DetServ , TipoEqui.Detalle as TpE_Detall, Partes.Kilometros, Partes.Adelanto, Partes.AdelEfec, Partes.AdelGasOil, Partes.AdelOtros, Partes.AdelChof, Partes.Negocio, Negocios.Nombre as Neg_Nombre, Partes.TipoViaje, TipoPedi.Nombre as TpV_Nombre, Partes.APagar_Fle, Partes.Pagado_Fle, Partes.Imp_Peajes, Partes.Pagado_Pea, Partes.PorcDescPa, Parttram.LiquidarCh, Parttram.SucuLiqCho, Parttram.LiquiChofe, Partes.SucuRendi, Partes.Rendicion, Partes.Observacio, Partes.SucuOrdCar, Partes.NumeOrdCar, Partes.Cumplido, Partes.Rendido, Partes.Anulado, PREFPVTA.Comprobant as Compr_Pref, PREFPVTA.TipFactu, PREFPVTA.SucFactu, PREFPVTA.NumFactu, ParteVta.SucuPrefac, ParteVta.NumePrefac, CASE WHEN Parttram.nume_cumtr=0 THEN 'No' ELSE 'Si' END as nume_cumtr,CASE WHEN Parttram.nume_rentr=0 THEN 'No' ELSE 'Si' END as nume_rentr, Partes.FechaIngre, Partes.HoraIngre, Partes.IdOperador, Partes.Operador, Partes.SucTranGlo, Partes.IdTranGlob, Partes.IdEmpresa, Origen.Localidad NomOrigen, Destino.Localidad NomDestino, Partes.CantEnvios, Partes.Unidades, Partes.Kilos, Partes.KmRecorri, Partes.Declarado, ParteVta.Importe as ImpVta, ParteVta.Cliente, dbo.clientes.nombre FROM dbo.clientes, Partes INNER JOIN PARTTRAM ON partes.sucursal_e = parttram.sucursal AND partes.parte = parttram.numero INNER JOIN Choferes ON parttram.Chofer = Choferes.Codigo INNER JOIN Fleteros ON parttram.Fletero = Fleteros.Fletero INNER JOIN Equipos ON parttram.Equipo = Equipos.Equipo INNER JOIN TipoEqui ON parttram.Tipo_Equi = TipoEqui.Codigo LEFT JOIN Equipos as Acoplado ON parttram.Acoplado = Acoplado.Equipo LEFT JOIN Ciudades Origen  ON PartTram.Origen = Origen.Codigo  LEFT JOIN Ciudades Destino ON PartTram.Destino = Destino.Codigo LEFT JOIN Negocios ON Partes.Negocio = Negocios.Codigo LEFT JOIN TipoPedi ON Partes.TipoViaje  = TipoPedi.Codigo LEFT JOIN TipoServ ON Partes.Tipo_Servi = TipoServ.Codigo LEFT JOIN AcomPart ON Partes.Sucursal_E = AcomPart.Sucursal_E AND Partes.Parte = AcomPart.Parte LEFT JOIN Choferes AS Acompa ON AcomPart.Chofer = Acompa.Codigo  INNER JOIN ParteVta ON Partes.Sucursal_E = ParteVta.Sucursal_E AND Partes.Parte = ParteVta.Parte LEFT JOIN PREFCVIA PREFPVTA ON PREFPVTA.Sucursal = ParteVta.SucuPrefac AND PREFPVTA.Numero = ParteVta.NumePrefac WHERE Partes.Anulado = 0 AND datepart(year, Partes.Salida)='$anio' AND datepart(month, Partes.Salida)='$mes' and rendtram=1 $soloExternos AND ParteVta.Cliente=dbo.clientes.codigo ORDER BY fle_nombre, tipoviaje, parttram.Salida, parttram.SalidaHora, Partes.Sucursal_E, Partes.Parte";//AND clientes.idCliente=1641
	echo $sqlClientes;
} else {
	$limit=' LIMIT 5';
	$sqlClientes = "select clientes.idCliente as idC, clientes.socio, facturas.idFacturaRecibida, sum(usoRed) AS uR, sum( interesMora ) AS iM, sum( ingresosBrutos ) AS iB,facturaB, (SELECT count(celular) FROM `movistar.celulares` WHERE idCliente=idC) AS q,numeroFactura, nombre, codigo, periodo, facturas.idCliente, sum(cargoFijo) as cF, cargoFijo, (SUM( cargoFijo ) - cargoFijo) as Dif, sum(cargoVariable) as cV, sum(cargoBB) as cB, sum(IVA21) as IV1, sum(IVA27) as IV7, sum(otros) as o, sum(impuestosInternos) as iI, empleado from `movistar.facturasrecibidas` as facturas, `movistar.facturasitems` as items, `movistar.clientes` as clientes WHERE facturas.idFacturaRecibida=items.idFacturaRecibida and clientes.idCliente=facturas.idCliente group by facturas.idFacturaRecibida order by facturas.idFacturaRecibida desc $limit";
}

 // echo $sqlClientes;


$stmt = odbc_exec( $mssql2, $sqlClientes);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlClientes<br/>";
     die( print_r( sqlsrv_errors(), true));
}
$tabla = "";$a=0;
$totalB = 0;
$totalA = 0;
$cantidadFacturas = $cantidadClientes = 0;
$comision=array();
$totalAComisionar = array();
while($fila = odbc_fetch_array($stmt)){
    if(!isset($idSocio)){
        $idSocio = $fila['Fletero'];
        $socio = $fila['Fle_Nombre'];
        $comision=array();
        $comision[$idSocio]=array();
    }
    if($fila['Fletero']<>$idSocio){
        $tablaEncabezado = "<tr class='info comisionEncabezado'><td>$fila[Fletero] - <b>".strtoupper(utf8_encode($socio))."</b></td><td colspan='4' style='text-align:right'><b>";
        $a=0;
        $totalAFacturar = 0;
        foreach($comision[$idSocio] as $alicuota => $monto){
            $totalAFacturar += $monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100;
            $a++;
            // en el pie de cada fletero diferencia los totales por distinto tipo de comisiones
            if($a==1){
                $tablaEncabezado .= " {$_SESSION['transporte_tipos_comisiones'][$alicuota]}: $".sprintf("%.2f",$monto)." || Facturar $".sprintf("%.2f",$monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100)."</b></td></tr>";
            } else {
                $tablaEncabezado .= "<tr class='info comisionEncabezado'><td></td><td colspan='4' style='text-align:right'><b> {$_SESSION['transporte_tipos_comisiones'][$alicuota]}: $".sprintf("%.2f",$monto)." || Facturar $".sprintf("%.2f",$monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100)."</b></td></tr>";
            }
        }
        if($a>1){
            // totaliza comisiones por fletero
            $tablaEncabezado .= "<tr class='info comisionEncabezado'><td></td><td colspan='4' style='text-align:right'><b> TOTAL A FACTURAR SOCIO: $".sprintf("%.2f",$totalAFacturar)."</b></td></tr>";
        }
        $idSocio = $fila['Fletero'];
        $comision=array();
        $comision[$idSocio]=array();
        $socio = $fila['Fle_Nombre'];
        $codigosocio = $fila['Fletero'];
        
        $cantidadClientes++;
	}
    if(isset($tablaEncabezado)){
        $tabla.=$tablaEncabezado;
        unset($tablaEncabezado);
    }
    //Salida	SalidaHora	Sucursal_E	Parte	Tramo	Origen	nom_Origen	Loc_Origen	ProvOrigen	Destino	Nom_Destin	Loc_Desti	Fletero	Fle_Nombre	Kilometros	TipoViaje	TpV_Nombre	APagar_Fle	Pagado_Fle	LiquidarCh	Cumplido	Rendido	Anulado	NomOrigen	NomDestino	ImpVta	Cliente

    $importe = ($fila['APagar_Fle']>0)?$fila['APagar_Fle']:(($fila['LiquidarCh']>0)?$fila['LiquidarCh']:$fila['ImpVta']);
    $tipoComision = ($fila['TipoViaje']==0)?1:$fila['TipoViaje'];
    
    if(!isset($comision[$idSocio][$tipoComision])){
        $comision[$idSocio][$tipoComision]=0;
    }
    
    @$totalAComisionar[$tipoComision] += $importe;
    $comision[$idSocio][$tipoComision] += $importe;
    
    $tabla.= "<tr class='viaje'><td class='no'>($fila[Fletero]) - ".utf8_encode($fila['Fle_Nombre'])."</td><td>$fila[Loc_Origen] - $fila[Loc_Desti] (<span class='no2'>Viaje </span>$fila[Sucursal_E]-$fila[Parte])</td><td style='text-align:right'>$ ".sprintf("%.2f",$importe)."</td><td><small>".$_SESSION['transporte_tipos_comisiones'][$tipoComision]."</small></td><td>($fila[Cliente]) <small>$fila[nombre]</small></td></tr>";
    $cantidadFacturas++;
	
	// if(strlen($fila['nombre'])>
	//cantidadFacturas
	if(isset($fila['cantidadFacturas'])&&$fila['cantidadFacturas']>1){
        
		$multiple=true;
		$numeros = explode(',',$fila['numeroFactura']);
		$ids = explode(',',$fila['IDS']);
		$enlaceModificar = '';
		foreach($numeros as $key => $n){
            $cantidadFacturas++;
			//$enlaceModificar .= "<a href='/ypf/cargaMovistar.php?id=$ids[$key]'>$n</a><br/>";
		}
		//$enlaceModificar = substr($enlaceModificar,0,-5);
	} else {
        $cantidadFacturas++;
		$multiple=false;
	}
	//$tabla .= "<tr id='g$fila[idCliente]'><td class='nombreCliente'>$fila[nombre] $socio </td><td><b>$fila[codigo]</b></td><td>$enlaceModificar</td><td>".sprintf("%01.2f", (($fila2['cF']+$fila2['cV']+$fila2['uR']+$fila2['iM'])*1.27))."</td><td>".sprintf("%01.2f", ($fila2['cB']*1.21))."</td><td>".sprintf("%01.2f", $fila2['iI'])."</td><td>".sprintf("%01.2f", $fila2['o'])."</td>$tdSocio<td class='no'>".sprintf("%01.2f", $fila2['IV7'])."</td><td class='no'>".sprintf("%01.2f", $fila2['IV1'])."</td><td class=''>$total</td><td>$totalAFacturar</td></tr>";
	
	//$_SESSION['transporte_tipos_comisiones']
	
	
}
if($tabla=="")$tabla="<tr><td colspan='5' class='label-info center'>NO HAY FACTURAS CARGADAS EN ESTE PERIODO</td></tr>";
elseif(!isset($limit)) {
    $tablaEncabezado = "<tr class='info comisionEncabezado'><td>$codigosocio - <b>".strtoupper(utf8_encode($socio))."</b></td><td colspan='4' style='text-align:right'><b>";
    $a=0;$totalAFacturar = 0;
    foreach($comision[$idSocio] as $alicuota => $monto){
        $a++;$totalAFacturar += $monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100;
        if($a==1){
            $tablaEncabezado .= " {$_SESSION['transporte_tipos_comisiones'][$alicuota]}: $".sprintf("%.2f",$monto)." || Facturar $".sprintf("%.2f",$monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100)."</b></td></tr>";
        } else {
            $tablaEncabezado .= "<tr class='info comisionEncabezado'><td></td><td colspan='4' style='text-align:right'><b> {$_SESSION['transporte_tipos_comisiones'][$alicuota]}: $".sprintf("%.2f",$monto)."</b></td></tr>";
        }
    }
    if($a>1){
        // totaliza comisiones por fletero
        $tablaEncabezado .= "<tr class='info comisionEncabezado'><td></td><td colspan='4' style='text-align:right'><b> TOTAL A FACTURAR SOCIO: $".sprintf("%.2f",$totalAFacturar)."</b></td></tr>";
    }
    $tabla.="$tablaEncabezado<tr class='warning'><td colspan='1'>$cantidadClientes Fleteros, $cantidadFacturas Viajes</td><td colspan='4'>Total comisiones mensuales</td></tr>";
    foreach($totalAComisionar as $alicuota => $monto){
        $tabla .= "<tr class='warning'><td colspan='1'></td><td colspan='4'>{$_SESSION['transporte_tipos_comisiones'][$alicuota]} \$".sprintf("%.2f",$monto)." <b>\$".sprintf("%.2f",$monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100)."</b></td></tr>";
    }
}
echo $tabla;
?>