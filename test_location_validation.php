<?php
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/helpers/LocationHelper.php';

// Test location validation
function testLocationValidation() {
    try {
        $db = Database::connect();
        
        echo "=== LOCATION VALIDATION TEST ===\n\n";
        
        // Get current settings
        $settings = LocationHelper::getOfficeSettings($db);
        echo "Current Office Settings:\n";
        echo "- Location: ({$settings['base_location_lat']}, {$settings['base_location_lng']})\n";
        echo "- Radius: {$settings['attendance_radius']}m\n";
        echo "- Title: " . ($settings['location_title'] ?? 'Main Office') . "\n\n";
        
        // Get all allowed locations
        $locations = LocationHelper::getAllowedLocations($db);
        echo "All Allowed Locations (" . count($locations) . "):\n";
        foreach ($locations as $i => $location) {
            echo ($i + 1) . ". {$location['name']} ({$location['type']})\n";
            echo "   Coordinates: ({$location['lat']}, {$location['lng']})\n";
            echo "   Radius: {$location['radius']}m\n\n";
        }
        
        // Test with sample coordinates (you can modify these)
        $testCoordinates = [
            ['lat' => 28.6139, 'lng' => 77.2090, 'name' => 'Delhi Center'],
            ['lat' => 19.0760, 'lng' => 72.8777, 'name' => 'Mumbai Center'],
            ['lat' => 0, 'lng' => 0, 'name' => 'Invalid Coordinates']
        ];
        
        echo "=== TESTING SAMPLE COORDINATES ===\n\n";
        foreach ($testCoordinates as $test) {
            echo "Testing: {$test['name']} ({$test['lat']}, {$test['lng']})\n";
            
            $validation = LocationHelper::validateMultipleLocations($test['lat'], $test['lng'], $locations);
            
            if ($validation['allowed']) {
                echo "✅ ALLOWED - Distance: {$validation['distance']}m\n";
                echo "   Location: {$validation['location']['name']}\n";
            } else {
                echo "❌ NOT ALLOWED\n";
                echo "   Error: {$validation['error']}\n";
            }
            echo "\n";
        }
        
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

// Run the test
testLocationValidation();
?>