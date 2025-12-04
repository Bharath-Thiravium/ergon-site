-- Project-based attendance system updates

-- 1. Create user_projects table for project assignments
CREATE TABLE IF NOT EXISTS user_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_project (user_id, project_id)
);

-- 2. Create service_history table for project-based work tracking
CREATE TABLE IF NOT EXISTS service_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    attendance_id INT NULL,
    service_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    hours_worked DECIMAL(4,2) DEFAULT 0,
    location_lat DECIMAL(10, 8) NULL,
    location_lng DECIMAL(11, 8) NULL,
    notes TEXT,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (attendance_id) REFERENCES attendance(id) ON DELETE SET NULL,
    INDEX idx_user_project_date (user_id, project_id, service_date)
);

-- 4. Sample data for testing
INSERT IGNORE INTO user_projects (user_id, project_id, status) 
SELECT u.id, p.id, 'active' 
FROM users u 
CROSS JOIN projects p 
WHERE u.role IN ('user', 'admin') 
AND p.status = 'active' 
LIMIT 10;

-- 5. Update existing attendance records with project assignments
UPDATE attendance a 
JOIN user_projects up ON a.user_id = up.user_id 
SET a.project_id = up.project_id 
WHERE a.project_id IS NULL 
AND up.status = 'active';