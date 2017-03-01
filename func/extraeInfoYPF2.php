<?php
$mysqli = new mysqli('localhost', 'coopetrans', 'vGCP6eZ6dqUFZ2pB', 'pedidosypf');

if ($mysqli->connect_error) {
    die('MySQL - Error de Conexion ('.$mysqli->connect_errno.') '.$mysqli->connect_error);
}

$username='estcotrans@gmail.com';
$password='Ingreso48';

date_default_timezone_set('America/Argentina/Buenos_Aires');
/* extraeInfoYPF.php
	- obtiene desde la web de YPF la informaci�n actualizada de las ultimas OP en curso.
	
// formulario de entrada de descarga de combustible
		- ingreso de OP
		- en virtud de esta OP tendr�a que ya presentar los litros recibidos y facilitar la distribuci�n en tanques
		- Cantidad de litros de cada combustible
		- Distribuci�n en tanques de esos litros
		
// formulario de carga de estado de tanques
		- formulario donde muestra la fecha y la hora
		- da para cargar en mm la cantidad de cada tanque y la cantidad de mm de agua
		- si hay purga permite cargar nuevamente los mm luego de la purga para calcular los litros eliminados
		
		- opcion por surtidores, formuklario con todos los surtidores y el input para la medici�n actual.
		- como resultado va tirando el stock que hay en cada tanque calculado como diferencia entre las cargas que haya habido + el estado al turno anterior menos los litros despachados.
*/

$myFile = "e:\COMPARTIDO\cron\actualizaYPF.log";
$fh = fopen($myFile, 'a') or die("can't open file");
//print_r($_SERVER);
$desdeDonde=(isset($_SERVER['HTTP_HOST']))?' forzado por usuario':' automatica';
$stringData = "\n\n\n".date('d/M/Y H:i:s', time())." Inicia actualizacion$desdeDonde\n";
fwrite($fh, $stringData);

function getSSLPage($url) {
    global $desdeDonde, $mysqli;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSLVERSION,3); 
    $result = curl_exec($ch);
    if($result === FALSE) {
        die(curl_error($ch));
    } else {
        $fecha = date("Y-m-d h:i:00");
        $actualizoUpdate = $mysqli->query("UPDATE ultimaactualizacion SET (fecha, tipo) VALUES ('$fecha', '$desdeDonde')");
    }
        
    curl_close($ch);
    return $result;
}


