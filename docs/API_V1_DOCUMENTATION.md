# Chenaker Smile Maker - API V1 Documentation

## Overview

The Chenaker Smile Maker API provides endpoints for patient registration, authentication, profile management, and appointment booking with doctors. The API uses token-based authentication (Laravel Sanctum) and follows RESTful principles.

**Base URL:** `https://your-domain.com/api/v1`

**Authentication:** Bearer Token (included in `Authorization: Bearer {token}` header)

---

## Table of Contents

1. [Patient Authentication](#patient-authentication)
2. [Patient Profile Management](#patient-profile-management)
3. [Doctor Availability & Browse](#doctor-availability--browse)
4. [Appointment Booking](#appointment-booking)
5. [Error Handling](#error-handling)
6. [Response Format](#response-format)

---

## Patient Authentication

### 1. Register Patient

**Endpoint:** `POST /patient/auth/register`

**Authentication:** Not required

**Description:** Creates a new patient account and generates access and refresh tokens. The patient can immediately use the returned tokens to access protected endpoints.

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "201234567890",
  "age": 30,
  "gender": "male",
  "password": "secure_password",
  "password_confirmation": "secure_password",
  "image": "file_upload (optional)"
}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "token": "1|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refresh_token": "2|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "patient": {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "phone": "201234567890",
      "age": 30,
      "gender": "male",
      "image": null
    }
  },
  "message": "Patient registered successfully."
}
```

**Validation Rules:**
- `first_name`: required, string
- `last_name`: required, string
- `email`: required, unique email format
- `phone`: required, unique
- `age`: required, integer (minimum 1)
- `gender`: required, in [male, female]
- `password`: required, minimum 6 characters
- `password_confirmation`: required, must match password
- `image`: optional, max 2MB, mimes: jpeg, png, jpg

**Error Responses:**
- `422 Unprocessable Entity`: Validation failed
- `409 Conflict`: Email or phone already registered

---

### 2. Login Patient

**Endpoint:** `POST /patient/auth/login`

**Authentication:** Not required

**Description:** Authenticates a patient with email and password. Returns access and refresh tokens for subsequent authenticated requests.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "secure_password"
}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "token": "1|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refresh_token": "2|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "patient": {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "phone": "201234567890",
      "age": 30,
      "gender": "male"
    }
  },
  "message": "Patient logged in successfully."
}
```

**Error Responses:**
- `401 Unauthorized`: Invalid credentials (email or password incorrect)
- `422 Unprocessable Entity`: Validation failed

---

### 3. Logout Patient

**Endpoint:** `POST /patient/auth/logout`

**Authentication:** Required (access token)

**Description:** Invalidates all authentication tokens for the patient. The patient must login again to access protected endpoints.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "message": "Patient logged out successfully."
}
```

---

### 4. Refresh Access Token

**Endpoint:** `POST /patient/auth/refresh-token`

**Authentication:** Required (refresh token)

**Description:** Generates a new access token using the refresh token. Use this when the access token expires to obtain a new one without re-logging in.

**Headers:**
```
Authorization: Bearer {refresh_token}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "token": "3|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  },
  "message": "Access token refreshed successfully."
}
```

---

## Patient Profile Management

### 1. Get Patient Profile

**Endpoint:** `GET /patient/profile/me`

**Authentication:** Required (access token)

**Description:** Retrieves the complete profile information of the currently authenticated patient.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "201234567890",
    "age": 30,
    "gender": "male",
    "image_url": "https://cdn.example.com/patient-1.jpg"
  },
  "message": "Patient retrieved successfully."
}
```

---

### 2. Update Patient Profile

**Endpoint:** `POST /patient/profile/update`

**Authentication:** Required (access token)

