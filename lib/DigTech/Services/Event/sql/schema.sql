DROP DATABASE IF EXISTS eventmgr;
CREATE DATABASE eventmgr;
USE eventmgr;

CREATE TABLE event_class
(
    class_seq               INTEGER         NOT NULL AUTO_INCREMENT,
    class_code              VARCHAR(10)     NOT NULL,
    class_description       VARCHAR(25)     DEFAULT '',

    create_by               VARCHAR(40)     DEFAULT NULL,
    create_date             DATETIME        DEFAULT NULL,
    modify_by               VARCHAR(40)     DEFAULT NULL,
    modify_date             DATETIME        DEFAULT NULL,

    PRIMARY KEY(class_seq)
) ENGINE=InnoDB;

CREATE TABLE event_performer
(
    performer_seq           INTEGER         NOT NULL AUTO_INCREMENT,
    performer_code          VARCHAR(10)     NOT NULL,
    performer_description   VARCHAR(25)     DEFAULT '',

    create_by               VARCHAR(40)     DEFAULT NULL,
    create_date             DATETIME        DEFAULT NULL,
    modify_by               VARCHAR(40)     DEFAULT NULL,
    modify_date             DATETIME        DEFAULT NULL,
    
    PRIMARY KEY(performer_seq)
) ENGINE=InnoDB;

CREATE UNIQUE INDEX event_performer_code_ndx
    ON event_performer(performer_code);

CREATE UNIQUE INDEX event_class_code_ndx
    ON event_class(class_code);

CREATE TABLE event_log
(
    event_seq               INTEGER         NOT NULL AUTO_INCREMENT,
    class_code              VARCHAR(10)     NOT NULL,
    performer_code          VARCHAR(10)     NOT NULL,
    event_timestamp         DATETIME,
    event_processed         DATETIME        DEFAULT NULL,
    event_payload           TEXT            NOT NULL,

    partition_key           INTEGER,

    create_by               VARCHAR(40)     DEFAULT NULL,
    create_date             DATETIME        DEFAULT NULL,
    modify_by               VARCHAR(40)     DEFAULT NULL,
    modify_date             DATETIME        DEFAULT NULL,
    
    PRIMARY KEY(event_seq, partition_key)
)
ENGINE=InnoDB
PARTITION BY LIST(partition_key)
(
    PARTITION January VALUES IN (1),
    PARTITION February VALUES IN (2),
    PARTITION March VALUES IN (3),
    PARTITION April VALUES IN (4),
    PARTITION May VALUES IN (5),
    PARTITION June VALUES IN (6),
    PARTITION July VALUES IN (7),
    PARTITION August VALUES IN (8),
    PARTITION September VALUES IN (9),
    PARTITION October VALUES IN (10),
    PARTITION November VALUES IN (11),
    PARTITION December VALUES IN (12)
);

CREATE INDEX event_log_class_ndx
    ON event_log(class_code);
CREATE INDEX event_log_performer_ndx
    ON event_log(performer_code);