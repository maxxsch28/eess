<?php
$nivelRequerido = 3;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');


setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
$titulo="Flujo de cobranza de cuentas corrientes";

if(isset($_POST['qMeses'])){
  $qMeses = (int) filter_var($_POST['qMeses'], FILTER_SANITIZE_NUMBER_INT);
  $_SESSION['qMeses'] = $qMeses;
} else if(isset($_GET['qMeses'])){
  $qMeses = (int) filter_var($_GET['qMeses'], FILTER_SANITIZE_NUMBER_INT);
  $_SESSION['qMeses'] = $qMeses;
} else if(isset($_SESSION['qMeses'])){
  $qMeses = (int) filter_var($_SESSION['qMeses'], FILTER_SANITIZE_NUMBER_INT);
} else {
  $qMeses = 13;
}


?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
    <style>
      .detRecibo{
        clear: both;
      }
      .facturas {
/*         float: left; */
      }
      .rec{clear:both;}
      .cheques {
        float: right;
      }
    </style>
  </head>
  <body>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
    <div class="container">
    <div class='row'>
      <div class="col-md-12">
        <h2>&nbsp;</h2>
        <div style='float:right'>
          <div id='comprimir' class='btn btn-success no2'>Comprimir</div>&nbsp;
          <div id='soloVencidas' class='btn btn-success no2'>Solo vencidas</div>&nbsp;
          <div id='filtraClientes' class='btn btn-success no2'>Filtra clientes</div>
        </div>
        <h2>Flujo mensual de cobranza de cuentas corrientes</h2>
      </div>
      <div id='clientesFiltrados' class='row ' style='display:'>
      <div class='col-md-12'>
        <form class='form-horizontal'>
          <legend >Clientes</legend>
          <fieldset>
          <div class='col-md-2'>
            <div class="controls">
              <div class="input">
                <input type='text' name='busca' id='busca' maxlength="20" class='input-sm form-control col-md-5'/>
              </div>
            </div>
          </div>
          <div id='listaClientesFiltrados' class='col-md-9'>
            <!--  borrar despues   
             <span id="filtro_6988"><input type="hidden" name="filtro[]" class="filtro" value="6988"><button class="btn btn-info btn-xs" type="button">Facal Marcelo Adrian <span class="badge sacaFiltro">X</span></button></span> <span id="filtro_5148"><input type="hidden" name="filtro[]" class="filtro" value="5148"><button class="btn btn-info btn-xs" type="button">ROTH JAVIER EMILIO <span class="badge sacaFiltro">X</span></button></span> <span id="filtro_5157"><input type="hidden" name="filtro[]" class="filtro" value="5157"><button class="btn btn-info btn-xs" type="button">Tarayre Hector Pedro <span class="badge sacaFiltro">X</span></button></span> <span id="filtro_5122"><input type="hidden" name="filtro[]" class="filtro" value="5122"><button class="btn btn-info btn-xs" type="button">Futuro Ganadero Srl <span class="badge sacaFiltro">X</span></button></span>  <span id="filtro_5142"><input type="hidden" name="filtro[]" class="filtro" value="5142"><button class="btn btn-info btn-xs" type="button">Marios Sa <span class="badge sacaFiltro">X</span></button></span>  <span id="filtro_5142"><input type="hidden" name="filtro[]" class="filtro" value="5142"><button class="btn btn-info btn-xs" type="button">Marios Sa <span class="badge sacaFiltro">X</span></button></span>-->
          </div>
          </fieldset>
        </form>
      </div>
      </div>
      <div class='row'>
      <div class='col-md-12'>
         <?php 
            for($i=0;$i<$qMeses;$i++){
              echo "<legend>".date('M y', strtotime("-$i months"))."</legend>";
              echo "<fieldset id='m".date('Ym', strtotime("-$i months"))."'></fieldset>";
            }?>
        <h2>Referencias</h2>
        <p>Si un cheque está resaltado <span class='alert-warning'>en amarillo</span> significa que está en poder de la Estación de servicio.<br/>
        Las facturas resaltadas <span class='alert-danger'>en rojo</span> son las ya vencidas al momento del recibo. La cantidad de días indicada entre paréntesis en esos casos corresponde a los días pasados entre la fecha de vencimiento del documento y la del recibo que lo imputa.</p>
      </div>
    </div>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
  </div> <!-- /container -->
  <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
  <script>
  $(document).ready(function() {
    refresca();
  
    function refresca(){
      var i;
      var d = new Date();
      d.setDate(1);
      for (i = 0; i < <?php echo $qMeses;?>; i++) {
        mes = d.getFullYear() + ("0" + (d.getMonth()+1) ).slice(-2);
        console.log('mes: ' + mes);
        d.setMonth(d.getMonth() - 1);
        $('#m'+mes).html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
        actualizaProyeccion(mes);
      }
    }
    
    
    $('#filtraClientes').click(function(){
      if($('#clientesFiltrados').is(":visible") === true ) {
        $("#clientesFiltrados" ).hide();
      } else {
        $("#clientesFiltrados").show();
        $("#busca").val('').focus();
      }
    });
    
    $("#busca").autocomplete({
      source: "func/setupAxBuscaCliente.php",
      minLength: 2,
      select: function( event, ui ) {
        $(this).value=ui.item.label;
        $(this).val(ui.item.label);
        $("#listaClientesFiltrados").append("<span id='filtro_"+ui.item.codigo+"'><input type='hidden' name='filtro[]' class='filtro' value='"+ui.item.codigo+"'/><button class='btn btn-info btn-xs' type='button'>"+ui.item.value+" <span class='badge sacaFiltro'>X</span></button></span> ");
        $("#busca").val('asadasdadasd ').focus();
        $(".sacaFiltro").click(function(){
          $(this).parent().parent().remove();
        });
        refresca();
      }
    });
  
  
  
    
    
    function actualizaProyeccion(mes){
      var str = $( ".filtro" ).map(function() { return $( this ).val(); }).get();
      $.post('func/setupAxFlujoCobranzas.php', { mes:mes, filtro:str }, function(data) {
        $('#m'+mes).html(data);
        $('#soloVencidas').click(function(){
          if($('.sinD').is(":visible") === true ) {
            $('.rowspan').attr('rowspan', '1');
            $(".sinD" ).hide();
            $('#muestraComprimido').val(1);
          } else {
            $('.rowspan').attr('rowspan', '2');
            $(".sinD").show();
            $('#muestraComprimido').val(0);
          }
        });
        $('#comprimir').click(function(){
          if($('.detRecibo').is(":visible") === true ) {
            $('.rowspan').attr('rowspan', '1');
            $(".detRecibo" ).hide();
            $('#muestraComprimido').val(1);
          } else {
            $('.rowspan').attr('rowspan', '2');
            $(".detRecibo").show();
            $('#muestraComprimido').val(0);
          }
        });
      });
    }

  });
  </script>
</body>
</html>
