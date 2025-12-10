# 🔍 Ergon Task Management - Audit & Testing Guide

## Quick Start

### 1. Install Dependencies
```bash
composer install
```

### 2. Run Complete Audit
```bash
# Windows
audit.bat

# Or manually
composer audit
```

## Individual Tools

### 🧪 Testing (PHPUnit)
```bash
composer test              # All tests
composer test-unit         # Unit tests only
composer test-integration  # Integration tests
```

### 🔍 Static Analysis
```bash
composer audit-code        # PHPStan analysis
composer audit-psalm       # Psalm analysis
```

### 🔒 Security Audit
```bash
composer audit-security    # Enlightn security scan
php security-audit.php     # Custom security checks
```

### 📏 Code Style
```bash
composer audit-style       # Check code style
composer fix-style         # Auto-fix style issues
```

## Audit Schedule

### Daily (Automated)
- Run `composer test` before commits
- Quick security scan with `php security-audit.php`

### Weekly
- Full audit with `audit.bat`
- Review and fix any issues found

### Before Production
- Complete audit suite
- External security scan (optional)
- Performance testing

## Security Checklist

✅ **Input Validation**
- All user inputs sanitized
- SQL injection prevention
- XSS protection

✅ **Authentication & Authorization**
- Secure session management
- Role-based access control
- Password security

✅ **File Security**
- Safe file uploads
- Directory traversal prevention
- Secure file permissions

✅ **Database Security**
- Prepared statements
- Connection security
- Data encryption

## Tool Configuration

- **PHPStan**: `phpstan.neon` (Level 5 analysis)
- **Psalm**: `psalm.xml` (Type checking)
- **CodeSniffer**: `phpcs.xml` (PSR-12 standards)
- **PHPUnit**: `phpunit.xml` (Test configuration)

## Compliance Notes

This audit setup helps maintain:
- **Code Quality**: Consistent, maintainable code
- **Security**: Protection against common vulnerabilities
- **Performance**: Optimized code patterns
- **Standards**: PSR-12 compliance for professional code

## Troubleshooting

### Common Issues
1. **Memory Limit**: Increase PHP memory limit for large scans
2. **Path Issues**: Ensure all paths in config files are correct
3. **Dependencies**: Run `composer install` if tools are missing

### Getting Help
- Check tool documentation for specific error messages
- Review configuration files for proper setup
- Ensure PHP version compatibility (>=8.0)