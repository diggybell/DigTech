USE eventmgr;

DROP FUNCTION IF EXISTS GetNextPartition;
DROP FUNCTION IF EXISTS GetPartitionName;

DELIMITER $$
CREATE FUNCTION GetNextPartition(monthno INTEGER) RETURNS INTEGER DETERMINISTIC
BEGIN
   IF monthno IS NULL THEN
      SET @month = MONTH(NOW()) + 1;
   ELSE
      SET @month = monthno;
   END IF;

	IF @month > 12 THEN
		SET @month = 1;
	END IF;

    RETURN @month;
END;$$
DELIMITER ;

DELIMITER $$
CREATE FUNCTION GetPartitionName(partno INTEGER) RETURNS VARCHAR(20) DETERMINISTIC
BEGIN
   SET @partname = NULL;
   SELECT
   CASE
      WHEN partno = 1 THEN 'January'
      WHEN partno = 2 THEN 'February'
      WHEN partno = 3 THEN 'March'
      WHEN partno = 4 THEN 'April'
      WHEN partno = 5 THEN 'May'
      WHEN partno = 6 THEN 'June'
      WHEN partno = 7 THEN 'July'
      WHEN partno = 8 THEN 'August'
      WHEN partno = 9 THEN 'September'
      WHEN partno = 10 THEN 'October'
      WHEN partno = 11 THEN 'November'
      WHEN partno = 12 THEN 'December'
      ELSE NULL
   END
   INTO @partname;

   RETURN @partname;
END;$$
DELIMITER ;