function levantaDatosOP($op, $idOP, $despachado=false){
	global $fh, $producto, $mysqli, $password;
	// levanto las cantidades pedidas y las grabo en la table 'pedidos'
	// tengo que acomodarlo para que no duplique las grabaciones.
	// tambien tiene que poder registrar como pedido y despachado cantidades de productos distintos
	//$url2 = "https://C30530341131:{$password}@downstream.ypf.com.ar/agent_portal/plsql/pkg_consulta_unificada.PRC_VER_DETALLE_PEDIDO?pe_id_cuenta=03710980&pe_id_direccion=&pe_id_pedido=&g_id_pedido=$op&pe_id_op=&pe_fe_pedido=&pe_id_estado=&pe_id_dien2=&p_tipo=o";
    //$url3 = "https://downstream.ypf.com.ar/agent_portal/plsql/pkg_consulta_unificada.PRC_VER_DETALLE_PEDIDO?pe_id_cuenta=03710980&pe_id_direccion=&pe_id_pedido=&g_id_pedido=$op&pe_id_op=&pe_fe_pedido=&pe_id_estado=&pe_id_dien2=&p_tipo=o";
	//$listadoUtil =  file_get_contents($url2);
	//$listadoUtil =  getSSLPage($url2);
    
    
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url3);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSLVERSION, 3);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "C30530341131:{$password}"); 
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    $listadoUtil = curl_exec($ch);
    
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    
   
    
	$listadoUtil = substr($listadoUtil, stripos($listadoUtil, $op)+12);
	$strpos1 = strpos("409900", $listadoUtil); // NP
	$strpos2 = strpos("406900", $listadoUtil); // UD
	$strpos3 = strpos("409700", $listadoUtil); // NS
	$strpos4 = strpos("405200", $listadoUtil); // ED
	$minimo = min($strpos1, $strpos2, $strpos3, $strpos4);
	$listadoUtil = substr($listadoUtil, $minimo);
	$listadoUtil = substr($listadoUtil, strrpos(' class="descripcion"', $listadoUtil));
	$listadoUtil = str_replace("</TD>", "|", $listadoUtil);
	$listadoUtil = str_replace("</TR>","<X>", $listadoUtil);
	$listadoUtil = strip_tags($listadoUtil);
	$listadoUtil = str_replace("&nbsp;","", $listadoUtil);
	$listadoUtil = str_replace("  "," ", $listadoUtil);
	$listadoUtil = preg_replace('/^\s+|\n|\r|\s+$/m', '', $listadoUtil);
	$listadoUtil = str_replace("||","|", $listadoUtil);
	$listadoU = explode('|', $listadoUtil);
	$listadoU = array_filter($listadoU);
	print_r($listadoU);
	foreach($listadoU as $key => $value){
		echo "$key ... $value\n";
		if(isset($producto[$value])){
			// 	idPedido	idOrden	idArticulo	litrosPedidos	litrosEntregados
			if(!$despachado){
				$sql4 = "INSERT INTO pedidos (idOrden, idArticulo, litrosPedidos) VALUES ('$idOP', '$producto[$value]', '{$listadoU[$key+2]}') ON DUPLICATE KEY UPDATE litrosPedidos='{$listadoU[$key+2]}'";
			}else{
				if(round($listadoU[$key+3]/1000)-round($listadoU[$key+2]/1000)<1){
					$despachados = $listadoU[$key+3];
					$despachados = 1000*round($despachados/1000);
				} else $despachados = 1000*round($listadoU[$key+2]/1000);
				
				$sql4 = "INSERT INTO pedidos (idOrden, idArticulo, litrosDespachados) VALUES ('$idOP', '$producto[$value]', '{$listadoU[$key+2]}') ON DUPLICATE KEY UPDATE litrosDespachados='$despachados'";
			}
			echo $sql4.'\n';
			fwrite($fh, $sql4."\n");
			$res4 = $mysqli->query($sql4);
		}
	}
}


// comienzo login con post nueva extranet

$login_url = 'http://www.ypf.com/extranets';
 
//These are the post data username and password
$post_data = "USER=$username&PASSWORD=$password";
 
//Create a curl object
$ch = curl_init();
 
//Set the useragent
$agent = $_SERVER["HTTP_USER_AGENT"];
curl_setopt($ch, CURLOPT_USERAGENT, $agent);
 
//Set the URL
curl_setopt($ch, CURLOPT_URL, $login_url );
 
//This is a POST query
curl_setopt($ch, CURLOPT_POST, 1 );
 
//Set the post data
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
 
//We want the content after the query
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 
//Follow Location redirects
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
 
/*
Set the cookie storing files
Cookie files are necessary since we are logging and session data needs to be saved
*/
 
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
 
//Execute the action to login
$postResult = curl_exec($ch);







$username = 'estcotrans@gmail.com';
$password = 'Ingreso48';
$loginUrl = 'http://www.ypf.com/extranets/';
 
//init curl
$ch = curl_init();
 
//Set the URL to work with
curl_setopt($ch, CURLOPT_URL, $loginUrl);
 
// ENABLE HTTP POST
curl_setopt($ch, CURLOPT_POST, 1);
 
//Set the post parameters
curl_setopt($ch, CURLOPT_POSTFIELDS, 'USER='.$username.'&PASSWORD='.$password);
 
//Handle cookies for the login
//set the directory for the cookie using defined document root var
$dir = $_SERVER['DOCUMENT_ROOT']."ctemp";
//build a unique path with every request to store 
//the info per user with custom func. 
curl_setopt($ch, CURLOPT_COOKIEJAR, $dir.'/cookie.txt');
//curl_setopt($ch, CURLOPT_COOKIEFILE, $dir.'/cookie.txt');
 
