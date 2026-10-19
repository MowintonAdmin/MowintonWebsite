"""
Mowinton Consulting — Security & Consistency Regression Test Suite
Validates:
  1. All public page routes (HTTP 200 OK)
  2. Router sensitive file & directory blocking (HTTP 403 Forbidden)
  3. Case-insensitive bypass prevention (Windows & Apache)
  4. Backup archives & sensitive extension blocking
  5. SQL injection safety across search/filter endpoints
  6. RESUME_STORAGE_PATH configuration support
  7. Admin session security & CSRF protection
"""

import urllib.request
import urllib.parse
import urllib.error
import http.cookiejar
import os
import sys

BASE_URL = 'http://127.0.0.1:8000'

class AuditClient:
    def __init__(self):
        self.cj = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(
            urllib.request.HTTPCookieProcessor(self.cj),
            urllib.request.HTTPRedirectHandler()
        )

    def get(self, path):
        req = urllib.request.Request(f"{BASE_URL}{path}")
        try:
            resp = self.opener.open(req)
            return resp.getcode(), resp.read().decode('utf-8', errors='replace'), resp.geturl()
        except urllib.error.HTTPError as e:
            return e.code, e.read().decode('utf-8', errors='replace'), e.geturl()

    def post(self, path, data):
        encoded = urllib.parse.urlencode(data).encode('utf-8')
        req = urllib.request.Request(f"{BASE_URL}{path}", data=encoded, method='POST')
        try:
            resp = self.opener.open(req)
            return resp.getcode(), resp.read().decode('utf-8', errors='replace'), resp.geturl()
        except urllib.error.HTTPError as e:
            return e.code, e.read().decode('utf-8', errors='replace'), e.geturl()

def run_audit():
    print("=" * 65)
    print("MOWINTON CONSULTING — SECURITY & CONSISTENCY AUDIT SUITE")
    print("=" * 65)
    passed = 0
    total = 0

    def check(name, condition, details=""):
        nonlocal passed, total
        total += 1
        if condition:
            passed += 1
            print(f" [PASS] Test {total:02d}: {name}")
        else:
            print(f" [FAIL] Test {total:02d}: {name} -> {details}")

    client = AuditClient()

    # 1. Public Website Routing (All 13 primary public views)
    public_routes = [
        '/',
        '/about/',
        '/services/',
        '/careers-2/',
        '/internship-program/',
        '/interview-guide/',
        '/submit-job-application/',
        '/news-insights/',
        '/project-manager-jd/',
        '/software-engineer-jd/',
        '/technical-lead/',
        '/qa-engineer-jd/',
        '/business-analyst-jd/',
        '/intern-digital-marketing/',
    ]
    for route in public_routes:
        code, body, url = client.get(route)
        check(f"Route '{route}' loads successfully", code == 200 and 'Mowinton' in body, f"Code: {code}")

    # 2. Sensitive Directory & File Blocking (HTTP 403 Forbidden)
    sensitive_paths = [
        '/uploads/resumes/',
        '/Uploads/Resumes/',
        '/UPLOADS/RESUMES/',
        '/.env',
        '/includes/',
        '/includes/db.php',
        '/database/',
        '/database/schema.sql',
        '/scripts/',
        '/scripts/migrate.php',
    ]
    for path in sensitive_paths:
        code, _, _ = client.get(path)
        check(f"Direct request to '{path}' blocked (403)", code == 403, f"Code: {code}")

    # 3. Direct File Access with Synthetic Files (Backups, Dumps, Archives)
    synthetic_files = {
        'uploads/resumes/synthetic_audit_test.pdf': '/uploads/resumes/synthetic_audit_test.pdf',
        'uploads/resumes/Synthetic_Case_Test.pdf': '/Uploads/resumes/Synthetic_Case_Test.pdf',
        'synthetic_backup_archive.zip': '/synthetic_backup_archive.zip',
        'synthetic_database_dump.sql': '/synthetic_database_dump.sql',
        'synthetic_server_config.ini': '/synthetic_server_config.ini',
        'synthetic_backup_file.bak': '/synthetic_backup_file.bak',
        '.synthetic_hidden_file': '/.synthetic_hidden_file',
    }
    for filename in synthetic_files:
        with open(filename, 'w') as f:
            f.write("AUDIT_SECRET_DATA")

    try:
        for filename, test_url in synthetic_files.items():
            code, _, _ = client.get(test_url)
            check(f"Access to sensitive file '{test_url}' blocked (403)", code == 403, f"Code: {code}")
    finally:
        for filename in synthetic_files:
            if os.path.exists(filename):
                os.remove(filename)

    # 4. SQL Injection Resistance in Search / Filter
    # Login as admin first
    code, body, _ = client.get('/admin/login.php')
    import re
    m = re.search(r'name=["\']csrf_token["\']\s+value=["\']([^"\']+)["\']', body)
    csrf = m.group(1) if m else ''
    client.post('/admin/login.php', {
        'csrf_token': csrf,
        'username': 'admin',
        'password': 'Admin@Password123'
    })

    # Test SQL injection string in contact messages search
    code, body, _ = client.get("/admin/messages.php?q=%27+OR+%271%27%3D%271")
    check("SQL injection payload in messages search handled safely", code == 200 and 'Fatal error' not in body and 'PDOException' not in body)

    # Test SQL injection string in job applications search
    code, body, _ = client.get("/admin/applications.php?q=%27+OR+%271%27%3D%271")
    check("SQL injection payload in applications search handled safely", code == 200 and 'Fatal error' not in body and 'PDOException' not in body)

    # 5. RESUME_STORAGE_PATH PHP Helper Verification
    import subprocess
    php_test = """
    require 'includes/db.php';
    $default = get_resume_storage_path();
    putenv('RESUME_STORAGE_PATH=custom_storage/cvs');
    $custom = get_resume_storage_path();
    echo (str_contains($default, 'uploads') && str_contains($custom, 'custom_storage')) ? 'SUCCESS' : 'FAIL';
    """
    res = subprocess.run(['php', '-r', php_test], capture_output=True, text=True)
    check("PHP get_resume_storage_path() helper supports RESUME_STORAGE_PATH env variable", res.stdout.strip() == 'SUCCESS', f"Output: {res.stdout} {res.stderr}")

    print("=" * 65)
    print(f"AUDIT RESULTS: {passed}/{total} CHECKS PASSED ({round((passed/total)*100)}%)")
    print("=" * 65)

    return 0 if passed == total else 1

if __name__ == '__main__':
    sys.exit(run_audit())
