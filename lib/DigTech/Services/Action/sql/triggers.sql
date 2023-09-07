
USE actionmgr;

DROP TRIGGER IF EXISTS action_class_insert;
DROP TRIGGER IF EXISTS action_class_update;

DELIMITER $$
CREATE TRIGGER action_class_insert BEFORE INSERT ON action_class 
   FOR EACH ROW
   BEGIN
      SET NEW.create_date = NOW();
      SET NEW.create_by   = CURRENT_USER;
      SET NEW.modify_date = '0000-00-00 00:00:00';
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER action_class_update BEFORE UPDATE ON action_class 
   FOR EACH ROW
   BEGIN
      SET NEW.modify_date = NOW();
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;

USE actionmgr;

DROP TRIGGER IF EXISTS action_log_insert;
DROP TRIGGER IF EXISTS action_log_update;

DELIMITER $$
CREATE TRIGGER action_log_insert BEFORE INSERT ON action_log 
   FOR EACH ROW
   BEGIN
      SET NEW.create_date = NOW();
      SET NEW.create_by   = CURRENT_USER;
      SET NEW.modify_date = '0000-00-00 00:00:00';
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER action_log_update BEFORE UPDATE ON action_log 
   FOR EACH ROW
   BEGIN
      SET NEW.modify_date = NOW();
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;

USE actionmgr;

DROP TRIGGER IF EXISTS action_performer_insert;
DROP TRIGGER IF EXISTS action_performer_update;

DELIMITER $$
CREATE TRIGGER action_performer_insert BEFORE INSERT ON action_performer 
   FOR EACH ROW
   BEGIN
      SET NEW.create_date = NOW();
      SET NEW.create_by   = CURRENT_USER;
      SET NEW.modify_date = '0000-00-00 00:00:00';
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER action_performer_update BEFORE UPDATE ON action_performer 
   FOR EACH ROW
   BEGIN
      SET NEW.modify_date = NOW();
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;
