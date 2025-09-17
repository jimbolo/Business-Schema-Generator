<?php 
    // Set schemas path
    $schemas_path = __DIR__ . '/schemas/';
    // Check if file exists and include it only once
    if (file_exists($schemas_path . 'store-schema.php')) {
        include_once $schemas_path . 'store-schema.php'; 
    } else {
        error_log("Error: store-schema.php not found in $schemas_path");
    }

?>
