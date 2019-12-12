CREATE DATABASE IF NOT EXISTS iboo;

USE iboo;

CREATE TABLE IF NOT EXISTS workers (
id         int(11) auto_increment not null,
role       varchar(255) not null,
username   varchar(255) not null,
email      varchar(255) not null,
password   varchar(255) not null,
created_at datetime not null,
updated_at datetime not null,
CONSTRAINT pk_workers PRIMARY KEY(id),
CONSTRAINT uc_workers UNIQUE (email)
)ENGINE=InnoDb DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS machines (
id         int(11) auto_increment not null,
type       varchar(255) not null,
worker_id  int(11) not null,
created_at datetime not null,
updated_at datetime not null,
CONSTRAINT pk_machines PRIMARY KEY(id),
CONSTRAINT uc_machines UNIQUE (type),
CONSTRAINT fk_machine_user FOREIGN KEY(worker_id) REFERENCES workers(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pieces (
id         int(11) auto_increment not null,
worker_id  int(11) not null,
machine_id int(11) not null,
created_at  datetime not null,
updated_at  datetime not null,
CONSTRAINT pk_pieces PRIMARY KEY(id),
CONSTRAINT fk_piece_worker FOREIGN KEY(worker_id) REFERENCES workers(id),
CONSTRAINT fk_piece_machine FOREIGN KEY(machine_id) REFERENCES machines(id)
)ENGINE=InnoDb DEFAULT CHARSET=utf8;



