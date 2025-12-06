-- Create trigger to automatically sync clock_in/clock_out to check_in/check_out
DELIMITER $$

CREATE TRIGGER sync_attendance_before_insert
BEFORE INSERT ON attendance
FOR EACH ROW
BEGIN
    IF NEW.clock_in IS NOT NULL AND (NEW.check_in IS NULL OR NEW.check_in = '') THEN
        SET NEW.check_in = NEW.clock_in;
    END IF;
    IF NEW.clock_out IS NOT NULL AND (NEW.check_out IS NULL OR NEW.check_out = '') THEN
        SET NEW.check_out = NEW.clock_out;
    END IF;
    IF NEW.check_in IS NOT NULL AND (NEW.clock_in IS NULL OR NEW.clock_in = '') THEN
        SET NEW.clock_in = NEW.check_in;
    END IF;
    IF NEW.check_out IS NOT NULL AND (NEW.clock_out IS NULL OR NEW.clock_out = '') THEN
        SET NEW.clock_out = NEW.check_out;
    END IF;
END$$

CREATE TRIGGER sync_attendance_before_update
BEFORE UPDATE ON attendance
FOR EACH ROW
BEGIN
    IF NEW.clock_in IS NOT NULL AND (NEW.check_in IS NULL OR NEW.check_in = '') THEN
        SET NEW.check_in = NEW.clock_in;
    END IF;
    IF NEW.clock_out IS NOT NULL AND (NEW.check_out IS NULL OR NEW.check_out = '') THEN
        SET NEW.check_out = NEW.clock_out;
    END IF;
    IF NEW.check_in IS NOT NULL AND (NEW.clock_in IS NULL OR NEW.clock_in = '') THEN
        SET NEW.clock_in = NEW.check_in;
    END IF;
    IF NEW.check_out IS NOT NULL AND (NEW.clock_out IS NULL OR NEW.clock_out = '') THEN
        SET NEW.clock_out = NEW.check_out;
    END IF;
END$$

DELIMITER ;
