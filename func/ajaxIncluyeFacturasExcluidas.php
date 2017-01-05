<?php 
/*
update dbo.MovimientosDetalleFac set ExcluidoDeTurno=0 where IdMovimientoFac in (select IdMovimientoFac from dbo.movimientosfac where PuntoVenta=8 and IdTipoMovimiento='FAA' and Numero in (75102, 75109, 75110, 75108,75101, 75104))

*/
?>