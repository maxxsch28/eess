<?php
// listaDocumentosComprasAFIP.php
// Muestra las facturas según Mis Comprobantes y las compara contra ambos sistemas
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

if(!isset($_SESSION['proveedoresCalden'])||1){
    unset($_SESSION['proveedoresCalden']);
    $sqlProveedoresCalden = "SELECT NumeroDocumento, idproveedor from [CoopDeTrabajo.Net].dbo.Proveedores WHERE activo=1 ORDER BY NumeroDocumento;";
    $stmt = odbc_exec2( $mssql, $sqlProveedoresCalden, __LINE__, __FILE__);
    while($fila = sqlsrv_fetch_array($stmt)){
        $cuitSinGuion = str_replace('-', '', $fila['NumeroDocumento']);
        if(array_key_exists($cuitSinGuion, $_SESSION['proveedoresCalden'])){
            $cuitSinGuion = $cuitSinGuion.'_';
        }
        $_SESSION['proveedoresCalden'][$cuitSinGuion] = $fila['idproveedor'];
    }
}

if(!isset($_SESSION['proveedoresCaldenEventuales'])||1){
    unset($_SESSION['proveedoresCaldenEventuales']);
    $sqlProveedoresCalden = "select distinct NumeroDocumento, RazonSocial FROM dbo.movimientospro where IdProveedor IS NULL AND NumeroDocumento NOT IN (SELECT NumeroDocumento FROM Proveedores) AND NumeroDocumento<>'  -        - ';";
    $stmt = odbc_exec2( $mssql, $sqlProveedoresCalden, __LINE__, __FILE__);
    while($fila = sqlsrv_fetch_array($stmt)){
        $cuitSinGuion = str_replace('-', '', $fila['NumeroDocumento']);
        $_SESSION['proveedoresCaldenEventuales'][$cuitSinGuion] = "1";
    }
}


if(!isset($_SESSION['proveedoresSetup'])){
    unset($_SESSION['proveedoresSetup'], $_SESSION['fleterosSetup'], $_SESSION['codprovivaSetup']);
    $sqlProveedoresSetup = "SELECT cuit, codigo, nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre FROM [sqlcoop_dbimplemen].dbo.proveedo  WHERE cuit<>'' AND CUIT<>'  -        - '  AND inhabfecha<=rehabfecha ORDER BY cuit";
    $stmt = odbc_exec2( $mssql2, $sqlProveedoresSetup, __LINE__, __FILE__);

    while($fila = sqlsrv_fetch_array($stmt)){
        $cuitSinGuion = str_replace('-', '', $fila['cuit']);
        $_SESSION['proveedoresSetup'][$cuitSinGuion] = $fila['codigo'];
        $_SESSION['codprovivaSetup'][$cuitSinGuion] = (strtoupper($fila['nombre']));
    }

    $sqlFleteroSetup = "SELECT cuit, fletero, nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre FROM [sqlcoop_dbimplemen].dbo.FLETEROS WHERE cuit<>''  AND CUIT<>'  -        - '  AND inhabfecha<=rehabfecha ORDER BY CUIT;";
    $stmt = odbc_exec2( $mssql2, $sqlFleteroSetup, __LINE__, __FILE__);

    while($fila = sqlsrv_fetch_array($stmt)){
        $cuitSinGuion = str_replace('-', '', $fila['cuit']);
        $_SESSION['fleterosSetup'][$cuitSinGuion] = $fila['fletero'];
        $_SESSION['codprovivaSetup'][$cuitSinGuion] = (strtoupper($fila['nombre']));
    }
}


