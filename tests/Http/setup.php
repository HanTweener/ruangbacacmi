<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
$root = dirname(__DIR__, 2);
$profile = $root.'/.env.upload-http';
$database = $root.'/.runtime/upload-http.sqlite';
if (file_exists($profile) || file_exists($database) || file_exists($root.'/bootstrap/cache/config.php') || file_exists($root.'/public/__local_upload_probe_cmi.php')) {
    throw new RuntimeException('Refusing to overwrite an existing HTTP test profile/database.');
}
$storage = $root.'/.runtime/upload-http-storage';
foreach (['app/private', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $directory) {
    mkdir($storage.'/'.$directory, 0777, true);
}
touch($database);
$settings = [
    'APP_NAME' => 'Synthetic Upload Check', 'APP_ENV' => 'upload-http',
    'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)), 'APP_DEBUG' => 'false',
    'APP_URL' => 'http://127.0.0.1:8011', 'APP_LOCALE' => 'id',
    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => str_replace('\\', '/', $database),
    'SESSION_DRIVER' => 'database', 'SESSION_ENCRYPT' => 'true',
    'CACHE_STORE' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array',
    'BCRYPT_ROUNDS' => '4', 'LARAVEL_STORAGE_PATH' => str_replace('\\', '/', $storage),
];
$lines = [];
foreach ($settings as $name => $value) {
    $lines[] = $name.'='.json_encode($value, JSON_UNESCAPED_SLASHES);
}
file_put_contents($profile, implode(PHP_EOL, $lines).PHP_EOL);
$image = imagecreatetruecolor(32, 32);
imagejpeg($image, $root.'/.runtime/upload-http-synthetic.jpeg');
copy(__DIR__.'/upload_probe.php', $root.'/public/__local_upload_probe_cmi.php');
echo "Isolated HTTP profile created (no app data or photo used).\n";
