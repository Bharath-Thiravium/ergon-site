<?php
session_start();
echo "<h2>Company Prefix Debug Script</h2>";

// Check session
echo "<h3>Session Info:</h3>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
echo "Role: " . ($_SESSION['role'] ?? 'Not set') . "<br>";
echo "User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "<br>";

// Check if company_owner
$isCompanyOwner = ($_SESSION['role'] ?? '') === 'company_owner';
echo "<br><strong>Is Company Owner: " . ($isCompanyOwner ? 'YES' : 'NO') . "</strong><br>";

// Check dashboard.php logic
echo "<h3>Dashboard Logic Check:</h3>";
if ($isCompanyOwner) {
    echo "✅ Should show hidden input field<br>";
    echo "✅ Should NOT show page-actions-section<br>";
} else {
    echo "❌ Should show visible input field<br>";
    echo "❌ Should show page-actions-section<br>";
}

// Test the condition from dashboard.php
echo "<h3>PHP Condition Test:</h3>";
$condition = ($_SESSION['role'] ?? '') !== 'company_owner';
echo "Condition (\$_SESSION['role'] !== 'company_owner'): " . ($condition ? 'TRUE' : 'FALSE') . "<br>";
echo "This means: " . ($condition ? 'Show visible input' : 'Show hidden input') . "<br>";

// Show what should be rendered
echo "<h3>Expected HTML:</h3>";
if ($isCompanyOwner) {
    echo "<pre>&lt;input type=\"hidden\" id=\"companyPrefix\" value=\"BKGE\"&gt;</pre>";
} else {
    echo "<pre>&lt;input type=\"text\" id=\"companyPrefix\" class=\"form-control\" placeholder=\"Prefix (SE, BK)\"&gt;</pre>";
}

// JavaScript fix
echo "<h3>JavaScript Fix:</h3>";
echo "<script>
document.addEventListener('DOMContentLoaded', () => {
    const prefixInput = document.getElementById('companyPrefix');
    const isCompanyOwner = " . ($isCompanyOwner ? 'true' : 'false') . ";
    
    console.log('Debug: Is Company Owner:', isCompanyOwner);
    console.log('Debug: Input element:', prefixInput);
    console.log('Debug: Input type:', prefixInput?.type);
    
    if (isCompanyOwner && prefixInput) {
        // Force hide the input for company_owner
        prefixInput.style.display = 'none';
        prefixInput.type = 'hidden';
        prefixInput.value = 'BKGE';
        
        // Hide the entire filter controls section
        const filterControls = prefixInput.closest('.filter-controls');
        if (filterControls) {
            filterControls.style.display = 'none';
        }
        
        // Hide the page actions section
        const pageActions = document.querySelector('.page-actions-section');
        if (pageActions) {
            pageActions.style.display = 'none';
        }
        
        console.log('Debug: Hidden input field for company_owner');
    }
});
</script>";

echo "<br><a href='/ergon-site/finance'>← Back to Finance Dashboard</a>";
?>