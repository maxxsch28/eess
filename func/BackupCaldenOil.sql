/*******************************************************************************************************************
**	Las variables que hay que definir para personalizar la instalación son los siguientes:
**
**		@HacerBackupCaldenOil: indica si se hará backup del contenido de la carpeta Release de CaldenOil.
**		@@HacerBackupSextante: indica si se hará backup del contenido de la carpeta Release del Sextante.
**
**		@MoverCopiasADiscoExterno: indica si se moverán las copias a una ubicación externa o no.
**
**		@strBackupFolder: define el directorio LOCAL en donde se harán las copias de seguridad.
**		@strBackupReleaseFolder: define el directorio LOCAL donde se harán las copias de seguridad de los ejecutables .
**
**		@strBackupFolder2: define el directorio DE RED en donde se copiarán los archivos.
**		@strBackupReleaseFolder2: define el directorio DE RED donde se harán las copias de seguridad de los ejecutables.
**
**		@strReleaseCaldenOilFolder: define el directorio en donde están los componentes de CaldenOil.
**		@strReleaseSextanteFolder: define el directorio en donde están los componentes del Sextante.
**
**		@strCantidadBackups: define la cantidad de copias de seguridad a dejar
**
********************************************************************************************************************/
/*
************************ 1 - Declaración de Variables ************************
*/
PRINT 'Comienzo de la copia de seguridad. Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
-- a) Declaramos las variables temporales que usaremos para hacer el Backup
DECLARE 
	@strDatabase AS VARCHAR(255),
	@strBackupDate AS CHAR(14),
	@strBackupFolder AS VARCHAR(255),
	@strBackupFolder2 AS VARCHAR(255),
	@strBackupReleaseFolder AS VARCHAR(255),
	@strBackupReleaseFolder2 AS VARCHAR(255),
	@strBackupDescription AS VARCHAR(255),
	@strBackupName AS VARCHAR(255),
	@strBackupFile AS VARCHAR(255),
	@strReleaseCaldenOilFolder AS VARCHAR(255),
	@strReleaseSextanteFolder AS VARCHAR(255),
	@MoverCopiasADiscoExterno AS BIT,	
	@HacerBackupCaldenOil AS BIT,
	@HacerBackupSextante AS BIT

-- b) Indicamos los parámetros generales y las carpeta en donde haremos las copias de seguridad
SET @MoverCopiasADiscoExterno		= 1
SET @HacerBackupCaldenOil		= 1
SET @HacerBackupSextante		= 0

-- Carpeta Local donde se harán las copias de seguridad
SET @strBackupFolder		= N'E:\backups\SQL\'
SET @strBackupReleaseFolder 	= N'E:\backups\Exes'

-- Carpeta de Red a donde se moverán los archivos una vez hecho el Backup
SET @strBackupFolder2		 = N'\\192.168.1.28\backupSQL'
SET @strBackupReleaseFolder2 	 = N'\\192.168.1.28\backupSQL\EXES'

-- Ubicación del sistema para backup de ejecutables
SET @strReleaseCaldenOilFolder	= N'C:\Program Files (x86)\Aoniken\CaldenOil.Net\Release\'
SET @strReleaseSextanteFolder	= N'C:\Program Files\Aoniken\Sextante\Release'

-- c) Declaramos las variables temporales que usaremos para borrar los archivos viejos
DECLARE 
	@CMD1 varchar(5000),
	@CMD2 varchar(5000),
	@strFilePath varchar(200),
	@strFilePath2 varchar(200),
	@strDatabase2 AS VARCHAR(255), 
	@strCuantos AS VARCHAR(255), 
	@strCantidadBackups INT,
	@strToDelete INT,
	@strFileToDelete VARCHAR(255)

-- d) Indicamos la cantidad de copias de seguridad A DEJAR
SET @strCantidadBackups = 7

-- e) Tomamos la fecha y hora actual para estampar en los nombres de las copias de seguridad
SELECT @strBackupDate =  
		RIGHT('0000' + RTRIM(CAST(DATEPART(yyyy, GETDATE()) AS CHAR(4))), 4) +
		RIGHT('00' + RTRIM(CAST(DATEPART(mm, GETDATE()) AS CHAR(2))), 2) +
		RIGHT('00' + RTRIM(CAST(DATEPART(dd, GETDATE()) AS CHAR(2))), 2) + 
		RIGHT('00' + RTRIM(CAST(DATEPART(hh, GETDATE()) AS CHAR(2))), 2) + 
		RIGHT('00' + RTRIM(CAST(DATEPART(mi, GETDATE()) AS CHAR(2))), 2) + 
		RIGHT('00' + RTRIM(CAST(DATEPART(ss, GETDATE()) AS CHAR(2))), 2)

/*
	Registramos las configuraciones:
*/
PRINT '**********************************************************************************************************'
PRINT '		@MoverCopiasADiscoExterno = ' + CAST(@MoverCopiasADiscoExterno AS CHAR(1))
PRINT '		@HacerBackupCaldenOil = ' + CAST(@HacerBackupCaldenOil AS CHAR(1))
PRINT '		@HacerBackupSextante = ' + CAST(@HacerBackupSextante AS CHAR(1))
PRINT '		@strBackupFolder = ' + @strBackupFolder
PRINT '		@strBackupReleaseFolder = ' + @strBackupReleaseFolder
PRINT '		@strReleaseCaldenOilFolder = ' + @strReleaseCaldenOilFolder
PRINT '		@strReleaseSextanteFolder = ' + @strReleaseSextanteFolder
PRINT '		@strCantidadBackups = ' + CAST(@strCantidadBackups AS CHAR(2))
PRINT '**********************************************************************************************************'
/*
************************ 2 - Copias de Seguridad de las BASES DE DATOS ************************
*/
-- a) Recuperamos todas las BD existentes de CaldenOil
DECLARE CUR_DatabasesNet CURSOR FOR
SELECT 
	Name
FROM
	master.dbo.sysdatabases 
WHERE
	UPPER(Name) LIKE '%.NET' -- = 'ESTACION.NET' --
ORDER BY
	UPPER(Name)
	
PRINT 'Recuperando los nombres de las Bases de Datos. Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
PRINT '*********************************************'
PRINT 'Bases de Datos de CaldenOil (*.NET): '
OPEN CUR_DatabasesNet
FETCH NEXT FROM CUR_DatabasesNet INTO @strDatabase
WHILE
	@@FETCH_STATUS = 0
	BEGIN
		PRINT '		' + @strDatabase
	FETCH NEXT FROM CUR_DatabasesNet INTO @strDatabase
