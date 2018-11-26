<?php
$nivelRequerido = 5;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
// prepara array clientes con movimientos en cuenta en los últimos 2 años
if(!isset($_SESSION['clientesCuentaCorriente'])){
  unset($_SESSION['clientesCuentaCorriente']);
  $sql = "SELECT IdCliente, RazonSocial, Identificador FROM dbo.Clientes WHERE IdCondicionVenta=2 AND Activo=1 AND (IdCliente IN (SELECT IdCliente FROM dbo.movimientosCta WHERE Fecha>'2015-01-01' GROUP BY IdCliente) OR IdClienteAsociado IN (SELECT IdCliente FROM dbo.movimientosCta WHERE Fecha>'2015-01-01' GROUP BY IdCliente)) AND Identificador NOT LIKE ('%ORDEN%') ORDER BY RazonSocial ASC";
  $stmt = odbc_exec2( $mssql, $sql, __LINE__, __FILE__);
  while($rowRemito = sqlsrv_fetch_array($stmt)){
    $_SESSION['clientesCuentaCorriente'][$rowRemito['IdCliente']] = $rowRemito;
  }
}
$titulo = "Calcula ajustes de precios";
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
            <div class="col-md-5 hidden-print">
              <h2>Selecciona cliente</h2>
              <form name='nuevaOP' id='nuevaOP' class='form-horizontal well'>
              <fieldset>
              <div class="form-group">  
                <label class="control-label" for="rangoFechas">Rango de fechas</label>
                <div class="controls">
                <div class="input-group" id='rop'>
                  <input type='text' name='rangoInicio' id='rangoInicio' class="input-sm form-control" value="01/01/<?php echo date("Y")?>" data-date-format="dd/mm/yy"  data-plus-as-tab='true'/>
                  <span class="input-group-addon">a</span>
                  <input type='text' name='rangoFin' id='rangoFin' class="input-sm form-control"  value="31/12/<?php echo date("Y")?>" data-date-format="dd/mm/yy" data-plus-as-tab='true'/>
                  <span class="input-group-addon presetAnio btn" id="1000">TODOS</span>
                  <span class="input-group-addon presetAnio btn" id="<?php echo date('Y', strtotime("-1 year"))?>"><?php echo date('Y', strtotime("-1 year"))?></span>
                  <span class="input-group-addon presetAnio btn" id="<?php echo date('Y')?>"><?php echo date('Y')?></span>
                </div></div></div>
                
                <div class="form-group">  
                <label class="control-label" for="activaCuentas">Cliente</label>
                <div class="controls">
                <div class="input-group" id=''>
                  <select name='idcliente' id='idcliente' class='input-sm form-control' placeholder='Filtrar por Cliente'>
                    <option value='' disabled selected>Seleccionar Cliente</option>
                    <?php 
                    foreach($_SESSION['clientesCuentaCorriente'] as $nada => $rowRemito){
                        echo "<option value='$rowRemito[IdCliente]'>$rowRemito[RazonSocial]".(($rowRemito['Identificador']<>'')?" ($rowRemito[Identificador])":'')."</option>";
                    }?>
                  </select>
                </div></div></div>
                <div class="form-group col-md-5 col-md-offset-1">
                <div class="controls">
                  <label class="radio">
                    <input type="radio" name="solocomb" id="idCaja1" value="">Todos
                  </label>
                  <label class="radio">
                    <input type="radio" name="solocomb" id="idCaja2" value="comb" checked><span class="label label-warning">Solo Combustibles</span>
                  </label>
                  <label class="radio">
                    <input type="radio" name="solocomb" id="idCaja3" value="nocomb"><span class="label label-info">No Combustibles</span>
                  </label>
                </div></div>

                <div class="form-group col-md-7 col-md-offset-1">
                  <div class="controls">
                    <label class="checkbox">
                      <input type="checkbox" name="precioAnterior" id="precioAnterior" value="1" checked>Calcula respecto al precio inmediato anterior
                    </label>
                </div></div>
                <div class="form-group col-md-7 col-md-offset-1">
                  <div class="controls">
                    <label class="checkbox">
                      <input type="checkbox" name="ordenArticulo" id="ordenArticulo" value="1">Ordena por articulo
                    </label>
                </div></div>
                <div class="form-group col-md-7 col-md-offset-1">
                  <div class="controls">
                    <label class="checkbox">
                      <input type="checkbox" name="yerContado" id="yerContado" value="1">Precio YER Contado
                    </label>
                </div></div>
              </fieldset>
                    
                <div class="form-group" id='botonEnvio'>
                <label for='enviar' class="control-label"></label>
                <div class="controls"> 
                        <button class="btn btn-primary btn-lg" id='enviar'>Buscar &raquo;</button>
                </div>
                </div>   
                <div class="form-group" id='botonEnviando' style="display:none">
                  <label for='enviandor' class="control-label"></label>
                  <div class="controls"> 
                    <button class="btn btn-primary btn-lg" >Buscando....</button>
                  </div>
                </div>
              </form>
            </div>
            <div class="col-md-7" id='detalleCliente'>
                    <!--<h2>Documentos</h2>
                    <div id='respuesta' style='display:none' class='bg-success'>Cambios grabados</div>
                    <div style='height:80%;overflow-y: scroll;max-height:600px'>
                    <table class='table' id='ultimasFacturas'>
                      <thead><tr><th>Fecha</th>
                      <th>Documento</th>
                      <th>Artículos</th>
                      <th>Precio original</th>
                      <th>Precio actual</th>
                      <th>Ajuste</th>
                      <tbody></tbody>
                    </table>
                    </div>-->
            </div>
            </div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
          $(document).ready(function() {
          $('#botonEnvio').fadeIn();
            
          $('#enviar').click(function() {
              var opciones= {
                  beforeSubmit: mostrarLoader, //funcion que se ejecuta antes de enviar el form
                  success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
                  url:       'func/listaFacturasCuentaCorrienteImpagas.php',         // override for form's 'action' attribute 
                  type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
              };
              $('#nuevaOP').ajaxForm(opciones);    
          });

            //lugar donde defino las funciones que utilizo dentro de "opciones"
          function mostrarLoader(){
              $('#botonEnvio').hide();
              $('#botonEnviando').show();
              $('ultimasFacturas').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
              
          };//lugar donde defino las funciones que utilizo dentro de "opciones"
          
          $( "#clientes" ).change(function() {
            $('#respuesta').hide('slow');
          });
            
          function mostrarRespuesta(responseText){
              $('#botonEnviando').hide();
              $('#botonEnvio').fadeIn();
              $('#detalleCliente').html(responseText);
           }
        });

      </script>
  </body>
</html>