//Setting CURLOPT_RETURNTRANSFER variable to 1 will force cURL
//not to print out the results of its query.
//Instead, it will return the results as a string return value
//from curl_exec() instead of the usual true/false.
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
 
//execute the request (the login)
$store = curl_exec($ch);







$url = "http://downstream.ypf.com/agent_portal/plsql/PKG_CONSULTA_UNIFICADA.prc_consulta";

$mes = array(1=>'JAN', 2=>'FEB',3=>'MAR',4=>'APR',5=>'MAY',6=>'JUN', 7=>'JUL',8=>'AUG',9=>'SEP',10=>'OCT',11=>'NOV',12=>'DEC');
$mes2 = array('JAN'=>1, 'ENE'=>1, 'FEB'=>2, 'MAR'=>3, 'APR'=>4, 'ABR'=>4, 'MAY'=>5 ,'JUN'=>6, 'JUL'=>7, 'AUG'=>8, 'AGO'=>8, 'SEP'=>9, 'OCT'=>10, 'NOV'=>11, 'DEC'=>12, 'DIC'=>12);
$producto = array(406900 => 2069, 409700 => 2078, 409900 => 2076, 405200 => 2068);


//$data = file_get_contents($url, false, $context);
//$data = file_get_contents($url, false);
//print_r($data);

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_SSLVERSION, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
$listadoOP = curl_exec($ch);

echo $listadoOP;

                
if($listadoOP === FALSE) {
    die(curl_error($ch));
} else {
    $fecha = date("Y-m-d h:i:00");
    $tipo = ($desdeDonde==' automatica')?'auto':'manual';
    echo "INSERT INTO ultimaactualizacion (fecha, tipo) VALUES ('$fecha', '$tipo')";
    $actualizoUpdate = $mysqli->query("INSERT INTO ultimaactualizacion (fecha, tipo) VALUES ('$fecha', '$tipo')");
}
$info = curl_getinfo($ch);
curl_close($ch);
$grabar = substr($listadoOP, strpos($listadoOP, '<HTML>'));
$grabar = str_replace('="/', '="https://downstream.ypf.com.ar/', $grabar);
$grabar = str_replace('"pkg_consulta_unificada.', '"https://downstream.ypf.com/pkg_consulta_unificada.', $grabar);
$grabar = str_replace('BODY>', 'BODY style="background-color:#fff">', $grabar);

$sqlWeb = "INSERT INTO webypf (web) VALUES ('".mysql_real_escape_string($grabar)."')";
fwrite($fh, "INSERT INTO webypf (web) VALUES ('ESTADO ACTUAL WEB YPF')\n");
$mysqli->query($sqlWeb);

//$listadoOP =  file_get_contents($url);
if(!$listadoOP){fwrite($fh, "Problemas de conectividad\n");}
// empiezo la limpieza
$listadoUtil = substr($listadoOP, stripos($listadoOP, "</TABLE>"));
$listadoUtil = substr($listadoOP, stripos($listadoOP, "RED XXI"));
//$listadoUtil = str_replace ("</TR>", "<x>", $listadoUtil);
$listadoUtil = str_replace ("</TD>", "|", $listadoUtil);
$listadoUtil = str_replace ("RED XXI                       |","<X>",$listadoUtil);
$listadoUtil = str_replace ("03710980|","",$listadoUtil);
$listadoUtil = str_replace ("Lugar de entrega:","",$listadoUtil);
$listadoUtil = str_replace ("SAN MARTIN Y PERU S/N","", $listadoUtil);
$listadoUtil = str_replace ("&nbsp;","", $listadoUtil);
$listadoUtil = str_replace ("  "," ", $listadoUtil);
$listadoUtil = preg_replace('/^\s+|\n|\r|\s+$/m', '', $listadoUtil);
$listadoUtil = str_replace ("||","|", $listadoUtil);

//echo $listadoUtil.'\n';
$listadoUtil = strip_tags($listadoUtil, "<X><A>");
//MostrarDetalle('03710980','0071262671','o') // js para acceder al detalle de cada OP-
//echo $listadoUtil.'\n';
$arrayOP = explode("<X>", $listadoUtil);
$previa=array();
$arrayOPprocesados = array();

