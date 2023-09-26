
USE eventmgr;

DROP TRIGGER IF EXISTS event_class_insert;
DROP TRIGGER IF EXISTS event_class_update;

DELIMITER $$
CREATE TRIGGER event_class_insert BEFORE INSERT ON event_class 
   FOR EACH ROW
   BEGIN
      SET NEW.create_date = NOW();
      SET NEW.create_by   = CURRENT_USER;
      SET NEW.modify_date = '0000-00-00 00:00:00';
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER event_class_update BEFORE UPDATE ON event_class 
   FOR EACH ROW
   BEGIN
      SET NEW.modify_date = NOW();
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;

USE eventmgr;

DROP TRIGGER IF EXISTS event_log_insert;
DROP TRIGGER IF EXISTS event_log_update;

DELIMITER $$
CREATE TRIGGER event_log_insert BEFORE INSERT ON event_log 
   FOR EACH ROW
   BEGIN
      SET NEW.create_date = NOW();
      SET NEW.create_by   = CURRENT_USER;
      SET NEW.modify_date = '0000-00-00 00:00:00';
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER event_log_update BEFORE UPDATE ON event_log 
   FOR EACH ROW
   BEGIN
      SET NEW.modify_date = NOW();
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;

USE eventmgr;

DROP TRIGGER IF EXISTS event_performer_insert;
DROP TRIGGER IF EXISTS event_performer_update;

DELIMITER $$
CREATE TRIGGER event_performer_insert BEFORE INSERT ON event_performer 
   FOR EACH ROW
   BEGIN
      SET NEW.create_date = NOW();
      SET NEW.create_by   = CURRENT_USER;
      SET NEW.modify_date = '0000-00-00 00:00:00';
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER event_performer_update BEFORE UPDATE ON event_performer 
   FOR EACH ROW
   BEGIN
      SET NEW.modify_date = NOW();
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;
