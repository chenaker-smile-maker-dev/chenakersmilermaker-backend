import marimo

__generated_with = "0.20.4"
app = marimo.App(width="full", app_title="API Client – ChenAker SmilerMaker")


@app.cell(hide_code=True)
def __(mo):
    mo.md(
        r"""
        # 🦷 ChenAker SmilerMaker – API Client

        Interactive notebook for exploring and testing the backend REST API.

        ---
        """
    )
    return


@app.cell
def __():
    import marimo as mo
    import requests
    import json
    from datetime import datetime
    return datetime, json, mo, requests


@app.cell(hide_code=True)
def __(mo):
    mo.Html("""
    <style>
      /* ── Reset & base ─────────────────────────────────────────── */
      *, *::before, *::after { box-sizing: border-box; }

      body, #root, .marimo {
        font-family: 'Inter', 'Segoe UI', system-ui, sans-serif !important;
        font-size: 15px !important;
        line-height: 1.6 !important;
        color: #1e293b !important;
        background: #f8fafc !important;
      }

      /* ── Typography ───────────────────────────────────────────── */
      h1 { font-size: 2rem !important; font-weight: 700 !important; color: #0f172a !important; margin-bottom: .5rem !important; }
      h2 { font-size: 1.35rem !important; font-weight: 600 !important; color: #1e293b !important; margin: 1.5rem 0 .75rem !important; border-left: 4px solid #3b82f6; padding-left: .6rem; }
      h3 { font-size: 1.1rem  !important; font-weight: 600 !important; color: #334155 !important; margin: 1rem 0 .5rem !important; }
      p, li { font-size: 15px !important; color: #475569 !important; }
      code { font-size: 13px !important; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; }
      hr { border: none; border-top: 1px solid #e2e8f0; margin: 1.5rem 0; }

      /* ── Inputs & textareas ───────────────────────────────────── */
      input[type="text"],
      input[type="email"],
      input[type="password"],
      input[type="number"],
      textarea,
      select {
        font-size: 14px !important;
        padding: 9px 13px !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 8px !important;
        background: #ffffff !important;
        color: #1e293b !important;
        width: 100% !important;
        transition: border-color .15s, box-shadow .15s !important;
        outline: none !important;
      }
      input:focus, textarea:focus, select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,.15) !important;
      }

      /* ── Labels ───────────────────────────────────────────────── */
      label {
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #475569 !important;
        display: block !important;
        margin-bottom: 4px !important;
        text-transform: uppercase !important;
        letter-spacing: .04em !important;
      }

      /* ── Buttons ──────────────────────────────────────────────── */
      button {
        font-size: 14px !important;
        font-weight: 600 !important;
        padding: 9px 20px !important;
        border-radius: 8px !important;
        border: none !important;
        cursor: pointer !important;
        background: #3b82f6 !important;
        color: #ffffff !important;
        transition: background .15s, transform .1s, box-shadow .15s !important;
        box-shadow: 0 1px 3px rgba(0,0,0,.12) !important;
        white-space: nowrap !important;
      }
      button:hover  { background: #2563eb !important; box-shadow: 0 4px 12px rgba(37,99,235,.3) !important; }
      button:active { transform: scale(.97) !important; }

      /* run-button variant */
      button[data-testid="run-button"],
      button.run-button {
        background: #10b981 !important;
      }
      button[data-testid="run-button"]:hover,
      button.run-button:hover {
        background: #059669 !important;
        box-shadow: 0 4px 12px rgba(5,150,105,.3) !important;
      }

      /* ── Dropdown / select ────────────────────────────────────── */
      select {
        appearance: auto !important;
        padding-right: 36px !important;
      }

      /* ── marimo cell output wrapper ───────────────────────────── */
      .output-area, [class*="output"] {
        padding: 8px 0 !important;
      }

      /* ── Code blocks (JSON responses) ────────────────────────── */
      pre, .cm-editor, [class*="CodeMirror"] {
        font-size: 13px !important;
        line-height: 1.55 !important;
        border-radius: 10px !important;
        padding: 14px 16px !important;
        background: #0f172a !important;
        color: #e2e8f0 !important;
        overflow-x: auto !important;
        border: none !important;
      }

      /* ── marimo vstack / hstack spacing ──────────────────────── */
      [class*="vstack"] > * + * { margin-top: 10px !important; }
      [class*="hstack"] > * + * { margin-left: 10px !important; }

      /* ── Status badges in responses ──────────────────────────── */
      span[style*="color:green"] { color: #16a34a !important; font-weight: 700; }
      span[style*="color:red"]   { color: #dc2626 !important; font-weight: 700; }

      /* ── Scrollbar ────────────────────────────────────────────── */
      ::-webkit-scrollbar { width: 6px; height: 6px; }
      ::-webkit-scrollbar-track { background: transparent; }
      ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
    </style>
    """)
    return


