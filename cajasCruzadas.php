<?php
// cajasCruzadas.php
// Muestra dos columnas con el mayor contable de las cajas en ambos sistemas para poder rastrear mejor los movimientos y detectar fallas en procesos

$nivelRequerido = 2;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
if(isset($_GET['gasoil'])){
  $titulo = "Muestra mayores adelantos de gasoil";
  $gasoil = 1;
} else {
  $titulo = "Muestra mayores de cajas cruzadas";
  $gasoil = 0;
}

?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .asiento {
        height: 8em;
      }
      .table th {
        text-align: center;
      }
      @page {
        size: A4;
        margin: 10px 0 10px;
      }
      @media print {
        html, body {
            width: 210mm;
            height: 297mm;
        }
      }
      .flash {
        -moz-animation: flash 1s ease-out;
        -moz-animation-iteration-count: infinite;

        -webkit-animation: flash 1s ease-out;
        -webkit-animation-iteration-count: infinite;

        -ms-animation: flash 1s ease-out;
        -ms-animation-iteration-count: infinite;
      }

      @-webkit-keyframes flash {
        0% { background-color: none; }
        50% { background-color: #fbf8b2; }
        100% { background-color: #ff0000; }
      }

      @-moz-keyframes flash {
        0% { background-color: none; }
        50% { background-color: #ff0000; }
        100% { background-color: #ddff00; }
      }

      @-ms-keyframes flash {
        0% { background-color: none; }
        50% { background-color: #fbf8b2; }
        100% { background-color: #ff0000; }
      }
    </style>
   
  </head>

  <body>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');?>
    <div class="container">
    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <table class='table' id='libroDiario'></table>
            <div id='detalle' class='well well-sm'></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <form name='nuevaOP' id='nuevaOP' class=''>
      <input type='hidden' name='gasoil' value='<?php echo $gasoil?>'/>
      <div class='row'>
        <div class="col-md-4 mitad">
            <h2>Setup <span class='pull-right' id='sumaSetup'>&nbsp;</span></h2>
        </div>
        <div class="col-md-4 center">
            <div class="form-group" id='botonEnvio' style='text-align:center'>
                <label for='enviar' class="control-label"></label>
                <div class="controls"> 
                  <select name='status' id='status' class='btn-bg btn primary'>
                    <option value='' <?php echo ($_GET['status']!=='nc'&&$_GET['status']!=='c')?" selected='selected'":"";?>>Todos</option>
                    <option value='nc' <?php echo ($_GET['status']=='nc')?" selected='selected'":"";?> >No conciliados</option>
                    <option value='c' <?php echo ($_GET['status']=='c')?" selected='selected'":"";?> >Conciliados</option>
                  </select>&nbsp;&nbsp;&nbsp;
                  <button id='enviar'><span class="glyphicon glyphicon-floppy-remove" aria-hidden="true" id='btnEnviar'></span></button>&nbsp;&nbsp;&nbsp;
                  <button id='refresh'><span class='glyphicon glyphicon-refresh'></span></button>
                </div>
            </div>
        </div>
        <div class="col-md-4 mitad">
            <h2><span id='sumaCalden'>&nbsp;</span><span class='pull-right'>CaldenOil</span></h2>
        </div>
      </div>
      <div class='row'>
            <input type='hidden' name='status' value='<?php echo (isset($_REQUEST['status']))?$_REQUEST['status']:''?>'>
          <div class="col-md-6 mitad">
            <div>
                <table id='cuentaSetup' class='table table-condensed'></table>
            </div>
          </div>
          <div class="col-md-6 mitad">
            <div>
                <table id='cuentaCalden' class='table table-condensed'></table>
            </div>
          </div>
          <?php if(isset($_REQUEST['status']) && $_REQUEST['status']=='nc' && $loggedInUser->user_id==1){?>
          <div class="col-md-12 center">
            <div class="form-group" id='botonEnvio2' style='text-align:center'>
                <label for='enviar2' class="control-label"></label>
                <div class="controls"> 
                    <button id='enviar2'><span class="glyphicon glyphicon-floppy-remove" aria-hidden="true" id='btnEnviar2'></span> Marca movimientos como conciliados de un solo sistema</button>
                </div>
            </div>
          </div>
          <?php }?>
      </div>
    </form>
    <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
  <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
  <script>
    $(document).ready(function() {

      $('#botonEnvio').fadeIn();
      var opciones= {
        success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
        url:       'func/ajaxBuscaConciliarCajas.php',         // override for form's 'action' attribute 
        dataType: 'json',
        type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
      };
        //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
      $('#nuevaOP').ajaxForm(opciones) ;
      
      function mostrarRespuesta(responseText, statusText, xhr, $form){
        if(responseText.status=='yes'){
          $("input[type=checkbox]:checked").closest('td').html("<td class='mConciliado n"+responseText.idConciliado+"'><span class='label label-info'>"+responseText.idConciliado+"</label></td>");
        }
      }
      a=0;
      actualiza();
      $('#refresh').click(function(){
        actualiza();
      });
      $('#status').change(function(){ 
        actualiza();
      });
      function actualiza(){
        $('#cuentaCalden').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
        $('#cuentaSetup').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
        $.post( "func/ajaxCuentaCruzada.php", {go: <?php echo $gasoil?>, caja:'Setup', status:$('#status').val()}, function( data ) {
          $("#cuentaSetup").html( data );

          $("input[type=checkbox].setup").change(function(){
            recalculate2();
          });
          $( ".noConciliado").each(function() { 
              if(a<1000){
              <?php if(!isset($_GET['nc'])){?>
                  $(this).removeClass("alert-danger");
                  $(this).addClass("alert-info");
              <?php }?>
              $.post("func/ajaxBuscaConciliarCajas.php", {go: <?php echo $gasoil?>, idTranglob: $(this).attr('id')}, function (data){
                // aca va lo que tiene que hacer si recibe un idConciliado en forma automática.
                // desaparecer el renglon o agregar el idConciliado
              });
              a++;
              }
          });

          $('.mConciliado').click(function() {
              $('.S').removeClass('flash');
              $('.C').removeClass('flash');
              $('.mConciliado').removeClass('alert-danger flash');
              var currentli = $(this).parent().prop('className');
              var currentli2 = $(this).parent().parent().prop('id');
              $('.'+currentli + ' .mConciliado').addClass('alert-danger flash');
              $('#'+currentli2).addClass('flash');
          });
          $('.mAsS').dblclick(function() {
              $.post("func/ajaxBuscaAsientoPorImporteTransporte.php", {go: <?php echo $gasoil?>, idTranglob: $(this).attr('id')}, function(data){
                $('#libroDiario').html( data );
              });
              $.get("func/muestraDetalleMovimientoTransporte.php", {go: <?php echo $gasoil?>, idTranglob: $(this).attr('id')}, function(data){
                $('#detalle').html( data );
              });
              $('#myModal').modal('show');
          });
        });

        $.post( "func/ajaxCuentaCruzada.php", {go: <?php echo $gasoil?>, caja:'Calden', status:$('#status').val()}, function( data ) {
          $("#cuentaCalden").html( data );

          $("input[type=checkbox].calden").change(function(){
            recalculate();
          });
          $('.mConciliado').click(function() {
              $('.C').removeClass('flash');
              $('.S').removeClass('flash');
              $('.mConciliado').removeClass('alert-danger flash');
              var currentli = $(this).parent().prop('className');
              var currentli2 = $(this).parent().parent().prop('id');
              $('.'+currentli + ' .mConciliado').addClass('alert-danger flash');
              $('#'+currentli2).addClass('flash');
          });
          $('.mAsC').dblclick(function() {
              $.post("func/ajaxBuscaAsientoPorImporte.php", {go: <?php echo $gasoil?>, idAsiento: $(this).attr('id')}, function(data){
                $('#libroDiario').html( data );
              });
              $.get("func/muestraDetalleMovimiento.php", {go: <?php echo $gasoil?>, idAsiento: $(this).attr('id')}, function(data){
                $('#detalle').html( data );
              });
              $('#myModal').modal('show');
          });
        });
      }
      function recalculate(){
          var sum = 0;
          $("input[type=checkbox]:checked.calden").each(function(){
          if($(this).attr('class')=='calden neg'){
              var resta=-1;
            } else {
              var resta=1;
            }
            sum += resta * parseFloat($(this).attr("rel"));
          });
          var sum = sum.toFixed(2);
          $('#sumaCalden').html(sum);
          if($('#sumaSetup').html()!=$('#sumaCalden').html()){
            $('#enviar').addClass('disabled1');
            $('#btnEnviar').removeClass('glyphicon-floppy-disk');
            $('#btnEnviar').addClass('glyphicon-floppy-remove');
          } else {
            $('#enviar').removeClass('disabled1');
            $('#btnEnviar').removeClass('glyphicon-floppy-remove');
            $('#btnEnviar').addClass('glyphicon-floppy-disk');
          }
          if($('#sumaSetup').html()==0&&$('#sumaCalden').html()==0){
            $('#enviar2').addClass('disabled1');
            $('#btnEnviar2').removeClass('glyphicon-floppy-disk');
            $('#btnEnviar2').addClass('glyphicon-floppy-remove');
          } else {
            $('#enviar2').removeClass('disabled1');
            $('#btnEnviar2').removeClass('glyphicon-floppy-remove');
            $('#btnEnviar2').addClass('glyphicon-floppy-disk');
          }
      }
      function recalculate2(){
          var sum = 0;
          $("input[type=checkbox]:checked.setup").each(function(){
            if($(this).attr('class')=='setup neg'){
              var resta=-1;
            } else {
              var resta=1;
            }
            sum += resta * parseFloat($(this).attr("rel"));
          });
          var sum = sum.toFixed(2);
          $('#sumaSetup').html(sum);
          if($('#sumaSetup').html()!=$('#sumaCalden').html()){
            $('#enviar').addClass('disabled1');
            $('#btnEnviar').removeClass('glyphicon-floppy-disk');
            $('#btnEnviar').addClass('glyphicon-floppy-remove');
          } else {
            $('#enviar').removeClass('disabled1');
            $('#btnEnviar').removeClass('glyphicon-floppy-remove');
            $('#btnEnviar').addClass('glyphicon-floppy-disk');
          }
      }
 
    });
  </script>
</body>
</html>