switch($_POST['tipo']){
    case 'afip':
        if($_POST['status']=='faltantes'){
            $where = ' AND idMovimientoPro IS NULL AND idtranglob IS NULL';
        } else if($_POST['status']=='cargados') {
            $where = ' AND idMovimientoPro IS NOT NULL AND idtranglob IS NOT NULL';
        } else {
            $where = '';
        }

        if(isset($_POST['mes'])&&strlen($_POST['mes'])==6){
            // mensual
            $sqlClientes = "SELECT [idComprobante], [fecha], [IdTipoMovimientoProveedor], [tipo],[pv],[numDesde],[tipoDocEmisor],[cuit],[razonSocial], [ImputacionCuentaCorriente], [ImputacionCuentaCorriente]*[netoGravado] as netoGravado, [ImputacionCuentaCorriente]*[netoNoGravado] as netoNoGravado, [ImputacionCuentaCorriente]*[opExentas] as opExentas, [ImputacionCuentaCorriente]*[iva] as iva, [ImputacionCuentaCorriente]*[total] as total, idMovimientoPro, idtranglob FROM [coop].[dbo].[misComprobantes] a, [CoopDeTrabajo.Net].[dbo].[TiposMovimientoProveedor] b WHERE a.tipo=b.CodigoAFIP AND CONCAT(YEAR(fecha),RIGHT('00' + CONVERT(NVARCHAR(2), DATEPART(MONTH, fecha)), 2))='$_POST[mes]' AND anulado IS NULL AND cuit<>'20261262445' $where ORDER BY razonSocial ASC, pv ASC, numDesde ASC";
            $anual = false;
            $topeRazonSocial=30;
        } else {
            // anual
            $sqlClientes = "SELECT [idComprobante], [fecha], [IdTipoMovimientoProveedor], [tipo],[pv],[numDesde],[tipoDocEmisor],[cuit],[razonSocial], [ImputacionCuentaCorriente], [ImputacionCuentaCorriente]*[netoGravado] as netoGravado, [ImputacionCuentaCorriente]*[netoNoGravado] as netoNoGravado, [ImputacionCuentaCorriente]*[opExentas] as opExentas, [ImputacionCuentaCorriente]*[iva] as iva, [ImputacionCuentaCorriente]*[total] as total, idMovimientoPro, idtranglob FROM [coop].[dbo].[misComprobantes] a, [CoopDeTrabajo.Net].[dbo].[TiposMovimientoProveedor] b WHERE a.tipo=b.CodigoAFIP AND YEAR(fecha)='$_POST[mes]' AND anulado IS NULL AND cuit<>'20-26126244-5' $where ORDER BY razonSocial ASC, pv ASC, numDesde ASC";
            $anual = 1;
            $topeRazonSocial=20;
        }
        //ChromePHP::log($sqlClientes);die;
        //id 	 idCliente 	 variosClientes 	idFacturaRecibida 	 idCliente 	numeroNC 	idItemFactura 	ingresosBrutos 	IVA10 	idFacturaRecibida 	 	
        $stmt = odbc_exec2( $mssql4, $sqlClientes, __LINE__, __FILE__);

        $tabla = "";


        while($fila = sqlsrv_fetch_array($stmt)){
            $neg = ($fila['ImputacionCuentaCorriente']<0)?" neg":'';
            $retenciones = ($fila['IdTipoMovimientoProveedor']=='FAC' && $fila['IdTipoMovimientoProveedor']=='NCC')?"":($fila['total']-$fila['netoGravado']-$fila['netoNoGravado']-$fila['opExentas']-$fila['iva']);

            $razonSocial = (strlen($fila['razonSocial'])>$topeRazonSocial)?substr($fila['razonSocial'],0,$topeRazonSocial).'...':$fila['razonSocial'];
            $tr = "";
            $trId = "";
            if(!is_null($fila['idtranglob'])&&!is_null($fila['idMovimientoPro'])){
                // error! está cargado en ambos sistemas
                $sistema = "<span class='label label-danger'>EN LOS DOS</span>";
            } else if (!is_null($fila['idtranglob'])){
                $trId = "s_$fila[idtranglob]";
                $sistema = "<span class='label label-success'>Setup</span>";
            } else if (!is_null($fila['idMovimientoPro'])){
                $trId = "c_$fila[idMovimientoPro]";
                $sistema = "<span class='label label-info'>Calden</span>";
            } else {
                $sistema = "";
                $tr = encuentraEnSistemas($fila['cuit'], $fila['pv'], $fila['numDesde']);
                if($tr == ""){
                    // agrego fila con datos de la factura faltante
                    $tr = "<tr><td colspan='2'>Documento faltante | Fecha ".$fila['fecha']->format('d/m/Y')."</td><td colspan='6'></td></tr>";
                } 
            }

            $tabla .= "<tr class='doc$neg' id='$trId'><td class='nombreCliente'>$razonSocial <small>($fila[cuit])</small></td><td>$sistema</td><td>$fila[IdTipoMovimientoProveedor] ".str_pad($fila['pv'], 4, '0', STR_PAD_LEFT).'-'.str_pad($fila['numDesde'], 8, '0', STR_PAD_LEFT).(($anual)?" <small>".$fila['fecha']->format('d/m/y')."</small>":'')."</td><td>".number_format($fila['netoGravado'], 2,',','.')."</td><td>".number_format($fila['netoNoGravado'], 2,',','.')."</td><td>".number_format( $fila['iva'], 2,',','.')."</td><td>".number_format( $fila['total'], 2,',','.')."</td><td>".number_format( $retenciones, 2,',','.')."</td></tr>";

            $tabla .= $tr;
        }

        if($tabla=="")$tabla="<tr><td colspan='8' class='label-info center'>NO HAY FACTURAS CARGADAS EN ESTE PERIODO</td></tr>";
        echo $tabla;
        break;
    

    case 'calden':
        if(isset($_POST['mes'])&&strlen($_POST['mes'])==6){
            // mensual
            $mes = substr($_POST['mes'],5,2);
            $anio = substr($_POST['mes'],0,4);
            $sqlClientes = "EXEC [dbo].[Listado_IVACompras3] @Mes = '$mes', @Anio = '$anio'";
            $anual = false;
            $topeRazonSocial=30;
        } else {
            // anual
            $anio = substr($_POST['mes'],0,4);
            $sqlClientes = "EXEC [dbo].[Listado_IVACompras3] @Anual = 1, @Anio= '$anio'";
            $anual = 1;
            $topeRazonSocial=20;
        }
        ChromePHP::log($sqlClientes);


        $query = sqlsrv_query($mssql, $sqlClientes);
        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        do {

            while ($row = sqlsrv_fetch_array($query)) {
                // Loop through each result set and add to result array
                $result[] = $row;
            }
         } while (sqlsrv_next_result($query));

       
        $tabla = "";$a=0;
        $tiposMovimientosExcluidos = Array('GAH', 'IVA', 'RET', 'ACR');
        $proveedoresExcluidos = Array(423);
        foreach($result as $fila2){
            if(in_array($fila2['IdTipoMovimientoProveedor'], $tiposMovimientosExcluidos) || in_array($fila2['IdProveedor'], $proveedoresExcluidos)){
                continue;
            }
                //print_r($fi);
            //var_dump($fi);die;
            /*
            [Fecha] => DateTime Object ( [date] => 2021-05-17 00:00:00.000000 
            [timezone] => America/Argentina/Buenos_Aires )
            [IdProveedor] => 173
            [RazonSocial] => 5 MARIAS DE BAHIA S.R.L
            [Descripcion] => RI
            [NumeroDocumento] => 30-70811786-9 
            [Documento] => FAA-0004-00010873
            [IdTipoMovimientoProveedor] => FAA
            [PuntoVenta] => 4
            [Numero] => 10873
            [NetoNoGravado] => .0000 
            [NetoGravado] => 41316.2400
            [Neto] => 41316.2400
            [IncluyeNoGravado] => 
            [IVA1Detalle] => 21.00% 
            [IVA1] => 8676.4100 
            [IVA2Detalle] => 10.50% 
            [IVA2] => .0000 
            [IVA3Detalle] => 27.00% 
            [IVA3] => .0000 
            [IVA4Detalle] => 2.50% 
            [IVA4] => .0000
            [IVA5Detalle] =>
            [IVA5] => .0000 
            [IncluyeOtrosIVA] => 
            [ImpuestoInterno] => .0000 
            [Tasas] => .0000 
            [PercepcionIVA] => .0000 
            [PercepcionIIBB] => .0000
            [PercepcionOtras] => .0000
            [PercepcionGanancias] => .0000
            [RetencionIVA] => .0000
            [RetencionIIBB] => .0000
            [RetencionGanancias] => .000
            [RetencionOtras] => .0000 
            [RetencionesNoIVA] => .0000 
            [Total] => 49992.6504
            [IVA] => 8676.4100
            [RetencionSellados] => .0000
            [RetencionCargasSociales] => .0000
            [PercepcionIIBB1] => .0000
            [PercepcionIIBB2] => .0000
            [PercepcionIIBB3] => .0000
            [IdProvinciaPercepcionIIBB1] => 
            [IdProvinciaPercepcionIIBB2] => 
            [IdProvinciaPercepcionIIBB3] => 
            [Descuento] => .0000 
            [ImputacionCuentaCorriente] => 1
            [ImpIntyTasas] => .0000
            [NetoIVA1] => 41316.2381
            [NetoIVA2] => .0000
            [NetoIVA3] => .0000
            [NetoIVA4] => .0000 
            [NetoIVA5] => .0000 
            [IdMovimientoPro] => 88107 
            [TotalRetenciones] => .0000
            [TotalIVA] => 8676.4100 
            [TotalPercepciones] => .0000 
            [TotalPercepcionesYRetenciones] => .0000
            */

            $neg = ($fila2['ImputacionCuentaCorriente']<0)?" neg":'';
            $retenciones = $fila2['RetencionIVA'] + $fila2['RetencionIIBB'] + $fila2['RetencionGanancias'] + $fila2['RetencionOtras'] + $fila2['RetencionesNoIVA'];

            $razonSocial = (strlen($fila2['RazonSocial'])>$topeRazonSocial)?substr($fila2['RazonSocial'],0,$topeRazonSocial).'...':$fila2['RazonSocial'];
            $tr = "";
            $trId = "";
            $sistema = "";
            $faltante = false;

            $tr = encuentraEnAFIP($fila2['NumeroDocumento'], $fila2['PuntoVenta'], $fila2['Numero']);
            if($tr == "" ){
                // agrego fila con datos de la factura faltante
                $tr = "<tr><td colspan='2'>Documento faltante | Fecha ".$fila2['Fecha']->format('d/m/Y')."</td><td colspan='6'></td></tr>";
                $faltante = true;
                $sistema = "";
            } else {
                $sistema = "<span class='label label-warning'>AFIP</span>";
            }
            
            if ($_POST['status']=='faltantes' && !$faltante){

            } else {

                $tabla .= "<tr class='doc$neg' id='$trId'><td class='nombreCliente'>$razonSocial <small>($fila2[NumeroDocumento])</small> </td><td>$sistema</td><td>$fila2[IdTipoMovimientoProveedor] ".str_pad($fila2['PuntoVenta'], 4, '0', STR_PAD_LEFT).'-'.str_pad($fila2['Numero'], 8, '0', STR_PAD_LEFT).(($anual)?" <small>".$fila2['Fecha']->format('d/m/y')."</small>":'')."</td><td>".number_format($fila2['NetoGravado'], 2,',','.')."</td><td>".number_format($fila2['NetoNoGravado'], 2,',','.')."</td><td>".number_format( $fila2['IVA'], 2,',','.')."</td><td>".number_format( $fila2['Total'], 2,',','.')."</td><td>".number_format( $retenciones, 2,',','.')."</td></tr>";
                if($faltante)
                    $tabla .= $tr;
            }

            
        }

        if($tabla=="")$tabla="<tr><td colspan='8' class='label-info center'>NO HAY FACTURAS CARGADAS EN ESTE PERIODO</td></tr>";
        echo $tabla;
        break;



    case 'setup':
        if(isset($_POST['mes'])&&strlen($_POST['mes'])==6){
            // mensual
            $mes = substr($_POST['mes'],5,2);
            $anio = substr($_POST['mes'],0,4);
            $sqlClientes = "SELECT idcompcomp, comprobant, sucursal, numero, codproviva, tipoprovee, iva, emision, YEAR(fechaimpu) as Anio, MONTH(fechaimpu) as Mes, total_grav as netoGravado, neto_nogra as netoNoGravado, monotribu as netoC, exento as impInt, percepcion as PercepcionIVA, percepib as PercepcionIIBB, retiva as RetencionIVA, retenganan as RetencionGanancias, importe as Total, idtranglob, cuit FROM dbo.ivacomp WHERE YEAR(fechaimpu)='$anio' AND MONTH(fechaimpu)='$mes' ORDER BY cuit;";
            $anual = false;
            $topeRazonSocial=30;
        } else {
            // anual
            $anio = substr($_POST['mes'],0,4);
            $sqlClientes = "SELECT idcompcomp, comprobant, sucursal, numero, codproviva, tipoprovee, iva, emision, YEAR(fechaimpu) as Anio, MONTH(fechaimpu) as Mes, total_grav as netoGravado, neto_nogra as netoNoGravado, monotribu as netoC, exento as impInt, percepcion as PercepcionIVA, percepib as PercepcionIIBB, retiva as RetencionIVA, retenganan as RetencionGanancias, importe as Total, idtranglob, cuit FROM dbo.ivacomp WHERE YEAR(fechaimpu)='$anio' ORDER BY cuit;";
            $anual = 1;
            $topeRazonSocial=20;
        }
        ChromePHP::log($sqlClientes);
        $stmt2 = odbc_exec2( $mssql2, $sqlClientes, __LINE__, __FILE__);
    
        $tabla = "";$a=0;
        $tiposMovimientosExcluidos = Array('GAH', 'IVA', 'RET', 'ACR');
        $proveedoresExcluidos = Array(321, 1);
        while($fila2 = sqlsrv_fetch_array($stmt2)){
            if(in_array($fila2['idcompcomp'], $tiposMovimientosExcluidos) || in_array($fila2['codproviva'], $proveedoresExcluidos)){
                continue;
            }
            $cuitSinGuion = str_replace('-', '', $fila2['cuit']);
            $razonSocial = trim($_SESSION['codprovivaSetup'][$cuitSinGuion]);
            
            $neg = ($fila2['ImputacionCuentaCorriente']<0)?" neg":'';
            $retenciones = $fila2['RetencionIVA'] + $fila2['RetencionIIBB'] + $fila2['RetencionGanancias'] + $fila2['RetencionOtras'] + $fila2['RetencionesNoIVA'];

            $razonSocial = (strlen($razonSocial)>$topeRazonSocial)?substr($razonSocial,0,$topeRazonSocial).'...':$razonSocial;
            $tr = "";
            $trId = "";
            $sistema = "";
            $faltante = false;

            $tr = encuentraEnAFIP($fila2['cuit'], $fila2['sucursal'], $fila2['numero']);
            if($tr == "" ){
                // agrego fila con datos de la factura faltante
                $tr = "<tr><td colspan='2'>Documento faltante | Fecha ".$fila2['emision']->format('d/m/Y')."</td><td colspan='6'></td></tr>";
                $faltante = true;
                $sistema = "";
            } else {
                $sistema = "<span class='label label-warning'>AFIP</span>";
            }
            
            if ($_POST['status']=='faltantes' && !$faltante){

            } else {

                $tabla .= "<tr class='doc$neg' id='$trId'><td class='nombreCliente'>$razonSocial <small>($fila2[cuit])</small> </td><td>$sistema</td><td>$fila2[comprobant] ".str_pad($fila2['sucursal'], 4, '0', STR_PAD_LEFT).'-'.str_pad($fila2['numero'], 8, '0', STR_PAD_LEFT).(($anual)?" <small>".$fila2['emision']->format('d/m/y')."</small>":'')."</td><td>".number_format($fila2['netoGravado'], 2,',','.')."</td><td>".number_format($fila2['netoNoGravado'], 2,',','.')."</td><td>".number_format( $fila2['iva'], 2,',','.')."</td><td>".number_format( $fila2['Total'], 2,',','.')."</td><td>".number_format( $retenciones, 2,',','.')."</td></tr>";
                if($faltante)
                    $tabla .= $tr;
            }

        }
        
        if($tabla=="")$tabla="<tr><td colspan='8' class='label-info center'>NO HAY FACTURAS CARGADAS EN ESTE PERIODO</td></tr>";
        echo $tabla;


        
        break;
}









