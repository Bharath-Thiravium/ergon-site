-- Update existing records to have a default location title
UPDATE settings SET location_title = 'Main Office' WHERE location_title IS NULL OR location_title = '';

-- Verify the column exists
DESCRIBE settings;