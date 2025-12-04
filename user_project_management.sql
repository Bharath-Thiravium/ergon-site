-- User-Project Assignment Management SQL Scripts

-- 1. View all user-project assignments (with GPS columns)
SELECT 
    u.name as user_name,
    u.email,
    u.role,
    p.name as project_name,
    p.latitude,
    p.longitude,
    p.checkin_radius,
    up.status as assignment_status,
    up.assigned_at
FROM user_projects up
JOIN users u ON up.user_id = u.id
JOIN projects p ON up.project_id = p.id
ORDER BY u.name, p.name;

-- 2. Assign user to project
-- INSERT INTO user_projects (user_id, project_id, status) VALUES (?, ?, 'active');

-- 3. Remove user from project
-- DELETE FROM user_projects WHERE user_id = ? AND project_id = ?;

-- 4. Get projects for a specific user
-- SELECT p.* FROM projects p 
-- JOIN user_projects up ON p.id = up.project_id 
-- WHERE up.user_id = ? AND up.status = 'active';

-- 5. Get users assigned to a specific project
-- SELECT u.* FROM users u 
-- JOIN user_projects up ON u.id = up.user_id 
-- WHERE up.project_id = ? AND up.status = 'active';

-- 6. Update assignment status
-- UPDATE user_projects SET status = ? WHERE user_id = ? AND project_id = ?;

-- 7. Bulk assign users to project
-- INSERT INTO user_projects (user_id, project_id, status) 
-- SELECT id, ?, 'active' FROM users WHERE role = 'user' AND status = 'active';

-- 8. View attendance with project information (safe version)
SELECT 
    a.id,
    u.name as user_name,
    COALESCE(p.name, 'No Project') as project_name,
    a.check_in,
    a.check_out,
    COALESCE(a.location_verified, 0) as location_verified,
    COALESCE(TIMESTAMPDIFF(HOUR, a.check_in, a.check_out), 0) as hours_worked
FROM attendance a
JOIN users u ON a.user_id = u.id
LEFT JOIN projects p ON a.project_id = p.id
WHERE DATE(a.check_in) = CURDATE()
ORDER BY a.check_in DESC;

-- 9. Service history report
SELECT 
    sh.service_date,
    u.name as user_name,
    p.name as project_name,
    sh.start_time,
    sh.end_time,
    sh.hours_worked,
    sh.status
FROM service_history sh
JOIN users u ON sh.user_id = u.id
JOIN projects p ON sh.project_id = p.id
WHERE sh.service_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE()
ORDER BY sh.service_date DESC, sh.start_time DESC;