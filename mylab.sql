-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 08-Maio-2022 às 19:51
-- Versão do servidor: 10.4.22-MariaDB
-- versão do PHP: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `mylab`
--

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AlterarCultura` (IN `IdCultura` INT(11), IN `NomeCultura` VARCHAR(50), IN `MinutosRealerta` INT(11), IN `Ativo` TINYINT(1))  BEGIN
DECLARE sqlQuery VARCHAR(250);
DECLARE idCult INT(11);

    SET idCult = (SELECT cultura.IdCultura FROM cultura,utilizador WHERE cultura.IdUtilizador=utilizador.IdUtilizador AND utilizador.Username=SUBSTRING_INDEX(USER(), '@', 1) AND cultura.IdCultura=IdCultura);
	SET sqlQuery = (SELECT "UPDATE cultura SET ");
    IF idCult IS NOT NULL THEN

    	IF NOT NomeCultura='' THEN
			SET sqlQuery = CONCAT(sqlQuery, CONCAT("NomeCultura=""", CONCAT(NomeCultura, """, ")));
		END IF;
		IF NOT MinutosRealerta='' THEN
			SET sqlQuery = CONCAT(sqlQuery, CONCAT("cultura.MinutosRealerta=", CONCAT(MinutosRealerta, ", ")));
		END IF;
		IF NOT Ativo='' THEN
			SET sqlQuery = CONCAT(sqlQuery, CONCAT("cultura.Ativo=", CONCAT(Ativo, ", ")));
		END IF;
		SET sqlQuery = TRIM(TRAILING ', ' FROM sqlQuery);
		SET sqlQuery = CONCAT(sqlQuery, CONCAT(" WHERE IdCultura=", CONCAT(IdCultura,";")));
		PREPARE stmt FROM sqlQuery;
		EXECUTE stmt;
    ELSE
    	SELECT 'ERRO';
	END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AlterarParametroCultura` (IN `idCultura` INT(11), IN `codPar` VARCHAR(1), IN `1Min` DECIMAL(5,2), IN `1Min_Urgente` DECIMAL(5,2), IN `1Min_Aviso` DECIMAL(5,2), IN `1Max` DECIMAL(5,2), IN `1Max_Urgente` DECIMAL(5,2), IN `1Max_Aviso` DECIMAL(5,2))  BEGIN
    DECLARE idCult INT(11);
    SET idCult = (SELECT cultura.IdCultura FROM cultura,utilizador WHERE cultura.IdUtilizador=utilizador.IdUtilizador AND utilizador.Username=SUBSTRING_INDEX(USER(), '@', 1) AND cultura.IdCultura=idCultura);
    IF idCult IS NOT NULL THEN
        UPDATE `parametrocultura` SET `Min`=1Min, `Min_Urgente`=1Min_Urgente, `Min_Aviso`=1Min_Aviso, `Max`=1Max, `Max_Urgente`=1Max_Urgente, `Max_Aviso`=1Max_Aviso WHERE parametrocultura.IdCultura=idCult AND `CodigoParametro`=codPar;
        SELECT 'Sucesso';
    ELSE
    	SELECT 'ERRO';
	END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AtribuirCulturaInvestigador` (IN `IdCultura` INT(11), IN `IdUtilizador` INT(11))  BEGIN
DECLARE tipoUt VARCHAR(15);
IF (SELECT TipoUtilizador FROM utilizador WHERE utilizador.IdUtilizador=IdUtilizador)='Investigador' THEN
 UPDATE cultura SET cultura.IdUtilizador=IdUtilizador WHERE cultura.IdCultura=IdCultura;
ELSE
 SELECT 'Utilizador não é um Investigador';
END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CriarAlerta` (IN `idCult` INT(11), IN `idMed` INT(11), IN `aviso` TINYINT(1), IN `sens` VARCHAR(2), IN `msg` VARCHAR(50))  BEGIN
	DECLARE warnCount INT(15);
    DECLARE mins INT(15);
    SET mins = (SELECT cultura.MinutosRealerta FROM cultura WHERE cultura.IdCultura=idCult);
    IF aviso < 0 THEN
    	SET warnCount = (SELECT COUNT(*) FROM alerta WHERE alerta.IdCultura=idCult AND alerta.Sensor like sens AND alerta.NivelAlerta<=aviso AND (TIMESTAMPDIFF(MINUTE,alerta.HoraEscrita,NOW())<mins));
    ELSE
    	SET warnCount = (SELECT COUNT(*) FROM alerta WHERE alerta.IdCultura=idCult AND alerta.Sensor like sens AND alerta.NivelAlerta>=aviso AND (TIMESTAMPDIFF(MINUTE,alerta.HoraEscrita,NOW())<mins));
    END IF;
    IF warnCount = 0 THEN
		INSERT INTO alerta (SELECT NULL,IdMedicao,medicao.Zona,Sensor,DataHoraObjectId,Leitura,aviso,NomeCultura,msg,IdUtilizador,IdCultura,current_timestamp() FROM medicao,cultura WHERE medicao.Zona=cultura.Zona AND medicao.IdMedicao=idMed AND cultura.IdCultura=idCult);
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CriarCultura` (IN `NomeCultura` VARCHAR(50), IN `IdUtilizador` INT(11), IN `Ativo` TINYINT(1), IN `Zona` INT(11))  BEGIN
insert into cultura (NomeCultura, idUtilizador, Ativo, Zona, MinutosRealerta) values (NomeCultura, IdUtilizador, Ativo, Zona,3);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CriarMedicao` (IN `Zona` INT, IN `Sensor` VARCHAR(2), IN `DataHora` DATETIME, IN `DataHoraObjectId` DATETIME, IN `Leitura` DECIMAL(5,2), IN `Invalido` TINYINT(1), IN `Excluido` TINYINT(1), IN `Json` LONGTEXT)  BEGIN

