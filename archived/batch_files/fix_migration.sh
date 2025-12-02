#!/bin/bash
# Comprehensive fix script for ergon to ergon-site migration

echo "🔧 Starting comprehensive ergon to ergon-site migration..."

# Fix all PHP files
echo "📝 Fixing PHP files..."
find . -name "*.php" -not -path "./vendor/*" -not -path "./node_modules/*" -exec sed -i 's|/ergon/|/ergon-site/|g' {} +

# Fix all JavaScript files
echo "🔧 Fixing JavaScript files..."
find . -name "*.js" -not -path "./vendor/*" -not -path "./node_modules/*" -exec sed -i 's|/ergon/|/ergon-site/|g' {} +

# Fix all CSS files
echo "🎨 Fixing CSS files..."
find . -name "*.css" -not -path "./vendor/*" -not -path "./node_modules/*" -exec sed -i 's|/ergon/|/ergon-site/|g' {} +

# Fix specific config files
echo "⚙️ Fixing config files..."
if [ -f "app/config/constants.php" ]; then
    sed -i "s|'https://athenas.co.in/ergon'|'https://athenas.co.in/ergon-site'|g" app/config/constants.php
    sed -i "s|'http://localhost/ergon'|'http://localhost/ergon-site'|g" app/config/constants.php
fi

if [ -f "app/config/environment.php" ]; then
    sed -i 's|/ergon|/ergon-site|g' app/config/environment.php
fi

echo "✅ Migration complete! Summary:"
echo "- Updated all PHP files"
echo "- Updated all JavaScript files" 
echo "- Updated all CSS files"
echo "- Updated configuration files"
echo ""
echo "🔍 Please test the application and check for any remaining issues."