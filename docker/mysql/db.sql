create database if not exists `prenotazioni_ristoranti` default character set utf8 collate utf8_general_ci;

use `prenotazioni_ristoranti`;

create table if not exists `ristorante` (
    id int not null auto_increment,
    regione_sociale varchar(255) not null,
    indirizzo varchar(255) not null,
    tipo_cucina varchar(255) not null,
    n_coperti_totale int not null,
    fascia_prenotazione varchar(255) not null,
    primary key (id),
    unique key (regione_sociale)
);

create table if not exists `utente`(
    id int not null auto_increment,
    nome varchar(255) not null,
    cognome varchar(255) not null,
    primary key (id)
);

create table if not exists `prenotazione`(
    id int not null auto_increment,
    id_utente int not null,
    id_ristorante int not null,
    data_prenotazione date not null,
    fascia_prenotazione varchar(255) not null,
    n_persone int not null,
    primary key (id),
    foreign key (id_utente) references utente(id),
    foreign key (id_ristorante) references ristorante(id)
);