END
CLOSE CUR_DatabasesNet
PRINT '*********************************************'

-- b) Hacemos Backup de las BD .Net
PRINT 'Comienzo del backup de las Bases de Datos. Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
OPEN CUR_DatabasesNet
FETCH NEXT FROM CUR_DatabasesNet INTO @strDatabase
WHILE
	@@FETCH_STATUS = 0
	BEGIN
		
		PRINT '*****  BD encontrada: ' + @strDatabase + '  ***** '
		SET @strBackupDescription = N'Backup completo ' + @strDatabase 
		SET @strBackupName = @strDatabase + @strBackupDate
		SET @strBackupFile = @strBackupFolder + N'\' + @strBackupName + N'.bak'
		
		PRINT 'Comenzando backup de la BD ' + @strDatabase + '. Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
		
		BACKUP DATABASE @strDatabase
		TO DISK = @strBackupFile
		WITH DESCRIPTION = @strBackupDescription, NOFORMAT, INIT,  
		NAME = @strBackupName, SKIP, NOREWIND, NOUNLOAD, STATS = 10
		
		PRINT 'Backup Finalizado. Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)

	FETCH NEXT FROM CUR_DatabasesNet INTO @strDatabase
END

CLOSE CUR_DatabasesNet
DEALLOCATE CUR_DatabasesNet

-- c) Hacemos Backup de la BD de Seguridad
SET @strDatabase = N'NetSqlAzManStorage'
PRINT '*****  BD encontrada: ' + @strDatabase + '  ***** '
SET @strBackupDescription = N'Backup completo ' + @strDatabase
SET @strBackupName = @strDatabase + @strBackupDate
SET @strBackupFile = @strBackupFolder + N'\' + @strBackupName + N'.bak'

PRINT 'Comenzando backup de la BD ' + @strDatabase + '. Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
BACKUP DATABASE @strDatabase
TO DISK = @strBackupFile
WITH DESCRIPTION = @strBackupDescription, NOFORMAT, INIT,  
NAME = @strBackupName, SKIP, NOREWIND, NOUNLOAD, STATS = 10
PRINT 'Backup Finalizado. Fecha Actual: ' + CAST(GETDATE() AS NVarChar)

PRINT 'Finalización de la copia de seguridad de las BD de CaldenOil. Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)

-- d) Hacemos Backup de la BD master de SQL
SET @strDatabase = N'master'
PRINT '*****  BD encontrada: ' + @strDatabase + '  ***** '
SET @strBackupDescription = N'Backup completo ' + @strDatabase
SET @strBackupName = @strDatabase + @strBackupDate
SET @strBackupFile = @strBackupFolder + N'\' + @strBackupName + N'.bak'

PRINT 'Comenzando backup de la BD ' + @strDatabase + '. Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
BACKUP DATABASE @strDatabase
TO DISK = @strBackupFile
WITH DESCRIPTION = @strBackupDescription, NOFORMAT, INIT,  
NAME = @strBackupName, SKIP, NOREWIND, NOUNLOAD, STATS = 10
PRINT 'Backup Finalizado. Fecha Actual: ' + CAST(GETDATE() AS NVarChar)
/*
************************ 3 - Comprimimos las copias y borramos los archivos *.bak ************************
*/
-- e) Construimos la cadena para capturar los nombres de los archivos existentes
PRINT 'Recuperando la lista de archivos con extensión .bak.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
SELECT @strFilePath = SUBSTRING(@strBackupFolder, 1, LEN(@strBackupFolder))
SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'dir ' + char(34) + @strFilePath + '\*.bak' + char(34) + char(39)
PRINT @CMD1
EXEC (@CMD1)

-- f) Comprimimos las copias
PRINT 'Comprimiendo los archivos con extensión .bak.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
SELECT @CMD1 = ''
--SELECT @strFilePath = SUBSTRING(@strBackupFolder, 1, LEN(@strBackupFolder))
--START /WAIT WinRAR a -agYYYYMMDDHHMMSS -ibck Backup *.bak
SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'START /WAIT WinRAR a -agYYYYMMDDHHMMSS -ibck ' + char(34) + @strFilePath + '\Backup' + char(34) + ' ' + char(34) + @strFilePath + '\*.bak' + char(34) + char(39)
PRINT @CMD1
EXEC (@CMD1)

-- g) Borramos los .bak pues ya están comprimidos
--Del *.bak
PRINT 'Borrando los archivos .bak recién comprimidos.' + CONVERT(VarChar, GETDATE(), 113)
SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'del ' + char(34) + @strFilePath + '\*.bak' + char(34) + char(39)
PRINT @CMD1
EXEC (@CMD1)

/*
************************ 4 - Borramos los archivos mas viejos (.rar) ************************
*/
PRINT 'Recuperando el listado de los archivos .rar.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
-- h) Creamos la tabla temporal #OriginalFileList que contendrá el listado con todos los datos de los archivos
CREATE TABLE #OriginalFileList1
	(
		Col1 varchar(1000) NULL
	)

-- i) Creamos la tabla @ParsedFileList que contendrá la lista con los datos de todos los archivos del directorio de backup
DECLARE @ParsedFileList1 TABLE
	(
		PFLID INT PRIMARY KEY IDENTITY (1,1) NOT NULL,
		DateTimeStamp varchar(50) NOT NULL,
		FileSize varchar(50) NOT NULL,
		FileName1 varchar (255) NOT NULL
	)

-- j) Creamos la tabla @FileListToDelete que contendrá la lista de los archivos a borrar
DECLARE @FileListToDelete1 TABLE
	(
		IdFileName1 INT PRIMARY KEY IDENTITY (1,1) NOT NULL,
		FileName1 varchar (255) NOT NULL
	)

-- k) Inicializamos las variables para la recuperación de los nombres de los archivos
SELECT @CMD1 = ''
SELECT @CMD2 = '' 
SELECT @strFilePath = SUBSTRING(@strBackupFolder, 1, LEN(@strBackupFolder))

-- l) Construimos la cadena para capturar los nombres de los archivos existentes
SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'dir ' + char(34) + @strFilePath + '\*.rar' + char(34) + char(39)
PRINT @CMD1
EXEC (@CMD1)

-- m) Construimos la cadena para publicar los datos dentro de la tabla temporal #OriginalFileList
SELECT @CMD2 = 'INSERT INTO #OriginalFileList1 (Col1)' + char(13) + 'EXEC ' + @CMD1

