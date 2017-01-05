<?php

// termina
/* Free statement and connection resources. */
if(isset($stmt)){
  sqlsrv_free_stmt($stmt);
  sqlsrv_close($mssql);
}
$mysqli->close();
?>
<script src="js/jquery-1.8.0.min.js"></script>
<?php if(substr($_SERVER['REMOTE_ADDR'],0,9)=='192.168.1'){?>
<script src="js/jquery-ui.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<?php } else {?>
<script src="https://code.jquery.com/ui/1.11.3/jquery-ui.min.js"></script>
<script src="http://netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>
<?php } ?>
<script src="js/jquery.form.js"></script>
<script src="js/plusastab.joelpurra.js"></script>
<script src="js/emulatetab.joelpurra.js"></script>
<script src="js/jquery.blockUI.js"></script>
<script src="js/bootstrap-switch.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/bootstrap-datepicker.es.js"></script>
