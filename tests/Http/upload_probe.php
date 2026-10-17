<?php

// Test fixture: copy temporarily to public only for explicit loopback HTTP QA.
if (! in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit;
}
header('Content-Type: application/json');
$file = $_FILES['gambar'] ?? null;
echo json_encode([
    'isolated_profile' => getenv('APP_ENV') === 'upload-http',
    'error' => $file['error'] ?? null,
    'bytes' => $file['size'] ?? null,
    'uploaded' => $file ? is_uploaded_file($file['tmp_name']) : false,
    'upload_tmp_dir' => ini_get('upload_tmp_dir'),
    'temp' => sys_get_temp_dir(),
    'temp_writable' => is_writable(sys_get_temp_dir()),
    'TEMP_present' => getenv('TEMP') !== false,
    'TMP_present' => getenv('TMP') !== false,
]);
