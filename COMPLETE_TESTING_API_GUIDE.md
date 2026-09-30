# Complete REOS API Testing Guide (For Backend/Web QA)

This document is a comprehensive, exhaustive guide to testing every single API endpoint available in `routes/api.php`. It is designed specifically for you to test everything in Postman smoothly.

---

## 1. Authentication & Common Endpoints

### 1.1 Login
- **Method:** `POST`
- **Endpoint:** `/api/auth/login`
- **Headers:** `Accept: application/json`
- **Body:**
```json
{
  "email": "executive@company.com",
  "password": "password123"
}
```

### 1.2 Verify OTP
- **Method:** `POST`
- **Endpoint:** `/api/auth/otp/verify`
- **Body:**
```json
{
  "email": "executive@company.com",
  "otp": "123456"
}
```

### 1.3 Get Current User Profile (Me)
- **Method:** `GET`
- **Endpoint:** `/api/me`
- **Headers:** `Authorization: Bearer {token}`

### 1.4 Update Profile
- **Method:** `PUT`
- **Endpoint:** `/api/auth/profile`
- **Body:**
```json
{
  "name": "New Name",
  "phone": "9988776655"
}
```

### 1.5 Logout
- **Method:** `POST`
- **Endpoint:** `/api/auth/logout`

### 1.6 Update FCM Token
- **Method:** `POST`
- **Endpoint:** `/api/fcm-token`
- **Body:**
```json
{
  "fcm_token": "your_device_fcm_token_string"
}
```

### 1.7 Remove FCM Token
- **Method:** `DELETE`
- **Endpoint:** `/api/fcm-token`

---

## 2. Webhooks & Support System

### 2.1 General Reports Summary
- **Method:** `GET`
- **Endpoint:** `/api/reports/summary`

### 2.2 Get Notifications
- **Method:** `GET`
- **Endpoint:** `/api/{role}/notifications`

### 2.3 Mark Notification as Read
- **Method:** `POST`
- **Endpoint:** `/api/{role}/notifications/{id}/read`

### 2.4 List Support Tickets
- **Method:** `GET`
- **Endpoint:** `/api/{role}/support/tickets`

### 2.5 Create Support Ticket
- **Method:** `POST`
- **Endpoint:** `/api/{role}/support/tickets`
- **Body:**
```json
{
  "category": "Technical",
  "subject": "App crashing on login",
  "description": "When I click login, the app closes.",
  "priority": "high"
}
```

### 2.6 View Ticket Details
- **Method:** `GET`
- **Endpoint:** `/api/{role}/support/tickets/{id}`

### 2.7 Reply to Ticket
- **Method:** `POST`
- **Endpoint:** `/api/{role}/support/tickets/{id}/reply`
- **Body:**
```json
{
  "message": "Thanks, it is working now."
}
```

### 2.8 Update Ticket Status
- **Method:** `PATCH`
- **Endpoint:** `/api/{role}/support/tickets/{id}/status`
- **Body:**
```json
{
  "status": "resolved"
}
```

---

## 3. Team Chat APIs

### 3.1 Fetch Conversations
- **Method:** `GET`
- **Endpoint:** `/api/chat/conversations`

### 3.2 Fetch Messages
- **Method:** `GET`
- **Endpoint:** `/api/chat/{chat_id}/messages`

### 3.3 Send Message
- **Method:** `POST`
- **Endpoint:** `/api/chat/{chat_id}/messages`
- **Body:**
```json
{
  "message": "Hello team!"
}
```

### 3.4 Start Direct Chat
- **Method:** `POST`
- **Endpoint:** `/api/chat/direct`
- **Body:**
```json
{
  "user_id": 5
}
```

### 3.5 Create Group Chat
- **Method:** `POST`
- **Endpoint:** `/api/chat/group`
- **Body:**
```json
{
  "name": "Sales Team Group",
  "participant_ids": [2, 3, 5]
}
```

---

## 4. Sales Executive Endpoints (`/api/executive/*`)

### 4.1 Dashboard Analytics
- **Method:** `GET`
- **Endpoint:** `/api/executive/dashboard`

### 4.2 Attendance: Clock-In
- **Method:** `POST`
- **Endpoint:** `/api/executive/attendance/clock-in`
- **Body:**
```json
{
  "work_location": "office",
  "latitude": 17.432,
  "longitude": 78.432
}
```

### 4.3 Attendance: Clock-Out
- **Method:** `POST`
- **Endpoint:** `/api/executive/attendance/clock-out`

### 4.4 Attendance: List Leaves
- **Method:** `GET`
- **Endpoint:** `/api/executive/attendance/leaves`

### 4.5 Attendance: Apply Leave
- **Method:** `POST`
- **Endpoint:** `/api/executive/attendance/leaves`
- **Body:**
```json
{
  "leave_type": "sick",
  "start_date": "2026-10-10",
  "end_date": "2026-10-12",
  "reason": "Viral fever"
}
```

