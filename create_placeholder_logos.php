<?php
// Script to create placeholder logo images if they don't exist
echo "<!DOCTYPE html>";
echo "<html><head><title>Create Placeholder Logos</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
</style></head><body>";

echo "<h1>🖼️ Create Placeholder Logos</h1>";
echo "<p>This script creates placeholder logo images for the print residents page.</p>";

// Check if GD extension is available
if (!extension_loaded('gd')) {
    echo "<div class='section'>";
    echo "<p class='error'>❌ GD extension is not available. Cannot create placeholder images.</p>";
    echo "<p>Please install the GD extension or manually add logo files:</p>";
    echo "<ul>";
    echo "<li><code>assets/images/barangay-logo.png</code></li>";
    echo "<li><code>assets/images/municipality-logo.png</code></li>";
    echo "</ul>";
    echo "</div>";
    echo "</body></html>";
    exit;
}

echo "<div class='section'>";
echo "<h2>📁 Directory Check</h2>";

// Ensure the images directory exists
$images_dir = 'assets/images';
if (!is_dir($images_dir)) {
    if (mkdir($images_dir, 0755, true)) {
        echo "<p class='success'>✅ Created directory: $images_dir</p>";
    } else {
        echo "<p class='error'>❌ Failed to create directory: $images_dir</p>";
        echo "</body></html>";
        exit;
    }
} else {
    echo "<p class='success'>✅ Directory exists: $images_dir</p>";
}
echo "</div>";

// Function to create a placeholder logo
function createPlaceholderLogo($filename, $text, $color) {
    $width = 200;
    $height = 200;
    
    // Create image
    $image = imagecreatetruecolor($width, $height);
    
    // Colors
    $background = imagecolorallocate($image, 255, 255, 255); // White
    $border = imagecolorallocate($image, 200, 200, 200); // Light gray
    $text_color = imagecolorallocate($image, $color[0], $color[1], $color[2]);
    
    // Fill background
    imagefill($image, 0, 0, $background);
    
    // Draw border circle
    $center_x = $width / 2;
    $center_y = $height / 2;
    $radius = min($width, $height) / 2 - 10;
    
    // Draw filled circle
    imagefilledellipse($image, $center_x, $center_y, $radius * 2, $radius * 2, $border);
    
    // Draw text
    $font_size = 5;
    $text_lines = explode('\n', $text);
    $line_height = 20;
    $total_height = count($text_lines) * $line_height;
    $start_y = $center_y - ($total_height / 2);
    
    foreach ($text_lines as $i => $line) {
        $text_width = imagefontwidth($font_size) * strlen($line);
        $text_x = $center_x - ($text_width / 2);
        $text_y = $start_y + ($i * $line_height);
        imagestring($image, $font_size, $text_x, $text_y, $line, $text_color);
    }
    
    // Save image
    $result = imagepng($image, $filename);
    imagedestroy($image);
    
    return $result;
}

echo "<div class='section'>";
echo "<h2>🎨 Creating Placeholder Logos</h2>";

// Create barangay logo
$barangay_logo = 'assets/images/barangay-logo.png';
if (!file_exists($barangay_logo)) {
    if (createPlaceholderLogo($barangay_logo, "BARANGAY\nCENTRAL GLAD\nLOGO", [52, 152, 219])) {
        echo "<p class='success'>✅ Created barangay placeholder logo: $barangay_logo</p>";
    } else {
        echo "<p class='error'>❌ Failed to create barangay logo</p>";
    }
} else {
    echo "<p class='info'>ℹ Barangay logo already exists: $barangay_logo</p>";
}

// Create municipality logo
$municipality_logo = 'assets/images/municipality-logo.png';
if (!file_exists($municipality_logo)) {
    if (createPlaceholderLogo($municipality_logo, "MUNICIPALITY\nLOGO", [46, 125, 50])) {
        echo "<p class='success'>✅ Created municipality placeholder logo: $municipality_logo</p>";
    } else {
        echo "<p class='error'>❌ Failed to create municipality logo</p>";
    }
} else {
    echo "<p class='info'>ℹ Municipality logo already exists: $municipality_logo</p>";
}

echo "</div>";

echo "<div class='section'>";
echo "<h2>🧪 Test the Print Page</h2>";
echo "<p>Now that placeholder logos are created, test the print page:</p>";
echo "<a href='print_puroks.php' target='_blank' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>📄 Test Print Residents Page</a>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>📝 Next Steps</h2>";
echo "<h3>To use actual logos:</h3>";
echo "<ol>";
echo "<li>Replace <code>assets/images/barangay-logo.png</code> with the actual barangay logo</li>";
echo "<li>Replace <code>assets/images/municipality-logo.png</code> with the actual municipality logo</li>";
echo "<li>Ensure logos are at least 200x200 pixels for best quality</li>";
echo "<li>Use PNG format with transparent background if possible</li>";
echo "</ol>";

echo "<h3>Logo Requirements:</h3>";
echo "<ul>";
echo "<li><strong>Size:</strong> Minimum 200x200 pixels (square format)</li>";
echo "<li><strong>Format:</strong> PNG, JPG, or GIF</li>";
echo "<li><strong>Background:</strong> Transparent PNG preferred</li>";
echo "<li><strong>Quality:</strong> High resolution for clear printing</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>
