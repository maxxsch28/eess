<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$titulo = "Control de stock en tanques";
if(isset($_GET['calden'])){
  $origen = 'calden';
} else {
  $origen = 'cem';
}
$tanques = array(1=>2068, 2=>2069, 3=>2078, 4=>2068, 5=>2076, 6=>2069);
$articulo = array(2068=>"Infinia D.",2069=>"Ultra",2076=>"Infinia",2078=>"Super");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
  <link rel="stylesheet" href="css/jquery.modal.css" type="text/css" media="screen" />
  <style type="text/css">
    body {
      padding-top: 60px;
      padding-bottom: 40px;
    }
    .neg {
        color: red;
    }
    .pos {
        color: green;
    }
    @media print {
          #fila1 {page-break-after: always;}
      }
  </style>
</head>
<body>
      <?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
      <div class="container">
              <div class='row' id='fila1'>
          <!--
          <div class='row'>
              <div class="col-md5">
              <table class='table table-condensed table-hover' id='listaGastos'>
                  <colgroup>
                      <col >
                      <?php
                      foreach($tanques as $numero => $idProducto){
                          echo "<col class='alert alert-$classArticulo[$idProducto]' span='2'>";
                      }?>
                      <col>
                      <?php
                      foreach($articulo as $idProducto => $producto){
                          echo "<col class='alert alert-$classArticulo[$idProducto]' span='2'>";
                      }?>
                  </colgroup>
                  <thead><tr><th class='nombre' rowspan="2">Fecha</th><th align=center colspan="<?php echo count($tanques)*2?>">Por tanques</th><th>&nbsp;</th><th colspan="<?php echo count($articulo)*2?>">Por Producto</th></tr>
                      <tr>
                      <?php
                      foreach($tanques as $numero => $idProducto){
                          echo "<th colspan='2'><b>$numero</b> - $articulo[$idProducto]</th>";
                      }
                      echo "<th></th>";
                      ksort($articulo);
                      foreach($articulo as $idProducto => $producto){
                          echo "<th colspan='2'><b>$producto</b></th>";
                      }
                      ?>
                      </tr>
                  </thead>
                  <tbody></tbody>
              </table>
              </div>
          </div>-->
          <div class="col-md-7 hidden-print">
            <ul class="nav nav-tabs" role="tablist" id="myTab" style='height:3.1em;'>
              <li>
                <form class='form-horizontal'><select name='periodo' id='periodo'>
                <?php
                  echo "<option value='".date('Y')."' >".date('Y')."</option>";
                  echo "<option value='".(date('Y')-1)."' >".(date('Y')-1)."</option>";
                ?>
                <?php 
                for ($abc = 11; $abc >= 0; $abc--) {
                    $mes = date("F y", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                    $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                    //echo "<option value='$valorMes' ".(($abc==0)?' selected="selected"':'').">$mes</option>";
                    echo "<option value='$valorMes' >$mes</option>";
                }?>
                <option value='30d' selected="selected">30 días</option>
                <option value='365d'>365 días</option>
                
                </select></form>
              </li><li>&nbsp;  &nbsp;</li>
              <?php foreach($tanques as $id => $idArticulo){
                echo "<li role='presentation'><a href='#tq$id' aria-controls='tq$id' role='tab' data-toggle='tab' class='alert alert-{$classArticulo[$idArticulo]}'>$articulo[$idArticulo]</a></li>";
              }?>
            </ul>

            <div class="tab-content" id="tab-content">
              <div role="tabpanel" class="tab-pane" id="tq1"></div>
              <div role="tabpanel" class="tab-pane" id="tq2"></div>
              <div role="tabpanel" class="tab-pane" id="tq3"></div>
              <div role="tabpanel" class="tab-pane" id="tq4"></div>
              <div role="tabpanel" class="tab-pane" id="tq5"></div>
              <div role="tabpanel" class="tab-pane" id="tq6"></div>
            </div>
          </div>
          <div class='col-md-5' id='resumen'>
          </div>
      </div>
      <div class="row">
        <h2 class="hidden-print">Detalle por producto</h2>
        <div id='listaProducto'>
        </div>
      </div>
      <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
  </div> <!-- /container -->
<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
<script src="js/jquery.modal.min.js" type="text/javascript" charset="utf-8"></script>
<script>
$(document).ready(function() {
  $.post('func/listaStockTanquesTabs.php', { mes: $('#periodo').val(), origen: '<?php echo $origen;?>' }, function(data) {
    $('#tab-content').html(data);
    $('#mytab a:first').tab('show');
  });
  $.post('func/listaStockTanquesTabs.php', { producto: $('#periodo').val(), origen: '<?php echo $origen;?>' }, function(data) {
    $('#listaProducto').html(data);
  });
  $.post('func/listaStockTanquesTabs.php', { resumen: $('#periodo').val(), origen: '<?php echo $origen;?>' }, function(data) {
    $('#resumen').html(data);
    $('.descargaCisterna').click(function(){
      $('#myModal').modal({remote:'func/modalDescargaCisterna.php?noOp=1'});
      var opciones= {
        success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
        url:       'func/asignaTanques2.php',         // override for form's 'action' attribute 
        type:      'post',       // 'get' or 'post', override for form's 'method' attribute
        dataType:   'json'
      };
      //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
      $('#formDescarga').ajaxForm(opciones) ; 

      function mostrarRespuesta(responseText){
        if(responseText.status==='success'){
          $('#myModal').modal('hide');
          location.reload();
        } else {
          $('.litros').effect( "highlight", {color:"#c7270a"}, 3000 );
        }
      };
    });
  });
  $('#periodo').change(function(){
    $.post('func/listaStockTanquesTabs.php', { mes: $('#periodo').val(), origen: '<?php echo $origen;?>' }, function(data) {
      $('#tab-content').html(data);
      $('#mytab a:first').tab('show');
      $.post('func/listaStockTanquesTabs.php', { producto: $('#periodo').val(), origen: '<?php echo $origen;?>' }, function(data) {
        $('#listaProducto').html(data);
      });
      $.post('func/listaStockTanquesTabs.php', { resumen: $('#periodo').val(), origen: '<?php echo $origen;?>' }, function(data) {
        $('#resumen').html(data);
        $('.descargaCisterna').click(function(){
          $('#myModal').modal({remote:'func/modalDescargaCisterna.php?noOp=1'});
          var opciones= {
            success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
            url:       'func/asignaTanques2.php',         // override for form's 'action' attribute 
            type:      'post',       // 'get' or 'post', override for form's 'method' attribute
            dataType:   'json'
          };
          //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
          $('#formDescarga').ajaxForm(opciones) ; 

          function mostrarRespuesta(responseText){
              if(responseText.status==='success'){
                  $('#myModal').modal('hide');
                  location.reload();
              } else {
                  $('.litros').effect( "highlight", {color:"#c7270a"}, 3000 );
              }
          };
        });
      });
    });
  });
  $('#mytab a:first').tab('show');
  $('#mytab a:first').click();
});
</script>
</body>
</html>
