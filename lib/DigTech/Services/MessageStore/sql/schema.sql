DROP DATABASE IF EXISTS msgstore;
CREATE DATABASE msgstore;
USE msgstore;

CREATE TABLE endpoint
(
    endpoint_seq            INTEGER         NOT NULL AUTO_INCREMENT,
    hostname                VARCHAR(40)     NOT NULL,
    uri                     VARCHAR(40)     NOT NULL,
    username                VARCHAR(30),
    password                VARCHAR(30),
    token                   VARCHAR(30),

    PRIMARY KEY(endpoint_seq)
) ENGINE=InnoDB;

CREATE TABLE message_store
(
    message_seq             INTEGER         NOT NULL AUTO_INCREMENT,
    endpoint_seq            INTEGER         NOT NULL,
    last_status             INTEGER         DEFAULT NULL,
    initial_time            DATETIME,
    send_time               DATETIME        DEFAULT NULL,
    retry_counter           INTEGER         DEFAULT 0,
    last_retry              DATETIME        DEFAULT NULL,
    payload                 TEXT            DEFAULT '',
    response                TEXT            DEFAULT '',

    PRIMARY KEY(endpoint_seq),
    FOREIGN KEY(endpoint_seq) REFERENCES endpoint.endpoint_seq
) ENGINE=InnoDB;

CREATE INDEX message_endpoint_ndx
    ON message_store.endpoint_seq;