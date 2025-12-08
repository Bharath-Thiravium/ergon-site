-- Update existing attendance records with default values
UPDATE attendance SET location_type = 'office' WHERE location_type IS NULL;
UPDATE attendance SET location_title = COALESCE(location_name, 'Main Office') WHERE location_title IS NULL;
UPDATE attendance SET location_radius = 50 WHERE location_radius IS NULL;
UPDATE attendance SET check_out = NULL WHERE check_out IS NOT NULL AND CAST(check_out AS CHAR) IN ('', '0000-00-00 00:00:00');
