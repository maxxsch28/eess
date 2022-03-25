<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$titulo = "Control de stock en tanques";
if(isset($_GET['calden'])){
  $origen = 'calden';
} elseif(isset($_GET['cem'])){
  $origen = 'cem';
} else {
  $origen = 'cio';
}
if(isset($_SESSION['tanques'])){
  $tanque = $_SESSION['tanques'];
} else {
  $tanque = tanques();
  $_SESSION['tanques'] = $tanque;
}
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
        

                <h2>Período <select name='periodo' id='periodo' class='small'>
                    <?php
                    echo "<option value='".date('Y')."' >".date('Y')."</option>";
                    echo "<option value='".(date('Y')-1)."' >".(date('Y')-1)."</option>";
                    ?>
                    <?php 
                    for ($abc = 11; $abc >= 0; $abc--) {
                        $mes = date("M y", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                        $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                        //echo "<option value='$valorMes' ".(($abc==0)?' selected="selected"':'').">$mes</option>";
                        echo "<option value='$valorMes' >$mes</option>";
                    }?>
                    <option value='30d' selected="selected">30 días</option>
                    <option value='365d'>365 días</option>
                    
                    </select>
                </h2>
            
                <div id='resumen'></div>

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
  function actualiza(){
    $.post('func/listaStockTanquesTabs.2.php', { que:'tanques', mes: $('#periodo').val(), origen: '<?php echo $origen;?>' }, function(data) {
      $('#tab-content').html(data);
      $('#mytab a:first').tab('show');
    });
    $.post('func/listaStockTanquesTabs.2.php', { que:'productos', mes: $('#periodo').val(), origen: '<?php echo $origen;?>' }, function(data) {
      $('#listaProducto').html(data);
    });
    $.post('func/listaStockTanquesTabs.2.php', { que:'resumen2', mes: $('#periodo').val(), origen: '<?php echo $origen;?>' }, function(data) {
      $('#resumen').html(data);
      
    });
  }
  $('#periodo').change(function(){
    actualiza();
  });
  actualiza();
  $('#mytab a:first').tab('show');
  $('#mytab a:first').click();
});
</script>
</body>
</html>
