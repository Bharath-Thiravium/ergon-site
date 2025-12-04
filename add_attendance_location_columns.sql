-- Add location tracking columns to attendance table
ALTER TABLE attendance ADD COLUMN location_type VARCHAR(50) NULL COMMENT 'project or office';
ALTER TABLE attendance ADD COLUMN location_title VARCHAR(255) NULL COMMENT 'Name of project or office location';
ALTER TABLE attendance ADD COLUMN location_radius INT NULL COMMENT 'Radius used for validation';

-- Add location title to projects table
ALTER TABLE projects ADD COLUMN location_title VARCHAR(255) NULL COMMENT 'Display name for project location';

-- Add location title to settings table for office location
ALTER TABLE settings ADD COLUMN location_title VARCHAR(255) DEFAULT 'Main Office' COMMENT 'Display name for office location';

-- Update existing projects with default location titles
UPDATE projects SET location_title = CONCAT(name, ' Site') WHERE location_title IS NULL AND name IS NOT NULL;

-- Update settings with default office title if not set
UPDATE settings SET location_title = 'Main Office' WHERE location_title IS NULL OR location_title = '';