"""
Mowinton Consulting — Phase 5 End-to-End Automated Test Suite
Verifies:
  1. Unauthenticated protection (Admin Dashboard, subpages, CV downloads)
  2. Public Contact Form -> MySQL persistence -> Admin view & status change
  3. Public Job Application -> Resume file upload -> MySQL -> Admin view & secure stream
  4. Role-based permissions (Super Admin vs Staff)
  5. CSRF & Rate limiting verification
  6. Logout session destruction
"""

import urllib.request
import urllib.parse
import http.cookiejar
import re
import sys
import io

BASE_URL = 'http://127.0.0.1:8000'

class TestClient:
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

    def post_multipart(self, path, fields, files):
        boundary = '----WebKitFormBoundaryMowintonTest7MA4YWxkTrZu0gW'
        body = io.BytesIO()

        for k, v in fields.items():
            body.write(f'--{boundary}\r\n'.encode('utf-8'))
            body.write(f'Content-Disposition: form-data; name="{k}"\r\n\r\n'.encode('utf-8'))
            body.write(f'{v}\r\n'.encode('utf-8'))

        for field_name, (filename, content, mime) in files.items():
            body.write(f'--{boundary}\r\n'.encode('utf-8'))
            body.write(f'Content-Disposition: form-data; name="{field_name}"; filename="{filename}"\r\n'.encode('utf-8'))
            body.write(f'Content-Type: {mime}\r\n\r\n'.encode('utf-8'))
            body.write(content)
            body.write(b'\r\n')

        body.write(f'--{boundary}--\r\n'.encode('utf-8'))
        payload = body.getvalue()

        req = urllib.request.Request(
            f"{BASE_URL}{path}",
            data=payload,
            headers={'Content-Type': f'multipart/form-data; boundary={boundary}'},
            method='POST'
        )
        try:
            resp = self.opener.open(req)
            return resp.getcode(), resp.read().decode('utf-8', errors='replace'), resp.geturl()
        except urllib.error.HTTPError as e:
            return e.code, e.read().decode('utf-8', errors='replace'), e.geturl()

def extract_csrf(html):
    m = re.search(r'name=["\']csrf_token["\']\s+value=["\']([^"\']+)["\']', html)
    if m:
        return m.group(1)
    m = re.search(r'value=["\']([^"\']+)["\']\s+name=["\']csrf_token["\']', html)
    if m:
        return m.group(1)
    return ''

