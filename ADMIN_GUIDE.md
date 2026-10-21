# Mowinton Consulting — Administrator & Staff Operations Manual

This guide describes the operation, roles, security controls, and day-to-day workflow for the Mowinton Admin System (`/admin/`).

---

## 1. Accessing the Admin System

- **Login Endpoint:** `http://127.0.0.1:8000/admin/login.php`
- **Dashboard Overview:** `http://127.0.0.1:8000/admin/`

---

## 2. Roles & Permissions (RBAC)

The system enforces role-based access control with two tiers:

| Feature / Action | Super Admin (`super_admin`) | Staff (`staff`) |
| :--- | :---: | :---: |
| **View Dashboard Overview** | Yes | Yes |
| **Review Contact Inquiries** | Yes | Yes |
| **Mark Inquiries Read / Resolved** | Yes | Yes |
| **Delete Contact Inquiries** | Yes | **Blocked (403)** |
| **Review Candidate Applications** | Yes | Yes |
| **Stream Download Resumes** | Yes | Yes |
| **Update Hiring Status** | Yes | Yes |
| **Add Internal Reviewer Notes** | Yes | Yes |
| **Delete Candidate Records** | Yes | **Blocked (403)** |
| **Create New Staff Accounts** | Yes | **Blocked (403)** |
| **Manage System Settings / SMTP** | Yes | **Blocked (403)** |
| **Inspect Security Audit Logs** | Yes | **Blocked (403)** |

---

## 3. Account Provisioning (CLI)

To create or reset an administrator account without exposing public registration forms, run:

```bash
php scripts/create_admin.php
```

The script will prompt for:
1. **Username:** 3–30 alphanumeric characters, underscores, or hyphens.
2. **Email:** Valid email address.
3. **Full Name:** Reviewer display name.
4. **Role:** `super_admin` (1) or `staff` (2).
5. **Password:** Minimum 8 characters.

> [!IMPORTANT]
> **Security Best Practice:** Always enter passwords interactively at the command prompt. Entering passwords via command-line flags exposes sensitive credentials in your shell history (`.bash_history`, PowerShell history) and process listings.

Non-password flags can be pre-filled if desired, with the password prompted securely:
```bash
php scripts/create_admin.php --username=hr_lead --email=hr@mowinton.com --role=staff --name="HR Lead"
```

---

## 4. Hiring Workflow & Status Progression

Candidate applications progress through the following states in `/admin/application-details.php?id=X`:

1. `NEW` — Default state upon applicant submission.
2. `UNDER REVIEW` — Application acknowledged by staff; initial vetting in progress.
3. `SHORTLISTED` — Candidate meets job requirements and is recommended for interview.
4. `INTERVIEW` — Interview scheduled with hiring team.
5. `ACCEPTED` — Formal offer extended and accepted.
6. `REJECTED` — Candidate unsuited for current opening.

### Internal Reviewer Notes
Staff and Super Admins can append timestamped comments to any candidate profile. Notes record the author's username and cannot be viewed by applicants.

---

## 5. Resume Download & File Security

- Candidate resumes are stored in `uploads/resumes/`.
- Direct web access to `http://127.0.0.1:8000/uploads/resumes/` returns **403 Forbidden**.
- Downloads must be initiated via `/admin/download-resume.php?id=X`.
- The streaming endpoint verifies administrator credentials, prevents directory traversal attacks, and logs the download event in `audit_logs`.
