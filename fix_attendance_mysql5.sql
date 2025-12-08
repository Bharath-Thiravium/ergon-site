-- Fix attendance table structure (MySQL 5.x compatible)
-- Run each statement separately and ignore errors if column/index already exists

-- Add project_id column
ALTER TABLE attendance ADD COLUMN project_id INT NULL AFTER user_id;

-- Add location_type column
ALTER TABLE attendance ADD COLUMN location_type VARCHAR(50) NULL AFTER location_name;

-- Add location_title column
ALTER TABLE attendance ADD COLUMN location_title VARCHAR(255) NULL AFTER location_type;

-- Add location_radius column
ALTER TABLE attendance ADD COLUMN location_radius INT NULL AFTER location_title;

-- Update existing records to have default values
UPDATE attendance SET location_type = 'office' WHERE location_type IS NULL;
UPDATE attendance SET location_title = COALESCE(location_name, 'Main Office') WHERE location_title IS NULL;
UPDATE attendance SET location_radius = 50 WHERE location_radius IS NULL;

-- Ensure check_out NULL values are properly set
UPDATE attendance SET check_out = NULL WHERE check_out = '' OR check_out = '0000-00-00 00:00:00';

-- Add indexes for better performance
CREATE INDEX idx_project_id ON attendance(project_id);
CREATE INDEX idx_location_type ON attendance(location_type);