**Description:** Updates the personal information of the authenticated patient. All fields are optional.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json or multipart/form-data
```

**Request Body:**
```json
{
  "first_name": "Jonathan",
  "last_name": "Smith",
  "phone": "201234567891",
  "image": "file_upload (optional)"
}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "first_name": "Jonathan",
    "last_name": "Smith",
    "email": "john@example.com",
    "phone": "201234567891",
    "age": 30,
    "gender": "male",
    "image_url": "https://cdn.example.com/patient-1.jpg"
  },
  "message": "Profile updated successfully."
}
```

**Validation Rules:**
- `first_name`: optional, string
- `last_name`: optional, string
- `phone`: optional, unique
- `image`: optional, max 2MB, mimes: jpeg, png, jpg

---

### 3. Update Patient Password

**Endpoint:** `POST /patient/profile/update-password`

**Authentication:** Required (access token)

**Description:** Changes the patient's password. The old password must be provided for verification. A new access token is generated for security.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
  "old_password": "current_password",
  "new_password": "new_secure_password",
  "new_password_confirmation": "new_secure_password"
}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "token": "4|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  },
  "message": "Password updated successfully. Please use the new access token."
}
```

**Validation Rules:**
- `old_password`: required, string
- `new_password`: required, minimum 6 characters
- `new_password_confirmation`: required, must match new_password

**Error Responses:**
- `422 Unprocessable Entity`: Old password incorrect or validation failed

---

## Doctor Availability & Browse

### 1. List All Doctors

**Endpoint:** `GET /appointement/doctor`

**Authentication:** Not required

**Description:** Returns a paginated list of all active doctors in the system. Each doctor includes basic information and count of services they provide.

**Query Parameters:**
- `page` (optional, default: 1): Page number for pagination
- `per_page` (optional, default: 10): Number of doctors per page

**Example Request:**
```
GET /appointement/doctor?page=1&per_page=20
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "first_name": "Ahmed",
        "last_name": "Hassan",
        "email": "ahmed@clinic.com",
        "specialization": "Dentist",
        "services_count": 5
      },
      {
        "id": 2,
        "first_name": "Fatima",
        "last_name": "Ali",
        "email": "fatima@clinic.com",
        "specialization": "Dermatologist",
        "services_count": 3
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 15,
      "last_page": 1
    }
  }
}
```

---

### 2. Get Doctor Details

**Endpoint:** `GET /appointement/doctor/{doctor_id}`

**Authentication:** Not required

**Description:** Retrieves detailed information about a specific doctor including their profile and complete list of services they provide with pricing and duration.

**Path Parameters:**
- `doctor_id`: ID of the doctor

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "first_name": "Ahmed",
    "last_name": "Hassan",
    "email": "ahmed@clinic.com",
    "phone": "201234567890",
    "specialization": "Dentist",
    "services": [
      {
        "id": 1,
        "name": "Teeth Cleaning",
        "description": "Professional teeth cleaning and whitening",
        "price": 250,
        "duration": 30,
        "image": null
      },
      {
        "id": 2,
        "name": "Root Canal Treatment",
        "description": "Endodontic treatment",
        "price": 1500,
        "duration": 60,
        "image": null
      }
    ]
  }
}
```

**Error Responses:**
- `404 Not Found`: Doctor does not exist
- `422 Unprocessable Entity`: Invalid doctor ID

---

### 3. List All Services

**Endpoint:** `GET /appointement/service`

**Authentication:** Not required

**Description:** Returns a paginated list of all active medical services available in the system. Each service includes pricing, duration, and availability status.

**Query Parameters:**
- `page` (optional, default: 1): Page number for pagination
- `per_page` (optional, default: 10): Number of services per page

**Example Request:**
```
GET /appointement/service?page=1&per_page=15
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Teeth Cleaning",
        "description": "Professional teeth cleaning and whitening",
        "price": 250,
        "duration": 30,
        "image": null
      },
      {
        "id": 2,
        "name": "Root Canal Treatment",
        "description": "Endodontic treatment",
        "price": 1500,
        "duration": 60,
        "image": null
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 8,
      "last_page": 1
    }
  }
}
```

---

### 4. Get Service Details

**Endpoint:** `GET /appointement/service/{service_id}`

**Authentication:** Not required

**Description:** Retrieves detailed information about a specific service including description, pricing, duration, and list of doctors who provide this service.

**Path Parameters:**
- `service_id`: ID of the service

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Teeth Cleaning",
    "description": "Professional teeth cleaning and whitening",
    "price": 250,
    "duration": 30,
    "image": null,
    "doctors": [
      {
        "id": 1,
        "first_name": "Ahmed",
        "last_name": "Hassan",
        "specialization": "Dentist",
        "email": "ahmed@clinic.com"
      },
      {
        "id": 3,
        "first_name": "Omar",
        "last_name": "Mohamed",
        "specialization": "Dentist",
        "email": "omar@clinic.com"
      }
    ]
  }
}
```

