use gestion_des_absences ;
-- Désactiver les contraintes de clé étrangère temporairement
SET FOREIGN_KEY_CHECKS = 0;

-- Vider les tables dans l'ordre des dépendances
TRUNCATE TABLE justif;
TRUNCATE TABLE retard;
TRUNCATE TABLE absences;
TRUNCATE TABLE stagiaires;
TRUNCATE TABLE utilisateur;
TRUNCATE TABLE surveillants;
TRUNCATE TABLE formateures;
TRUNCATE TABLE seances;
TRUNCATE TABLE motifs;
TRUNCATE TABLE groupes;
TRUNCATE TABLE filieres;

-- Réactiver les contraintes de clé étrangère
SET FOREIGN_KEY_CHECKS = 1;
update stagiaires 
set note_disipline = 20;


SELECT * FROM gestion_des_absences.stagiaires;
select * from `base-plat` where `cin`='BB214307';
use gestion_des_absences;

insert into utilisateur (matricul,mot_de_passe,type_utilisateur)
values ('17691','assia17691',"surveillants"),
  ('8864','taloub8864',"surveillants");
  SET FOREIGN_KEY_CHECKS = 0;
  TRUNCATE TABLE utilisateur;
  
  SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO filieres (nom_filier)
SELECT distinct`Code Filière`
FROM `base-plat`;

INSERT INTO utilisateur (email)
SELECT `Email`
FROM `base-plat`;


DELETE f1
FROM filieres f1
INNER JOIN filieres f2 
ON f1.nom_filier = f2.nom_filier 
AND f1.id > f2.id;




alter table groupes
modify nom_group varchar(50);




INSERT INTO groupes (nom_group)
SELECT distinct`Groupe`
FROM `base-plat`;








UPDATE groupes SET filiere_id = 1 WHERE nom_group = '%TSGE101%';
UPDATE groupes SET filiere_id = 2 WHERE nom_group = 'TSGE201';
UPDATE groupes SET filiere_id = 3 WHERE nom_group = 'TSGE301';
UPDATE groupes SET filiere_id = 4 WHERE nom_group = 'DEV101';
UPDATE groupes SET filiere_id = 4 WHERE nom_group = 'DEV102';
UPDATE groupes SET filiere_id = 4 WHERE nom_group = 'DEV103';
UPDATE groupes SET filiere_id = 4 WHERE nom_group = 'DEV104';
UPDATE groupes SET filiere_id = 4 WHERE nom_group = 'DEV105';
UPDATE groupes SET filiere_id = 4 WHERE nom_group = 'DEV106';
UPDATE groupes SET filiere_id = 5 WHERE nom_group = 'DEVOWFS201';
UPDATE groupes SET filiere_id = 5 WHERE nom_group = 'DEVOWFS202';
UPDATE groupes SET filiere_id = 5 WHERE nom_group = 'DEVOWFS203';
UPDATE groupes SET filiere_id = 5 WHERE nom_group = 'DEVOWFS204';
UPDATE groupes SET filiere_id = 5 WHERE nom_group = 'DEVOWFS205';
UPDATE groupes SET filiere_id = 5 WHERE nom_group = 'DEVOWFS206';
UPDATE groupes SET filiere_id = 6 WHERE nom_group = 'DEVOWFS101';
UPDATE groupes SET filiere_id = 6 WHERE nom_group = 'DEVOWFS102';


UPDATE groupes SET filiere_id = 7 WHERE nom_group = 'ID101';
UPDATE groupes SET filiere_id = 7 WHERE nom_group = 'ID102';

UPDATE groupes SET filiere_id =  8 WHERE nom_group = 'IDOCS201';
UPDATE groupes SET filiere_id =  9 WHERE nom_group = 'IDOSR201';
UPDATE groupes SET filiere_id =  9 WHERE nom_group = 'IDOSR202';



UPDATE groupes SET filiere_id = 10 WHERE nom_group = 'GE101';
UPDATE groupes SET filiere_id = 10 WHERE nom_group = 'GE102';
UPDATE groupes SET filiere_id = 10 WHERE nom_group = 'GE103';
UPDATE groupes SET filiere_id = 10 WHERE nom_group = 'GE104';
UPDATE groupes SET filiere_id = 10 WHERE nom_group = 'GE105';
UPDATE groupes SET filiere_id = 10 WHERE nom_group = 'GE106';
UPDATE groupes SET filiere_id = 11 WHERE nom_group = 'GEOCF201';
UPDATE groupes SET filiere_id = 11 WHERE nom_group = 'GEOCF202';
UPDATE groupes SET filiere_id = 11 WHERE nom_group = 'GEOCF203';
UPDATE groupes SET filiere_id = 12 WHERE nom_group = 'GEOCF301';
UPDATE groupes SET filiere_id = 12 WHERE nom_group = 'GEOCF302';