-- n) Ejecutamos la cadena para publicar los datos en la tabla @OriginalFileList
PRINT @CMD2
EXEC (@CMD2)

-- Para Debug:
 --SELECT *
 --FROM #OriginalFileList1

-- o) Borramos los datos que no necesitamos de la tabla @OriginalFileList
DELETE FROM #OriginalFileList1
WHERE
	(
		Col1 IS NULL OR
		Col1 LIKE '%Volume%' OR 
		Col1 LIKE '%Directory%' OR
		Col1 LIKE '%Directorio%' OR
		Col1 LIKE '%<DIR>%' OR
		Col1 LIKE '%bytes%'
	)	

-- p) Publicamos los datos en la tabla final @ParsedFileList
INSERT INTO @ParsedFileList1 (DateTimeStamp, FileSize, FileName1)
SELECT 
	LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '
	AS 'DateTimeStamp',
	LTRIM(SUBSTRING(LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1, CHARINDEX(' ', LTRIM(SUBSTRING(Col1,CHARINDEX(' ' , LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1)))
	AS 'FileSize',
	LTRIM(SUBSTRING(Col1, CHARINDEX('B', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  ') + LEN(LTRIM(SUBSTRING(LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1, CHARINDEX(' ', LTRIM(SUBSTRING(Col1, CHARINDEX(' ' , LTRIM(Col1), CHARINDEX(' ' , LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1,CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1))))), LEN(Col1)))
	AS 'FileName1'
FROM #OriginalFileList1

-- Para Debug:
 SELECT *
 FROM @ParsedFileList1

--  q) Creamos un cursor con los nombres de las BD 
DECLARE CUR_Databases CURSOR FOR
SELECT 
	SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18) as strDatabase, 
	COUNT(SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18)) as Cuantos 
FROM 
	@ParsedFileList1
GROUP BY 
	SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18)

OPEN CUR_Databases
FETCH NEXT FROM CUR_Databases INTO @strDatabase2, @strCuantos
WHILE
	@@FETCH_STATUS = 0
	BEGIN
	IF CAST(@strCuantos AS INT) > @strCantidadBackups
	-- Si la cantidad de archivos existentes es mayor que la cantidad a dejar, cargamos en una
	-- tabla temporal los nombres de los archivos para luego leerlos y borrarlos
		BEGIN
			SET @strToDelete = @strCuantos - @strCantidadBackups
			INSERT INTO @FileListToDelete1
				(FileName1)
			SELECT TOP (@strToDelete) FileName1
			FROM @ParsedFileList1
			WHERE SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18) = @strDataBase2
		END
	FETCH NEXT FROM CUR_Databases INTO @strDatabase2, @strCuantos
END

SELECT *
FROM @FileListToDelete1

CLOSE CUR_Databases
DEALLOCATE CUR_Databases

--  r) Finalmente borramos los archivos de las copias de seguridad mas viejas
PRINT 'Borrando los archivos más antiguos.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
SELECT @CMD1 = ''
SELECT @CMD2 = '' 

DECLARE CUR_FileToDelete CURSOR FOR
SELECT 
	RTRIM(FileName1)
FROM 
	@FileListToDelete1

OPEN CUR_FileToDelete
FETCH NEXT FROM CUR_FileToDelete INTO @strFileToDelete
WHILE
	@@FETCH_STATUS = 0
	BEGIN
		-- Chr(32) debería ser el espacio en blanco
		SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'del ' + char(34) + @strFilePath + '\'
		SELECT @CMD2 = @CMD1 + @strFileToDelete + char(34) + char(39)
		Print @CMD2
		EXEC (@CMD2)
	FETCH NEXT FROM CUR_FileToDelete INTO @strFileToDelete
END

CLOSE CUR_FileToDelete
DEALLOCATE CUR_FileToDelete

-- s) Borramos la tabla temporal
DROP TABLE #OriginalFileList1
PRINT 'Finalizada la copia de seguridad en el directorio local: ' + @strBackupFolder + '. Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)

