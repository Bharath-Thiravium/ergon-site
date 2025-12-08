-- Remove old latitude/longitude columns
ALTER TABLE attendance DROP COLUMN check_in_latitude;
ALTER TABLE attendance DROP COLUMN check_in_longitude;
ALTER TABLE attendance DROP COLUMN check_out_latitude;
ALTER TABLE attendance DROP COLUMN check_out_longitude;

-- Add recommended indexes
ALTER TABLE attendance ADD INDEX idx_user_id (user_id);
ALTER TABLE attendance ADD INDEX idx_check_in_date (check_in);
ALTER TABLE attendance ADD INDEX idx_project_id (project_id);
ALTER TABLE attendance ADD INDEX idx_location_type (location_type);