UPDATE groupes SET filiere_id = 13 WHERE nom_group = 'GEOCM201';
UPDATE groupes SET filiere_id = 13 WHERE nom_group = 'GEOCM202';
UPDATE groupes SET filiere_id = 13 WHERE nom_group = 'GEOCM203';
UPDATE groupes SET filiere_id = 14 WHERE nom_group = 'GEOCM301';
UPDATE groupes SET filiere_id = 14 WHERE nom_group = 'GEOCM302';

UPDATE groupes SET filiere_id = 15 WHERE nom_group = 'GEORH201';
UPDATE groupes SET filiere_id = 16 WHERE nom_group = 'GEORH301';

UPDATE groupes SET filiere_id = 17 WHERE nom_group = 'TDI201';
UPDATE groupes SET filiere_id = 17 WHERE nom_group = 'TDI202';
UPDATE groupes SET filiere_id = 18 WHERE nom_group = 'TDI301';
UPDATE groupes SET filiere_id = 18 WHERE nom_group = 'TDI302';
UPDATE groupes SET filiere_id = 19 WHERE nom_group = 'TRI201';



insert into seances ( id,date_seance )
values(1,'08:30:00/11:30:00'),
	  (2,'011:30:00/13:30:00'),
      (3,'13:30:00/16:30:00'), 
      (4,'16:30:00/18:30:00');
alter table stagiaires
modify note_disipline decimal(10,2) default 20;

update stagiaires
set  note_disipline = 20;



UPDATE stagiaires
JOIN `base-plat` ON stagiaires.nom = `base-plat`.Nom AND stagiaires.prenom = `base-plat`.`Prénom`
JOIN groupes ON `base-plat`.`Groupe` = groupes.nom_group
SET stagiaires.group_id = groupes.id;

ALTER TABLE stagiaires
ADD filiere_id int;

UPDATE stagiaires
JOIN `base-plat` ON stagiaires.nom = `base-plat`.Nom AND stagiaires.prenom = `base-plat`.`Prénom`
JOIN filieres ON `base-plat`.`Filière` = filieres.nom_filier
join groupes on groupes.filiere_id = filieres.id
SET stagiaires.filiere_id = filieres.id;



UPDATE stagiaires s
JOIN groupes g ON s.group_id = g.id
SET s.filiere_id = g.filiere_id;


insert into utilisateur(matricul,mot_de_passe,type_utilisateur)
values('123@123',"123@123","stagiaire"),
	  ('111@111',"111@111","stagiaire"),
      ('222@222',"222@222","stagiaire"),
      ('aaa@123','aaa@123',"surveillants"),
      ('bbb@112','1234',"surveillants"),
      ('abc@123','123',"formateure"),
       ('abc@122','1235',"formateure");
       
       
       
       
INSERT INTO utilisateur (matricul, mot_de_passe, type_utilisateur)
SELECT matricul, mot_de_passe, 'stagiaire'
FROM stagiaires;
INSERT INTO utilisateur (matricul, mot_de_passe, type_utilisateur,email)
values('123','123','formateure','123@gmail.com'),
	  ('456','456',"surveillants",'456@gmail.com');
select * from utilisateur
where matricul = '456';
      
      
INSERT IGNORE INTO utilisateur (matricul, mot_de_passe,type_utilisateur, email)
SELECT DISTINCT `Cin`, `Numéro stagiaire`,'stagiaire', `Email`
FROM `base-plat`;
select g.nom_group from groupes g join filieres f on g.filiere_id = f.id;
select nom_group from groupes;


insert into motifs (motif)
values("Maladie"),
	("Rendez-vous médical"),
    ("Accident"),
    ("Obligations familiales"),
    ("Problémes de transport");
alter table motifs
modify id int  auto_increment;








       
       
       
       

      
      
      
INSERT INTO stagiaires (matricul,mot_de_passe,nom,prenom)
SELECT distinct`Cin`,`Numéro stagiaire`,`Nom`,`Prénom`
FROM `base-plat`;


    
    select * from  temp_filiere_mappings;


