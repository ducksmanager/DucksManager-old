-- This script should be executed daily

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

-- Set issues' popularity. This number will vary over time
TRUNCATE numeros_popularite;
INSERT INTO numeros_popularite(Pays,Magazine,Numero,Popularite)
  SELECT DISTINCT
    n.Pays,
    n.Magazine,
    REPLACE(n.Numero, ' ', '')
    COUNT(*) AS Popularite
  FROM numeros n
  WHERE
    n.ID_Utilisateur NOT IN (
      SELECT u.ID
      FROM users u
      WHERE u.username LIKE 'test%'
    ) AND
    n.DateAjout < DATE_SUB(NOW(), INTERVAL -1 MONTH)
  GROUP BY n.Pays, n.Magazine, n.Numero;

-- Associate issues' popularity with edges. This will not vary over time: we only modify the edges that don't have their popularity set
UPDATE tranches_pretes tp
SET points = (
  SELECT Popularite
  FROM numeros_popularite np
  WHERE
    np.Pays = SUBSTRING(tp.publicationcode, 1, POSITION('/' IN tp.publicationcode) - 1) AND
    np.Magazine = SUBSTRING(tp.publicationcode, -POSITION('/' IN tp.publicationcode)) AND
    np.Numero = tp.issuenumber
)
WHERE points IS NULL;

-- Update the users' points
TRUNCATE users_points;
INSERT INTO users_points
SELECT
  null,
  contributions.contributeur,
  contributions.type_contribution,
  sum(contributions.Popularite) AS points
FROM (
  SELECT
    tp.*,
    tpc.contributeur,
    tpc.contribution AS type_contribution,
    (
      SELECT np.Popularite
      FROM numeros_popularite np
      WHERE
       np.Pays = SUBSTRING_INDEX(tp.publicationcode, '/', 1) AND
       np.Magazine = SUBSTRING_INDEX(tp.publicationcode, '/', -1) AND
       np.Numero = tp.issuenumber
    ) AS Popularite
  FROM tranches_pretes tp
    INNER JOIN tranches_pretes_contributeurs tpc USING (publicationcode, issuenumber)
) contributions
INNER JOIN users ON contributions.contributeur = users.ID
GROUP BY contributions.contributeur, contributions.type_contribution
HAVING sum(contributions.Popularite) > 0
ORDER BY sum(contributions.Popularite);