function encuentraEnAFIP ($cuit, $pv, $numero){
    /* */
    //ChromePHP::log("Documento $pv-$numero $cuit");
    // recibe esos parámetros y devuelve los datos de la factura cargada declarada en AFIP
    global $mssql4;
    $tr ='';
    $cuit = str_replace('-', '', $cuit);
    $sqlClientes = "SELECT [idComprobante], [fecha], [IdTipoMovimientoProveedor], [tipo],[pv],[numDesde],[tipoDocEmisor],[cuit],[razonSocial], [ImputacionCuentaCorriente], [ImputacionCuentaCorriente]*[netoGravado] as netoGravado, [ImputacionCuentaCorriente]*[netoNoGravado] as netoNoGravado, [ImputacionCuentaCorriente]*[opExentas] as opExentas, [ImputacionCuentaCorriente]*[iva] as iva, [ImputacionCuentaCorriente]*[total] as total, idMovimientoPro, idtranglob FROM [coop].[dbo].[misComprobantes] a, [CoopDeTrabajo.Net].[dbo].[TiposMovimientoProveedor] b WHERE a.tipo=b.CodigoAFIP AND pv=$pv AND numDesde=$numero AND cuit='$cuit' AND anulado IS NULL ORDER BY razonSocial ASC, pv ASC, numDesde ASC";
    //ChromePHP::log($sqlClientes);
    $stmt2 = odbc_exec2( $mssql4, $sqlClientes, __LINE__, __FILE__);
    $fila2 = sqlsrv_fetch_array($stmt2);
    if(is_array($fila2)){
        // Está declarao en AFIP
        $tr .= "<tr><td><span class='label label-warning'>AFIP</span> ".$fila2['fecha']->format('d/m/Y')."</b></td><td></td><td></td><td>".number_format($fila2['netoGravado'], 2,',','.')."</td><td>".number_format($fila2['netoNoGravado'], 2,',','.')."</td><td>".number_format( $fila2['iva'], 2,',','.')."</td><td>".number_format( $fila2['total'], 2,',','.')."</td><td>".number_format( $fila2['opExentas'], 2,',','.')."</td></tr>";
    } else {
        //ChromePHP::log("Documento $pv-$numero no existe en Setup");    
    }
    return $tr;
}



