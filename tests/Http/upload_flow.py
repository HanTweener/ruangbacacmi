"""Real multipart HTTP regression. Run only against the isolated upload-http profile."""
import hashlib
import http.cookiejar
import json
import re
import secrets
import sys
import urllib.parse
import urllib.request
from pathlib import Path

base = 'http://127.0.0.1:8011'
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))


def get(path):
    return opener.open(base + path, timeout=15)


def token(html):
    return re.search(r'name="_token" value="([^"]+)"', html).group(1)


probe = json.loads(get('/__local_upload_probe_cmi.php').read())
assert probe['isolated_profile'], 'Refusing writes outside the isolated upload-http profile'
assert probe['TEMP_present'] and probe['TMP_present'] and probe['temp_writable']
html = get('/register').read().decode()
password = secrets.token_urlsafe(24) + '12a'
registration = urllib.parse.urlencode({
    '_token': token(html), 'name': 'Synthetic HTTP Author',
    'email': 'http-upload-check@example.test',
    'password': password, 'password_confirmation': password,
}).encode()
response = opener.open(base + '/register', registration, timeout=15)
assert response.geturl() == base + '/dashboard'
html = get('/articles/create').read().decode()
csrf = token(html)
image_bytes = Path(sys.argv[1]).read_bytes()
boundary = '----SyntheticUpload' + secrets.token_hex(16)
parts = []
fields = {
    '_token': csrf, 'judul': 'Synthetic HTTP multipart article',
    'isi': 'Synthetic content to verify the complete multipart upload and private image response.',
    'kategori': 'Kesehatan', 'status': '0',
}
for key, value in fields.items():
    parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode())
parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="gambar"; filename="synthetic.jpeg"\r\nContent-Type: image/jpeg\r\n\r\n'.encode() + image_bytes + b'\r\n')
parts.append(f'--{boundary}--\r\n'.encode())
request = urllib.request.Request(base + '/articles', b''.join(parts), {
    'Content-Type': f'multipart/form-data; boundary={boundary}',
})
response = opener.open(request, timeout=15)
html = response.read().decode()
assert response.geturl() == base + '/articles', 'Upload did not redirect to saved articles'
assert 'Artikel berhasil disimpan.' in html, 'Missing upload success feedback'
slug = re.search(r'/articles/(synthetic-http-multipart-article-[a-f0-9-]+)', html).group(1)
image_response = get('/artikel/' + slug + '/gambar')
assert image_response.status == 200
assert image_response.headers.get_content_type() == 'image/jpeg'
assert hashlib.sha256(image_response.read()).digest() == hashlib.sha256(image_bytes).digest()
print(json.dumps({'registration': 'PASS', 'multipart_upload': 'PASS', 'article_save': 'PASS', 'private_image_response': 'PASS', 'image_bytes_match': 'PASS'}))