--/*
--************************ 5 - Movemos los archivos a la ubicación de red y eliminamos los más viejos ************************
--*/
IF @MoverCopiasADiscoExterno = 1
BEGIN
	-- t) Copiamos las copias de seguridad a la ubicación de red
	PRINT 'Copiando los archivos a la ubicación de red.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	SELECT @strFilePath = SUBSTRING(@strBackupFolder, 1, LEN(@strBackupFolder))
	SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'xcopy ' + char(34) + @strFilePath + '\*.rar' + char(34) + ' ' + char(34) + @strBackupFolder2 + char(34) + ' /D /Y' + char(39)
	PRINT @CMD1
	EXEC (@CMD1)

	-- u) Eliminamos los más viejos (repetimos el proceso anterior pero para el directorio de red
	--		Creamos la tabla temporal #OriginalFileList que contendrá el listado con todos los datos de los archivos
	PRINT 'Recuperando el listado de los archivos .rar.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	CREATE TABLE #OriginalFileList2
		(
			Col1 varchar(1000) NULL
		)

	-- v) Creamos la tabla @ParsedFileList que contendrá la lista con los datos de todos los archivos del directorio de backup
	DECLARE @ParsedFileList2 TABLE
		(
			PFLID INT PRIMARY KEY IDENTITY (1,1) NOT NULL,
			DateTimeStamp varchar(50) NOT NULL,
			FileSize varchar(50) NOT NULL,
			FileName1 varchar (255) NOT NULL
		)

	-- w) Creamos la tabla @FileListToDelete que contendrá la lista de los archivos a borrar
	DECLARE @FileListToDelete2 TABLE
		(
			IdFileName1 INT PRIMARY KEY IDENTITY (1,1) NOT NULL,
			FileName1 varchar (255) NOT NULL
		)

	-- x) Inicializamos las variables para la recuperación de los nombres de los archivos
	SELECT @CMD1 = ''
	SELECT @CMD2 = '' 
	SELECT @strFilePath = SUBSTRING(@strBackupFolder2, 1, LEN(@strBackupFolder2))

	-- y) Construimos la cadena para capturar los nombres de los archivos existentes
	SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'dir ' + char(34) + @strFilePath + '\*.rar' + char(34) + char(39)
	PRINT @CMD1
	EXEC (@CMD1)

	-- z) Construimos la cadena para publicar los datos dentro de la tabla temporal #OriginalFileList2
	SELECT @CMD2 = 'INSERT INTO #OriginalFileList2 (Col1)' + char(13) + 'EXEC ' + @CMD1

	-- a) Ejecutamos la cadena para publicar los datos en la tabla @OriginalFileList
	PRINT @CMD2
	EXEC (@CMD2)

	-- Para Debug:
	 --SELECT *
	 --FROM #OriginalFileList2

	-- b) Borramos los datos que no necesitamos de la tabla @OriginalFileList
	DELETE FROM #OriginalFileList2
	WHERE
		(
			Col1 IS NULL OR
			Col1 LIKE '%Volume%' OR 
			Col1 LIKE '%Directory%' OR
			Col1 LIKE '%Directorio%' OR
			Col1 LIKE '%<DIR>%' OR
			Col1 LIKE '%bytes%'
		)	

	-- c) Publicamos los datos en la tabla final @ParsedFileList
	INSERT INTO @ParsedFileList2 (DateTimeStamp, FileSize, FileName1)
	SELECT 
		LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '
		AS 'DateTimeStamp',
		LTRIM(SUBSTRING(LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1, CHARINDEX(' ', LTRIM(SUBSTRING(Col1,CHARINDEX(' ' , LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1)))
		AS 'FileSize',
		LTRIM(SUBSTRING(Col1, CHARINDEX('B', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  ') + LEN(LTRIM(SUBSTRING(LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1, CHARINDEX(' ', LTRIM(SUBSTRING(Col1, CHARINDEX(' ' , LTRIM(Col1), CHARINDEX(' ' , LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1,CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1))))), LEN(Col1)))
		AS 'FileName1'
	FROM #OriginalFileList2

	-- Para Debug:
	 SELECT *
	 FROM @ParsedFileList2

	--  d) Creamos un cursor con los nombres de las BD 
	DECLARE CUR_Databases CURSOR FOR
	SELECT 
		SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18) as strDatabase, 
		COUNT(SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18)) as Cuantos 
	FROM 
		@ParsedFileList2
	GROUP BY 
		SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18)

	OPEN CUR_Databases
	FETCH NEXT FROM CUR_Databases INTO @strDatabase2, @strCuantos
	WHILE
		@@FETCH_STATUS = 0
		BEGIN
		IF CAST(@strCuantos AS INT) > @strCantidadBackups
		-- Si la cantidad de archivos existentes es mayor que la cantidad a dejar, cargamos en una
		-- tabla temporal los nombres de los archivos para luego leerlos y borrarlos
			BEGIN
				SET @strToDelete = @strCuantos - @strCantidadBackups
				INSERT INTO @FileListToDelete2	
					(FileName1)
				SELECT TOP (@strToDelete) FileName1
				FROM @ParsedFileList2
				WHERE SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18) = @strDataBase2
			END
		FETCH NEXT FROM CUR_Databases INTO @strDatabase2, @strCuantos
	END

	SELECT *
	FROM @FileListToDelete2

	CLOSE CUR_Databases
	DEALLOCATE CUR_Databases

	--  e) Finalmente borramos los archivos de las copias de seguridad mas viejas
	PRINT 'Borrando los archivos más antiguos.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	SELECT @CMD1 = ''
	SELECT @CMD2 = '' 

	DECLARE CUR_FileToDelete CURSOR FOR
	SELECT 
		RTRIM(FileName1)
	FROM 
		@FileListToDelete2

	OPEN CUR_FileToDelete
	FETCH NEXT FROM CUR_FileToDelete INTO @strFileToDelete
	WHILE
		@@FETCH_STATUS = 0
		BEGIN
			-- Chr(32) debería ser el espacio en blanco
			SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'del ' + char(34) + @strFilePath + '\'
			SELECT @CMD2 = @CMD1 + @strFileToDelete + char(34) + char(39)
			Print @CMD2
			EXEC (@CMD2)
		FETCH NEXT FROM CUR_FileToDelete INTO @strFileToDelete
	END

	CLOSE CUR_FileToDelete
	DEALLOCATE CUR_FileToDelete

	-- f) Borramos la tabla temporal
	DROP TABLE #OriginalFileList2
	PRINT 'Finalizada la copia de seguridad en el directorio de red: ' + @strBackupFolder2 + '. Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
END

--/*
--************************ 6 - Copias de Seguridad de los EJECUTABLES ************************
--*/
IF @HacerBackupCaldenOil = 1
BEGIN
	-- Release de CALDENOIL
	-- a) Construimos la cadena para capturar los nombres de los archivos existentes
	SELECT @strFilePath2 = SUBSTRING(@strReleaseCaldenOilFolder, 1, LEN(@strReleaseCaldenOilFolder))
	PRINT 'Recuperando la lista de archivos con extensión .bak.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'dir ' + char(34) + @strFilePath2 + '\*.*' + char(34) + ' /S' + char(39)
	PRINT @CMD1
	EXEC (@CMD1)

	-- b) Comprimimos los archivos y sus subdirectorios
	PRINT 'Comprimiendo los archivos que están dentro de Release.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	SELECT @CMD1 = ''
	SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'START /WAIT WinRAR a -agYYYYMMDDHHMMSS -ibck -r -x*\Logs -x*\Temp*\ -x*\Varios -x*.rar ' + char(34) + @strBackupReleaseFolder + '\BackupReleaseCaldenOil' + char(34) + ' ' + char(34) + @strFilePath2 + '\*.*' + char(34) + char(39)
	PRINT @CMD1
	EXEC (@CMD1)

	PRINT 'Recuperando el archivo recién creado.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	SELECT @CMD1 = ''
	SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'dir ' + char(34) + @strBackupReleaseFolder + '\BackupReleaseCaldenOil*.rar' + char(34) + ' /od' + char(39)
	PRINT @CMD1
	EXEC (@CMD1)
END

IF @HacerBackupSextante = 1
BEGIN
	-- Release de SEXTANTE
	-- c) Construimos la cadena para capturar los nombres de los archivos existentes
	SELECT @strFilePath2 = SUBSTRING(@strReleaseSextanteFolder, 1, LEN(@strReleaseSextanteFolder))
	PRINT 'Recuperando la lista de archivos con extensión .bak.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'dir ' + char(34) + @strFilePath2 + '\*.*' + char(34) + ' /S' + char(39)
	PRINT @CMD1
	EXEC (@CMD1)

	-- d) Comprimimos los archivos y sus subdirectorios
	PRINT 'Comprimiendo los archivos que están dentro de Release.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	SELECT @CMD1 = ''
	SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'START /WAIT WinRAR a -agYYYYMMDDHHMMSS -ibck -r -x*\Logs -x*\Temp*\ -x*\Varios -x*.rar ' + char(34) + @strBackupReleaseFolder + '\BackupReleaseSextante' + char(34) + ' ' + char(34) + @strFilePath2 + '\*.*' + char(34) + char(39)
	PRINT @CMD1
	EXEC (@CMD1)

	PRINT 'Recuperando el archivo recién creado.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	SELECT @CMD1 = ''
	SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'dir ' + char(34) + @strBackupReleaseFolder + '\BackupReleaseSextante*.rar' + char(34) + ' /od' + char(39)
	PRINT @CMD1
	EXEC (@CMD1)
