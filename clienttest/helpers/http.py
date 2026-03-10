"""
helpers/http.py
───────────────
Thin wrapper around `requests` that returns a structured result dict so the
notebook cells stay clean and the UI layer (helpers/ui.py) can render
consistent panels for every request.
"""
from __future__ import annotations

import json
import time
from typing import Any

import requests as _requests


def http_call(
    method: str,
    url: str,
    *,
    headers: dict[str, str] | None = None,
    payload: dict[str, Any] | None = None,
    timeout: int = 15,
) -> dict[str, Any]:
    """
    Execute an HTTP request and return a normalised result dict.

    Return shape:
        {
            "method":      str,          # e.g. "POST"
            "url":         str,          # full URL
            "payload":     dict | None,  # JSON body sent (if any)
            "headers_sent":dict,         # headers that were sent
            "status":      int | None,   # HTTP status code, None on network error
            "reason":      str,          # e.g. "OK", "Not Found", or error message
            "ok":          bool,         # True when 2xx
            "elapsed_ms":  float,        # round-trip time in milliseconds
            "data":        dict | None,  # parsed JSON body, or None
            "raw":         str,          # raw response text (fallback)
            "error":       str | None,   # set only on exception
        }
    """
    method = method.upper()
    headers = headers or {"Accept": "application/json"}

    result: dict[str, Any] = {
        "method": method,
        "url": url,
        "payload": payload,
        "headers_sent": headers,
        "status": None,
        "reason": "",
        "ok": False,
        "elapsed_ms": 0.0,
        "data": None,
        "raw": "",
        "error": None,
    }

    t0 = time.perf_counter()
    try:
        resp = _requests.request(
            method,
            url,
            json=payload if payload else None,
            headers=headers,
            timeout=timeout,
        )
        result["elapsed_ms"] = round((time.perf_counter() - t0) * 1000, 1)
        result["status"] = resp.status_code
        result["reason"] = resp.reason
        result["ok"] = resp.ok
        result["raw"] = resp.text
        try:
            result["data"] = resp.json()
        except Exception:
            result["data"] = None
    except Exception as exc:
        result["elapsed_ms"] = round((time.perf_counter() - t0) * 1000, 1)
        result["error"] = str(exc)
        result["reason"] = type(exc).__name__

    return result