insert into medicao (Zona, Sensor, DataHora, Leitura, DataHoraObjectId, Invalido, Excluido, Json) values (Zona, Sensor, DataHora, Leitura, DataHoraObjectId, Invalido, Excluido, Json);

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CriarParametroCultura` (IN `idCultura` INT(11), IN `codPar` VARCHAR(1), IN `1Min` DECIMAL(5,2), IN `1Min_Urgente` DECIMAL(5,2), IN `1Min_Aviso` DECIMAL(5,2), IN `1Max` DECIMAL(5,2), IN `1Max_Urgente` DECIMAL(5,2), IN `1Max_Aviso` DECIMAL(5,2))  BEGIN
    DECLARE idCult INT(11);
    SET idCult = (SELECT cultura.IdCultura FROM cultura,utilizador WHERE cultura.IdUtilizador=utilizador.IdUtilizador AND utilizador.Username=SUBSTRING_INDEX(USER(), '@', 1) AND cultura.IdCultura=idCultura);
    IF idCult IS NOT NULL THEN
    	INSERT INTO `parametrocultura` (`CodigoParametro`, `IdCultura`, `Min`, `Min_Urgente`, `Min_Aviso`, `Max`, `Max_Urgente`, `Max_Aviso`) VALUES (codPar, idCult, 1Min, 1Min_Urgente, 1Min_Aviso, 1Max, 1Max_Urgente, 1Max_Aviso);
        SELECT 'Sucesso';
    ELSE
    	SELECT 'ERRO';
	END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CriarUtilizador` (IN `UserName` VARCHAR(20), IN `Password` VARCHAR(30), IN `Nome` VARCHAR(100), IN `Email` VARCHAR(50), IN `TipoUtilizador` VARCHAR(15))  BEGIN
	DECLARE `_HOST` CHAR(14) DEFAULT '@''localhost''';
	IF EXISTS (SELECT * FROM tipoutilizador WHERE TipoUtilizador like `TipoUtilizador`) then
       	SET `UserName` := REPLACE(TRIM(`UserName`), CHAR(39), CONCAT(CHAR(92), CHAR(39))), `Password` := REPLACE(`Password`, CHAR(39), CONCAT(CHAR(92), CHAR(39)));
		SET @`sql` := CONCAT('CREATE USER ', `UserName`, `_HOST`, ' IDENTIFIED BY ''', `Password`,'''');
		PREPARE `stmt` FROM @`sql`;
		EXECUTE `stmt`;
		SET @`sql` := CONCAT('GRANT ', `TipoUtilizador`, ' TO ', `UserName`,'@\'localhost\';');
		PREPARE `stmt` FROM @`sql`;
		EXECUTE `stmt`;
		FLUSH PRIVILEGES;

        SET @`sql` := CONCAT('SET DEFAULT ROLE ', `TipoUtilizador`, ' FOR ', `UserName`,'@\'localhost\';');
		PREPARE `stmt` FROM @`sql`;
		EXECUTE `stmt`;
        DEALLOCATE PREPARE `stmt`;
        FLUSH PRIVILEGES;


		insert into utilizador (Nome, Username, Email, TipoUtilizador) values (Nome, UserName, Email, TipoUtilizador);
		SELECT MAX(IdUtilizador) as Id FROM utilizador;
    end if;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObterIdUtilizador` (IN `Username` VARCHAR(20))  BEGIN
	select IdUtilizador from utilizador where utilizador.Username like Username;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RemoverCultura` (IN `IdCultura` INT(11))  BEGIN
DELETE FROM cultura WHERE IdCultura = IdCultura;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RemoverParametroCultura` (IN `id` INT(11), IN `codigoParam` VARCHAR(1))  BEGIN
    DECLARE idCult INT(11);
    SET idCult = (SELECT cultura.IdCultura FROM cultura,utilizador WHERE cultura.IdUtilizador=utilizador.IdUtilizador AND utilizador.Username=SUBSTRING_INDEX(USER(), '@', 1) AND cultura.IdCultura=id);
    IF idCult IS NOT NULL THEN
		delete from parametrocultura where CodigoParametro like `codigoParam` and IdCultura = `id`;
        SELECT 'Sucesso';
    ELSE
    	SELECT 'ERRO';
	END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `VerificarParametros` (IN `idCult` INT(11), IN `idMed` INT(11), IN `leit` DECIMAL(5,2), IN `sens` VARCHAR(2))  BEGIN
DECLARE cMax decimal(5,2); DECLARE cMax_U decimal(5,2); DECLARE cMax_A decimal(5,2); DECLARE cMin decimal(5,2); DECLARE cMin_U decimal(5,2); DECLARE cMin_A decimal(5,2); DECLARE aviso TINYINT(1);
Declare msg Varchar(50);

set msg = Concat("O sensor ",sens, " ultrapassou um limite ");

SELECT parametrocultura.Max, parametrocultura.Max_Urgente, parametrocultura.Max_Aviso, parametrocultura.Min, parametrocultura.Min_Urgente, parametrocultura.Min_Aviso
INTO cMax, cMax_U, cMax_A, cMin, cMin_U, cMin_A FROM parametrocultura WHERE parametrocultura.IdCultura=idCult AND parametrocultura.CodigoParametro=LEFT(sens, 1);

IF leit >= cMax THEN
	SET aviso = 3;
    set msg = Concat(msg, "superior, alerta emergente.");
ELSEIF leit >= cMax_U THEN
	SET aviso = 2;
    set msg = Concat(msg, "superior, alerta urgente.");
ELSEIF leit >= cMax_A THEN
	SET aviso = 1;
    set msg = Concat(msg, "superior, alerta de aviso.");
ELSEIF leit <= cMin THEN
	SET aviso = -3;
    set msg = Concat(msg, "inferior, alerta emergente.");
ELSEIF leit <= cMin_U THEN
	SET aviso = -2;
    set msg = Concat(msg, "inferior, alerta urgente.");
ELSEIF leit <= cMin_A THEN
	SET aviso = -1;
    set msg = Concat(msg, "inferior, alerta de aviso.");
END IF;


IF aviso IS NOT NULL THEN
	CALL CriarAlerta(idCult,idMed,aviso,sens,msg);
END IF;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `alarmeadministrador`
--

CREATE TABLE `alarmeadministrador` (
  `idAlarme` int(11) NOT NULL,
  `Mensagem` varchar(100) NOT NULL,
  `Data_Hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `DataHoraObjectId` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `alerta`
--

CREATE TABLE `alerta` (
  `IdAlerta` int(11) NOT NULL,
  `idMedicao` int(11) DEFAULT NULL,
  `Zona` int(11) NOT NULL,
  `Sensor` varchar(2) NOT NULL,
  `Hora` datetime NOT NULL,
  `Leitura` decimal(5,2) NOT NULL,
  `NivelAlerta` varchar(50) NOT NULL,
  `NomeCultura` varchar(50) NOT NULL,
  `Mensagem` varchar(50) NOT NULL,
  `IdUtilizador` int(11) DEFAULT NULL,
  `IdCultura` int(11) NOT NULL,
  `HoraEscrita` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `alerta`
--

INSERT INTO `alerta` (`IdAlerta`, `idMedicao`, `Zona`, `Sensor`, `Hora`, `Leitura`, `NivelAlerta`, `NomeCultura`, `Mensagem`, `IdUtilizador`, `IdCultura`, `HoraEscrita`) VALUES
(5499, 173621, 1, 'T1', '2022-05-08 18:45:04', '14.00', '3', 'cultura webuserA', 'O sensor T1 ultrapassou um limite superior, alerta', 1, 5, '2022-05-08 18:45:04'),
(5500, 173621, 1, 'T1', '2022-05-08 18:45:04', '14.00', '-1', 'Teste', 'O sensor T1 ultrapassou um limite inferior, alerta', 3, 8, '2022-05-08 18:45:04');

-- --------------------------------------------------------

--
-- Estrutura da tabela `cultura`
--

CREATE TABLE `cultura` (
  `IdCultura` int(11) NOT NULL,
  `NomeCultura` varchar(50) NOT NULL,
  `IdUtilizador` int(11) DEFAULT NULL,
  `Ativo` tinyint(1) NOT NULL,
  `Zona` int(11) NOT NULL,
  `MinutosRealerta` int(11) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `cultura`
--

INSERT INTO `cultura` (`IdCultura`, `NomeCultura`, `IdUtilizador`, `Ativo`, `Zona`, `MinutosRealerta`) VALUES
(2, 'cult1', 3, 1, 1, 1),
(4, 'cultura2', 2, 0, 743, 1),
(5, 'cultura webuserA', 1, 1, 1, 1),
(6, 'Cultura 2 webuserA', 1, 1, 1, 1),
(7, 'Cultura webuserB', 4, 1, 1, 1),
(8, 'Teste', 3, 1, 1, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `medicao`
--

CREATE TABLE `medicao` (
  `IdMedicao` int(11) NOT NULL,
  `Zona` int(11) DEFAULT NULL,
  `Sensor` varchar(2) DEFAULT NULL,
  `DataHora` datetime DEFAULT NULL,
  `Leitura` decimal(5,2) DEFAULT NULL,
  `DataHoraObjectId` datetime NOT NULL DEFAULT current_timestamp(),
  `Invalido` tinyint(1) NOT NULL DEFAULT 0,
  `Excluido` tinyint(1) NOT NULL DEFAULT 0,
  `Json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`Json`)),
  `DatHoraRegistoMySQL` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `medicao`
--

INSERT INTO `medicao` (`IdMedicao`, `Zona`, `Sensor`, `DataHora`, `Leitura`, `DataHoraObjectId`, `Invalido`, `Excluido`, `Json`, `DatHoraRegistoMySQL`) VALUES
(173621, 1, 'T1', '2022-05-08 19:44:12', '14.00', '2022-05-08 18:45:04', 0, 0, NULL, '2022-05-08 18:45:04');

--
-- Acionadores `medicao`
--
DELIMITER $$
CREATE TRIGGER `VerificarExcluidosSeguidos` AFTER INSERT ON `medicao` FOR EACH ROW BEGIN
DECLARE numExc INT(11);
IF NEW.Excluido THEN
 SET numExc = (SELECT variaveiscontrolo.MaxExcluidos FROM variaveiscontrolo ORDER BY variaveiscontrolo.IdVControlo LIMIT 1);
 IF (SELECT MIN(med.Excluido) FROM (SELECT * FROM medicao WHERE medicao.Sensor=NEW.Sensor ORDER BY DataHoraObjectId DESC LIMIT numExc) med)=1 THEN
 	 INSERT INTO alarmeAdministrador(Mensagem, DataHoraObjectId) VALUES (CONCAT("Sensor ", NEW.Sensor, " está com anomalias!"), NEW.DataHoraObjectId);
 END IF;
END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `VerificarMedicoes` AFTER INSERT ON `medicao` FOR EACH ROW BEGIN
DECLARE done INT DEFAULT FALSE;
DECLARE vCult INT(11);
DECLARE cur CURSOR FOR SELECT IdCultura FROM medicao,cultura WHERE medicao.Zona=cultura.Zona AND medicao.IdMedicao=NEW.IdMedicao AND medicao.Excluido=0;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
OPEN cur;
ins_loop: LOOP


    FETCH cur INTO vCult;
	IF done THEN
    	LEAVE ins_loop;
    END IF;


    CALL VerificarParametros(vCult, NEW.IdMedicao, NEW.Leitura, NEW.Sensor);

    END LOOP ins_loop;
    CLOSE cur;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `parametrocultura`
--

CREATE TABLE `parametrocultura` (
  `CodigoParametro` varchar(1) NOT NULL,
  `IdCultura` int(11) NOT NULL,
  `Min` decimal(5,2) DEFAULT NULL,
  `Min_Urgente` decimal(5,2) DEFAULT NULL,
  `Min_Aviso` decimal(5,2) DEFAULT NULL,
  `Max` decimal(5,2) DEFAULT NULL,
  `Max_Urgente` decimal(5,2) DEFAULT NULL,
  `Max_Aviso` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `parametrocultura`
--

INSERT INTO `parametrocultura` (`CodigoParametro`, `IdCultura`, `Min`, `Min_Urgente`, `Min_Aviso`, `Max`, `Max_Urgente`, `Max_Aviso`) VALUES
('H', 2, '1.00', '2.00', '3.00', '10.00', '9.00', '8.00'),
('H', 4, '2.00', '3.00', '2.00', '3.00', NULL, NULL),
('H', 5, '2.00', '3.00', '2.00', '3.00', NULL, NULL),
('H', 6, '2.00', '3.00', '2.00', '3.00', NULL, '6.00'),
('H', 8, '4.00', NULL, NULL, '50.00', '49.00', NULL),
('L', 8, '0.00', NULL, NULL, '100.00', NULL, NULL),
('T', 5, '1.00', '1.00', '2.00', '1.00', '4.00', '34.00'),
('T', 8, '10.00', '12.00', '16.00', '30.00', '28.00', '25.00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipoparametro`
--

CREATE TABLE `tipoparametro` (
  `CodigoParametro` varchar(1) NOT NULL,
  `UnidadeMedida` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `tipoparametro`
--

INSERT INTO `tipoparametro` (`CodigoParametro`, `UnidadeMedida`) VALUES
('H', '%'),
('L', 'Lumens'),
('T', 'Celsius');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipoutilizador`
--

CREATE TABLE `tipoutilizador` (
  `TipoUtilizador` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `tipoutilizador`
--

INSERT INTO `tipoutilizador` (`TipoUtilizador`) VALUES
('Administrador'),
('Investigador'),
('Migrador');

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizador`
--

CREATE TABLE `utilizador` (
  `IdUtilizador` int(11) NOT NULL,
  `Nome` varchar(100) NOT NULL,
  `Username` varchar(20) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `TipoUtilizador` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `utilizador`
--

INSERT INTO `utilizador` (`IdUtilizador`, `Nome`, `Username`, `Email`, `TipoUtilizador`) VALUES
(1, 'ugoncalo', 'ugoncalo', 'gonc@gonc.com', 'Investigador'),
(2, 'pedro', 'upedro3', 'pedro@pedro.com', 'Investigador'),
(3, 'webuserA', 'webuserA', 'webuserA@webuserA.com', 'Investigador'),
(4, 'webuserB', 'webuserB', 'webuserB@webuserB.com', 'Investigador'),
(8, 'DataMigrator', 'DataMigrator', 'DataMigrator@DataMigrator.com', 'Migrador');

-- --------------------------------------------------------

--
-- Estrutura da tabela `variaveiscontrolo`
--

CREATE TABLE `variaveiscontrolo` (
  `IdVControlo` int(11) NOT NULL,
  `MaxExcluidos` int(11) NOT NULL,
  `TaxaFalhaSensor` decimal(3,2) NOT NULL,
  `NumeroMinMedicoes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `alarmeadministrador`
--
ALTER TABLE `alarmeadministrador`
  ADD PRIMARY KEY (`idAlarme`);

--
-- Índices para tabela `alerta`
--
ALTER TABLE `alerta`
  ADD PRIMARY KEY (`IdAlerta`),
  ADD KEY `alerta-cultura` (`IdCultura`),
  ADD KEY `alerta-medicao` (`idMedicao`),
  ADD KEY `alerta-utilizador` (`IdUtilizador`);

--
-- Índices para tabela `cultura`
--
ALTER TABLE `cultura`
  ADD PRIMARY KEY (`IdCultura`),
  ADD KEY `cultura-utilizador` (`IdUtilizador`);

--
-- Índices para tabela `medicao`
--
ALTER TABLE `medicao`
  ADD PRIMARY KEY (`IdMedicao`);

--
-- Índices para tabela `parametrocultura`
--
ALTER TABLE `parametrocultura`
  ADD PRIMARY KEY (`CodigoParametro`,`IdCultura`),
  ADD KEY `paramCult-cultura` (`IdCultura`);

--
-- Índices para tabela `tipoparametro`
--
ALTER TABLE `tipoparametro`
  ADD PRIMARY KEY (`CodigoParametro`);

--
-- Índices para tabela `tipoutilizador`
--
ALTER TABLE `tipoutilizador`
  ADD PRIMARY KEY (`TipoUtilizador`);

--
-- Índices para tabela `utilizador`
--
ALTER TABLE `utilizador`
  ADD PRIMARY KEY (`IdUtilizador`),
  ADD KEY `utilizador-tipo` (`TipoUtilizador`);

--
-- Índices para tabela `variaveiscontrolo`
--
ALTER TABLE `variaveiscontrolo`
  ADD PRIMARY KEY (`IdVControlo`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alarmeadministrador`
--
ALTER TABLE `alarmeadministrador`
  MODIFY `idAlarme` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `alerta`
--
ALTER TABLE `alerta`
  MODIFY `IdAlerta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5501;

--
-- AUTO_INCREMENT de tabela `cultura`
--
ALTER TABLE `cultura`
  MODIFY `IdCultura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `medicao`
--
ALTER TABLE `medicao`
  MODIFY `IdMedicao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173622;

--
-- AUTO_INCREMENT de tabela `utilizador`
--
ALTER TABLE `utilizador`
  MODIFY `IdUtilizador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `variaveiscontrolo`
--
ALTER TABLE `variaveiscontrolo`
  MODIFY `IdVControlo` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `alerta`
--
ALTER TABLE `alerta`
  ADD CONSTRAINT `alerta-cultura` FOREIGN KEY (`IdCultura`) REFERENCES `cultura` (`IdCultura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `alerta-medicao` FOREIGN KEY (`idMedicao`) REFERENCES `medicao` (`IdMedicao`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `alerta-utilizador` FOREIGN KEY (`IdUtilizador`) REFERENCES `utilizador` (`IdUtilizador`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `cultura`
--
ALTER TABLE `cultura`
  ADD CONSTRAINT `cultura-utilizador` FOREIGN KEY (`IdUtilizador`) REFERENCES `utilizador` (`IdUtilizador`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `parametrocultura`
--
ALTER TABLE `parametrocultura`
  ADD CONSTRAINT `paramCult-cultura` FOREIGN KEY (`IdCultura`) REFERENCES `cultura` (`IdCultura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `paramCult-parametro` FOREIGN KEY (`CodigoParametro`) REFERENCES `tipoparametro` (`CodigoParametro`);

--
-- Limitadores para a tabela `utilizador`
--
ALTER TABLE `utilizador`
  ADD CONSTRAINT `utilizador-tipo` FOREIGN KEY (`TipoUtilizador`) REFERENCES `tipoutilizador` (`TipoUtilizador`);

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `ApagarMedicoesAnteriores` ON SCHEDULE EVERY 1 DAY STARTS '2022-04-28 16:51:17' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM medicao WHERE medicao.DataHoraObjectId < DATE_SUB(NOW() , INTERVAL 30 DAY)$$

CREATE DEFINER=`root`@`localhost` EVENT `AvaliarTaxaDeFalhas` ON SCHEDULE EVERY 1 HOUR STARTS '2022-04-28 16:51:17' ON COMPLETION PRESERVE ENABLE DO BEGIN

DECLARE taxa decimal(3,2); DECLARE vSens VARCHAR(5); DECLARE done INT DEFAULT FALSE; DECLARE numInv INT(15); DECLARE taxaTot decimal(3,2);
DECLARE cur CURSOR FOR SELECT DISTINCT Sensor FROM `medicao`;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

SET taxa = (SELECT variaveiscontrolo.TaxaFalhaSensor FROM variaveiscontrolo ORDER BY variaveiscontrolo.IdVControlo LIMIT 1);

OPEN cur;
ins_loop: LOOP
	FETCH cur INTO vSens;
	IF done THEN
		LEAVE ins_loop;
	END IF;
	SET numInv = (SELECT SUM(medicao.Invalido) FROM medicao WHERE medicao.Sensor=vSens AND TIMESTAMPDIFF(HOUR,medicao.DataHoraObjectId,NOW())<1);
	SET taxaTot = numInv / (SELECT COUNT(*) FROM medicao WHERE medicao.Sensor=vSens AND TIMESTAMPDIFF(HOUR,medicao.DataHoraObjectId,NOW())<1);
	IF taxaTot > taxa THEN
    	INSERT INTO alarmeAdministrador(Mensagem) VALUES (CONCAT('Sensor ', vSens, ' está com anomalias!'));
    END IF;
END LOOP ins_loop;
CLOSE cur;

END$$

CREATE DEFINER=`root`@`localhost` EVENT `AvaliarSensoresSemMedicao` ON SCHEDULE EVERY 5 MINUTE STARTS '2022-04-28 16:51:17' ON COMPLETION PRESERVE ENABLE DO BEGIN

DECLARE vSens VARCHAR(5); DECLARE done INT DEFAULT FALSE;
DECLARE cur CURSOR FOR SELECT DISTINCT Sensor FROM `medicao`;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

OPEN cur;
ins_loop: LOOP
	FETCH cur INTO vSens;
	IF done THEN
		LEAVE ins_loop;
	END IF;
	IF (SELECT COUNT(*) FROM medicao WHERE medicao.Sensor=vSens AND TIMESTAMPDIFF(HOUR,medicao.DataHoraObjectId,NOW())<1) = 0 THEN
    	INSERT INTO alarmeAdministrador(Mensagem) VALUES (CONCAT('Sensor ', vSens, ' não está a enviar valores!'));
    END IF;
END LOOP ins_loop;
CLOSE cur;

END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;






CREATE ROLE IF NOT EXISTS 'Administrador';
CREATE ROLE IF NOT EXISTS 'Investigador';
CREATE ROLE IF NOT EXISTS 'Migrador';

GRANT SELECT, INSERT, DELETE ON utilizador TO 'Administrador'; 

GRANT SELECT, INSERT, DELETE ON cultura TO 'Administrador';
GRANT SELECT ON cultura TO 'Investigador';

GRANT SELECT ON parametrocultura TO 'Administrador';
GRANT SELECT ON parametrocultura TO 'Investigador';

GRANT SELECT ON alarmeadministrador TO 'Administrador';

GRANT SELECT ON medicao TO 'Administrador';
GRANT SELECT ON medicao TO 'Investigador';
GRANT SELECT, INSERT ON medicao TO 'Migrador';

GRANT SELECT ON alerta TO 'Administrador';
GRANT SELECT ON alerta TO 'Investigador';

GRANT SELECT, INSERT, UPDATE, DELETE ON tipoparametro TO 'Administrador';
GRANT SELECT ON tipoparametro TO 'Investigador';

GRANT SELECT, INSERT, UPDATE, DELETE ON tipoutilizador TO 'Administrador';

GRANT SELECT, INSERT, UPDATE, DELETE ON variaveiscontrolo TO 'Administrador';

GRANT EXECUTE ON PROCEDURE AlterarCultura TO 'Investigador';
GRANT EXECUTE ON PROCEDURE AlterarParametroCultura TO 'Investigador';
GRANT EXECUTE ON PROCEDURE ObterIdUtilizador TO 'Investigador';
GRANT EXECUTE ON PROCEDURE RemoverParametroCultura TO 'Investigador';
GRANT EXECUTE ON PROCEDURE CriarParametroCultura TO 'Investigador';

GRANT EXECUTE ON PROCEDURE CriarUtilizador TO 'Administrador';
GRANT EXECUTE ON PROCEDURE CriarCultura TO 'Administrador';
GRANT EXECUTE ON PROCEDURE RemoverCultura TO 'Administrador';
GRANT EXECUTE ON PROCEDURE AtribuirCulturaInvestigador TO 'Administrador';
GRANT EXECUTE ON PROCEDURE ObterIdUtilizador TO 'Administrador';

GRANT EXECUTE ON PROCEDURE CriarMedicao TO 'Migrador';

GRANT SELECT ON mysql.proc TO 'DataMigrator'@'localhost';

FLUSH PRIVILEGES;
