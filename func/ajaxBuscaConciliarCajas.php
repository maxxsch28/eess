<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

if(isset($_POST['go'])&&$_POST['go']==1){
    // Adelantos en gasoil
    $cuentaContableCalden = 529;
    $cuentaContableSetup = 211101;
    $proveedor = 321;
    $gasoil='1';
    ChromePhp::log("CAJAS ADELANTOS GASOIL");
} else {
    // lo que ya está hecho
    $cuentaContableCalden = 742;
    $cuentaContableSetup = 340019;
    ChromePhp::log("CAJAS CRUZADAS");
    $gasoil='0';
}

if(isset($_POST['idTranglob'])){
    // Automatico
    // recibe idtranglob, tengo que buscar datos del mismo y, a partir de eso emparejarlo con Calden.
    $tmp = explode('_', $_POST['idTranglob']);
    $idTranglob = $tmp[1];
    $idTranglob = $_POST['idTranglob'];
    $sqlSetup = "SELECT cantidad, [sqlcoop_dbimplemen].[dbo].[asiecont].asiento, [sqlcoop_dbimplemen].[dbo].[concasie].detalle, cantidad, fecha, [sqlcoop_dbshared].[dbo].[plancuen].nombre, cuentacont, ordenamien, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[asiecont].idtranglob, [sqlcoop_dbimplemen].[dbo].[asiecont].cod_libro FROM [sqlcoop_dbimplemen].[dbo].[asiecont], [sqlcoop_dbimplemen].[dbo].[concasie], [sqlcoop_dbshared].[dbo].[plancuen] WHERE [sqlcoop_dbshared].[dbo].[plancuen].codigo=cuentacont AND [sqlcoop_dbimplemen].[dbo].[asiecont].cod_libro=[sqlcoop_dbimplemen].[dbo].[concasie].cod_libro AND [sqlcoop_dbimplemen].[dbo].[asiecont].asiento=[sqlcoop_dbimplemen].[dbo].[concasie].asiento AND [sqlcoop_dbimplemen].[dbo].[asiecont].transaccio=[sqlcoop_dbimplemen].[dbo].[concasie].transaccio AND cuentacont = $cuentaContableSetup AND [sqlcoop_dbimplemen].[dbo].[asiecont].idtranglob='$idTranglob' UNION SELECT cantidad, [sqlcoop_dbimplemen].[dbo].[diario].asiento, [sqlcoop_dbimplemen].[dbo].[concasie].detalle, cantidad, fecha, [sqlcoop_dbshared].[dbo].[plancuen].nombre, cuentacont, ordenamien, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[diario].idtranglob, [sqlcoop_dbimplemen].[dbo].[diario].cod_libro FROM [sqlcoop_dbimplemen].[dbo].[diario], [sqlcoop_dbimplemen].[dbo].[concasie], [sqlcoop_dbshared].[dbo].[plancuen] WHERE [sqlcoop_dbshared].[dbo].[plancuen].codigo=cuentacont AND [sqlcoop_dbimplemen].[dbo].[diario].cod_libro=[sqlcoop_dbimplemen].[dbo].[concasie].cod_libro AND [sqlcoop_dbimplemen].[dbo].[diario].asiento=[sqlcoop_dbimplemen].[dbo].[concasie].asiento AND [sqlcoop_dbimplemen].[dbo].[diario].transaccio=[sqlcoop_dbimplemen].[dbo].[concasie].transaccio AND cuentacont=$cuentaContableSetup AND [sqlcoop_dbimplemen].[dbo].[diario].idtranglob='$idTranglob' ORDER BY fecha DESC";
    ChromePhp::log("SQLSetup: $sqlSetup");

    $stmt = odbc_exec2($mssql2, $sqlSetup, __LINE__, __FILE__);

    while($filaSetup = sqlsrv_fetch_array($stmt)){
        // hago while pero tendría que ser un solo resultado.
        // Busco en Calden un valor similar en el rango de día +/- 1
        if($filaSetup['cantidad']<>0){
            $sqlCalden = "SELECT a.idasiento, a.fecha, Concepto, IdModeloContable, DebitoCredito, Importe FROM dbo.asientos a, dbo.AsientosDetalle b WHERE IdCuentaContable=$cuentaContableCalden AND a.IdAsiento=b.IdAsiento AND a.fecha>=DATEADD(day,-1,'".$filaSetup['fecha']->format('Y-m-d')."') AND a.fecha<=DATEADD(day,+1,'".$filaSetup['fecha']->format('Y-m-d 23:59:59')."') AND Importe='$filaSetup[cantidad]' ORDER BY a.fecha DESC;";
            //ChromePhp::log("SQLCalden: $sqlCalden");

            $stmt2 = odbc_exec2( $mssql, $sqlCalden, __LINE__, __FILE__);
            if(sqlsrv_num_rows($stmt2)>1){
                // varios resultados, no hago nada
                ChromePHP::log("Se encontraron varios resultados para \$$filaSetup[cantidad] del ".$filaSetup['fecha']->format('Y-m-d'));
            } elseif(sqlsrv_num_rows($stmt2)==1) {
                // tengo un resultado
                // grabo match
                $filaCalden = sqlsrv_fetch_array($stmt2);
                $sqlMatch = "INSERT INTO [coop].[dbo].[cajasCruzadas] (idAsiento, fechaCalden, idTranglob, fechaSetup, auto, [go]) VALUES ('$filaCalden[idasiento]', '".$filaCalden['fecha']->format('Y-m-d')."', '$filaSetup[idtranglob]', '".$filaSetup['fecha']->format('Y-m-d')."', '1', '$gasoil');";
                ChromePhp::log("SQLMatch: $sqlMatch");
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
                if(sqlsrv_rows_affected($stmt3)>0){
                    $respuesta = array('status' => 'yes','idConciliado'=> $idConciliado);
                } else {
                    $respuesta = array('status' => 'no','error'=> 'aca va el error');
                }
            } else {
                // No encontré resultados
            }
        }
    }
} else {
    // manual
    $qCalden = count($_POST['idcalden']);
    $qSetup = count($_POST['idsetup']);
    // tengo que detectar cuantos id hay de Calden y de Setup. El sistema me dejaría marcar m:1 o 1:n pero no m:n
    if($qCalden==1&&$qSetup==1){
        // 1:1
        $tmp = explode('_', $_POST['idsetup'][0]);
        $idSetup = $tmp[1];
        $fechaSetup = date("Y-m-d", $tmp[0]);
        $tmp = explode('_', $_POST['idcalden'][0]);
        $idCalden = $tmp[1];
        $fechaCalden = date("Y-m-d", $tmp[0]);

        $sqlMatch = "INSERT INTO [coop].[dbo].[cajasCruzadas] (idAsiento, fechaCalden, idTranglob, fechaSetup, auto, qCalden, qSetup, [go]) VALUES ('$idCalden', '$fechaCalden', '$idSetup', '$fechaSetup', '0', 1, 1, '$gasoil'); SELECT SCOPE_IDENTITY()";
        ChromePhp::log("SQLMatch manual: $sqlMatch");
        $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
        sqlsrv_next_result($stmt3);
        sqlsrv_fetch($stmt3);
        $idConciliado = sqlsrv_get_field($stmt3, 0);

        if($idConciliado>0){
            $respuesta = array('status' => 'yes','idConciliado'=> $idConciliado);
        } else {
            $respuesta = array('status' => 'no','error'=> 'aca va el error: '.sqlsrv_rows_affected($stmt3));
        }
    } else if($qSetup>1 && $qCalden==1) {
        // m:1
        $tmp = explode('_', $_POST['idcalden'][0]);
        $idCalden = $tmp[1];
        $fechaCalden = date("Y-m-d", $tmp[0]);
        foreach($_POST['idsetup'] as $id => $value){
            $tmp = explode('_', $value);
            $idSetup = $tmp[1];
            $fechaSetup = date("Y-m-d", $tmp[0]);
            if(!isset($idConciliado)){
                $sqlMatch = "INSERT INTO [coop].[dbo].[cajasCruzadas] (idAsiento, fechaCalden, idTranglob, fechaSetup, auto, qCalden, qSetup, [go]) VALUES ('$idCalden', '$fechaCalden', '$idSetup', '$fechaSetup', '0', 1, '$qSetup', '$gasoil'); SELECT SCOPE_IDENTITY()";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
                sqlsrv_next_result($stmt3);
                sqlsrv_fetch($stmt3);
                $idConciliado = sqlsrv_get_field($stmt3, 0);
                $sqlMatch = "UPDATE [coop].[dbo].[cajasCruzadas] SET idConciliacionOriginal=$idConciliado WHERE idConciliacion=$idConciliado";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
            } else {
                $sqlMatch = "INSERT INTO [coop].[dbo].[cajasCruzadas] (idAsiento, fechaCalden, idTranglob, fechaSetup, auto, qCalden, qSetup, idConciliacionOriginal, [go]) VALUES ('$idCalden', '$fechaCalden', '$idSetup', '$fechaSetup', '0', 1, '$qSetup', '$idConciliado', '$gasoil');";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
            }
        }
        if($idConciliado>0){
            $respuesta = array('status' => 'yes','idConciliado'=> $idConciliado);
        } else {
            $respuesta = array('status' => 'no','error'=> 'aca va el error: '.sqlsrv_rows_affected($stmt3));
        }
    } else if ($qSetup==1 && $qCalden>1) {
        // 1:n
        $tmp = explode('_', $_POST['idsetup'][0]);
        $idSetup = $tmp[1];
        $fechaSetup = date("Y-m-d", $tmp[0]);
        foreach($_POST['idcalden'] as $id => $value){
            $tmp = explode('_', $value);
            $idCalden = $tmp[1];
            $fechaCalden = date("Y-m-d", $tmp[0]);
            if(!isset($idConciliado)){
                $sqlMatch = "INSERT INTO [coop].[dbo].[cajasCruzadas] (idAsiento, fechaCalden, idTranglob, fechaSetup, auto, qCalden, qSetup, [go]) VALUES ('$idCalden', '$fechaCalden', '$idSetup', '$fechaSetup', '0', '$qCalden', '1', '$gasoil'); SELECT SCOPE_IDENTITY()";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
                sqlsrv_next_result($stmt3);
                sqlsrv_fetch($stmt3);
                $idConciliado = sqlsrv_get_field($stmt3, 0);
                $sqlMatch = "UPDATE [coop].[dbo].[cajasCruzadas] SET idConciliacionOriginal=$idConciliado WHERE idConciliacion=$idConciliado";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
            } else {
                $sqlMatch = "INSERT INTO [coop].[dbo].[cajasCruzadas] (idAsiento, fechaCalden, idTranglob, fechaSetup, auto, qCalden, qSetup, idConciliacionOriginal, [go]) VALUES ('$idCalden', '$fechaCalden', '$idSetup', '$fechaSetup', '0', '$qCalden', '1', '$idConciliado', '$gasoil');";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
            }
        }
        if($idConciliado>0){
            $respuesta = array('status' => 'yes','idConciliado'=> $idConciliado);
        } else {
            $respuesta = array('status' => 'no','error'=> 'aca va el error: '.sqlsrv_rows_affected($stmt3));
        }
    } else if ($qCalden>0 && $qSetup ==0){
        // solo puedo hacerlo yo
        foreach($_POST['idcalden'] as $id => $value){
            $tmp = explode('_', $value);
            $idCalden = $tmp[1];
            $fechaCalden = date("Y-m-d", $tmp[0]);
            if(!isset($idConciliado)){
                $sqlMatch = "INSERT INTO [coop].[dbo].[cajasCruzadas] (idAsiento, fechaCalden, idTranglob, fechaSetup, auto, qCalden, qSetup, corrigeSistema, [go]) VALUES ('$idCalden', '$fechaCalden', '', '', '0', '$qCalden', '0', '1', '$gasoil'); SELECT SCOPE_IDENTITY()";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
                sqlsrv_next_result($stmt3);
                sqlsrv_fetch($stmt3);
                $idConciliado = sqlsrv_get_field($stmt3, 0);
                $sqlMatch = "UPDATE [coop].[dbo].[cajasCruzadas] SET idConciliacionOriginal=$idConciliado WHERE idConciliacion=$idConciliado";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
            } else {
                $sqlMatch = "INSERT INTO [coop].[dbo].[cajasCruzadas] (idAsiento, fechaCalden, idTranglob, fechaSetup, auto, qCalden, qSetup, idConciliacionOriginal, corrigeSistema, [go]) VALUES ('$idCalden', '$fechaCalden', '', '', '0', '$qCalden', '0', '$idConciliado', '1', '$gasoil');";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
            }
        }
        if($idConciliado>0){
            $respuesta = array('status' => 'yes','idConciliado'=> $idConciliado);
        } else {
            $respuesta = array('status' => 'no','error'=> 'aca va el error: '.sqlsrv_rows_affected($stmt3));
        }
    }else if ($qCalden==0 && $qSetup>0){
        // solo puedo hacerlo yo
        foreach($_POST['idsetup'] as $id => $value){
            $tmp = explode('_', $value);
            $idSetup = $tmp[1];
            $fechaCalden = date("Y-m-d", $tmp[0]);
            if(!isset($idConciliado)){
                $sqlMatch = "INSERT INTO [coop].[dbo].[cajasCruzadas] (idAsiento, fechaCalden, idTranglob, fechaSetup, auto, qCalden, qSetup, corrigeSistema, [go]) VALUES ('', '', '$idSetup', '$fechaSetup', '0', '0', '$qSetup', '1', '$gasoil'); SELECT SCOPE_IDENTITY()";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
                sqlsrv_next_result($stmt3);
                sqlsrv_fetch($stmt3);
                $idConciliado = sqlsrv_get_field($stmt3, 0);
                $sqlMatch = "UPDATE [coop].[dbo].[cajasCruzadas] SET idConciliacionOriginal=$idConciliado WHERE idConciliacion=$idConciliado";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
            } else {
                $sqlMatch = "INSERT INTO [coop].[dbo].[cajasCruzadas] (idAsiento, fechaCalden, idTranglob, fechaSetup, auto, qCalden, qSetup, idConciliacionOriginal, corrigeSistema, [go]) VALUES ('', '', '$idSetup', '$fechaSetup', '0', '0', '$qSetup', '$idConciliado', '1', '$gasoil');";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
            }
        }
        if($idConciliado>0){
            $respuesta = array('status' => 'yes','idConciliado'=> $idConciliado);
        } else {
            $respuesta = array('status' => 'no','error'=> 'aca va el error: '.sqlsrv_rows_affected($stmt3));
        }
    } else {
        // m:n
        // changos!
        // tengo que hacer un for hasta el mayor entre m y n
        // va cargando un "ultimoCalden" y "ultimoSetup", si existe idsetup[i] o idCalden[i]
        for($i=0; i<=max($qCalden, $qSetup); $i++){
            $ultimoCalden = (isset($_POST['idcalden'][$i]))?$_POST['idcalden'][$i]:$ultimoCalden;
            $ultimoSetup = (isset($_POST['idsetup'][$i]))?$_POST['idsetup'][$i]:$ultimoSetup;
            $tmp = explode('_', $ultimoCalden);
            $idCalden = $tmp[1];
            $fechaCalden = date("Y-m-d", $tmp[0]);
            $tmp = explode('_', $ultimoSetup);
            $idSetup = $tmp[1];
            $fechaSetup = date("Y-m-d", $tmp[0]);
            if(!isset($idConciliado)){
                $sqlMatch = "INSERT INTO [coop].[dbo].[cajasCruzadas] (idAsiento, fechaCalden, idTranglob, fechaSetup, auto, qCalden, qSetup, [go]) VALUES ('$idCalden', '$fechaCalden', '$idSetup', '$fechaSetup', '0', '$qCalden', '$qSetup', '$gasoil'); SELECT SCOPE_IDENTITY()";
                echo("kkk $sqlMatch");
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
                sqlsrv_next_result($stmt3);
                sqlsrv_fetch($stmt3);
                $idConciliado = sqlsrv_get_field($stmt3, 0);
                $sqlMatch = "UPDATE [coop].[dbo].[cajasCruzadas] SET idConciliacionOriginal=$idConciliado WHERE idConciliacion=$idConciliado";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
            } else {
                $sqlMatch = "INSERT INTO [coop].[dbo].[cajasCruzadas] (idAsiento, fechaCalden, idTranglob, fechaSetup, auto, qCalden, qSetup, idConciliacionOriginal, [go]) VALUES ('$idCalden', '$fechaCalden', '$idSetup', '$fechaSetup', '0', '$qCalden', '$qSetup', '$idConciliado', '$gasoil');";
                $stmt3 = odbc_exec2( $mssql4, $sqlMatch, __LINE__, __FILE__);
            }
        }
        if($idConciliado>0){
            $respuesta = array('status' => 'yes','idConciliado'=> $idConciliado);
        } else {
            $respuesta = array('status' => 'no','error'=> 'aca va el error: '.sqlsrv_rows_affected($stmt3));
        }
    }
}
echo json_encode($respuesta);

?>
