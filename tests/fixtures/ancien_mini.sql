DROP TABLE IF EXISTS Mequipe, H1equipe, Midentifiant, H1identifiant;
CREATE TABLE Mequipe (ID int NOT NULL, nomequipe varchar(50) NOT NULL, nomgolf varchar(50) NOT NULL, nomcap varchar(50) NOT NULL, prenomcap varchar(50) NOT NULL, telcap varchar(50) NOT NULL, email varchar(50) NOT NULL, adressecap varchar(100) NOT NULL, datearrive year NOT NULL, PRIMARY KEY (ID)) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO Mequipe VALUES
(185,'SAINTE-MAXIME','SAINTE-MAXIME','CHARPENTIER','Patrick','0600000001','capitaine1@exemple.fr','',0000),
(190,'VALGARDE-2','VALGARDE','BERNARDI','Yves','0600000002','capitaine2@exemple.fr','',0000),
(198,'SAINT-MARTIN-2','SAINT-MARTIN-2','MAILLIS','Michèle','0600000003','capitaine3@exemple.fr','',0000),
(202,'SALON','SALON','TRAPY','Jean-Paul','0600000004','capitaine4@exemple.fr','',0000),
(187,'FREGATE','FREGATE','BERGONZI','Maurice','0600000005','capitaine5@exemple.fr','',0000),
(192,'LUBERON-1','LUBERON','HOLC','Patrick','0600000006','capitaine6@exemple.fr','',0000);
CREATE TABLE H1equipe LIKE Mequipe;
INSERT INTO H1equipe VALUES
(92,'SALON','','Thibault','Kevin','0600000007','capitaine7@exemple.fr','',2027),
(115,'VALGARDE-1','VALGARDE','THOMAS','Christian','0600000008','capitaine8@exemple.fr','',0000),
(116,'ORANGE','ORANGE','Martins Torres','Albert','0600000009','capitaine9@exemple.fr','',0000),
(125,'VICTORIA','VICTORIA','MORALDO','FREDDY','0600000010','capitaine10@exemple.fr','',0000),
(112,'LUBERON','GOLF DU LUBERON','ARNAUD','Etienne','0600000011','capitaine11@exemple.fr','',2001);
CREATE TABLE Midentifiant (ID int NOT NULL, nomequipe varchar(255) NOT NULL, pass varchar(255) NOT NULL, PRIMARY KEY (ID)) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO Midentifiant VALUES
(12,'ADMIN','12c0fb04e8a9ce38dec7e915e0eb46c9a76cd209'),
(126,'SALON','3527d9c343f46bfbe1147bb7d19e869435095958'),
(131,'SAINT-MARTIN-1','5c979e3a3a41870e1c208e0448cb4409647d808c'),
(176,'SAINT-MARTIN-1','cadcf2be05007d9f9fa50e7974c4ade02b1d31f2'),
(167,'SAINTE-MAXIME','2150e921ea616773c5536c626b1b84166ee32e23'),
(169,'FREGATE','2bb95c218c274625732b0d5acd38655caa01ce0f'),
(172,'VALGARDE-2','220f928ca69ebc9894389e926516dc8b61805cf9'),
(174,'LUBERON-1','9777114e94048e4b95c7f152ff27f13d25a23f84'),
(180,'SAINT-MARTIN-2','b983167552448e2e59bbb1634a0dcd40af1b54f0'),
(184,'SALON','3527d9c343f46bfbe1147bb7d19e869435095958');
CREATE TABLE H1identifiant LIKE Midentifiant;
INSERT INTO H1identifiant VALUES
(12,'ADMIN','12c0fb04e8a9ce38dec7e915e0eb46c9a76cd209'),
(68,'SALON','4f6c12e311de54b58f83bfd4228730b2b53fe37c'),
(91,'VALGARDE-1','771b70b4b92f04d31d1e5ab308ed161267eecd37'),
(92,'ORANGE','8eddff1a82227d8b2f1c07ba9e53685b9e334d13'),
(101,'VICTORIA','4e817bc54761bc979fe9245774ecbb0559cf4892');