**Error Responses:**
- `404 Not Found`: Service does not exist
- `422 Unprocessable Entity`: Invalid service ID

---

### 5. Get Doctor's Next Available Slot

**Endpoint:** `GET /appointement/{doctor_id}/{service_id}/availability`

**Authentication:** Not required

**Description:** Retrieves the next available appointment slot for a doctor offering a specific service. Takes into account the doctor's availability schedule (days of week and hours) and existing appointments. Returns the first available slot within the next 30 days.

**Path Parameters:**
- `doctor_id`: ID of the doctor
- `service_id`: ID of the service

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "available": true,
    "doctor_id": 1,
    "service_id": 1,
    "next_available_date": "2025-12-15",
    "next_available_time": "14:30",
    "service_duration": 30,
    "end_time": "15:00"
  }
}
```

**Error Responses:**
- `404 Not Found`: Doctor or service does not exist
- `422 Unprocessable Entity`: No available slots in the next 30 days

---

## Appointment Booking

### 1. Check Availability for Specific Time Slot

**Endpoint:** `POST /booking/{doctor_id}/{service_id}/check-availability`

**Authentication:** Not required

**Description:** Checks if a specific time slot is available for booking. Takes a date and start time, validates if the doctor has an available slot for the service duration at that time. Respects doctor's availability schedule (days of week and hours).

**Path Parameters:**
- `doctor_id`: ID of the doctor
- `service_id`: ID of the service

**Request Body:**
```json
{
  "date": "15-12-2025",
  "start_time": "14:30"
}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "available": true,
    "doctor_id": 1,
    "service_id": 1,
    "date": "2025-12-15",
    "start_time": "14:30",
    "end_time": "15:00",
    "service_duration": 30
  }
}
```

**Response (Slot Not Available - 200):**
```json
{
  "success": true,
  "data": {
    "available": false,
    "reason": "Doctor is not available on this day",
    "doctor_id": 1,
    "service_id": 1,
    "date": "2025-12-15",
    "start_time": "14:30"
  }
}
```

**Validation Rules:**
- `date`: required, format: d-m-Y (e.g., 15-12-2025)
- `start_time`: required, format: H:i (24-hour, e.g., 14:30)

**Error Responses:**
- `404 Not Found`: Doctor or service does not exist
- `422 Unprocessable Entity`: Invalid date/time format

---

### 2. Book Appointment

**Endpoint:** `POST /booking/{doctor_id}/{service_id}/book`

**Authentication:** Required (access token)

**Description:** Creates an appointment for the authenticated patient with the specified doctor and service. The time slot must be available and within the doctor's availability schedule. Also creates a block in Zap to prevent double-booking.

**Path Parameters:**
- `doctor_id`: ID of the doctor
- `service_id`: ID of the service

**Headers:**
```
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
  "date": "15-12-2025",
  "start_time": "14:30"
}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "appointment_id": 42,
    "patient_id": 1,
    "doctor_id": 1,
    "service_id": 1,
    "date": "2025-12-15",
    "start_time": "14:30",
    "end_time": "15:00",
    "status": "pending",
    "created_at": "2025-12-01T12:00:00Z"
  },
  "message": "Appointment booked successfully"
}
```

**Response (Slot Unavailable - 422):**
```json
{
  "success": false,
  "message": "This time slot is no longer available",
  "data": []
}
```

**Validation Rules:**
- `date`: required, format: d-m-Y
- `start_time`: required, format: H:i

**Error Responses:**
- `401 Unauthorized`: Patient not authenticated
- `404 Not Found`: Patient, doctor, or service does not exist
- `422 Unprocessable Entity`: Invalid date/time format or slot unavailable

**Workflow:**
1. System validates the time slot availability
2. Creates appointment record with status "pending"
3. Creates a block in Zap (external calendar system) to prevent double-booking
4. Returns appointment details to the patient

---

## Error Handling

### Standard Error Response Format

```json
{
  "success": false,
  "message": "Error message describing what went wrong",
  "data": []
}
```

### Common HTTP Status Codes

| Status | Meaning | Common Cause |
|--------|---------|--------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request parameters |
| 401 | Unauthorized | Missing or invalid authentication token |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 409 | Conflict | Resource already exists (e.g., email taken) |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Internal Server Error | Server-side error |

---

## Response Format

### Success Response

```json
{
  "success": true,
  "data": { /* ... */ },
  "message": "Operation completed successfully"
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error description",
  "data": []
}
```

---

## Authentication Flow Diagram

```
1. Patient Registration/Login
   ├─ POST /patient/auth/register
   └─ POST /patient/auth/login
   └─ Returns: access_token & refresh_token

