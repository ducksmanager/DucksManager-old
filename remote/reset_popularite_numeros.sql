-- Cleanup: prevents issues with issues having the same issuenumber but with a different case
UPDATE numeros n
  INNER JOIN (
               SELECT DISTINCT
                 n_inner.Pays,
                 n_inner.Magazine,
                 n_inner.Numero
               FROM numeros n_inner, numeros n2_inner
               WHERE n_inner.NUMERO NOT REGEXP '^[0-9]+$' AND n2_inner.NUMERO NOT REGEXP '^[0-9]+$' AND
                     LOWER(n_inner.Numero) = LOWER(n2_inner.Numero) AND n_inner.Numero != n2_inner.Numero
             ) n2
SET n.Numero = LOWER(n.Numero)
WHERE n.Pays = n2.Pays AND n.Magazine = n2.Magazine AND n.Numero = n2.Numero;

UPDATE tranches_pretes tp
SET points = (
  SELECT COUNT(*) AS Popularite
  FROM numeros n
    INNER JOIN users u ON n.ID_Utilisateur = u.ID
  WHERE
    n.Pays = SUBSTRING(tp.publicationcode, 1, POSITION('/' IN tp.publicationcode) - 1) AND
    n.Magazine = SUBSTRING(tp.publicationcode, -POSITION('/' IN tp.publicationcode)) AND
    n.Numero = tp.issuenumber
);

# CREATE TABLE numeros_popularite (
#   Pays       VARCHAR(3) NOT NULL,
#   Magazine   VARCHAR(6) NOT NULL,
#   Numero     VARCHAR(8) NOT NULL,
#   Popularite INT        NOT NULL
# );
TRUNCATE numeros_popularite;
INSERT INTO numeros_popularite(Pays,Magazine,Numero,Popularite)
  SELECT DISTINCT
    n.Pays,
    n.Magazine,
    n.Numero,
    COUNT(*) AS Popularite
  FROM numeros n
    INNER JOIN numeros n2 ON n.Pays = n2.Pays AND n.Magazine=n2.Magazine AND n.Numero=n2.Numero AND n2.ID_Utilisateur NOT IN (
      SELECT u.ID
      FROM users u
      WHERE u.username LIKE 'test%'
    )
  WHERE
    n.ID = (SELECT MIN(ID) FROM numeros where Pays=n.Pays AND Magazine=n.Magazine AND Numero=n.Numero) AND
    n.ID != n2.ID AND
    n2.DateAjout < DATE_SUB(NOW(), INTERVAL -1 MONTH)
  GROUP BY n.Pays, n.Magazine, n.Numero
  ORDER BY Popularite desc;