END

/*
************************ 7 - Borramos los archivos mas viejos de la ubicación local para los ejecutables (.rar) ************************
*/
IF @HacerBackupCaldenOil = 1
BEGIN
	-- CALDENOIL
	PRINT 'Recuperando el listado de los archivos .rar.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	-- h) Creamos la tabla temporal #OriginalFileList que contendrá el listado con todos los datos de los archivos
	CREATE TABLE #OriginalFileList3
		(
			Col1 varchar(1000) NULL
		)

	-- i) Creamos la tabla @ParsedFileList que contendrá la lista con los datos de todos los archivos del directorio de backup
	DECLARE @ParsedFileList3 TABLE
		(
			PFLID INT PRIMARY KEY IDENTITY (1,1) NOT NULL,
			DateTimeStamp varchar(50) NOT NULL,
			FileSize varchar(50) NOT NULL,
			FileName1 varchar (255) NOT NULL
		)

	-- j) Creamos la tabla @FileListToDelete que contendrá la lista de los archivos a borrar
	DECLARE @FileListToDelete3 TABLE
		(
			IdFileName1 INT PRIMARY KEY IDENTITY (1,1) NOT NULL,
			FileName1 varchar (255) NOT NULL
		)

	-- k) Inicializamos las variables para la recuperación de los nombres de los archivos
	SELECT @CMD1 = ''
	SELECT @CMD2 = '' 
	SELECT @strFilePath = SUBSTRING(@strBackupReleaseFolder, 1, LEN(@strBackupReleaseFolder))

	-- l) Construimos la cadena para capturar los nombres de los archivos existentes 
	SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'dir ' + char(34) + @strFilePath + '\BackupReleaseCaldenOil*.rar' + char(34) + char(39)
	PRINT @CMD1
	EXEC (@CMD1)

	-- m) Construimos la cadena para publicar los datos dentro de la tabla temporal #OriginalFileList
	SELECT @CMD2 = 'INSERT INTO #OriginalFileList3 (Col1)' + char(13) + 'EXEC ' + @CMD1

	-- n) Ejecutamos la cadena para publicar los datos en la tabla @OriginalFileList
	PRINT @CMD2
	EXEC (@CMD2)

	-- Para Debug:
	 --SELECT *
	 --FROM #OriginalFileList3

	-- o) Borramos los datos que no necesitamos de la tabla @OriginalFileList
	DELETE FROM #OriginalFileList3
	WHERE
		(
			Col1 IS NULL OR
			Col1 LIKE '%Volume%' OR 
			Col1 LIKE '%Directory%' OR
			Col1 LIKE '%Directorio%' OR
			Col1 LIKE '%<DIR>%' OR
			Col1 LIKE '%bytes%'
		)	

	-- p) Publicamos los datos en la tabla final @ParsedFileList
	INSERT INTO @ParsedFileList3 (DateTimeStamp, FileSize, FileName1)
	SELECT 
		LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '
		AS 'DateTimeStamp',
		LTRIM(SUBSTRING(LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1, CHARINDEX(' ', LTRIM(SUBSTRING(Col1,CHARINDEX(' ' , LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1)))
		AS 'FileSize',
		LTRIM(SUBSTRING(Col1, CHARINDEX('B', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  ') + LEN(LTRIM(SUBSTRING(LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1, CHARINDEX(' ', LTRIM(SUBSTRING(Col1, CHARINDEX(' ' , LTRIM(Col1), CHARINDEX(' ' , LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1,CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1))))), LEN(Col1)))
		AS 'FileName1'
	FROM #OriginalFileList3

	-- Para Debug:
	 SELECT *
	 FROM @ParsedFileList3

	--  q) Creamos un cursor con los nombres de las BD 
	DECLARE CUR_Databases CURSOR FOR
	SELECT 
		SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18) as strDatabase, 
		COUNT(SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18)) as Cuantos 
	FROM 
		@ParsedFileList3
	GROUP BY 
		SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18)

	OPEN CUR_Databases
	FETCH NEXT FROM CUR_Databases INTO @strDatabase2, @strCuantos
	WHILE
		@@FETCH_STATUS = 0
		BEGIN
		IF CAST(@strCuantos AS INT) > @strCantidadBackups
		-- Si la cantidad de archivos existentes es mayor que la cantidad a dejar, cargamos en una
		-- tabla temporal los nombres de los archivos para luego leerlos y borrarlos
			BEGIN
				SET @strToDelete = @strCuantos - @strCantidadBackups
				INSERT INTO @FileListToDelete3
					(FileName1)
				SELECT TOP (@strToDelete) FileName1
				FROM @ParsedFileList3
				WHERE SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18) = @strDataBase2
			END
		FETCH NEXT FROM CUR_Databases INTO @strDatabase2, @strCuantos
	END

	SELECT *
	FROM @FileListToDelete3

	CLOSE CUR_Databases
	DEALLOCATE CUR_Databases

	--  r) Finalmente borramos los archivos de las copias de seguridad mas viejas
	PRINT 'Borrando los archivos más antiguos.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	SELECT @CMD1 = ''
	SELECT @CMD2 = '' 

	DECLARE CUR_FileToDelete CURSOR FOR
	SELECT 
		RTRIM(FileName1)
	FROM 
		@FileListToDelete3

	OPEN CUR_FileToDelete
	FETCH NEXT FROM CUR_FileToDelete INTO @strFileToDelete
	WHILE
		@@FETCH_STATUS = 0
		BEGIN
			-- Chr(32) debería ser el espacio en blanco
			SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'del ' + char(34) + @strFilePath + '\'
			SELECT @CMD2 = @CMD1 + @strFileToDelete + char(34) + char(39)
			Print @CMD2
			EXEC (@CMD2)
		FETCH NEXT FROM CUR_FileToDelete INTO @strFileToDelete
	END

	CLOSE CUR_FileToDelete
	DEALLOCATE CUR_FileToDelete

	-- s) Borramos la tabla temporal
	DROP TABLE #OriginalFileList3
