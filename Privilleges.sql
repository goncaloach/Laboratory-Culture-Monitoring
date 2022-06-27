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
