// saca los movimientos de combustible facturados excluidos de turnos.

select dbo.MovimientosDetalleFac.IdArticulo, RazonSocial, IdTipoMovimiento, PuntoVenta, Numero, Descripcion, Codigo, IdAsiento, IdMovimientoCancelado, Cantidad, Precio, MovimientosDetalleFac.IVA, Facturado, ImporteImpuestoInterno from dbo.articulos, dbo.MovimientosFac, dbo.MovimientosDetalleFac where dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac and IdCierreTurno is NULL and Articulos.IdArticulo=MovimientosDetalleFac.IdArticulo and dbo.movimientosFac.Consignado=1 and DocumentoCancelado=0 and ExcluidoDeTurno=1 and IdTipoMovimiento<>'REM' and MovimientosFac.Fecha>'2013-01-01' and MovimientosFac.Fecha<'2014-01-01'




// actualiza fleteros en viajes con EMPRESA PROPIA
select * from partes where chofer=41 and fletero=0
select * from parttram where chofer=41 and fletero=0

select * from FLETEROS where nombre like ('FOGEL%')

update partes set fletero=202  where chofer=41 and fletero=0;
update parttram set fletero=202  where chofer=41 and fletero=0



// actualiza cuenta contable proveedores
update dbo.proveedo set cuentacont=211101 where cuentacont=0



// configuracion reportes
SELECT     TOP (200) idreporte, origen, reporte, alcance, alcancedef, alcancedoc, detalle, detalledef, reimprime, sesionpriv, cola, pregunta, previsuali, exportabl, prompt, copias, 
                      ascii, pagelen, asciicols, asciirows, initstr, dosrepo, dosenv, winenv, items, expr, tipohoja, e_orientat, e_papersiz, e_paperlen, e_paperwid, e_copies, e_defsourc, 
                      e_color, gm, gt, idrepoorig, [default], exportacio, alcanceusu, dbcactivo, winenvusu, propio, cursoradap, cursorinst, reporte9, bloquear, repornuevo, idreporte9, foxinst, 
                      modulo, compfiscal, deshabilit, migrado, enedicion
FROM         cfgprint



// mas de configuracion de reportes
SELECT     idreporte, origen, reporte, reimprime, idrepoorig, alcance, alcancedoc, deshabilit, tipodeim, entornoext, docentext, detalle, detalledoc, tipodeex, orientacio, pregunta, 
                      copias, cola, ascii, initstr, pagelen, asciicols, asciirows, compfiscal
FROM         repocrys


// sigue


// reportes, impresion
SELECT     idreporte, origen, reporte, reimprime, idrepoorig, alcance, alcancedoc, deshabilit, tipodeim, entornoext, docentext, detalle, detalledoc, tipodeex, orientacio, pregunta, 
                      copias, cola, ascii, initstr, pagelen, asciicols, asciirows, compfiscal
FROM         REPOCRYS
WHERE     (reporte = 'PARVIAJE')





// capitalizador campos
CREATE FUNCTION [dbo].[InitCap] ( @InputString varchar(4000) ) 
RETURNS VARCHAR(4000)
AS
BEGIN

DECLARE @Index          INT
DECLARE @Char           CHAR(1)
DECLARE @PrevChar       CHAR(1)
DECLARE @OutputString   VARCHAR(255)

SET @OutputString = LOWER(@InputString)
SET @Index = 1

WHILE @Index <= LEN(@InputString)
BEGIN
    SET @Char     = SUBSTRING(@InputString, @Index, 1)
    SET @PrevChar = CASE WHEN @Index = 1 THEN ' '
                         ELSE SUBSTRING(@InputString, @Index - 1, 1)
                    END

    IF @PrevChar IN (' ', ';', ':', '!', '?', ',', '.', '_', '-', '/', '&', '''', '(')
    BEGIN
        IF @PrevChar != '''' OR UPPER(@Char) != 'S'
            SET @OutputString = STUFF(@OutputString, @Index, 1, UPPER(@Char))
    END

    SET @Index = @Index + 1
END

RETURN @OutputString

END
GO