END

IF @HacerBackupSextante = 1
BEGIN
	-- SEXTANTE
	PRINT 'Recuperando el listado de los archivos .rar.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	-- h) Creamos la tabla temporal #OriginalFileList11 que contendrá el listado con todos los datos de los archivos
	CREATE TABLE #OriginalFileList4
		(
			Col1 varchar(1000) NULL
		)

	-- i) Creamos la tabla @ParsedFileList que contendrá la lista con los datos de todos los archivos del directorio de backup
	DECLARE @ParsedFileList4 TABLE
		(
			PFLID INT PRIMARY KEY IDENTITY (1,1) NOT NULL,
			DateTimeStamp varchar(50) NOT NULL,
			FileSize varchar(50) NOT NULL,
			FileName1 varchar (255) NOT NULL
		)

	-- j) Creamos la tabla @FileListToDelete que contendrá la lista de los archivos a borrar
	DECLARE @FileListToDelete4 TABLE
		(
			IdFileName1 INT PRIMARY KEY IDENTITY (1,1) NOT NULL,
			FileName1 varchar (255) NOT NULL
		)

	-- k) Inicializamos las variables para la recuperación de los nombres de los archivos
	SELECT @CMD1 = ''
	SELECT @CMD2 = '' 
	SELECT @strFilePath = SUBSTRING(@strBackupReleaseFolder, 1, LEN(@strBackupReleaseFolder))

	-- l) Construimos la cadena para capturar los nombres de los archivos existentes 
	SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'dir ' + char(34) + @strFilePath + '\BackupReleaseSextante*.rar' + char(34) + char(39)
	PRINT @CMD1
	EXEC (@CMD1)

	-- m) Construimos la cadena para publicar los datos dentro de la tabla temporal #OriginalFileList
	SELECT @CMD2 = 'INSERT INTO #OriginalFileList4 (Col1)' + char(13) + 'EXEC ' + @CMD1

	-- n) Ejecutamos la cadena para publicar los datos en la tabla @OriginalFileList
	PRINT @CMD2
	EXEC (@CMD2)

	-- Para Debug:
	 SELECT *
	 FROM #OriginalFileList4

	-- o) Borramos los datos que no necesitamos de la tabla @OriginalFileList
	DELETE FROM #OriginalFileList4
	WHERE
		(
			Col1 IS NULL OR
			Col1 LIKE '%Volume%' OR 
			Col1 LIKE '%Directory%' OR
			Col1 LIKE '%Directorio%' OR
			Col1 LIKE '%<DIR>%' OR
			Col1 LIKE '%bytes%'
		)	

	-- p) Publicamos los datos en la tabla final @ParsedFileList
	INSERT INTO @ParsedFileList4 (DateTimeStamp, FileSize, FileName1)
	SELECT 
		LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '
		AS 'DateTimeStamp',
		LTRIM(SUBSTRING(LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1, CHARINDEX(' ', LTRIM(SUBSTRING(Col1,CHARINDEX(' ' , LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1)))
		AS 'FileSize',
		LTRIM(SUBSTRING(Col1, CHARINDEX('B', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  ') + LEN(LTRIM(SUBSTRING(LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1, CHARINDEX(' ', LTRIM(SUBSTRING(Col1, CHARINDEX(' ' , LTRIM(Col1), CHARINDEX(' ' , LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1,CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1))))), LEN(Col1)))
		AS 'FileName1'
	FROM #OriginalFileList4

	-- Para Debug:
	 SELECT *
	 FROM @ParsedFileList4

	--  q) Creamos un cursor con los nombres de las BD 
	DECLARE CUR_Databases CURSOR FOR
	SELECT 
		SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18) as strDatabase, 
		COUNT(SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18)) as Cuantos 
	FROM 
		@ParsedFileList4
	GROUP BY 
		SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18)

	OPEN CUR_Databases
	FETCH NEXT FROM CUR_Databases INTO @strDatabase2, @strCuantos
	WHILE
		@@FETCH_STATUS = 0
		BEGIN
		IF CAST(@strCuantos AS INT) > @strCantidadBackups
		-- Si la cantidad de archivos existentes es mayor que la cantidad a dejar, cargamos en una
		-- tabla temporal los nombres de los archivos para luego leerlos y borrarlos
			BEGIN
				SET @strToDelete = @strCuantos - @strCantidadBackups
				INSERT INTO @FileListToDelete4
					(FileName1)
				SELECT TOP (@strToDelete) FileName1
				FROM @ParsedFileList4
				WHERE SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18) = @strDataBase2
			END
		FETCH NEXT FROM CUR_Databases INTO @strDatabase2, @strCuantos
	END

	SELECT *
	FROM @FileListToDelete4

	CLOSE CUR_Databases
	DEALLOCATE CUR_Databases

	--  r) Finalmente borramos los archivos de las copias de seguridad mas viejas
	PRINT 'Borrando los archivos más antiguos.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	SELECT @CMD1 = ''
	SELECT @CMD2 = '' 

	DECLARE CUR_FileToDelete CURSOR FOR
	SELECT 
		RTRIM(FileName1)
	FROM 
		@FileListToDelete4

	OPEN CUR_FileToDelete
	FETCH NEXT FROM CUR_FileToDelete INTO @strFileToDelete
	WHILE
		@@FETCH_STATUS = 0
		BEGIN
			-- Chr(32) debería ser el espacio en blanco
			SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'del ' + char(34) + @strFilePath + '\'
			SELECT @CMD2 = @CMD1 + @strFileToDelete + char(34) + char(39)
			Print @CMD2
			EXEC (@CMD2)
		FETCH NEXT FROM CUR_FileToDelete INTO @strFileToDelete
	END

	CLOSE CUR_FileToDelete
	DEALLOCATE CUR_FileToDelete

	-- s) Borramos la tabla temporal
	DROP TABLE #OriginalFileList4
END

PRINT 'Finalizada la copia de seguridad en el directorio local: ' + @strBackupFolder + '. Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)