# ─── Configuration ───────────────────────────────────────────────────────────

@app.cell(hide_code=True)
def __(mo):
    mo.md("## ⚙️ Configuration")
    return


@app.cell
def __(mo):
    base_url_input = mo.ui.text(
        value="http://localhost:8000",
        label="Base URL",
        placeholder="http://localhost:8000",
        full_width=True,
    )
    base_url_input
    return (base_url_input,)


@app.cell
def __(base_url_input):
    BASE_URL = base_url_input.value.rstrip("/") + "/api/v1"
    print(f"Active base URL: {BASE_URL}")
    return (BASE_URL,)


# ─── Auth token storage ───────────────────────────────────────────────────────

@app.cell
def __(mo):
    token_input = mo.ui.text(
        value="",
        label="🔑 Bearer token (filled automatically on login)",
        placeholder="Paste token here or login below",
        full_width=True,
    )
    token_input
    return (token_input,)


@app.cell
def __(token_input):
    def auth_headers():
        t = token_input.value.strip()
        if t:
            return {"Authorization": f"Bearer {t}", "Accept": "application/json"}
        return {"Accept": "application/json"}
    return (auth_headers,)


# ─── Helper ───────────────────────────────────────────────────────────────────

@app.cell
def __(json, mo):
    def show_response(resp):
        """Pretty-print an HTTP response."""
        color = "green" if resp.ok else "red"
        try:
            body = json.dumps(resp.json(), indent=2, ensure_ascii=False)
        except Exception:
            body = resp.text
        return mo.vstack([
            mo.md(f"**Status:** <span style='color:{color}'>{resp.status_code} {resp.reason}</span>"),
            mo.code(body, language="json"),
        ])
    return (show_response,)


# ═════════════════════════════════════════════════════════════════════════════
#  1. AUTHENTICATION
# ═════════════════════════════════════════════════════════════════════════════

@app.cell(hide_code=True)
def __(mo):
    mo.md("---\n## 1. 🔐 Authentication")
    return


# ── Login ─────────────────────────────────────────────────────────────────────

@app.cell(hide_code=True)
def __(mo):
    mo.md("### Login")
    return


@app.cell
def __(mo):
    login_email = mo.ui.text(label="Email", value="test@example.com", full_width=True)
    login_password = mo.ui.text(label="Password", kind="password", value="password", full_width=True)
    login_btn = mo.ui.run_button(label="Login")
    mo.vstack([login_email, login_password, login_btn])
    return login_btn, login_email, login_password


@app.cell
def __(BASE_URL, login_btn, login_email, login_password, requests, show_response):
    login_result = None
    if login_btn.value:
        login_result = requests.post(
            f"{BASE_URL}/patient/auth/login",
            json={"email": login_email.value, "password": login_password.value},
            headers={"Accept": "application/json"},
        )
    login_result and show_response(login_result)
    return (login_result,)


@app.cell
def __(login_result, mo):
    _token = ""
    if login_result and login_result.ok:
        _data = login_result.json().get("data", {})
        _token = _data.get("token", "")
        if _token:
            mo.md(f"✅ **Token extracted** – paste it into the token field above or copy it:\n\n`{_token}`")
    return


# ── Register ──────────────────────────────────────────────────────────────────

@app.cell(hide_code=True)
def __(mo):
    mo.md("### Register new patient")
    return


