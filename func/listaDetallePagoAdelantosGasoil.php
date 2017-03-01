<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';

$numeroPago = '20150723';




$soloExternos = (isset($_POST['soloExternos']))?" AND tipoviaje=$_POST[soloExternos]":"";
if(isset($_POST['numero'])){
	$sqlClientes = "select nombre, ordservi.importe, totadelant, ordservi.proveedor, ordservi.sucursal_e ,ordservi.numero, fleteros.fletero, observacio, numeinter, fechaprest from dbo.orsefact, ordservi, FLETEROS where ordservi.numero=ordenservi and ordservi.fletero=fleteros.fletero and orsefact.numero=$_POST[numero] and comprobant='NOTA DE DEBITO' AND ordservi.sucursal_e=orsefact.sucursal_e order by nombre asc";
	echo $sqlClientes;
} else {
	$limit=' LIMIT 5';
	$sqlClientes = "select clientes.idCliente as idC, clientes.socio, facturas.idFacturaRecibida, sum(usoRed) AS uR, sum( interesMora ) AS iM, sum( ingresosBrutos ) AS iB,facturaB, (SELECT count(celular) FROM `movistar.celulares` WHERE idCliente=idC) AS q,numeroFactura, nombre, codigo, periodo, facturas.idCliente, sum(cargoFijo) as cF, cargoFijo, (SUM( cargoFijo ) - cargoFijo) as Dif, sum(cargoVariable) as cV, sum(cargoBB) as cB, sum(IVA21) as IV1, sum(IVA27) as IV7, sum(otros) as o, sum(impuestosInternos) as iI, empleado from `movistar.facturasrecibidas` as facturas, `movistar.facturasitems` as items, `movistar.clientes` as clientes WHERE facturas.idFacturaRecibida=items.idFacturaRecibida and clientes.idCliente=facturas.idCliente group by facturas.idFacturaRecibida order by facturas.idFacturaRecibida desc $limit";
}

 echo $sqlClientes;


$stmt = odbc_exec( $mssql2, $sqlClientes);
//print_r($stmt);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlClientes<br/>";
     die( print_r( sqlsrv_errors(), true));
}
$tabla = "";
$cantidadFacturas = $cantidadClientes = 0;
$comision=array();
$totalAComisionar = array();
while($fila = odbc_fetch_array($stmt)){
    //$date = date_create_from_format('j-M-Y', $fila['fechaprest']);
    //echo date_format($date, 'Y-m-d');
    //echo $fila['fechaprest'];
    if(!isset($idSocio)){
        $idSocio = $fila['fletero'];
        $socio = $fila['nombre'];
        $comision=array();
        $comision[$idSocio]=array();
    }
    if($fila['fletero']<>$idSocio){
        $tablaEncabezado = "<tr class='info comisionEncabezado'><td colspan='3' style='text-align:left' >$fila[fletero] - <b>".strtoupper(utf8_encode($socio))."</b></td></tr>";
        $totalAFacturar = 0;

        $idSocio = $fila['fletero'];
        $comision=array();
        $comision[$idSocio]=array();
        $socio = $fila['nombre'];
        $codigosocio = $fila['fletero'];
        
        $cantidadClientes++;
	}
    if(isset($tablaEncabezado)){
        $tabla.=$tablaEncabezado;
        unset($tablaEncabezado);
    }
    //Salida	SalidaHora	Sucursal_E	Parte	Tramo	Origen	nom_Origen	Loc_Origen	ProvOrigen	Destino	Nom_Destin	Loc_Desti	Fletero	Fle_Nombre	Kilometros	TipoViaje	TpV_Nombre	APagar_Fle	Pagado_Fle	LiquidarCh	Cumplido	Rendido	Anulado	NomOrigen	NomDestino	ImpVta	Cliente


    
    $tabla.= "<tr><td class='col-md-3'>Adelanto Nº $fila[sucursal_e]-$fila[numero]</td>"
            . "<td style='text-align:left'  class='col-md-3'>Fc $fila[observacio] del ".$fila['fechaprest']->format('d/m/Y')."</td>"
            . "<td style='text-align:left' class='col-md-4'>$ ".sprintf("%.2f",$fila['importe'])."</td></tr>";
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
	
}
if($tabla===""){$tabla="<tr><td colspan='5' class='label-info center'>NO HAY FACTURAS CARGADAS EN ESTE PERIODO</td></tr>";}
elseif(!isset($limit)) {
    $tablaEncabezado = "<tr class='info comisionEncabezado'><td style='text-align:left' colspan=3>$codigosocio - <b>".strtoupper(utf8_encode($socio))."</b></td></tr>";
    $a=0;$totalAFacturar = 0;
    if($a>1){
        // totaliza comisiones por fletero
        $tablaEncabezado .= "<tr class='info comisionEncabezado'><td></td><td colspan='4' style='text-align:right'><b> TOTAL A FACTURAR SOCIO: $".sprintf("%.2f",$totalAFacturar)."</b></td></tr>";
    }
    $tabla.=$tablaEncabezado;
}
echo $tabla;