--/*
--************************ 8 - Movemos los archivos a la ubicación de red y eliminamos los más viejos ************************
--*/
IF @MoverCopiasADiscoExterno = 1 AND (@HacerBackupCaldenOil = 1 OR @HacerBackupSextante = 1)
BEGIN
	-- t) Copiamos las copias de seguridad a la ubicación de red
	PRINT 'Copiando los archivos a la ubicación de red.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	SELECT @strFilePath = SUBSTRING(@strBackupReleaseFolder, 1, LEN(@strBackupReleaseFolder))
	SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'xcopy ' + char(34) + @strFilePath + '\*.rar' + char(34) + ' ' + char(34) + @strBackupReleaseFolder2 + char(34) + ' /D /Y' + char(39)
	PRINT @CMD1
	EXEC (@CMD1)
END

IF @MoverCopiasADiscoExterno = 1 AND @HacerBackupCaldenOil = 1
BEGIN
	-- u) Eliminamos los más viejos (repetimos el proceso anterior pero para el directorio de red
	-- CALDENOIL
	--		Creamos la tabla temporal #OriginalFileList que contendrá el listado con todos los datos de los archivos
	PRINT 'Recuperando el listado de los archivos .rar.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	CREATE TABLE #OriginalFileList5
		(
			Col1 varchar(1000) NULL
		)

	-- v) Creamos la tabla @ParsedFileList que contendrá la lista con los datos de todos los archivos del directorio de backup
	DECLARE @ParsedFileList5 TABLE
		(
			PFLID INT PRIMARY KEY IDENTITY (1,1) NOT NULL,
			DateTimeStamp varchar(50) NOT NULL,
			FileSize varchar(50) NOT NULL,
			FileName1 varchar (255) NOT NULL
		)

	-- w) Creamos la tabla @FileListToDelete que contendrá la lista de los archivos a borrar
	DECLARE @FileListToDelete5 TABLE
		(
			IdFileName1 INT PRIMARY KEY IDENTITY (1,1) NOT NULL,
			FileName1 varchar (255) NOT NULL
		)

	-- x) Inicializamos las variables para la recuperación de los nombres de los archivos
	SELECT @CMD1 = ''
	SELECT @CMD2 = '' 
	SELECT @strFilePath = SUBSTRING(@strBackupReleaseFolder2, 1, LEN(@strBackupReleaseFolder2))

	-- y) Construimos la cadena para capturar los nombres de los archivos existentes
	SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'dir ' + char(34) + @strFilePath + '\BackupReleaseCaldenOil*.rar' + char(34) + char(39)
	PRINT @CMD1
	EXEC (@CMD1)

	-- z) Construimos la cadena para publicar los datos dentro de la tabla temporal #OriginalFileList2
	SELECT @CMD2 = 'INSERT INTO #OriginalFileList5 (Col1)' + char(13) + 'EXEC ' + @CMD1

	-- a) Ejecutamos la cadena para publicar los datos en la tabla @OriginalFileList
	PRINT @CMD2
	EXEC (@CMD2)

	-- Para Debug:
	-- SELECT *
	-- FROM #OriginalFileList5

	-- b) Borramos los datos que no necesitamos de la tabla @OriginalFileList
	DELETE FROM #OriginalFileList5
	WHERE
		(
			Col1 IS NULL OR
			Col1 LIKE '%Volume%' OR 
			Col1 LIKE '%Directory%' OR
			Col1 LIKE '%Directorio%' OR
			Col1 LIKE '%<DIR>%' OR
			Col1 LIKE '%bytes%'
		)	

	-- c) Publicamos los datos en la tabla final @ParsedFileList
	INSERT INTO @ParsedFileList5 (DateTimeStamp, FileSize, FileName1)
	SELECT 
		LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '
		AS 'DateTimeStamp',
		LTRIM(SUBSTRING(LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1, CHARINDEX(' ', LTRIM(SUBSTRING(Col1,CHARINDEX(' ' , LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1)))
		AS 'FileSize',
		LTRIM(SUBSTRING(Col1, CHARINDEX('B', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  ') + LEN(LTRIM(SUBSTRING(LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1, CHARINDEX(' ', LTRIM(SUBSTRING(Col1, CHARINDEX(' ' , LTRIM(Col1), CHARINDEX(' ' , LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1,CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1))))), LEN(Col1)))
		AS 'FileName1'
	FROM #OriginalFileList5

	-- Para Debug:
	 SELECT *
	 FROM @ParsedFileList5

	--  d) Creamos un cursor con los nombres de las BD 
	DECLARE CUR_Databases CURSOR FOR
	SELECT 
		SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18) as strDatabase, 
		COUNT(SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18)) as Cuantos 
	FROM 
		@ParsedFileList5
	GROUP BY 
		SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18)

	OPEN CUR_Databases
	FETCH NEXT FROM CUR_Databases INTO @strDatabase2, @strCuantos
	WHILE
		@@FETCH_STATUS = 0
		BEGIN
		IF CAST(@strCuantos AS INT) > @strCantidadBackups
		-- Si la cantidad de archivos existentes es mayor que la cantidad a dejar, cargamos en una
		-- tabla temporal los nombres de los archivos para luego leerlos y borrarlos
			BEGIN
				SET @strToDelete = @strCuantos - @strCantidadBackups
				INSERT INTO @FileListToDelete5
					(FileName1)
				SELECT TOP (@strToDelete) FileName1
				FROM @ParsedFileList5
				WHERE SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18) = @strDataBase2
			END
		FETCH NEXT FROM CUR_Databases INTO @strDatabase2, @strCuantos
	END

	SELECT *
	FROM @FileListToDelete5

	CLOSE CUR_Databases
	DEALLOCATE CUR_Databases

	--  e) Finalmente borramos los archivos de las copias de seguridad mas viejas
	PRINT 'Borrando los archivos más antiguos.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	SELECT @CMD1 = ''
	SELECT @CMD2 = '' 

	DECLARE CUR_FileToDelete CURSOR FOR
	SELECT 
		RTRIM(FileName1)
	FROM 
		@FileListToDelete5

	OPEN CUR_FileToDelete
	FETCH NEXT FROM CUR_FileToDelete INTO @strFileToDelete
	WHILE
		@@FETCH_STATUS = 0
		BEGIN
			-- Chr(32) debería ser el espacio en blanco
			SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'del ' + char(34) + @strFilePath + '\'
			SELECT @CMD2 = @CMD1 + @strFileToDelete + char(34) + char(39)
			Print @CMD2
			EXEC (@CMD2)
		FETCH NEXT FROM CUR_FileToDelete INTO @strFileToDelete
	END

	CLOSE CUR_FileToDelete
	DEALLOCATE CUR_FileToDelete

	PRINT 'Finalización del borrado.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)

	-- f) Borramos la tabla temporal
	DROP TABLE #OriginalFileList5
END

