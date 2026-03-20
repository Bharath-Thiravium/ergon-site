# Database Connectivity Fix Guide

## Current Status
- ✅ Application code is working correctly
- ✅ URL routing and redirects are fixed
- ❌ Database connection failing on subdomain `aes.athenas.co.in`

## Error Details
```
SQLSTATE[HY000] [1045] Access denied for user 'u494785662_ergon_site'@'localhost' (using password: YES)
```

## Step-by-Step Resolution

### Step 1: Check Hostinger Panel Database Settings

1. **Login to Hostinger hPanel**
   - Go to https://hpanel.hostinger.com
   - Login with your credentials

2. **Navigate to Databases**
   - Click on "Databases" in the left sidebar
   - Look for MySQL databases section

3. **Verify Database Information**
   - Check if database `u494785662_ergon_site` exists
   - Note the correct database name (might be different for subdomain)
   - Check the database user and permissions

4. **Check Subdomain-Specific Settings**
   - Look for any subdomain-specific database configurations
   - Check if `aes.athenas.co.in` has its own database setup

### Step 2: Get Correct Database Credentials

**Main Domain Database:**
- Database Name: `u494785662_ergon_site`
- Username: `u494785662_ergon_site`
- Password: `[CHECK IN HOSTINGER PANEL]`
- Host: `localhost`

**If Subdomain Has Separate Database:**
- Database Name: `[CHECK IN HOSTINGER PANEL]`
- Username: `[CHECK IN HOSTINGER PANEL]`
- Password: `[CHECK IN HOSTINGER PANEL]`
- Host: `localhost` or `[SPECIFIC HOST]`

### Step 3: Update .env.production File

Once you have the correct credentials, update the file:

```bash
# MySQL Database Configuration
DB_HOST=localhost
DB_NAME=[CORRECT_DATABASE_NAME]
DB_USER=[CORRECT_USERNAME]
DB_PASS=[CORRECT_PASSWORD]

# PostgreSQL Configuration (keep existing)
SAP_PG_HOST=72.60.218.167
SAP_PG_PORT=5432
SAP_PG_DB=modernsap
SAP_PG_USER=postgres
SAP_PG_PASS=mango
```

### Step 4: Test the Connection

After updating credentials, run these diagnostic scripts:

```bash
# Test basic database connection
php test_database_connection.php

# Test different credential combinations
php diagnose_database_credentials.php

# Test admin dashboard functionality
php comprehensive_debug.php
```

### Step 5: Common Hostinger Database Scenarios

**Scenario A: Shared Database**
- Both main domain and subdomain use the same database
- Update subdomain to use main domain's database credentials

**Scenario B: Separate Databases**
- Subdomain has its own database
- Create/import database structure for subdomain
- Use subdomain-specific credentials

**Scenario C: Database Server Differences**
- Subdomain might be on a different server
- Host might be different (not `localhost`)
- Check for server-specific database hosts

### Step 6: Alternative Solutions

**If Database Doesn't Exist:**
1. Create new database in Hostinger panel
2. Import SQL structure from main domain
3. Update credentials in `.env.production`

**If User Permissions Issue:**
1. Check user permissions in Hostinger panel
2. Ensure user has access to the database
3. Grant necessary privileges (SELECT, INSERT, UPDATE, DELETE)

**If Host is Different:**
1. Check if subdomain uses different database host
2. Update `DB_HOST` in `.env.production`
3. Common alternatives: `localhost`, `mysql.hostinger.com`, or specific IP

## Quick Test Commands

After updating credentials, test immediately:

```bash
# Quick connection test
php -r "
try {
    \$pdo = new PDO('mysql:host=localhost;dbname=YOUR_DB_NAME', 'YOUR_USERNAME', 'YOUR_PASSWORD');
    echo 'Connection successful!';
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage();
}
"
```

## Expected Result

Once correct credentials are set:
- ✅ `https://aes.athenas.co.in/ergon-site/admin/dashboard` loads successfully
- ✅ No more 500 Internal Server Error
- ✅ Admin dashboard displays properly
- ✅ All database-dependent features work

## Need Help?

If you encounter issues:
1. Share the exact database information from Hostinger panel
2. Run the diagnostic scripts and share results
3. Check Hostinger documentation for subdomain database setup

The application is ready - just needs the correct database connection!