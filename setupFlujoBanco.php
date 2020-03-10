<?php
$nivelRequerido = 3;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');


setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
$titulo="Proyección Banco Provincia";
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
    <style>
      .dtHorizontalVerticalExampleWrapper {
      max-width: 600px;
      margin: 0 auto;
      }
      #dtHorizontalVerticalExample th, td {
      white-space: nowrap;
      }
      table.dataTable thead .sorting:after,
      table.dataTable thead .sorting:before,
      table.dataTable thead .sorting_asc:after,
      table.dataTable thead .sorting_asc:before,
      table.dataTable thead .sorting_asc_disabled:after,
      table.dataTable thead .sorting_asc_disabled:before,
      table.dataTable thead .sorting_desc:after,
      table.dataTable thead .sorting_desc:before,
      table.dataTable thead .sorting_desc_disabled:after,
      table.dataTable thead .sorting_desc_disabled:before {
      bottom: .5em;
      }
    </style>
  </head>
  <body>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
    <div class="container">
    <div class='row'>
      <div class="col-md-12">
        <h2>&nbsp;</h2>
        <div style='float:right' id='comprimir' class='btn btn-success no2'>Ver comprimido</div>
        <h2>Proyección cuenta 11185/1</h2>
        <div class='table-responsive'>
        <table class='table table-striped table-condensed table-bordered table-sm' cellspacing="0" width="100%" id='flujoBanco'>
          <thead>
          <tr>
            <th width='150px'>
              <input type="text" class="form-control input-sm" id="saldoBanco" placeholder="Saldo" value="<?php if(isset($_SESSION['saldoBanco']))echo $_SESSION['saldoBanco'];?>"/>
            </th>
            <th class='' >Hoy <?php echo date('j/n')?></th>
            
            <?php 
            $j=30;
            for($i=1;$i<=$j;$i++){
              if(date('N', time()+$i*86400)>5){
                // sabado y domingo
                $j++;
              } else {
                $d = time()+$i*86400;
                echo "<th class=''>".$weekday[date('l', $d)].' '.date('j/n', $d)."</th>";
              }
            }?>
          </tr></thead>
          <tbody></tbody>
        </table>
        <h2>Referencias</h2>
        <p>Si un cheque está resaltado <span class='alert-warning'>en amarillo</span> significa que está en poder de la Estación de servicio.<br/>
        Los cheques resaltados <span class='alert-danger'>en rojo</span> son aquellos que estuvieron en poder de la Estación pero se utilizaron para pagos a proveedores</p>
        </div>
      </div>
    </div>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
  </div> <!-- /container -->
  <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
  <script>
  $(document).ready(function() {
    $('#flujoBanco tbody').html("<tr><td colspan='32'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
    function actualizaProyeccion(){
      $.post('func/setupAxFlujoBanco.php', { m:1, saldoBanco:$('#saldoBanco').val() }, function(data) {
        $('#flujoBanco tbody').html(data);
        $('#comprimir').click(function(){
          if($('.detalle').is(":visible") === true ) {
            $('.rowspan').attr('rowspan', '1');
            $(".detalle" ).hide();
            $('#muestraComprimido').val(1);
          } else {
            $('.rowspan').attr('rowspan', '2');
            $(".detalle").show();
            $('#muestraComprimido').val(0);
          }
        });
        $(".pagado").click(function(){
          var cheque = this.id;
          $.post('func/setupAxFlujoBancoMarcaPagado.php', { cheque:this.id }, function(data){
            if(data == 'yes'){
              $('#ch_'+cheque).remove();
            }
          });
        });
        $(".pagado2").click(function(){
          var cheque = this.id;
          $.post('func/setupAxFlujoBancoMarcaPagado.php', { deposito:this.id }, function(data){
            if(data == 'yes'){
              $('#ch_'+cheque).remove();
            }
          });
        });
      });
    }
    actualizaProyeccion();
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
