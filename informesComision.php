<?php
$nivelRequerido = 5;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

// asignaFacturasSocios.php
// lista las facturas emitidas sin turno asociado y permite asignarlas al turno actual

$titulo = "Informes de gestión para la Comisión";
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
    <style type="text/css">
      
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
  </head>
  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');?>
	<div class="container">
          <div class='row'>
              <div class='col-md-6'><h2>Estación de servicio</h2>
                <h3><a href='/stockTanques.php'>Control de stock de tanques</a></h3>
                <p>Durante la semana se cargan en forma diaria las ventas entre las 22hs de un día y las 22hs del día siguiente, en forma automática y a partir de la telemedición el sistema recoge los stocks disponibles en cada tanque a esa misma hora.<br>Este informe muestra en forma clara como están los stocks y si hubo o no desvíos que puedan resultar ser faltantes injustificados o señales de pérdida de producto.</p>
                <h3><a href='/cierreMensual.php'>Saldo de Tesorería</a></h3>
                <p>Permite ver para cada día el flujo de dinero y cheques que pasó por la Tesorería de Estación de Servicio.</p>
                <h3><a href='/estadoTanques.V3.php'>Combustibles</a></h3>
                <p>Permite ver movimientos diarios de Estación de Servicio.</p>
                <h3><a href='/informeMensual.php'>Informe mensual Gastos EESS</a></h3>
                <p>Muestra todos los gastos para el mes seleccionado que ha tenido la Estación de Servicio. Agrupados por rubro y dentro de ellos por proveedor.</p>
                <h3><a href='/muestraFacturasIVA.php'>Facturas por diferencia socios</a></h3>
                <p>Muestra para el año actual todas las facturas emitidas por combustible a nombre de los socios por concepto de cuota mensual.</p>
              </div>
              <div class='col-md-6'><h2>Transporte</h2>
                <h3><a href='/setupCargadora.php'>Resumen anual Cargadora</a></h3>
                <p>Desde esta pantalla se podrán ver todos los gastos, sueldos e ingresos que se registran para cada año por la actividad de la Cargadora.<br/>
                En la sección de gastos de personal se incluyen los viáticos y porcentajes que se pagaron a los empleados implicados.</p>
                <h3><a href='/comisionesTransporte.php'>Comisiones viajes Socios</a></h3>
                <p>Para cada mes seleccionado muestra cuanto se le facturará a cada socio por comisiones de los viajes realizados.<br/>
                También permite ver cuantos viajes realizó cada uno y sobre el final la cantidad de viajes totales sobre los que se comisionó, seperando cuales son viajes propios y cuales son externos.</p>
                <h3><a href='/setupProductosPorCliente.php'>Detalle de facturación Transporte</a></h3>
                <p>Permite ver en forma mensual como se compuso la facturación de la sección Transporte, agrupado por rubros (FLETES, CUOTAS, EXTRACTORA, COMISIONES, etc...)</p>
                <h3><a href='/setupTableroSocios.php'>Tablero socios (flecha)</a></h3>
                <p>Esto se desarrolló pensando en cambiar el pizarrón por un televisor que permita ir mostrando las flechas a medida que se asignaban los viajes.<br/>
                Para el dador de carga este tablero quería reemplazar distintos listados de manera de permitirle desde el teléfono celular directamente llamar al chofer que correspondía y desde el mismo teléfono dejar registrado, ante una respuesta negativa del mismo, el motivo.<br/>
                El nombre en Verde significaba que alguna de las 3 flechas estaba ahí, a la derecha del nombre una letra "L", "M" o "C" indicaba si era la larga, media o corta.<br/>
                También permite ver las fechas de vencimiento de la distinta documentación requerida para transitar cada chofer.</p>
                <h3><a href='/setupOrdenesDeServicioImputadas.php'>Ordenes de servicio imputadas en pagos a Socios</a></h3>
                <p>Este reporte sirve para la cancelación en Estación de Servicio del gasoil que se adelanta a cada socio. Cómo al momento de informar los pagos desde Transporte a Estación sólo se indica para cada socio un importe total, la forma de saber a que facturas corresponde imputar el recibo es mediante este reporte accediendo a la información del socio respectivo.</p>
                <h3><a href='/setupViajesPendientesLiquidacion.php'>Viajes pendientes de liquidación</a></h3>
                <p>Permite ver entre un rango de fechas los viajes que cada socio tiene realizados pero sin cobrar.<br>En el final indica cuanto dinero se debe en total por este concepto.</p>
                <h3><a href='setupOrdenesDeServicioAdeudadas.php'>Adelantos de gasoil pendientes de cancelar</a></h3>
                <p>En esta pantalla se podrá ver cuantos adelantos que se han dado a los socios por parte de distintos proveedores aún quedan impagos.<br/>
                Entre <a href='/setupViajesPendientesLiquidacion.php'>Viajes pendientes de liquidación</a> y <a href='setupOrdenesDeServicioAdeudadas.php'>Adelantos de gasoil pendientes de cancelar</a> se puede tener una idea del grueso que la sección transporte tiene pendiente de pago.</p>
              </div>
            </div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>

      </script>
  </body>
</html>
