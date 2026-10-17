# Real HTTP upload regression (local, opt-in)

This exercises real cookies/CSRF, multipart parsing, article persistence and private image retrieval. It uses a dedicated SQLite database/storage and a generated JPEG, never a personal photo. It is additional to PHPUnit: `UploadedFile::fake()` bypasses PHP's actual upload/temp-directory handling.

Requirements: PHP with GD/pdo_sqlite, Python 3, free loopback port 8011. Do not run on a deployed host. The setup refuses existing test profiles/databases, an existing probe or a cached Laravel config.

1. From repository root: `php tests/Http/setup.php`.
2. `php artisan migrate --env=upload-http --no-interaction`.
3. In a separate terminal: `php artisan serve --env=upload-http --host=127.0.0.1 --port=8011 --tries=1 --quiet`. Use the default reload behavior: `--no-reload` would conceal the Windows regression.
4. `python tests/Http/upload_flow.py .runtime/upload-http-synthetic.jpeg`. The client first checks that the target is the isolated profile and its temp environment is writable before any registration/article write.
5. Stop only the isolated test server with Ctrl+C. Remove the temporary `public/__local_upload_probe_cmi.php` and `.env.upload-http`. Preserve or remove only this test's `.runtime/upload-http.sqlite` and `.runtime/upload-http-storage` when no longer needed. Do not delete application storage or database data.

The probe is never a permanent application route. It returns upload error/temp-environment metadata only and rejects remote addresses outside loopback. Never print message bodies, passwords, tokens or cookies in test output.
