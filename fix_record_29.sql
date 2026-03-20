-- Fix current attendance record
UPDATE attendance SET project_id = 15 WHERE id = 29;

-- Verify the fix
SELECT id, user_id, project_id, check_in FROM attendance WHERE id = 29;