### 4.6 List All Follow-ups
- **Method:** `GET`
- **Endpoint:** `/api/executive/follow-ups`

### 4.7 Update Follow-up Status
- **Method:** `PATCH`
- **Endpoint:** `/api/executive/follow-ups/{id}/status`
- **Body:**
```json
{
  "status": "completed",
  "notes": "Client agreed for site visit"
}
```

### 4.8 List Leads
- **Method:** `GET`
- **Endpoint:** `/api/executive/leads`

### 4.9 Create Lead
- **Method:** `POST`
- **Endpoint:** `/api/executive/leads`
- **Body:**
```json
{
  "first_name": "Rohan",
  "phone": "9876543210",
  "budget": 5000000
}
```

### 4.10 Check Duplicate Lead
- **Method:** `POST`
- **Endpoint:** `/api/executive/leads/check-duplicate`
- **Body:**
```json
{
  "phone": "9876543210"
}
```

### 4.11 Get Lead Details
- **Method:** `GET`
- **Endpoint:** `/api/executive/leads/{id}`

### 4.12 Update Lead Status
- **Method:** `POST`
- **Endpoint:** `/api/executive/leads/{id}/status`
- **Body:**
```json
{
  "status": "interested"
}
```

### 4.13 Log Call
- **Method:** `POST`
- **Endpoint:** `/api/executive/leads/{id}/calls`
- **Body:**
```json
{
  "duration_seconds": 120,
  "summary": "Discussed pricing",
  "call_type": "outbound"
}
```

### 4.14 Schedule Site Visit
- **Method:** `POST`
- **Endpoint:** `/api/executive/site-visits`
- **Body:**
```json
{
  "lead_id": 5,
  "project_id": 1,
  "scheduled_at": "2026-10-15 10:00:00"
}
```

### 4.15 Update Site Visit Status
- **Method:** `POST`
- **Endpoint:** `/api/executive/site-visits/{id}/status`
- **Body:**
```json
{
  "status": "conducted"
}
```

### 4.16 List Active Projects
- **Method:** `GET`
- **Endpoint:** `/api/executive/projects`

### 4.17 Create Booking
- **Method:** `POST`
- **Endpoint:** `/api/executive/bookings`
- **Body:**
```json
{
  "lead_id": 5,
  "project_id": 1,
  "unit_id": 105,
  "booking_amount": 50000
}
```

---

## 5. Manager Endpoints (`/api/manager/*`)

### 5.1 Manager Dashboard
- **Method:** `GET`
- **Endpoint:** `/api/manager/dashboard`

### 5.2 Manager Clock-in
- **Method:** `POST`
- **Endpoint:** `/api/manager/attendance/clock-in`
- **Body:**
```json
{
  "work_location": "remote",
  "latitude": 28.7041,
  "longitude": 77.1025
}
```

### 5.3 Team Follow-ups
- **Method:** `GET`
- **Endpoint:** `/api/manager/follow-ups`

### 5.4 List Team Members
- **Method:** `GET`
- **Endpoint:** `/api/manager/team`

### 5.5 Add Team Executive
- **Method:** `POST`
- **Endpoint:** `/api/manager/team/executives`
- **Body:**
```json
{
  "name": "Vikas Sales",
  "email": "vikas@company.com",
  "phone": "9998887776",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "sales_executive"
}
```

### 5.6 Approve Team Leave
- **Method:** `POST`
- **Endpoint:** `/api/manager/team/leaves/{id}/approve`
- **Body:**
```json
{
  "status": "approved"
}
```

### 5.7 List Team Leads
- **Method:** `GET`
- **Endpoint:** `/api/manager/leads`

### 5.8 Re-assign Lead (Within Team)
- **Method:** `POST`
- **Endpoint:** `/api/manager/leads/{id}/assign`
- **Body:**
```json
{
  "assigned_to_user_id": 12
}
```

### 5.9 List Distribution Rules
- **Method:** `GET`
- **Endpoint:** `/api/manager/distribution-rules`

---

## 6. Broker Endpoints (`/api/broker/*`)

### 6.1 Broker Dashboard
- **Method:** `GET`
- **Endpoint:** `/api/broker/dashboard`

### 6.2 Submit New Lead
- **Method:** `POST`
- **Endpoint:** `/api/broker/leads`
- **Body:**
```json
{
  "first_name": "Suresh",
  "phone": "9988776655",
  "project_id": 2,
  "budget_min": 5000000,
  "budget_max": 8000000
}
```

### 6.3 Update Bank Details
- **Method:** `POST`
- **Endpoint:** `/api/broker/bank-details`
- **Body:**
```json
{
  "account_number": "1234567890",
  "ifsc": "HDFC000123",
  "bank_name": "HDFC Bank"
}
```

### 6.4 Request Payout
- **Method:** `POST`
- **Endpoint:** `/api/broker/payout-request`
- **Body:**
```json
{
  "amount": 20000
}
```

### 6.5 View Commissions
- **Method:** `GET`
- **Endpoint:** `/api/broker/commissions`