2. Accessing Protected Resources
   ├─ Include header: Authorization: Bearer {access_token}
   └─ Examples: GET /patient/profile/me, POST /booking/{doctor}/{service}/book

3. Token Refresh
   ├─ When access_token expires
   ├─ POST /patient/auth/refresh-token
   ├─ Use refresh_token in Authorization header
   └─ Returns: new access_token

4. Logout
   ├─ POST /patient/auth/logout
   └─ Invalidates all tokens
```

---

## Appointment Booking Workflow Diagram

```
1. Browse Available Options
   ├─ GET /appointement/doctor (list all doctors)
   ├─ GET /appointement/doctor/{id} (doctor details + services)
   ├─ GET /appointement/service (list all services)
   └─ GET /appointement/service/{id} (service details + doctors)

2. Check Availability
   ├─ GET /appointement/{doctor_id}/{service_id}/availability
   │  └─ Returns: next available slot (date & time)
   │
   └─ POST /booking/{doctor_id}/{service_id}/check-availability
      ├─ Input: specific date & time
      └─ Returns: slot available or reason for unavailability

3. Book Appointment
   ├─ POST /booking/{doctor_id}/{service_id}/book
   ├─ Authentication Required (patient login token)
   ├─ Input: date & start_time (must be available)
   │
   └─ Returns: appointment_id & confirmation
      ├─ Appointment created with status: "pending"
      └─ Block created in Zap calendar to prevent double-booking
```

---

## Example Complete User Journey

### Step 1: Register Patient
```bash
curl -X POST https://api.example.com/api/v1/patient/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "201234567890",
    "age": 30,
    "gender": "male",
    "password": "secure123",
    "password_confirmation": "secure123"
  }'
```

### Step 2: Get Available Doctors
```bash
curl -X GET "https://api.example.com/api/v1/appointement/doctor?per_page=10"
```

### Step 3: View Specific Doctor Details
```bash
curl -X GET https://api.example.com/api/v1/appointement/doctor/1
```

### Step 4: Check Next Available Slot
```bash
curl -X GET https://api.example.com/api/v1/appointement/1/1/availability
```

### Step 5: Check Specific Time Slot
```bash
curl -X POST https://api.example.com/api/v1/booking/1/1/check-availability \
  -H "Content-Type: application/json" \
  -d '{
    "date": "15-12-2025",
    "start_time": "14:30"
  }'
```

### Step 6: Book Appointment
```bash
curl -X POST https://api.example.com/api/v1/booking/1/1/book \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {access_token}" \
  -d '{
    "date": "15-12-2025",
    "start_time": "14:30"
  }'
```

---

## Rate Limiting & Throttling

Currently, rate limiting is not enforced. Future versions may implement:
- Per-minute request limits
- Per-day appointment booking limits
- Throttling for search operations

---

## Versioning

This is **API Version 1 (v1)**. Future versions will be available at:
- `/api/v2`
- `/api/v3`
- etc.

---

## Support & Issues

For API issues, errors, or feature requests, contact the development team at `dev@example.com`
