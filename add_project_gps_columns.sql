-- Check if columns exist first, then add if needed
-- Run this query first to check:
-- SHOW COLUMNS FROM projects LIKE 'latitude';

-- Only run these if columns don't exist:
-- ALTER TABLE projects ADD COLUMN latitude DECIMAL(10, 8) NULL;
-- ALTER TABLE projects ADD COLUMN longitude DECIMAL(11, 8) NULL;
-- ALTER TABLE projects ADD COLUMN checkin_radius INT DEFAULT 100;

-- Since columns already exist, no action needed