@app.cell
def __(mo):
    reg_first  = mo.ui.text(label="First name",  value="John",                full_width=True)
    reg_last   = mo.ui.text(label="Last name",   value="Doe",                 full_width=True)
    reg_email  = mo.ui.text(label="Email",       value="john@example.com",    full_width=True)
    reg_phone  = mo.ui.text(label="Phone",       value="201234567890",        full_width=True)
    reg_age    = mo.ui.number(label="Age",        value=30, start=1, stop=120)
    reg_gender = mo.ui.dropdown(label="Gender",  options=["male", "female"],  value="male")
    reg_pass   = mo.ui.text(label="Password",    kind="password", value="password123", full_width=True)
    reg_btn    = mo.ui.run_button(label="Register")
    mo.vstack([
        mo.hstack([reg_first, reg_last], widths="equal"),
        mo.hstack([reg_email, reg_phone], widths="equal"),
        mo.hstack([reg_age, reg_gender], widths="equal"),
        reg_pass,
        reg_btn,
    ])
    return reg_age, reg_btn, reg_email, reg_first, reg_gender, reg_last, reg_pass, reg_phone


@app.cell
def __(BASE_URL, reg_age, reg_btn, reg_email, reg_first, reg_gender, reg_last, reg_pass, reg_phone, requests, show_response):
    reg_result = None
    if reg_btn.value:
        reg_result = requests.post(
            f"{BASE_URL}/patient/auth/register",
            json={
                "first_name": reg_first.value,
                "last_name":  reg_last.value,
                "email":      reg_email.value,
                "phone":      reg_phone.value,
                "age":        int(reg_age.value),
                "gender":     reg_gender.value,
                "password":          reg_pass.value,
                "password_confirmation": reg_pass.value,
            },
            headers={"Accept": "application/json"},
        )
    reg_result and show_response(reg_result)
    return (reg_result,)


# ── Logout ────────────────────────────────────────────────────────────────────

@app.cell(hide_code=True)
def __(mo):
    mo.md("### Logout")
    return


@app.cell
def __(mo):
    logout_btn = mo.ui.run_button(label="Logout (uses current token)")
    logout_btn
    return (logout_btn,)


@app.cell
def __(BASE_URL, auth_headers, logout_btn, requests, show_response):
    logout_result = None
    if logout_btn.value:
        logout_result = requests.post(
            f"{BASE_URL}/patient/auth/logout",
            headers=auth_headers(),
        )
    logout_result and show_response(logout_result)
    return (logout_result,)


# ═════════════════════════════════════════════════════════════════════════════
#  2. PROFILE
# ═════════════════════════════════════════════════════════════════════════════

@app.cell(hide_code=True)
def __(mo):
    mo.md("---\n## 2. 👤 Patient Profile")
    return


@app.cell
def __(mo):
    profile_get_btn = mo.ui.run_button(label="Get My Profile")
    profile_get_btn
    return (profile_get_btn,)


@app.cell
def __(BASE_URL, auth_headers, profile_get_btn, requests, show_response):
    profile_result = None
    if profile_get_btn.value:
        profile_result = requests.get(
            f"{BASE_URL}/patient/profile/me",
            headers=auth_headers(),
        )
    profile_result and show_response(profile_result)
    return (profile_result,)


@app.cell(hide_code=True)
def __(mo):
    mo.md("### Update Password")
    return


@app.cell
def __(mo):
    upd_current  = mo.ui.text(label="Current password", kind="password", full_width=True)
    upd_new      = mo.ui.text(label="New password",     kind="password", full_width=True)
    upd_pass_btn = mo.ui.run_button(label="Update Password")
    mo.vstack([upd_current, upd_new, upd_pass_btn])
    return upd_current, upd_new, upd_pass_btn


@app.cell
def __(BASE_URL, auth_headers, requests, show_response, upd_current, upd_new, upd_pass_btn):
    upd_pass_result = None
    if upd_pass_btn.value:
        upd_pass_result = requests.post(
            f"{BASE_URL}/patient/profile/update-password",
            json={
                "current_password": upd_current.value,
                "password": upd_new.value,
                "password_confirmation": upd_new.value,
            },
            headers=auth_headers(),
        )
    upd_pass_result and show_response(upd_pass_result)
    return (upd_pass_result,)


# ═════════════════════════════════════════════════════════════════════════════
#  3. PUBLIC CONTENT
# ═════════════════════════════════════════════════════════════════════════════

@app.cell(hide_code=True)
def __(mo):
    mo.md("---\n## 3. 🌐 Public Content")
    return