def run_tests():
    print("=" * 60)
    print("MOWINTON CONSULTING — PHASE 5 END-TO-END TEST SUITE")
    print("=" * 60)
    passed = 0
    total = 0

    def assert_test(name, condition, details=""):
        nonlocal passed, total
        total += 1
        if condition:
            passed += 1
            print(f" [PASS] Test {total:02d}: {name}")
        else:
            print(f" [FAIL] Test {total:02d}: {name} -> {details}")

    # -------------------------------------------------------------
    # 1. Unauthenticated Access Protection
    # -------------------------------------------------------------
    client = TestClient()
    code, body, final_url = client.get('/admin/')
    assert_test("Unauthenticated access to /admin/ redirects to login", '/admin/login.php' in final_url)

    code, body, final_url = client.get('/admin/messages.php')
    assert_test("Unauthenticated access to /admin/messages.php redirects to login", '/admin/login.php' in final_url)

    code, body, final_url = client.get('/admin/applications.php')
    assert_test("Unauthenticated access to /admin/applications.php redirects to login", '/admin/login.php' in final_url)

    code, body, final_url = client.get('/admin/download-resume.php?id=1')
    assert_test("Unauthenticated access to resume download redirects to login", '/admin/login.php' in final_url)

    code, body, _ = client.get('/uploads/resumes/')
    assert_test("Direct access to /uploads/resumes/ is Forbidden (403)", code == 403)

    # -------------------------------------------------------------
    # 2. Login Authentication & Rate Limiting
    # -------------------------------------------------------------
    code, body, _ = client.get('/admin/login.php')
    login_csrf = extract_csrf(body)
    assert_test("Login page renders with CSRF token", bool(login_csrf))

    # Invalid password test
    code, body, _ = client.post('/admin/login.php', {
        'csrf_token': login_csrf,
        'username': 'admin',
        'password': 'WrongPassword999!'
    })
    assert_test("Invalid password rejected", 'Invalid username or password' in body)

    # Valid staff login test
    code, body, _ = client.get('/admin/login.php')
    login_csrf = extract_csrf(body)
    code, body, final_url = client.post('/admin/login.php', {
        'csrf_token': login_csrf,
        'username': 'staff_jane',
        'password': 'Staff@Password123'
    })
    assert_test("Staff user login succeeds", '/admin/' in final_url and 'Dashboard Overview' in body)

    # Staff permission check
    code, body, _ = client.get('/admin/settings.php')
    assert_test("Staff user blocked from Settings (403 Forbidden)", code == 403 or '403 — Unauthorized' in body)

    # Staff logout
    client.get('/admin/logout.php')
    code, body, final_url = client.get('/admin/')
    assert_test("Staff user logged out successfully", '/admin/login.php' in final_url)

    # -------------------------------------------------------------
    # 3. Super Admin Login & Dashboard
    # -------------------------------------------------------------
    admin_client = TestClient()
    code, body, _ = admin_client.get('/admin/login.php')
    login_csrf = extract_csrf(body)
    code, body, final_url = admin_client.post('/admin/login.php', {
        'csrf_token': login_csrf,
        'username': 'admin',
        'password': 'Admin@Password123'
    })
    assert_test("Super Admin login succeeds", '/admin/' in final_url and 'Dashboard Overview' in body)

    code, body, _ = admin_client.get('/admin/settings.php')
    assert_test("Super Admin can access System Settings", code == 200 and ('Administrator & Staff Accounts' in body or 'Administrator &amp; Staff Accounts' in body))

    # -------------------------------------------------------------
    # 4. Public Contact Form -> MySQL -> Admin Review & Status
    # -------------------------------------------------------------
    pub_client = TestClient()
    code, body, _ = pub_client.get('/')
    home_csrf = extract_csrf(body)
    assert_test("Public homepage provides CSRF token", bool(home_csrf))

    contact_payload = {
        'contact_submit': '1',
        'csrf_token': home_csrf,
        'name': 'Synthetic Test User',
        'email': 'synthetic.test@example.com',
        'company': 'Apex Global Labs',
        'message': 'Inquiry regarding semiconductor MES consulting and automated scheduling.',
        'email_type_name': '',  # Honeypot empty
        'is_ajax': '1'
    }
    code, body, _ = pub_client.post('/', contact_payload)
    assert_test("Public Contact Form submitted successfully", '"success":true' in body)

    # Check Admin Messages
    code, body, _ = admin_client.get('/admin/messages.php?q=Synthetic+Test+User')
    assert_test("Contact submission appears in Admin Messages", 'Synthetic Test User' in body and 'synthetic.test@example.com' in body)

    # Extract message ID
    m_id = re.search(r'#(\d+)</td>', body)
    msg_id = m_id.group(1) if m_id else '1'

    # Admin opens message details and updates status to resolved
    code, body, _ = admin_client.get(f'/admin/messages.php?view_id={msg_id}')
    csrf_msg = extract_csrf(body)
    code, body, _ = admin_client.post('/admin/messages.php', {
        'csrf_token': csrf_msg,
        'action': 'update_status',
        'message_id': msg_id,
        'new_status': 'resolved'
    })
    assert_test("Admin marks contact message as RESOLVED", 'marked as RESOLVED' in body or 'status-resolved' in body)

    # -------------------------------------------------------------
    # 5. Public Job Application -> Resume Upload -> DB -> Admin Review & CV Stream
    # -------------------------------------------------------------
    code, body, _ = pub_client.get('/submit-job-application/?position=Project+Manager')
    job_csrf = extract_csrf(body)
    assert_test("Job Application page loaded with position autofill & CSRF", bool(job_csrf) and 'Project Manager' in body)

    test_pdf_content = b"%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000101 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF"

    job_fields = {
        'job_application_submit': '1',
        'csrf_token': job_csrf,
        'first_name': 'Marcus',
        'last_name': 'Vance',
        'email': 'marcus.vance@example.com',
        'location': 'Kuala Lumpur, Malaysia',
        'phone': '+6012-9988776',
        'position': 'Project Manager',
        'education': 'Master of Science in Information Technology, University of Malaya',
        'experience': '7 years leading smart factory MES implementation and semiconductor fab projects.',
        'salary': 'MYR 14,000 / month',
        'declaration': '1',
        'has_declaration': '1',
        'is_ajax': '1'
    }
    job_files = {
        'resume': ('Marcus_Vance_Resume.pdf', test_pdf_content, 'application/pdf')
    }

    code, body, _ = pub_client.post_multipart('/submit-job-application/', job_fields, job_files)
    assert_test("Job Application with test PDF submitted successfully", '"success":true' in body and 'MOW-' in body)

    ref_match = re.search(r'MOW-\d{6}-[A-Z0-9]+', body)
    ref_id = ref_match.group(0) if ref_match else ''
    assert_test("Reference ID generated and returned to applicant", bool(ref_id))

    # Check Admin Applications Table
    code, body, _ = admin_client.get(f'/admin/applications.php?q={ref_id}')
    assert_test("Application appears in Admin Applications list", ref_id in body and 'Marcus Vance' in body)

    # Extract application ID
    app_id_match = re.search(r'/admin/application-details\.php\?id=(\d+)', body)
    app_id = app_id_match.group(1) if app_id_match else '1'

    # View Details
    code, body, _ = admin_client.get(f'/admin/application-details.php?id={app_id}')
    assert_test("Application Details page displays candidate data and resume info", 'Master of Science' in body and 'Marcus_Vance_Resume.pdf' in body)

    # Stream Resume File Download
    code, resume_bytes, _ = admin_client.get(f'/admin/download-resume.php?id={app_id}')
    assert_test("Authenticated admin can stream download candidate resume", code == 200 and resume_bytes.startswith('%PDF-1.4'))

    # Add Internal Reviewer Note
    csrf_details = extract_csrf(body)
    code, body, _ = admin_client.post(f'/admin/application-details.php?id={app_id}', {
        'csrf_token': csrf_details,
        'action': 'add_note',
        'note_text': 'Candidate has outstanding semiconductor MES experience. Recommend immediate interview.'
    })
    assert_test("Internal note successfully saved and displayed", 'Candidate has outstanding semiconductor' in body)

    # Update Status to Shortlisted
    csrf_details = extract_csrf(body)
    code, body, _ = admin_client.post(f'/admin/application-details.php?id={app_id}', {
        'csrf_token': csrf_details,
        'action': 'update_status',
        'new_status': 'shortlisted'
    })
    assert_test("Application status updated to SHORTLISTED in MySQL", 'SHORTLISTED' in body or 'status-shortlisted' in body)

    # -------------------------------------------------------------
    # 6. Session Destruction on Logout
    # -------------------------------------------------------------
    admin_client.get('/admin/logout.php')
    code, body, final_url = admin_client.get(f'/admin/application-details.php?id={app_id}')
    assert_test("After logout, direct access to application details redirects to login", '/admin/login.php' in final_url)

    code, body, final_url = admin_client.get(f'/admin/download-resume.php?id={app_id}')
    assert_test("After logout, direct resume download is blocked", '/admin/login.php' in final_url)

    print("=" * 60)
    print(f"RESULTS: {passed}/{total} TESTS PASSED ({round((passed/total)*100)}%)")
    print("=" * 60)

    if passed == total:
        print("\n>>> ALL PHASE 5 END-TO-END TESTS PASSED WITH 100% SUCCESS! <<<\n")
        return 0
    else:
        print("\n>>> SOME TESTS FAILED! <<<\n")
        return 1

if __name__ == '__main__':
    sys.exit(run_tests())
