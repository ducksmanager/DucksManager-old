LOAD DATA LOW_PRIORITY LOCAL INFILE 'export/auteurs_pseudos.csv'
REPLACE
INTO TABLE auteurs_pseudos_simple
CHARACTER SET utf8mb4
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' ESCAPED BY '\"' LINES TERMINATED BY '\n'
IGNORE 1 LINES
(ID_User, NomAuteurAbrege);

LOAD DATA LOW_PRIORITY LOCAL INFILE 'export/numeros.csv'
REPLACE
INTO TABLE numeros_simple
CHARACTER SET utf8mb4
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' ESCAPED BY '\"' LINES TERMINATED BY '\n'
IGNORE 1 LINES
(ID_Utilisateur, Publicationcode, Numero);

TRUNCATE TABLE dm_stats.utilisateurs_histoires_manquantes;

TRUNCATE TABLE dm_stats.auteurs_histoires;

TRUNCATE TABLE dm_stats.histoires_publications;

insert into dm_stats.histoires_publications(storycode, publicationcode, issuenumber)
  select distinct sv.storycode, i.publicationcode, i.issuenumber
  from coa.inducks_storyjob sj
    inner join coa.inducks_storyversion sv on sj.storyversioncode = sv.storyversioncode
    inner join coa.inducks_entry e on sj.storyversioncode = e.storyversioncode
    inner join coa.inducks_issue i on e.issuecode = i.issuecode
  where sj.personcode IN (
    select distinct a_p.NomAuteurAbrege
    FROM auteurs_pseudos_simple a_p
  );

insert into dm_stats.auteurs_histoires(personcode, storycode)
  select distinct sj.personcode, sv.storycode
  from coa.inducks_storyjob sj
    inner join coa.inducks_storyversion sv on sj.storyversioncode = sv.storyversioncode
  WHERE sv.what='s'
    and sv.kind='n'
    and sj.personcode IN (
      select distinct a_p.NomAuteurAbrege
      FROM auteurs_pseudos_simple a_p
    )
    and EXISTS (
      select 1
      from coa.inducks_entry e
      where e.storyversioncode = sv.storyversioncode
    );

insert into dm_stats.utilisateurs_histoires_manquantes (ID_User, personcode, storycode)
  select a_p.ID_User, a_h.personcode, a_h.storycode
  from auteurs_pseudos_simple a_p
    inner join dm_stats.auteurs_histoires a_h on a_p.NomAuteurAbrege = a_h.personcode
  where not exists (
      select 1
      from dm_stats.histoires_publications h_pub
        inner join numeros_simple n on h_pub.publicationcode = n.Publicationcode and h_pub.issuenumber = n.Numero
      where a_h.storycode = h_pub.storycode  and a_p.ID_User = n.ID_Utilisateur
  );