# ── Services ──────────────────────────────────────────────────────────────────

@app.cell(hide_code=True)
def __(mo):
    mo.md("### Services")
    return


@app.cell
def __(mo):
    svc_btn = mo.ui.run_button(label="List Services")
    svc_btn
    return (svc_btn,)


@app.cell
def __(BASE_URL, requests, show_response, svc_btn):
    svc_result = None
    if svc_btn.value:
        svc_result = requests.get(f"{BASE_URL}/services/service", headers={"Accept": "application/json"})
    svc_result and show_response(svc_result)
    return (svc_result,)


# ── Events ────────────────────────────────────────────────────────────────────

@app.cell(hide_code=True)
def __(mo):
    mo.md("### Events")
    return


@app.cell
def __(mo):
    evt_btn = mo.ui.run_button(label="List Events")
    evt_btn
    return (evt_btn,)


@app.cell
def __(BASE_URL, evt_btn, requests, show_response):
    evt_result = None
    if evt_btn.value:
        evt_result = requests.get(f"{BASE_URL}/events/", headers={"Accept": "application/json"})
    evt_result and show_response(evt_result)
    return (evt_result,)


# ── Trainings ─────────────────────────────────────────────────────────────────

@app.cell(hide_code=True)
def __(mo):
    mo.md("### Trainings")
    return


@app.cell
def __(mo):
    trn_btn = mo.ui.run_button(label="List Trainings")
    trn_btn
    return (trn_btn,)


@app.cell
def __(BASE_URL, requests, show_response, trn_btn):
    trn_result = None
    if trn_btn.value:
        trn_result = requests.get(f"{BASE_URL}/trainings/", headers={"Accept": "application/json"})
    trn_result and show_response(trn_result)
    return (trn_result,)


# ── Testimonials ──────────────────────────────────────────────────────────────

@app.cell(hide_code=True)
def __(mo):
    mo.md("### Testimonials")
    return


@app.cell
def __(mo):
    tst_btn = mo.ui.run_button(label="List Testimonials")
    tst_btn
    return (tst_btn,)


@app.cell
def __(BASE_URL, requests, show_response, tst_btn):
    tst_result = None
    if tst_btn.value:
        tst_result = requests.get(f"{BASE_URL}/testimonials/", headers={"Accept": "application/json"})
    tst_result and show_response(tst_result)
    return (tst_result,)


# ═════════════════════════════════════════════════════════════════════════════
#  4. DOCTOR AVAILABILITY
# ═════════════════════════════════════════════════════════════════════════════

@app.cell(hide_code=True)
def __(mo):
    mo.md("---\n## 4. 🩺 Doctors & Availability")
    return


@app.cell
def __(mo):
    doc_list_btn = mo.ui.run_button(label="List Doctors")
    doc_list_btn
    return (doc_list_btn,)


@app.cell
def __(BASE_URL, doc_list_btn, requests, show_response):
    doc_list_result = None
    if doc_list_btn.value:
        doc_list_result = requests.get(f"{BASE_URL}/appointement/doctor", headers={"Accept": "application/json"})
    doc_list_result and show_response(doc_list_result)
    return (doc_list_result,)


@app.cell(hide_code=True)
def __(mo):
    mo.md("### Doctor availability")
    return


@app.cell
def __(mo):
    avail_doctor_id  = mo.ui.text(label="Doctor ID",  placeholder="e.g. 1", full_width=True)
    avail_service_id = mo.ui.text(label="Service ID", placeholder="e.g. 1", full_width=True)
    avail_btn        = mo.ui.run_button(label="Get Availability")
    mo.vstack([mo.hstack([avail_doctor_id, avail_service_id], widths="equal"), avail_btn])
    return avail_btn, avail_doctor_id, avail_service_id


@app.cell
def __(BASE_URL, avail_btn, avail_doctor_id, avail_service_id, requests, show_response):
    avail_result = None
    if avail_btn.value and avail_doctor_id.value and avail_service_id.value:
        avail_result = requests.get(
            f"{BASE_URL}/appointement/{avail_doctor_id.value}/{avail_service_id.value}/availability",
            headers={"Accept": "application/json"},
        )
    avail_result and show_response(avail_result)
    return (avail_result,)


