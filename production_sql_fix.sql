-- Production SQL Fix for Company Owner Creation
-- Run these commands in your production database

-- Step 1: Update role column to support company_owner
ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'owner', 'company_owner', 'system_admin') DEFAULT 'user';

-- Step 2: Fix gender column to prevent data truncation
ALTER TABLE users MODIFY COLUMN gender ENUM('male', 'female', 'other') NULL DEFAULT NULL;

-- Step 3: Add missing columns (ignore errors if columns already exist)
ALTER TABLE users ADD COLUMN employee_id VARCHAR(20) UNIQUE;
ALTER TABLE users ADD COLUMN phone VARCHAR(20);
ALTER TABLE users ADD COLUMN department_id INT DEFAULT NULL;
ALTER TABLE users ADD COLUMN designation VARCHAR(255);
ALTER TABLE users ADD COLUMN joining_date DATE;
ALTER TABLE users ADD COLUMN salary DECIMAL(10,2);
ALTER TABLE users ADD COLUMN date_of_birth DATE;
ALTER TABLE users ADD COLUMN address TEXT;
ALTER TABLE users ADD COLUMN emergency_contact VARCHAR(255);
ALTER TABLE users ADD COLUMN temp_password VARCHAR(255);
ALTER TABLE users ADD COLUMN is_first_login BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN password_reset_required BOOLEAN DEFAULT FALSE;

-- Test query (optional - run to verify company_owner role works)
-- SELECT 'company_owner' as test_role;