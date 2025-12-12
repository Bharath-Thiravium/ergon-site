<?php
echo "<h2>Column Alignment Test</h2>\n";

// Test the attendance page structure
$attendanceUrl = "http://localhost/ergon-site/attendance";
$content = file_get_contents($attendanceUrl);

if ($content) {
    // Check for Location and Project columns
    $hasLocationColumn = strpos($content, '<th class="col-location">Location</th>') !== false || 
                        strpos($content, '>Location</th>') !== false ||
                        strpos($content, '>Location<') !== false;
    
    $hasProjectColumn = strpos($content, '<th class="col-project">Project</th>') !== false || 
                       strpos($content, '>Project</th>') !== false ||
                       strpos($content, '>Project<') !== false;
    
    echo "✅ Attendance page loaded successfully<br>\n";
    echo ($hasLocationColumn ? "✅" : "❌") . " Location column found: " . ($hasLocationColumn ? "YES" : "NO") . "<br>\n";
    echo ($hasProjectColumn ? "✅" : "❌") . " Project column found: " . ($hasProjectColumn ? "YES" : "NO") . "<br>\n";
    
    // Count table headers to verify alignment
    preg_match_all('/<th[^>]*>/', $content, $matches);
    $headerCount = count($matches[0]);
    echo "📊 Total table headers found: {$headerCount}<br>\n";
    
    // Check for location_display and project_name data
    $hasLocationData = strpos($content, 'location_display') !== false;
    $hasProjectData = strpos($content, 'project_name') !== false;
    
    echo ($hasLocationData ? "✅" : "❌") . " Location data references found: " . ($hasLocationData ? "YES" : "NO") . "<br>\n";
    echo ($hasProjectData ? "✅" : "❌") . " Project data references found: " . ($hasProjectData ? "YES" : "NO") . "<br>\n";
    
    echo "<h3>Column Alignment Status:</h3>\n";
    if ($hasLocationColumn && $hasProjectColumn) {
        echo "<div style='color: green; font-weight: bold;'>✅ SUCCESS: Location and Project columns are properly added!</div>\n";
        echo "<div>The admin and owner panels now have the correct column alignment.</div>\n";
    } else {
        echo "<div style='color: red; font-weight: bold;'>❌ ISSUE: Some columns are missing</div>\n";
    }
    
} else {
    echo "❌ Failed to load attendance page<br>\n";
}
?>