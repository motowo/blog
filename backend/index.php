<?php
// Temporary file for backend service
echo json_encode([
    'message' => 'Backend service is running',
    'timestamp' => date('Y-m-d H:i:s'),
    'note' => 'Laravel will be installed here'
]);
header('Content-Type: application/json');