function encuentraEnSistemas($cuit, $pv, $numero){
    //ChromePHP::log("Documento $pv-$numero $cuit");
    // recibe esos parámetros y devuelve los datos de la factura cargada en Setup
    global $mssql2, $mssql, $mssql4;
    $tr ='';

    // verifico si minimamente existe el proveedor en Setup
    if(array_key_exists($cuit, $_SESSION['proveedoresSetup'])||array_key_exists($cuit, $_SESSION['fleterosSetup'])){
        // recibe esos parámetros y devuelve los datos de la factura cargada en Setup
        $idFletero = (array_key_exists($cuit, $_SESSION['fleterosSetup']))?$_SESSION['fleterosSetup'][$cuit]:'0';
        $idProveedor = (array_key_exists($cuit, $_SESSION['proveedoresSetup']))?$_SESSION['proveedoresSetup'][$cuit]:'0';

        $sql2 = "SELECT idcompcomp, iva, emision, YEAR(fechaimpu) as Anio, MONTH(fechaimpu) as Mes, total_grav as netoGravado, neto_nogra as netoNoGravado, monotribu as netoC, exento as impInt, percepcion as PercepcionIVA, percepib as PercepcionIIBB, retiva as RetencionIVA, retenganan as RetencionGanancias, importe as Total, idtranglob FROM dbo.ivacomp WHERE sucursal='$pv' AND numero='$numero' AND codproviva IN ($idFletero, $idProveedor);";
        
        
        /*ingreso	emision	fechaimpu	idcompcomp	comprobant	tipo	sucursal	numero	tipoprovee	codproviva	tpo_suj_af	neto_grava	neto_grav1	neto_grav2	total_grav	neto_nogra	exento	monotribu	respnoins	iva	sobretasa	ivadife	total_iva	impodife	importe	percepcion	percepib	ltrgasoil	impgasoil	tasagasoil	signo	sucursal_e	retiva	retencion	reteniva	retenganan	retenib	exportacio	anulado	suctranglo	idtranglob	idempresa	tcomafip	cai	fechvtocai	canthojas	aduana	destinacio	por_iva	por_sobre	por_ivadif	por_impgas	por_tasgas	moneda	cotizacion	fecha_anul	contfiscal	exento_ib	fechapago	fij_gasoil	nro_origen	pagada	sucu_renpa	nume_renpa	imponograv	ctactebanc	idoperador	operador	feho_audit	sucu_renfr	nume_renfr	fondofijor	sucu_lotec	nume_lotec	total_neto	exentosuss	cod_barras	tipodoc	cuit	cantidad	cantidadsg	importesg	proxi_venc	observacio	exento_rmu	expsoreifa	id_clfacpr	item_anul	redondeo	exentoiva	percegana	totivaotr	totgravotr	canalicotr */
        
        //if($cuit=='20348552474') ChromePHP::log($sql2);

        $stmt2 = odbc_exec2( $mssql2, $sql2, __LINE__, __FILE__);
        $fila2 = sqlsrv_fetch_array($stmt2);
        if(is_array($fila2)){
            // actualiza base
            $sqlActualiza = "UPDATE  [coop].[dbo].[misComprobantes] SET idtranglob='$fila2[idtranglob]' WHERE pv='$pv' and numDesde='$numero' AND cuit='$cuit';";
            $stmt = odbc_exec2( $mssql4, $sqlActualiza, __LINE__, __FILE__);

            @$netoGravado = $fila2['netoGravado'];

            $tr .= "<tr><td><span class='label label-success'>Setup</span> ".$fila2['emision']->format('d/m/Y')." <b>(".$fila2['Mes'].'-'.$fila2['Anio'].")</b></td><td></td><td>".number_format($netoGravado, 2,',','.')."</td><td>".number_format($fila2['netoNoGravado'], 2,',','.')."</td><td>".number_format( $fila2['iva'], 2,',','.')."</td><td>".number_format( $fila2['Total'], 2,',','.')."</td><td>".number_format( $fila2['impInt'], 2,',','.')."</td></tr>";
        } else {
            //ChromePHP::log("Documento $pv-$numero no existe en Setup");    
        }
    } else {
        //ChromePHP::log($cuit.' no existe en Setup');
    }

    if(array_key_exists($cuit, $_SESSION['proveedoresCalden'])||array_key_exists($cuit, $_SESSION['proveedoresCaldenEventuales'])){

        $idProveedor = (array_key_exists($cuit, $_SESSION['proveedoresCalden']))?$_SESSION['proveedoresCalden'][$cuit]:"";
        if($idProveedor == ""){
            $idProveedor = (array_key_exists($cuit.'_', $_SESSION['proveedoresCalden']))?$_SESSION['proveedoresCalden'][$cuit.'_']:"";
        }
        
        $sql2 = "SELECT IdTipoMovimientoProveedor, sum(b.importe) as iva, Fecha, Anio, Mes, NetoGastos, NetoNoGravado, NetoMercaderias, NetoCombustibles,NetoLubricantes,NetoFinanciacion,NetoFletes, (a.ImpuestoInterno+a.Tasas) AS impInt,  PercepcionIVA, PercepcionIIBB, RetencionIVA, RetencionGanancias, Total, a.IdMovimientoPro from dbo.movimientospro a, dbo.MovimientosProIVA b WHERE PuntoVenta='$pv' AND numero='$numero' AND (idProveedor='$idProveedor' OR idProveedor IS NULL) AND a.IdMovimientoPro=b.IdMovimientoPro group by IdTipoMovimientoProveedor,Fecha, Anio, Mes, NetoNoGravado, NetoMercaderias, NetoCombustibles,NetoLubricantes,NetoFinanciacion,NetoFletes, a.ImpuestoInterno,a.Tasas,  PercepcionIVA, PercepcionIIBB, RetencionIVA, RetencionGanancias, Total, NetoGastos, a.IdMovimientoPro  UNION SELECT IdTipoMovimientoProveedor, 0 as iva, Fecha, Anio, Mes, NetoGastos, NetoNoGravado, NetoMercaderias, NetoCombustibles,NetoLubricantes,NetoFinanciacion,NetoFletes, (a.ImpuestoInterno+a.Tasas) AS impInt,  PercepcionIVA, PercepcionIIBB, RetencionIVA, RetencionGanancias, Total, a.IdMovimientoPro from dbo.movimientospro a, dbo.MovimientosDetallePro b WHERE PuntoVenta='$pv' AND numero='$numero' AND (idProveedor='$idProveedor' OR idProveedor IS NULL) AND a.IdTipoMovimientoProveedor='FAC' AND a.IdMovimientoPro=b.IdMovimientoPro group by IdTipoMovimientoProveedor,Fecha, Anio, Mes, NetoNoGravado, NetoMercaderias, NetoCombustibles,NetoLubricantes,NetoFinanciacion,NetoFletes, a.ImpuestoInterno,a.Tasas,  PercepcionIVA, PercepcionIIBB, RetencionIVA, RetencionGanancias, Total, NetoGastos, a.IdMovimientoPro UNION SELECT IdTipoMovimientoProveedor, 0 as iva, Fecha, Anio, Mes, NetoGastos, NetoNoGravado, NetoMercaderias, NetoCombustibles,NetoLubricantes,NetoFinanciacion,NetoFletes, (a.ImpuestoInterno+a.Tasas) AS impInt,  PercepcionIVA, PercepcionIIBB, RetencionIVA, RetencionGanancias, Total, a.IdMovimientoPro from dbo.movimientospro a, dbo.MovimientosDetallePro b WHERE PuntoVenta='$pv' AND numero='$numero' AND idProveedor='$idProveedor' AND a.IdTipoMovimientoProveedor='FAA' AND NetoGastos=Total AND a.IdMovimientoPro=b.IdMovimientoPro group by IdTipoMovimientoProveedor,Fecha, Anio, Mes, NetoNoGravado, NetoMercaderias, NetoCombustibles,NetoLubricantes,NetoFinanciacion,NetoFletes, a.ImpuestoInterno,a.Tasas,  PercepcionIVA, PercepcionIIBB, RetencionIVA, RetencionGanancias, Total, NetoGastos, a.IdMovimientoPro ";
        //ChromePHP::log($sql2);
        
        if($cuit=='30522211563') ChromePHP::log($sql2);

        $stmt2 = odbc_exec2( $mssql, $sql2, __LINE__, __FILE__);
        $fila2 = sqlsrv_fetch_array($stmt2);
        if(is_array($fila2)){
            // actualiza base
            $sqlActualiza = "UPDATE  [coop].[dbo].[misComprobantes] SET IdMovimientoPro='$fila2[IdMovimientoPro]' WHERE pv='$pv' and numDesde='$numero' AND cuit='$cuit';";
            $stmt = odbc_exec2( $mssql4, $sqlActualiza, __LINE__, __FILE__);

            @$netoGravado = $fila2['NetoMercaderias']+$fila2['NetoCombustibles']+$fila2['NetoLubricantes']+$fila2['NetoFinanciacion']+$fila2['NetoFletes']+$fila2['NetoGastos'];
            $tr .= "<tr><td><span class='label label-primary'>Calden</span> ".$fila2['Fecha']->format('d/m/Y')." <b>(".$fila2['Mes'].'-'.$fila2['Anio'].")</b></td><td></td><td>".number_format($netoGravado, 2,',','.')."</td><td>".number_format($fila2['NetoNoGravado'], 2,',','.')."</td><td>".number_format( $fila2['iva'], 2,',','.')."</td><td>".number_format( $fila2['Total'], 2,',','.')."</td><td>".number_format( $fila2['impInt'], 2,',','.')."</td></tr>";
        } else {
            //ChromePHP::log("Documento $pv-$numero no existe en Calden"); 
        }
    } else {
        //ChromePHP::log($cuit.' no existe en Calden');
    }
    return $tr;
}

?>