# ═════════════════════════════════════════════════════════════════════════════
#  5. BOOKING
# ═════════════════════════════════════════════════════════════════════════════

@app.cell(hide_code=True)
def __(mo):
    mo.md("---\n## 5. 📅 Booking")
    return


@app.cell
def __(mo):
    bk_doctor_id  = mo.ui.text(label="Doctor ID",   placeholder="e.g. 1",         full_width=True)
    bk_service_id = mo.ui.text(label="Service ID",  placeholder="e.g. 1",         full_width=True)
    bk_date       = mo.ui.text(label="Date (dd-mm-yyyy)", value="15-06-2026",      full_width=True)
    bk_time       = mo.ui.text(label="Start time (HH:MM)", value="10:00",         full_width=True)
    bk_check_btn  = mo.ui.run_button(label="Check Availability")
    bk_book_btn   = mo.ui.run_button(label="Book Appointment (needs token)")
    mo.vstack([
        mo.hstack([bk_doctor_id, bk_service_id], widths="equal"),
        mo.hstack([bk_date, bk_time], widths="equal"),
        mo.hstack([bk_check_btn, bk_book_btn]),
    ])
    return bk_book_btn, bk_check_btn, bk_date, bk_doctor_id, bk_service_id, bk_time


@app.cell
def __(BASE_URL, auth_headers, bk_book_btn, bk_check_btn, bk_date, bk_doctor_id, bk_service_id, bk_time, mo, requests, show_response):
    bk_result = None
    _payload = {"date": bk_date.value, "start_time": bk_time.value}

    if bk_check_btn.value and bk_doctor_id.value and bk_service_id.value:
        bk_result = requests.post(
            f"{BASE_URL}/booking/{bk_doctor_id.value}/{bk_service_id.value}/check-availability",
            json=_payload,
            headers={"Accept": "application/json"},
        )
    elif bk_book_btn.value and bk_doctor_id.value and bk_service_id.value:
        bk_result = requests.post(
            f"{BASE_URL}/booking/{bk_doctor_id.value}/{bk_service_id.value}/book",
            json=_payload,
            headers=auth_headers(),
        )

    bk_result and show_response(bk_result)
    return (bk_result,)


# ═════════════════════════════════════════════════════════════════════════════
#  6. URGENT BOOKING
# ═════════════════════════════════════════════════════════════════════════════

@app.cell(hide_code=True)
def __(mo):
    mo.md("---\n## 6. 🚨 Urgent Booking")
    return


@app.cell
def __(mo):
    urg_name    = mo.ui.text(label="Full name",  value="Jane Doe",           full_width=True)
    urg_phone   = mo.ui.text(label="Phone",      value="201234567891",       full_width=True)
    urg_notes   = mo.ui.text(label="Notes",      value="Urgent toothache",   full_width=True)
    urg_btn     = mo.ui.run_button(label="Submit Urgent Booking")
    mo.vstack([mo.hstack([urg_name, urg_phone], widths="equal"), urg_notes, urg_btn])
    return urg_btn, urg_name, urg_notes, urg_phone


@app.cell
def __(BASE_URL, requests, show_response, urg_btn, urg_name, urg_notes, urg_phone):
    urg_result = None
    if urg_btn.value:
        urg_result = requests.post(
            f"{BASE_URL}/urgent-booking/submit",
            json={"name": urg_name.value, "phone": urg_phone.value, "notes": urg_notes.value},
            headers={"Accept": "application/json"},
        )
    urg_result and show_response(urg_result)
    return (urg_result,)


@app.cell(hide_code=True)
def __(mo):
    mo.md("### My urgent bookings (authenticated)")
    return


@app.cell
def __(mo):
    urg_list_btn = mo.ui.run_button(label="Get My Urgent Bookings")
    urg_list_btn
    return (urg_list_btn,)


@app.cell
def __(BASE_URL, auth_headers, requests, show_response, urg_list_btn):
    urg_list_result = None
    if urg_list_btn.value:
        urg_list_result = requests.get(
            f"{BASE_URL}/urgent-booking/my-bookings",
            headers=auth_headers(),
        )
    urg_list_result and show_response(urg_list_result)
    return (urg_list_result,)


