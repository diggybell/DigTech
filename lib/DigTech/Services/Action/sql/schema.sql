DROP DATABASE IF EXISTS actionmgr;
CREATE DATABASE actionmgr;
USE actionmgr;

CREATE TABLE action_class
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

CREATE TABLE action_performer
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

CREATE UNIQUE INDEX action_performer_code_ndx
    ON action_performer(performer_code);

CREATE UNIQUE INDEX action_class_code_ndx
    ON action_class(class_code);

CREATE TABLE action_log
(
    action_seq              INTEGER         NOT NULL AUTO_INCREMENT,
    class_seq               INTEGER         NOT NULL,
    performer_seq           INTEGER         NOT NULL,
    action_timestamp        DATETIME,
    action_processed        DATETIME        DEFAULT NULL,
    action_payload          TEXT            NOT NULL,

    create_by               VARCHAR(40)     DEFAULT NULL,
    create_date             DATETIME        DEFAULT NULL,
    modify_by               VARCHAR(40)     DEFAULT NULL,
    modify_date             DATETIME        DEFAULT NULL,
    
    PRIMARY KEY(action_seq),
    FOREIGN KEY(class_seq) REFERENCES action_class(class_seq),
    FOREIGN KEY(performer_seq) REFERENCES action_performer(performer_seq)
) ENGINE=InnoDB;

CREATE INDEX action_log_class_ndx
    ON action_log(class_seq);
CREATE INDEX action_log_performer_ndx
    ON action_log(performer_seq);