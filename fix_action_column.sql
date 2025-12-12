-- Fix the action column to allow NULL or set a default value
ALTER TABLE attendance_logs MODIFY COLUMN action varchar(50) DEFAULT 'manual_entry';

-- Alternative: Make action column nullable
-- ALTER TABLE attendance_logs MODIFY COLUMN action varchar(50) NULL;