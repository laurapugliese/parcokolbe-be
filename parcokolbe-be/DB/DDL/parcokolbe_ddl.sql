DROP TABLE IF EXISTS sale;
CREATE TABLE sale (
	id        	INT(10) NOT NULL,
	nome    	VARCHAR(45) NOT NULL,
	categoria	VARCHAR(45),
	PRIMARY KEY (id, nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS corsi;
CREATE TABLE corsi (
	idsala				INT(10) NOT NULL,
	giorno				DATE NOT NULL,
	corso    			VARCHAR(150) NOT NULL,
	ora_inizio			VARCHAR(20) NOT NULL,
	ora_fine			VARCHAR(20) NOT NULL,
	istruttore			VARCHAR(45),
	data_aggiornamento	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (idsala, giorno, corso, ora_inizio, ora_fine)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
