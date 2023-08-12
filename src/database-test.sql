DROP DATABASE IF EXISTS digtech_testdata;
CREATE DATABASE digtech_testdata;
USE digtech_testdata;

CREATE TABLE test_table
(
    rec_seq INTEGER NOT NULL AUTO_INCREMENT,
    rec_name VARCHAR(20) NOT NULL,
    rec_tstamp DATETIME DEFAULT NULL,

    create_by VARCHAR(25),
    create_date DATETIME DEFAULT NULL,
    modify_by VARCHAR(25),
    modify_date DATETIME DEFAULT NULL,

    PRIMARY KEY(rec_seq)
) ENGINE=InnoDB;
