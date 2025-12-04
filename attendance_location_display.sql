-- Columns already exist, test the query:
SELECT a.*, u.name as user_name, 
       a.location_type, a.location_title, a.location_radius,
       COALESCE(d.name, 'Not Assigned') as department 
FROM attendance a 
LEFT JOIN users u ON a.user_id = u.id 
LEFT JOIN departments d ON u.department_id = d.id 
WHERE a.user_id = 1 
ORDER BY a.check_in DESC;