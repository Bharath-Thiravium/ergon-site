-- Backup data first: CREATE TABLE attendance_backup AS SELECT * FROM attendance;

-- Step 1: Copy all clock_in/clock_out data to check_in/check_out
UPDATE attendance 
SET check_in = clock_in, 
    check_out = clock_out 
WHERE clock_in IS NOT NULL OR clock_out IS NOT NULL;

-- Step 2: Drop redundant columns
ALTER TABLE attendance
DROP COLUMN clock_in,
DROP COLUMN clock_out,
DROP COLUMN clock_in_time,
DROP COLUMN clock_out_time,
DROP COLUMN latitude,
DROP COLUMN longitude,
DROP COLUMN location_lat,
DROP COLUMN location_lng;

-- Keep only:
-- check_in, check_out (main time tracking)
-- check_in_latitude, check_in_longitude, check_out_latitude, check_out_longitude (location tracking)
-- date (for indexing)
-- All other columns remain

-- Step 3: Update all code to use check_in/check_out only
