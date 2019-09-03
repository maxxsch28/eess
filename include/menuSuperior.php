<?php 
$ypfUrl = '/ypf';
$ypfUrl = '';
?>
 <div class="navbar navbar-fixed-top navbar-default hidden-print" role="navigation" id='menu'>
      <div class="container">
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li role="presentation" class="dropdown" style='height:50px'>
                <a class="dropdown-toggle " data-toggle="dropdown" href="#" role="button" aria-expanded="false">
                  <img alt="CoopeTrans" src="img/iconoCooperativa2.png"  style="margin-top: -8px;">
                  <b><?php if($loggedInUser){echo $loggedInUser->display_username;}?></b>
                  <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu">
                <?php if(isUserLoggedIn()) { ?>
                    <li><a href="users_change-password.php">Cambiar contraseña</a></li>
                    <li><a href="users_update-email-address.php">Actualizar correo electronico</a></li>
                    <li><a href="logout.php">Salir</a></li>
                <?php } else { ?>
                    <li><a href="login.php">Ingresar</a></li>
                    <li><a href="users_register.php">Registrar usuario</a></li>
                <?php } ?>
                </ul>
            </li>
                <?php if($loggedInUser){
                    if($loggedInUser->group_id==2||$loggedInUser->group_id==4||$loggedInUser->group_id==7&&$loggedInUser->display_username=='maxxs'){ // Playa?>
                    <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/estadoTanquesV3.php")echo ' class="active"';?>><a href="/">C</a></li>
                <?php } else if($loggedInUser->group_id==2||$loggedInUser->group_id==4||$loggedInUser->group_id==7){ // Playa?>
                    <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/estadoTanquesV3.php")echo ' class="active"';?>><a href="/">Combustibles</a></li>
                <?php } 
                if($loggedInUser->group_id==2){ // Gestion?>
                    <li role="presentation" class="dropdown" style='height:50px'>
                        <a class="dropdown-toggle " data-toggle="dropdown" href="#" role="button" aria-expanded="false" href="/cargaMovistar.php">
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li<?php if($_SERVER['PHP_SELF']=='/cargaCierreCIO.php')echo ' class="active"';?>><a href="/cargaCierreCIO.php">Cierre CIO</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/saldoYER.php')echo ' class="active"';?>><a href="/saldoYER.php">Saldo YPF en Ruta</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/stockTanques.2.php')echo ' class="active"';?>><a href="/stockTanques.2.php">Control de stock de tanques</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/combTrackingVentas.php')echo ' class="active"';?>><a href="/combTrackingVentas.php">Tracking Ventas</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/registroRemito.php')echo ' class="active"';?>><a href="/registroRemito.php">Recepcion combustible</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/stockCierre.php')echo ' class="active"';?>><a href="/stockCierre.php">Stocks al cierre</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/evolucionDespachos.php')echo ' class="active"';?>><a href="/evolucionDespachos.php">CREA - Despachos horarios</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/preciosCombustibles.php')echo ' class="active"';?>><a href="/preciosCombustibles.php">Precio Combustibles</a></li>
                        </ul>
                    </li>
                <?php } 
                    if($loggedInUser->group_id==7||$loggedInUser->display_username=='maxxs'){ // Comision?>
                    <li role="presentation" class="dropdown" style='height:50px'>
                        <a class="dropdown-toggle " data-toggle="dropdown" href="#" role="button" aria-expanded="false" href="/informesComision.php">
                            Comisión
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/informesComision.php")echo ' class="active"';?>><a href="/informesComision.php">Introduccion a los informes</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/stockTanques.php')echo ' class="active"';?>><a href="/stockTanques.php">Control de stock de tanques</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/calculaSaldoCaja.php")echo ' class="active"';?>><a href="/cierreMensual.php">Saldo de Tesorería</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/informeMensual.php')echo ' class="active"';?>><a href="/informeMensual.php">Informe mensual Gastos EESS</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/muestraFacturasIVA.php')echo ' class="active"';?>><a href="/muestraFacturasIVA.php">Facturas por diferencia socios</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/setupCargadora.php')echo ' class="active"';?>><a href="/setupCargadora.php">Resumen anual Cargadora</a></li>
                             <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/comisionesTransporte.php")echo ' class="active"';?>><a href="/comisionesTransporte.php">Comisiones viajes Socios</a></li>
                             <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/setupPagoAdelantos.php")echo ' class="active"';?>><a href="/setupPagoAdelantos.php">Detalle pagos adelantos Socios</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/setupProductosPorCliente.php")echo ' class="active"';?>><a href="/setupProductosPorCliente.php">Detalle de facturación Transporte</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/setupTableroSocios.php")echo ' class="active"';?>><a href="/setupTableroSocios.php">Tablero socios (flecha)</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/setupOrdenesDeServicioImputadas.php")echo ' class="active"';?>><a href="/setupOrdenesDeServicioImputadas.php">Ordenes de servicio imputadas en pagos a Socios</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/setupViajesPendientesLiquidacion.php")echo ' class="active"';?>><a href="/setupViajesPendientesLiquidacion.php">Viajes pendientes de liquidación</a></li>
                        </ul>
                    </li>
                    <?php }
                    if($loggedInUser->group_id==2||$loggedInUser->group_id==3){ // Movistar?>
                    <li role="presentation" class="dropdown" style='height:50px'>
                        <a class="dropdown-toggle " data-toggle="dropdown" href="#" role="button" aria-expanded="false" href="/cargaMovistar.php">
                            Teresa
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/calculaSaldoCaja.php")echo ' class="active"';?>><a href="/cierreMensual.php?saldoCaja=1">Saldo Caja</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/litrosDisponibles.php")echo ' class="active"';?>><a href="/litrosDisponibles.php">Litros disponibles</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/cargaMovistar.php")echo ' class="active"';?>><a href="/cargaMovistar.php">Carga facturas</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/listaFacturacion.php")echo ' class="active"';?>><a href="/listaFacturacion.php">Refacturacion</a></li>
                        </ul>
                    </li>
                <?php } 
                    if($loggedInUser->group_id==2){ // Gestion?>
                    <li role="presentation" class="dropdown" style='height:50px'>
                        <a class="dropdown-toggle " data-toggle="dropdown" href="#" role="button" aria-expanded="false" >
                            Gestión
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/tablero.php")echo ' class="active"';?>><a href="/tablero.php">Tablero de control</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/buscaFacturasPorDiferencia.php")echo ' class="active"';?>><a href="/buscaFacturasPorDiferencia.php">Fc x Diferencia</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/calculaLubricantes.php")echo ' class="active"';?>><a href="/calculaLubricantes.php">Premios Lubricantes</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/estimuloShop.php")echo ' class="active"';?>><a href="/estimuloShop.php">Ventas en Servicompras</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/buscaTurnosTarjetas.php')echo ' class="active"';?>><a href="/buscaTurnosTarjetas.php">Lotes presentados x Turnos</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/cambiaClienteRemito.php')echo ' class="active"';?>><a href="/cambiaClienteRemito.php">Cambia clientes remitos</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/calculaAjustesPrecios.php')echo ' class="active"';?>><a href="/calculaAjustesPrecios.php">Ajustes cambio de precio</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/asignaFacturasSocios.php')echo ' class="active"';?>><a href="/asignaFacturasSocios.php">Asigna facturas x diferencia</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/acomodaRecibos.php')echo ' class="active"';?>><a href="/acomodaRecibos.php">Acomoda recibos caja Admin</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/promoCargaComprobantes.php')echo ' class="active"';?>><a href="/promoCargaComprobantes.php">Carga cupones desayuno</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/setupCargadora.php')echo ' class="active"';?>><a href="/setupCargadora.php">Cargadora</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/informeMensual.php')echo ' class="active"';?>><a href="/informeMensual.php">Informe mensual</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/muestraFacturasIVA.php')echo ' class="active"';?>><a href="/muestraFacturasIVA.php">Facturas x dif socios</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/buscaTurnos.php")echo ' class="active"';?>><a href="/buscaTurnos.php">Abre y cierra Turnos</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/listadoPrecios.php")echo ' class="active"';?>><a href="/listadoPrecios.php">Listas de precios</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/tableroTurnos.php")echo ' class="active"';?>><a href="/tableroTurnos.php">Tablero objetivos</a></li>
                        </ul>
                    </li>
                <?php } 
                    if($loggedInUser->group_id==4){ // Turnos?>
                    <li role="presentation" class="dropdown" style='height:50px'>
                        <a class="dropdown-toggle " data-toggle="dropdown" href="#" role="button" aria-expanded="false" >
                            Turnos
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/buscaTurnos.php")echo ' class="active"';?>><a href="/buscaTurnos.php">Abre y cierra Turnos</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/listadoPrecios.php")echo ' class="active"';?>><a href="/listadoPrecios.php">Listas de precios</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/tableroTurnos.php")echo ' class="active"';?>><a href="/tableroTurnos.php">Tablero objetivos</a></li>
                            
                        </ul>
                    </li>
                <?php } 
                    if($loggedInUser->group_id==2||$loggedInUser->group_id==5){ // Contables?>
                    
                    <li role="presentation" class="dropdown" style='height:50px'>
                        <a class="dropdown-toggle " data-toggle="dropdown" href="#" role="button" aria-expanded="false" href="/cargaMovistar.php">
                            Contable
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/buscaAsiento.php")echo ' class="active"';?>><a href="/buscaAsiento.php">Busca Asientos</a></li>

                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/buscaCuentaCorriente.php")echo ' class="active"';?>><a href="/buscaCuentaCorriente.php">Rastrea cuentas corrientes</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/conciliaYPF.php")echo ' class="active"';?>><a href="/conciliaYPF.php">Concilia YPF</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/verificaConciliaYPF.php")echo ' class="active"';?>><a href="/verificaConciliaYPF.php">Verifica conciliación YPF</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/conciliaBAPRO.php")echo ' class="active"';?>><a href="/conciliaBAPRO.php">Concilia BAPRO Setup</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/estadoTanques.php")echo ' class="active"';?>><a href="/estadoTanques.php?juli=1">¿Hay Nafta?</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/libroDiarioTransporte.php")echo ' class="active"';?>><a href="/libroDiarioTransporte.php">Busca desbalances Transporte</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/cierreMensual.php")echo ' class="active"';?>><a href="/cierreMensual.php">Cierre mensual Tesorería</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/ivaPorActividadEESS.php")echo ' class="active"';?>><a href="/ivaPorActividadEESS.php">Detalle IVA por actividad EESS</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/ivaPorActividadTransporte.php")echo ' class="active"';?>><a href="/ivaPorActividadTransporte.php">Detalle IVA por actividad Transporte</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/libroDiario2.php")echo ' class="active"';?>><a href="/libroDiario2.php">Libro Diario comprimido</a></li>
                        </ul>
                    </li>
                <?php }
                if($loggedInUser->group_id==2||$loggedInUser->group_id==5){ // MENU YPF?>
                    <li role="presentation" class="dropdown" style='height:50px'>
                        <a class="dropdown-toggle " data-toggle="dropdown" href="#" role="button" aria-expanded="false" href="/cargaMovistar.php">
                            YPF
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/cargaPromoSC.php")echo ' class="active"';?>><a href="/cargaPromoSC.php">Carga conceptos promos SC</a></li>

                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/listaPromoSC.php")echo ' class="active"';?>><a href="/listaPromoSC.php">Listados promos SC Infinia</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/listaYER.php")echo ' class="active"';?>><a href="/listaYER.php">Listados devoluciones YER</a></li>
                        </ul>
                    </li>
                <?php }
                if($loggedInUser->group_id==2||$loggedInUser->group_id==5){ // MENU ASOCIADOS?>
                    <li role="presentation" class="dropdown" style='height:50px'>
                        <a class="dropdown-toggle " data-toggle="dropdown" href="#" role="button" aria-expanded="false" href="/cargaMovistar.php">
                            Socios
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/sociosAlta.php")echo ' class="active"';?>><a href="/sociosAlta.php">Nuevo Asociado</a></li>

                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/sociosListados.php")echo ' class="active"';?>><a href="/sociosListados.php">Listados Asociados</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/sociosComision.php")echo ' class="active"';?>><a href="/sociosComision.php">Listados Comisiones</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/sociosEventos.php")echo ' class="active"';?>><a href="/sociosEventos.php">Modificaciones Asociados</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/sociosListaEventos.php")echo ' class="active"';?>><a href="/sociosListaEventos.php">Resúmenes eventos asociados</a></li>
                            <li>   -----------------   </li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/cooptransporte.ddns.net/sociosLibrosAlta.php")echo ' class="active"';?>><a href="/sociosLibrosAlta.php">Carga foja libro</a></li>
                        </ul>
                    </li>
                <?php }
                
                
                if($loggedInUser->group_id==2||$loggedInUser->group_id==6||$loggedInUser->group_id==5||$loggedInUser->group_id==7){ // Transporte?>
                    
                    <li role="presentation" class="dropdown" style='height:50px'>
                        <a class="dropdown-toggle " data-toggle="dropdown" href="#" role="button" aria-expanded="false" href="/cargaIVA.php">
                            Transporte
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                             <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/comisionesTransporte.php")echo ' class="active"';?>><a href="/comisionesTransporte.php">Comisiones Viajes</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/buscaAsientoTransporte.php")echo ' class="active"';?>><a href="/buscaAsientoTransporte.php">Busca Asientos <b>TRANSPORTE</b></a></li>
                             <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/setupPagoAdelantos.php")echo ' class="active"';?>><a href="/setupPagoAdelantos.php">Detalle pago adelantos</a></li>
                             <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/ivaCargaComprobantes.php")echo ' class="active"';?>><a href="/ivaCargaComprobantes.php">Carga comprobante</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/ivaCargaSocio.php")echo ' class="active"';?>><a href="/ivaCargaSocio.php">Nuevo socio</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/ivaCargaTerceros.php")echo ' class="active"';?>><a href="/ivaCargaTerceros.php">Cliente/Proveedor</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/ivaPreparaLibros.php")echo ' class="active"';?>><a href="/ivaPreparaLibros.php">Genera IVA</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/setupProductosPorCliente.php")echo ' class="active"';?>><a href="/setupProductosPorCliente.php">Detalle producto por Clientes</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/setupTableroSocios.php")echo ' class="active"';?>><a href="/setupTableroSocios.php">Tablero socios</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/setupOrdenesDeServicioImputadas.php")echo ' class="active"';?>><a href="/setupOrdenesDeServicioImputadas.php">Ordenes imputadas por Socio</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/setupViajesPendientesLiquidacion.php")echo ' class="active"';?>><a href="/setupViajesPendientesLiquidacion.php">Viajes pendientes de liquidación</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/setupOrdenesImpagas.php")echo ' class="active"';?>><a href="/setupOrdenesImpagas.php">Adelantos de EESS pendientes de pago</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/setupCargadora.php')echo ' class="active"';?>><a href="/setupCargadora.php">Resumen anual Cargadora</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=='/setupFlujoBanco.php')echo ' class="active"';?>><a href="/setupFlujoBanco.php">Flujo banco Provincia</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/setupChequesDiferidos.php")echo ' class="active"';?>><a href="/setupChequesDiferidos.php">Cheques diferidos a fin de ejercicio</a></li>
                            <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/setupOrdenesDescontadasMensuales.php")echo ' class="active"';?>><a href="/setupOrdenesDescontadasMensuales.php">Ordenes descontadas por mes</a></li>

                        </ul>
                    </li>
                <?php }
                if($loggedInUser->group_id==2){ // Gestion?>
                    <div class="col-sm-3 col-md-2 pull-right">
                        <form class="navbar-form" role="search" action='/buscaAsiento.php' method="post">
                        <div class="input-group">
                            <input type="text" class="form-control input input-sm" placeholder="Asiento" name="srch-term" id="srch-term">
                            <div class="input-group-btn">
                                <button class="btn btn-default btn-sm" type="submit">
                                <i class="glyphicon glyphicon-search"></i>
                                </button>
                            </div>
                        </div>
                        </form>
                    </div>
                <?php }
                } else { // generico ?>
                    <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/estadoTanques.php")echo ' class="active"';?>><a href="/">Estado tanques</a></li>
                    <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/cargaMovistar.php")echo ' class="active"';?>><a href="/cargaMovistar.php">Movistar</a></li>
                    <li<?php if($_SERVER['PHP_SELF']=="$ypfUrl/buscaAsiento.php")echo ' class="active"';?>><a href="/buscaAsiento.php">Contable</a></li>
                <?php }?>
		</ul> 
		</div><!--/.navbar-collapse -->
	</div>
    </div>