IF @MoverCopiasADiscoExterno = 1 AND @HacerBackupSextante = 1
BEGIN
	-- SEXTANTE
	--		Creamos la tabla temporal #OriginalFileList que contendrá el listado con todos los datos de los archivos
	PRINT 'Recuperando el listado de los archivos .rar.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	CREATE TABLE #OriginalFileList6
		(
			Col1 varchar(1000) NULL
		)

	-- v) Creamos la tabla @ParsedFileList que contendrá la lista con los datos de todos los archivos del directorio de backup
	DECLARE @ParsedFileList6 TABLE
		(
			PFLID INT PRIMARY KEY IDENTITY (1,1) NOT NULL,
			DateTimeStamp varchar(50) NOT NULL,
			FileSize varchar(50) NOT NULL,
			FileName1 varchar (255) NOT NULL
		)

	-- w) Creamos la tabla @FileListToDelete que contendrá la lista de los archivos a borrar
	DECLARE @FileListToDelete6 TABLE
		(
			IdFileName1 INT PRIMARY KEY IDENTITY (1,1) NOT NULL,
			FileName1 varchar (255) NOT NULL
		)

	-- x) Inicializamos las variables para la recuperación de los nombres de los archivos
	SELECT @CMD1 = ''
	SELECT @CMD2 = '' 
	SELECT @strFilePath = SUBSTRING(@strBackupReleaseFolder2, 1, LEN(@strBackupReleaseFolder2))

	-- y) Construimos la cadena para capturar los nombres de los archivos existentes
	SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'dir ' + char(34) + @strFilePath + '\BackupReleaseSextante*.rar' + char(34) + char(39)
	PRINT @CMD1
	EXEC (@CMD1)

	-- z) Construimos la cadena para publicar los datos dentro de la tabla temporal #OriginalFileList2
	SELECT @CMD2 = 'INSERT INTO #OriginalFileList6 (Col1)' + char(13) + 'EXEC ' + @CMD1

	-- a) Ejecutamos la cadena para publicar los datos en la tabla @OriginalFileList
	PRINT @CMD2
	EXEC (@CMD2)

	-- Para Debug:
	-- SELECT *
	-- FROM #OriginalFileList6

	-- b) Borramos los datos que no necesitamos de la tabla @OriginalFileList
	DELETE FROM #OriginalFileList6
	WHERE
		(
			Col1 IS NULL OR
			Col1 LIKE '%Volume%' OR 
			Col1 LIKE '%Directory%' OR
			Col1 LIKE '%Directorio%' OR
			Col1 LIKE '%<DIR>%' OR
			Col1 LIKE '%bytes%'
		)	

	-- c) Publicamos los datos en la tabla final @ParsedFileList
	INSERT INTO @ParsedFileList6 (DateTimeStamp, FileSize, FileName1)
	SELECT 
		LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '
		AS 'DateTimeStamp',
		LTRIM(SUBSTRING(LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1, CHARINDEX(' ', LTRIM(SUBSTRING(Col1,CHARINDEX(' ' , LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1)))
		AS 'FileSize',
		LTRIM(SUBSTRING(Col1, CHARINDEX('B', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  ') + LEN(LTRIM(SUBSTRING(LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1, CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1, CHARINDEX(' ', LTRIM(SUBSTRING(Col1, CHARINDEX(' ' , LTRIM(Col1), CHARINDEX(' ' , LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10)) + '  ' + LTRIM(SUBSTRING(Col1,CHARINDEX(' ', LTRIM(Col1), LEN(LTRIM(SUBSTRING(Col1, 1, 10))) + 2), 11)) + '  '))), LEN(Col1))), 1))))), LEN(Col1)))
		AS 'FileName1'
	FROM #OriginalFileList6

	-- Para Debug:
	 SELECT *
	 FROM @ParsedFileList6

	--  d) Creamos un cursor con los nombres de las BD 
	DECLARE CUR_Databases CURSOR FOR
	SELECT 
		SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18) as strDatabase, 
		COUNT(SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18)) as Cuantos 
	FROM 
		@ParsedFileList6
	GROUP BY 
		SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18)

	OPEN CUR_Databases
	FETCH NEXT FROM CUR_Databases INTO @strDatabase2, @strCuantos
	WHILE
		@@FETCH_STATUS = 0
		BEGIN
		IF CAST(@strCuantos AS INT) > @strCantidadBackups
		-- Si la cantidad de archivos existentes es mayor que la cantidad a dejar, cargamos en una
		-- tabla temporal los nombres de los archivos para luego leerlos y borrarlos
			BEGIN
				SET @strToDelete = @strCuantos - @strCantidadBackups
				INSERT INTO @FileListToDelete6
					(FileName1)
				SELECT TOP (@strToDelete) FileName1
				FROM @ParsedFileList6
				WHERE SUBSTRING(RTRIM(FileName1), 1, LEN(FileName1) - 18) = @strDataBase2
			END
		FETCH NEXT FROM CUR_Databases INTO @strDatabase2, @strCuantos
	END

	SELECT *
	FROM @FileListToDelete6

	CLOSE CUR_Databases
	DEALLOCATE CUR_Databases

	--  e) Finalmente borramos los archivos de las copias de seguridad mas viejas
	PRINT 'Borrando los archivos más antiguos.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
	SELECT @CMD1 = ''
	SELECT @CMD2 = '' 

	DECLARE CUR_FileToDelete CURSOR FOR
	SELECT 
		RTRIM(FileName1)
	FROM 
		@FileListToDelete6

	OPEN CUR_FileToDelete
	FETCH NEXT FROM CUR_FileToDelete INTO @strFileToDelete
	WHILE
		@@FETCH_STATUS = 0
		BEGIN
			-- Chr(32) debería ser el espacio en blanco
			SELECT @CMD1 = 'master.dbo.xp_cmdshell ' + char(39) + 'del ' + char(34) + @strFilePath + '\'
			SELECT @CMD2 = @CMD1 + @strFileToDelete + char(34) + char(39)
			Print @CMD2
			EXEC (@CMD2)
		FETCH NEXT FROM CUR_FileToDelete INTO @strFileToDelete
	END

	CLOSE CUR_FileToDelete
	DEALLOCATE CUR_FileToDelete

	-- f) Borramos la tabla temporal
	DROP TABLE #OriginalFileList6

	PRINT 'Finalización del borrado.' + ' Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
END

PRINT 'BACKUP FINALIZADO. Fecha Actual: ' + CONVERT(VarChar, GETDATE(), 113)
GO