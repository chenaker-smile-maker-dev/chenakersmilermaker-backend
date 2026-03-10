"""
helpers/ui.py
─────────────
Rendering helpers for the marimo notebook.
All functions accept `mo` (the marimo module) as first argument so this file
stays free of a hard marimo import and can be unit-tested easily.
"""
from __future__ import annotations

import html
import json
import re
from typing import Any


# ── JSON syntax highlighter ───────────────────────────────────────────────────

_RE_TOKEN = re.compile(
    r'(?P<key>"[^"\\]*(?:\\.[^"\\]*)*")\s*:'   # object key
    r'|(?P<str>"[^"\\]*(?:\\.[^"\\]*)*")'       # string value
    r'|(?P<num>-?\d+\.?\d*(?:[eE][+-]?\d+)?)'  # number
    r'|(?P<bool>true|false)'                    # boolean
    r'|(?P<null>null)',                          # null
    re.DOTALL,
)


def _highlight_json(text: str) -> str:
    """Return HTML string with span-based syntax highlighting."""
    out: list[str] = []
    last = 0
    for m in _RE_TOKEN.finditer(text):
        # Verbatim text between tokens (braces, commas, whitespace…)
        raw = html.escape(text[last : m.start()])
        out.append(f'<span style="color:#94a3b8">{raw}</span>')
        last = m.end()

        s = html.escape(m.group())
        if m.group("key"):
            out.append(f'<span class="ct-j-key">{s}:</span>')
        elif m.group("str"):
            out.append(f'<span class="ct-j-str">{s}</span>')
        elif m.group("num"):
            out.append(f'<span class="ct-j-num">{s}</span>')
        elif m.group("bool"):
            out.append(f'<span class="ct-j-bool">{s}</span>')
        elif m.group("null"):
            out.append(f'<span class="ct-j-null">{s}</span>')

    out.append(f'<span style="color:#94a3b8">{html.escape(text[last:])}</span>')
    return "".join(out)


def _fmt_json(data: Any) -> str:
    """Serialise `data` to indented JSON string."""
    try:
        return json.dumps(data, indent=2, ensure_ascii=False)
    except Exception:
        return str(data)


# ── Request preview ───────────────────────────────────────────────────────────

def show_request(mo: Any, result: dict[str, Any]) -> Any:
    """
    Render a dark panel showing the outgoing request details:
    method, URL and, if present, the JSON payload.

    Parameters
    ----------
    mo     : the `marimo` module
    result : dict returned by helpers.http.http_call()
    """
    method  = result.get("method", "GET")
    url     = html.escape(result.get("url", ""))
    payload = result.get("payload")

    # method badge colour via CSS class
    method_span = f'<span class="ct-method-{method}">{html.escape(method)}</span>'
    url_span    = f'<span class="ct-url">{url}</span>'

    body_lines = [
        f'<span class="ct-label">Request</span>',
        f'{method_span}  {url_span}',
    ]

    if payload:
        body_lines.append("")
        body_lines.append('<span class="ct-label">Body (JSON)</span>')
        for line in _fmt_json(payload).splitlines():
            body_lines.append(html.escape(line))

    inner = "\n".join(body_lines)
    return mo.Html(f'<pre class="ct-request">{inner}</pre>')


# ── Response panel ────────────────────────────────────────────────────────────

def show_response(mo: Any, result: dict[str, Any]) -> Any:
    """
    Render a response card:
    • coloured status badge  (green 2xx / yellow 3xx / red 4xx-5xx / grey error)
    • elapsed time
    • highlighted JSON body or raw text

    Parameters
    ----------
    mo     : the `marimo` module
    result : dict returned by helpers.http.http_call()
    """
    status  = result.get("status")
    reason  = html.escape(result.get("reason", ""))
    ok      = result.get("ok", False)
    elapsed = result.get("elapsed_ms", 0.0)
    error   = result.get("error")
    data    = result.get("data")
    raw     = result.get("raw", "")

    # ── badge ──────────────────────────────────────────────────────────────
    if error:
        badge_cls  = "ct-badge-err"
        badge_text = f"⚠ {reason}"
    elif status is None:
        badge_cls  = "ct-badge-err"
        badge_text = "No response"
    elif 200 <= status < 300:
        badge_cls  = "ct-badge-ok"
        badge_text = f"✓ {status} {reason}"
    elif 300 <= status < 400:
        badge_cls  = "ct-badge-warn"
        badge_text = f"→ {status} {reason}"
    else:
        badge_cls  = "ct-badge-err"
        badge_text = f"✗ {status} {reason}"

    badge   = f'<span class="ct-badge {badge_cls}">{html.escape(badge_text)}</span>'
    elapsed_span = f'<span class="ct-elapsed">{elapsed} ms</span>'

    header = (
        f'<div class="ct-response-header">'
        f'{badge}{elapsed_span}'
        f'</div>'
    )

    # ── body ───────────────────────────────────────────────────────────────
    if error:
        body_html = html.escape(error)
    elif data is not None:
        body_html = _highlight_json(_fmt_json(data))
    else:
        body_html = html.escape(raw) if raw else '<span style="color:#475569">— empty —</span>'

    body = f'<div class="ct-response-body">{body_html}</div>'

    return mo.Html(
        f'<div class="ct-response">{header}{body}</div>'
    )


# ── Live request preview (before sending) ────────────────────────────────────

def preview_request(mo: Any, method: str, url: str, payload: Any = None) -> Any:
    """
    Render a request preview panel directly from method/url/payload —
    no result dict needed.  Use this to show the request that *will* be sent
    as form values change in real-time.
    """
    return show_request(mo, {
        "method": method,
        "url": url,
        "payload": payload,
        "headers_sent": {},
    })


# ── Combined helper (request + response stacked) ─────────────────────────────

def show_exchange(mo: Any, result: dict[str, Any]) -> Any:
    """Render request preview followed immediately by the response card."""
    return mo.vstack([
        show_request(mo, result),
        show_response(mo, result),
    ])
