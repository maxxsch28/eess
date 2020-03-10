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
        <div style='float:right'><div id='comprimir' class='btn btn-success no2'>Ver comprimido</div>&nbsp;
        <div id='soloVencidas' class='btn btn-success no2'>Muestra solo vencidas</div></div>
        <h2>Flujo mensual de cobranza de cuentas corrientes</h2>
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

    function actualizaProyeccion(mes){
      $.post('func/setupAxFlujoCobranzas.php', { mes:mes }, function(data) {
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
        $(".pagado").click(function(){
          var cheque = this.id;
          $.post('func/setupFlujoBancoMarcaPagado.php', { cheque:this.id }, function(data){
            if(data == 'yes'){
              $('#ch_'+cheque).remove();
            }
          });
        });
        $(".pagado2").click(function(){
          var cheque = this.id;
          $.post('func/setupFlujoBancoMarcaPagado.php', { deposito:this.id }, function(data){
            if(data == 'yes'){
              $('#ch_'+cheque).remove();
            }
          });
        });
      });
    }
    //actualizaProyeccion();
    $('#saldoBanco').change(function(){
      actualizaProyeccion();
    });
    /*$('#flujoBanco').DataTable({
      "scrollX": true,
      "scrollY": 200,
    });
    $('.dataTables_length').addClass('bs-select');
    */
    
  });
  </script>
</body>
</html>
