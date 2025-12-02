<?php
/**
 * Module Configuration
 * Defines available modules and their subscription requirements
 */

return [
    'basic_modules' => [
        'attendance',
        'leaves', 
        'advances',
        'expenses',
        'dashboard'
    ],
    
    'premium_modules' => [
        'tasks',
        'projects',
        'reports',
        'users',
        'departments',
        'notifications',
        'finance',
        'followups',
        'gamification',
        'analytics',
        'system_admin'
    ],
    
    'module_labels' => [
        'attendance' => 'Attendance Management',
        'leaves' => 'Leave Management',
        'advances' => 'Advance Requests',
        'expenses' => 'Expense Management',
        'dashboard' => 'Dashboard',
        'tasks' => 'Task Management',
        'projects' => 'Project Management',
        'reports' => 'Reports & Analytics',
        'users' => 'User Management',
        'departments' => 'Department Management',
        'notifications' => 'Notifications',
        'finance' => 'Finance Module',
        'followups' => 'Follow-ups',
        'gamification' => 'Gamification',
        'analytics' => 'Advanced Analytics',
        'system_admin' => 'System Administration'
    ]
];