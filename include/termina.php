<?php
// termina
/* Free statement and connection resources. */
if(isset($stmt)){
  sqlsrv_free_stmt($stmt);
  sqlsrv_close($mssql);
}
$mysqli->close();
?>
<?php if(substr($_SERVER['REMOTE_ADDR'],0,9)=='192.168.1'||true){?>
  <!--<script src="js/jquery-3.2.1.slim.min.js"></script>-->
  <script src="js/jquery-1.8.0.min.js"></script>
  <script src="js/jquery-ui.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
<?php } else {?>
  <script src="https://code.jquery.com/ui/1.11.3/jquery-ui.min.js"></script>
  <script src="http://netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>
  <!--<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>-->
<?php } ?>
<script src="js/jquery.form.js"></script>
<script src="js/plusastab.joelpurra.js"></script>
<script src="js/emulatetab.joelpurra.js"></script>
<script src="js/jquery.blockUI.js"></script>
<script src="js/bootstrap-switch.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/bootstrap-datepicker.es.js"></script>
<script src="js/jquery.ui.position.min.js"></script>
<script src="js/jquery.contextMenu.min.js"></script>
<script>
  function coma(numero){
    if(numero<0){
      numero = -1*numero;
    }
    if(isNaN(numero)){
      numero = numero.toString().replace(/,/g , "__COMMA__").replace(/\./g, '').replace(/__COMMA__/g, '.');
    }
    return numero;
  }
</script>