foreach(array_filter($arrayOP) as $key => $values){
	$op = array_filter(explode("|", $values));
	if(!in_array($op[2], $previa)){
		$previa[]=$op[2];
		echo "<br><br><br>\n\n\nOP $key: $op[2]\n";
		print_r($op);
		// reviso la tabla de OPs buscando si existe esa OP
		// 	idOrden	op	fechaPedido	fechaDespacho	fechaEstimada
		$sql1 = "SELECT fechaDespacho, ultimoEstado, ordenes.idOrden, op, estado, entregado, DATE_FORMAT(fechaDespacho, '%d/%m/%Y') as fechaFormato FROM ordenes, estados WHERE ordenes.op='$op[2]' AND ordenes.idOrden=estados.idOrden ORDER BY estados.idEstado DESC LIMIT 1";
		// echo '\n'.$sql1.'\n';
		
		
		
		fwrite($fh, "Selecciona el ultimo estado de la OP $op[2]\n");
		$res1 = $mysqli->query($sql1);
		if($res1&&$res1->num_rows>0){
			echo "<br/>\nDBG -> Existe la OP\n";
			// existe la OP
			// compuebo si el estado vari� respecto al que ya est� registrado
			$orden = $res1->fetch_array();
			echo "\$orden: <br/>";
			print_r($orden);

			if((trim($orden[1])<>trim($orden[4])) || (trim($orden[4]) <> trim($op[3]) && $op[2]<>'') || ( trim($orden[4])==trim($op[3] && ($orden[6]<>$op[4]&&$orden[6]<>'00/00/0000')))){
				echo "<br><br>\nEl estado vario respecto al registrado: <b>$orden[4]</b>  ($orden[1]<>$op[3] y \$op[2]=$op[2]) o (\$orden[6] $orden[6]<>\$op[4] $op[4]&&\$orden[6] $orden[6]<>'00/00/0000')\n";
				fwrite($fh, "El estado vario respecto al registrado: $orden[4]<>$op[3]\n");
				// registro el cambio en el estado y act�o
				switch(trim($op[3])){
				case 'Pedido Ruteado Parcialmente':
				case 'Pedido Ruteado':
					echo "<br>\n<b>NUEVO ESTADO $op[3]</b>\n<br>";
					$sql2 = "INSERT INTO estados (`idOrden`, `fechaEstado`, `estado`) VALUES ('$orden[2]', now(), '".trim($op[3])."')";
					echo '\n'.$sql2.'\n';
					fwrite($fh, $sql2."\n");
					$mysqli->query($sql2);
					// tengo la informaci�n 
					/*	
					[0] => 13/11/2012
					[1] =>  
					[2] => 0071265567
					[3] => Pedido Ruteado Parcialmente
					[4] => 13/11/2012
					[6] => Producto:ULTRADIESEL XXI
					[7] => Cantidad:25000
					[8] => Producto:NAFTA SUPER XXI
					[9] => Cantidad:4000
					[10] => Producto:N PREMIUN
					[11] => Cantidad:6000
					[12] => Fecha de despacho Estimada:14-NOV-12
					[13] => Transportista:PEDRO DIAZ     
					[14] => Camion:0035-Diaz 
					[15] => Numero de viaje:1
					*/
					$update = '';
					foreach($op as $k => $renglon){
						// TABLA PEDIDOS:  	idPedido	idOrden	idArticulo	litrosPedidos	litrosEntregados
						echo '<br>'.$renglon.'<br>';
						if($renglon == 'Producto:ULTRADIESEL XXI'){
							$mysqli->query("UPDATE pedidos SET litrosEntregados='".preg_replace("/[^0-9]/","",$op[$k+1])."' WHERE idArticulo='2069' AND idOrden='$orden[2]'");
                                                        echo "\n<br>UPDATE pedidos SET litrosEntregados='".preg_replace("/[^0-9]/","",$op[$k+1])."' WHERE idArticulo='2069' AND idOrden='$orden[2]'";
						} elseif($renglon == 'Producto:NAFTA SUPER XXI'){
							$mysqli->query("UPDATE pedidos SET litrosEntregados='".preg_replace("/[^0-9]/","",$op[$k+1])."' WHERE idArticulo='2078' AND idOrden='$orden[2]'");
                                                        echo "\n<br>UPDATE pedidos SET litrosEntregados='".preg_replace("/[^0-9]/","",$op[$k+1])."' WHERE idArticulo='2078' AND idOrden='$orden[2]'";
						} elseif(strstr($renglon, 'PREMIU')){
							$mysqli->query("UPDATE pedidos SET litrosEntregados='".preg_replace("/[^0-9]/","",$op[$k+1])."' WHERE idArticulo='2076' AND idOrden='$orden[2]'");
                                                         echo "\n<br>UPDATE pedidos SET litrosEntregados='".preg_replace("/[^0-9]/","",$op[$k+1])."' WHERE idArticulo='2076' AND idOrden='$orden[2]'";   
						} elseif(strstr($renglon, 'INFINI')){
							$mysqli->query("UPDATE pedidos SET litrosEntregados='".preg_replace("/[^0-9]/","",$op[$k+1])."' WHERE idArticulo='2076' AND idOrden='$orden[2]'");
                                                         echo "\n<br>UPDATE pedidos SET litrosEntregados='".preg_replace("/[^0-9]/","",$op[$k+1])."' WHERE idArticulo='2076' AND idOrden='$orden[2]'";   
						} elseif(strstr($renglon, 'Producto:')){
							$mysqli->query("UPDATE pedidos SET litrosEntregados='".preg_replace("/[^0-9]/","",$op[$k+1])."' WHERE idArticulo='2068' AND idOrden='$orden[2]'");
                                                        echo "\n<br>UPDATE pedidos SET litrosEntregados='".preg_replace("/[^0-9]/","",$op[$k+1])."' WHERE idArticulo='2068' AND idOrden='$orden[2]'";
						} elseif(strstr($renglon, 'Transportista')){
							$update .= "transportista='".trim(substr($renglon,14))."', "; 
						} elseif(strstr($renglon, 'Camion')){
							$tmp = explode(':',$renglon);
							$update .= "camion='".trim($tmp[1])."', ";
						} elseif(strstr($renglon, 'Fecha de despacho')){
                            echo "<br><br><br>$renglon<br><br><br>";
							$tmp = explode(':',$renglon);
							$date = explode('-', trim($tmp[1]));
							$D = $date[0];
							$M = $mes2[$date[1]];
							echo "<br>$M<br>$date[1]<br>";
							$Y = $date[2];
							$update .= "fechaDespachoEstimada='$Y/$M/$D', ";
						}
					}
					// TABLA ORDENES: idOrden	op	fechaPedido	fechaDespacho	fechaEstimada	transportista	camion	fechaDespachoEstimada	horaDespacho	remito
					$sqlActualizaOrdenes = "UPDATE ordenes SET {$update}ultimoEstado='".trim($op[3])."' WHERE idOrden='$orden[2]'";
					echo $sqlActualizaOrdenes.'\n';
					fwrite($fh, $sqlActualizaOrdenes."\n");
					$mysqli->query($sqlActualizaOrdenes);
                                        
					break;
				case 'Despachado':
				case 'Despachado Parcialmente':
				case 'OP Abierta':
                    echo "\n<b>Estado Despachado / Abierta</b>\n";
					//despachado:
                    if(trim($op[3])=="OP Abierta"&&strpos($orden[1], 'uteado')){
                        // el pedido ahora figura como OP Abierta pero antes estaba Ruteado
                        // debe revisar los datos de la OP y verificar que si no fue despachado.
                    } elseif(trim($op[3])=="OP Abierta"&&!strpos($orden[1], 'uteado')) {
                        //break;
                        $sqlActualizaOrdenes="";
                        // no se porqué pasaba esto
                    }
					echo "\nNUEVO ESTADO $op[3]\n";
					$sql2 = "INSERT INTO estados (`idOrden`, `fechaEstado`, `estado`) VALUES ('$orden[2]', now(), '".trim($op[3])."')";
					echo '\n'.$sql2.'\n';
					// fwrite($fh, $sql2."\n");
					$mysqli->query($sql2);
					/*
					[3] => Despachado
					[4] => 14/11/2012
					[6] => Fecha de despacho:13/11/2012
					[7] => Hora de despacho:16:09:41
					[8] => Numero de Remito: 000600754499
					*/
					$update = '';
					foreach($op as $k => $renglon){
						if(strstr($renglon,'Fecha d')){
							echo $renglon." <- 1 Fecha / $k\n";
							$tmp = explode(':', $renglon);
							$date = explode('/', trim($tmp[1]));
							$update .= "fechaDespacho='$date[2]/$date[1]/$date[0]', ";
						} elseif(strstr($renglon,'Hora de')){
							echo $renglon." <- 2 Hora / $k\n";
							$hora = explode(':', $renglon);
							$update .= "horaDespacho='$hora[1]:$hora[2]:$hora[3]', ";
						} elseif(strstr($renglon,'Remito')){
							echo $renglon." <- 3 Remito / $k\n";
							$remito = explode(':', $renglon);
							$update .= "remito='".trim($remito[1])."', ";
						}
					}
					// Por las dudas tengo que revisar si se registraron los datos despachados
					$sql3 = "SELECT idArticulo, litrosDespachados FROM pedidos WHERE idOrden='$orden[2]'";
					$res3 = $mysqli->query($sql3);
					fwrite($fh, $sql3."\n");
					echo $sql3;
					$total = 0;
					while($row = $res3->fetch_array()){
						$total+= $row[1];
					}
					if($total==0){
                        echo "<br><br>Levanto datos de la OP $op[2], $orden[2]<br>";
						levantaDatosOP($op[2], $orden[2], true);
					} else {
                        echo "<br><br>No levanto pedido<br><br>";
                        
                    }
                    
					// TABLA ORDENES: idOrden	op	fechaPedido	fechaDespacho	fechaEstimada	transportista	camion	fechaDespachoEstimada	horaDespacho	remito
					$sqlActualizaOrdenes = "UPDATE ordenes SET {$update}ultimoEstado='".trim($op[3])."' WHERE idOrden='$orden[2]'";
					echo $sqlActualizaOrdenes.'\n';
					$mysqli->query($sqlActualizaOrdenes);
					break;
				case 'Dado de baja':
					echo "\nNUEVO ESTADO Dado de baja\n";
					// por las dudas reviso si se dio de baja sin haber habido un despacho y aviso.
					if(strstr($orden[1], 'Despach')){
						// compruebo, si ya fue entregado lo doy de baja, sino lo sigo manteniendo con el estado actual
						if($orden[5]==1){
							// se despacho, actualizo estado
							$sql2 = "INSERT INTO estados (`idOrden`, `fechaEstado`, `estado`) VALUES ('$orden[2]', now(), 'Dado de baja')";
							$sqlActualizaOrdenes = "UPDATE ordenes SET ultimoEstado='".trim($op[3])."', entregado=1 WHERE idOrden='$orden[2]'";
							$mysqli->query($sqlActualizaOrdenes);
						} else {
							// no hago nada y espero hasta el proximo cron
							$sql2 = "SELECT 'No hago nada, pedido despachado y aun no recibido'";
							$sqlActualizaOrdenes ="";
						}
                    } elseif($orden[5]==1){
                        // Ya esta entregado, no hago nada.
						$sql2 = "SELECT 'No hago nada, pedido despachado y aun no recibido'";
						$sqlActualizaOrdenes ="";
					} else {
						$sql2 = "INSERT INTO estados (`idOrden`, `fechaEstado`, `estado`) VALUES ('$orden[2]', now(), 'Dado de baja')";
						ini_set("SMTP","localhost");
						ini_set("SMTP_PORT", 25);
						ini_set('sendmail_from', 'estcotrans@gmail.com');
						$Name = "OP Dada de baja"; //senders name
						$email = "estcotrans@gmail.com"; //senders e-mail adress
						$recipient = "maxi.schimmel@gmail.com"; //recipient
						$mail_body = "OP $op[0]\n$op[1]\n$op[2]\n$op[3]"; //mail body
						$subject = "OP Dada de Baja"; //subject
						$header = "From: ". $Name . " <" . $email . ">\r\n"; //optional headerfields
//						mail($recipient, $subject, $mail_body, $header); //mail command :)
						$sqlActualizaOrdenes = "UPDATE ordenes SET ultimoEstado='".trim($op[3])."', entregado=0, caida=1 WHERE idOrden='$orden[2]'";
						$mysqli->query($sqlActualizaOrdenes);
					}
					echo '\n'.$sql2.'\n';
					$mysqli->query($sql2);
					fwrite($fh, $sql2."\n");
					break;
				case 'OP Pendiente de Autorizacion':
					echo "\nNUEVO ESTADO OP Pendiente de Autorizacion\n";
					$sql2 = "INSERT INTO estados (`idOrden`, `fechaEstado`, `estado`) VALUES ('$orden[2]', now(), '$op[3]')";
					$sqlActualizaOrdenes = "UPDATE ordenes SET ultimoEstado='".trim($op[3])."' WHERE idOrden='$orden[2]'";
					$mysqli->query($sqlActualizaOrdenes);
					levantaDatosOP($op[2], $orden[2]);
					echo '\n'.$sql2.'\n';
					$mysqli->query($sql2);
					fwrite($fh, $sql2."\n");
					break;
				default:
					echo "\nNUEVO ESTADO CASO ELSE\n";
					$sql2 = "INSERT INTO estados (`idOrden`, `fechaEstado`, `estado`) VALUES ('$orden[2]', now(), '$op[3]')";
					$sqlActualizaOrdenes = "UPDATE ordenes SET ultimoEstado='".trim($op[3])."' WHERE idOrden='$orden[2]'";
					$mysqli->query($sqlActualizaOrdenes);
					echo '\n'.$sql2.'\n';
					$mysqli->query($sql2);
					fwrite($fh, $sql2."\n");
					break;
				}
				echo $sqlActualizaOrdenes;
			} else {
				// no hago nada, la OP est�  cargada y el estado no vari�
				echo "\nEl estado no vario. No hago nada\n";
				fwrite($fh, "El estado no vario. No hago nada\n");
			}
		} else {
			// NO existe la OP
			echo "\nDBG -> No existe la OP\n";
				fwrite($fh, "DBG -> No existe la OP\n");
			$date = explode('/', $op[0]);
			$D = $date[0];
			$M = $date[1];
			$Y = $date[2];
			$dobshow = $Y."/".$M."/".$D;
			$sql2 = "INSERT INTO ordenes (op, fechaPedido, ultimoEstado) VALUES ('$op[2]', '$dobshow', '$op[3]')";
			echo $sql2.'\n';
			$res2 = $mysqli->query($sql2);
			$idOP = $mysqli->insert_id;
			fwrite($fh, $sql2."\n");
			//idEstado	idOrden	fechaEstado	estado
			$sql3 = "INSERT INTO estados (idOrden, fechaEstado, estado) VALUES ('$idOP', now(), '$op[3]')";
			echo $sql3.'\n';
			fwrite($fh, $sql3."\n");
			$res3 = $mysqli->query($sql3);
			
			// actua de acuerdo al estado. En teor�a a partir que funcione solo caer� ac� con "OP Abierta"
			if($op[3]=='OP Abierta'||$op[3]=='OP Pendiente de Autorizacion'){
				echo "\nDBG -> $op[3], debo levantar los datos de la OP y grabarla en pedidos\n";
				fwrite($fh, "DBG -> $op[3], debo levantar los datos de la OP y grabarla en pedidos\n");
				levantaDatosOP($op[2], $idOP);

			} else {
				echo "\nDBG -> NO OP Abierta: ". $op[3].', esto deber�a suceder solo en fase de pruebas antes que quede andando el cron\n';
				fwrite($fh, "DBG -> NO OP Abierta: $op[3]\n");
				levantaDatosOP($op[2], $idOP);
				
			}
			
		}
	}	
}
//print_r($arrayOP);
fclose($fh);
echo "chau";
?>