# ═════════════════════════════════════════════════════════════════════════════
#  7. APPOINTMENTS (patient)
# ═════════════════════════════════════════════════════════════════════════════

@app.cell(hide_code=True)
def __(mo):
    mo.md("---\n## 7. 🗂 My Appointments (authenticated)")
    return


@app.cell
def __(mo):
    apt_list_btn = mo.ui.run_button(label="List My Appointments")
    apt_list_btn
    return (apt_list_btn,)


@app.cell
def __(BASE_URL, apt_list_btn, auth_headers, requests, show_response):
    apt_list_result = None
    if apt_list_btn.value:
        apt_list_result = requests.get(
            f"{BASE_URL}/patient/appointments",
            headers=auth_headers(),
        )
    apt_list_result and show_response(apt_list_result)
    return (apt_list_result,)


@app.cell(hide_code=True)
def __(mo):
    mo.md("### Cancel / Reschedule appointment")
    return


@app.cell
def __(mo):
    apt_id        = mo.ui.text(label="Appointment ID", placeholder="e.g. 5",     full_width=True)
    apt_action    = mo.ui.dropdown(label="Action", options=["cancel", "reschedule"], value="cancel")
    apt_new_date  = mo.ui.text(label="New date (dd-mm-yyyy, reschedule only)", value="", full_width=True)
    apt_new_time  = mo.ui.text(label="New time (HH:MM, reschedule only)",     value="", full_width=True)
    apt_act_btn   = mo.ui.run_button(label="Perform Action")
    mo.vstack([
        mo.hstack([apt_id, apt_action], widths="equal"),
        mo.hstack([apt_new_date, apt_new_time], widths="equal"),
        apt_act_btn,
    ])
    return apt_act_btn, apt_action, apt_id, apt_new_date, apt_new_time


@app.cell
def __(BASE_URL, apt_act_btn, apt_action, apt_id, apt_new_date, apt_new_time, auth_headers, requests, show_response):
    apt_act_result = None
    if apt_act_btn.value and apt_id.value:
        _action = apt_action.value
        if _action == "cancel":
            apt_act_result = requests.post(
                f"{BASE_URL}/patient/appointments/{apt_id.value}/cancel",
                headers=auth_headers(),
            )
        else:
            apt_act_result = requests.post(
                f"{BASE_URL}/patient/appointments/{apt_id.value}/reschedule",
                json={"date": apt_new_date.value, "start_time": apt_new_time.value},
                headers=auth_headers(),
            )
    apt_act_result and show_response(apt_act_result)
    return (apt_act_result,)


# ═════════════════════════════════════════════════════════════════════════════
#  8. NOTIFICATIONS
# ═════════════════════════════════════════════════════════════════════════════

@app.cell(hide_code=True)
def __(mo):
    mo.md("---\n## 8. 🔔 Notifications (authenticated)")
    return


@app.cell
def __(mo):
    notif_list_btn       = mo.ui.run_button(label="List Notifications")
    notif_unread_btn     = mo.ui.run_button(label="Unread Count")
    notif_read_all_btn   = mo.ui.run_button(label="Mark All as Read")
    mo.hstack([notif_list_btn, notif_unread_btn, notif_read_all_btn])
    return notif_list_btn, notif_read_all_btn, notif_unread_btn


@app.cell
def __(BASE_URL, auth_headers, mo, notif_list_btn, notif_read_all_btn, notif_unread_btn, requests, show_response):
    notif_result = None
    if notif_list_btn.value:
        notif_result = requests.get(f"{BASE_URL}/patient/notifications",       headers=auth_headers())
    elif notif_unread_btn.value:
        notif_result = requests.get(f"{BASE_URL}/patient/notifications/unread-count", headers=auth_headers())
    elif notif_read_all_btn.value:
        notif_result = requests.post(f"{BASE_URL}/patient/notifications/read-all",   headers=auth_headers())

    notif_result and show_response(notif_result)
    return (notif_result,)


# ─── Footer ───────────────────────────────────────────────────────────────────

@app.cell(hide_code=True)
def __(mo):
    mo.md(
        """
        ---
        > 🦷 **ChenAker SmilerMaker API Client** · built with [marimo](https://marimo.io) & [uv](https://github.com/astral-sh/uv)
        """
    )
    return


if __name__ == "__main__":
    app.run()
