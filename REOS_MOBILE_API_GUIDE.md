# REOS Mobile App API Guide (React Native)

This guide provides comprehensive documentation for integrating the React Native mobile applications (Sales Executive App, Manager App, and Broker App) with the REOS Laravel Backend via REST APIs.

---

## 1. Postman Setup & Authentication

All API endpoints (except Webhooks) are secured using **Laravel Sanctum**.

### **Base URL**
For local development: `http://127.0.0.1:8000/api`
*(Change `127.0.0.1` to your local IP address e.g. `192.168.1.5` when testing on a real physical mobile device).*

### **Step 1: Get the Auth Token**
**Endpoint:** `POST /auth/login`

**Headers:**
- `Accept`: `application/json`
- `Content-Type`: `application/json`

**Body (Raw JSON):**
```json
{
    "email": "executive@company.com",
    "password": "password123"
}
```
**Response:**
```json
{
    "status": "success",
    "token": "1|abcdefghijklmnopqrstuvwxyz123456789",
    "user": {
        "id": 10,
        "name": "Rohan Sales",
        "role": "sales_executive"
    }
}
```

### **Step 2: Add Token in Postman**
1. Copy the `token` string from the login response.
2. For all subsequent requests, go to the **Authorization** tab in Postman.
3. Select **Bearer Token** type.
4. Paste the token in the Token field.

*(Ensure `Accept: application/json` is added to Headers for every request to force JSON responses instead of HTML redirects).*

---

## 2. Sales Executive APIs (`/api/executive/*`)
*These endpoints require a user with `sales_executive` or `executive` role.*

### **Dashboard Analytics**
`GET /executive/dashboard`
- **Response**: Returns metrics like `total_leads`, `pending_follow_ups`, `site_visits_today`, `conversions`.

### **Leads Management**
- `GET /executive/leads`: Fetch all leads assigned to the executive.
- `POST /executive/leads`: Create a new lead manually.
- `GET /executive/leads/{id}`: Get full lead details including timeline and calls.
- `PUT /executive/leads/{id}`: Update lead basic details (first_name, phone, budget, etc).
- `GET /executive/leads/{id}/timeline`: View full activity timeline of a specific lead.
- `POST /executive/leads/{id}/status`: Update lead status (e.g., `new` -> `contacted`).
- `POST /executive/leads/{id}/transfer`: Manually transfer a lead with reason (`{"new_assignee_id": 1, "transfer_reason": "Out of town"}`).
- `POST /executive/leads/{id}/negotiate`: Move lead to negotiation (`{"neg_offered_price": 500000, "neg_expected_close_date": "2026-10-01"}`).
- `POST /executive/leads/{id}/lost`: Drop a lead with a reason (`{"lost_reason": "Budget issue"}`).
    ```json
    { "status": "contacted" }
    ```
- `POST /executive/leads/{id}/notes`: Add a note to the lead.
    ```json
    { "note": "Customer is interested in 3BHK." }
    ```
- `POST /executive/leads/{id}/calls`: Log a phone call.
    ```json
    {
        "duration_seconds": 120,
        "summary": "Discussed pricing",
        "call_type": "outbound"
    }
    ```

### **Follow-Ups & Calendar**
- `GET /executive/follow-ups`: Get all upcoming follow-ups (general list).
- `PATCH /executive/follow-ups/{id}/status`: Update follow-up status (`pending`, `completed`, `missed`, `cancelled`).
- `GET /executive/leads/{id}/follow-ups`: Get upcoming follow-ups for a specific lead.
- `POST /executive/leads/{id}/follow-ups`: Schedule a new follow-up.
    ```json
    {
        "scheduled_at": "2026-10-01 14:30:00",
        "type": "call",
        "notes": "Call back after lunch"
    }
    ```

### **HRMS & Attendance**
- `GET /executive/attendance`: View attendance summary.
- `POST /executive/attendance/clock-in`: Clock in for the day (requires location).
    ```json
    { "work_location": "office", "latitude": 17.432, "longitude": 78.432, "selfie": "file_binary_data" }
    ```
- `POST /executive/attendance/clock-out`: Clock out.
- `GET /executive/attendance/leaves`: Fetch applied leaves.
- `POST /executive/attendance/leaves`: Apply for a new leave.
    ```json
    {
        "leave_type": "sick_leave",
        "start_date": "2026-10-15",
        "end_date": "2026-10-16",
        "reason": "Not feeling well"
    }
    ```

### **Site Visits**
- `GET /executive/site-visits`: List upcoming and past site visits.
- `POST /executive/site-visits`: Schedule a site visit.
    ```json
    {
        "lead_id": 5,
        "project_id": 1,
        "scheduled_at": "2026-10-05 10:00:00"
    }
    ```
- `POST /executive/site-visits/{id}/verify-visit`: Verify physical presence via Geo-location/Selfie.

### **Inventory & Bookings**
- `GET /executive/projects`: Get active projects.
- `GET /executive/projects/{id}/units`: Get unit availability for a project.
- `POST /executive/bookings`: Initiate a booking token for a unit. Supports `co_applicants` array (e.g. `[{"name": "Wife", "relationship": "Spouse", "phone": "123"}]`).

---

## 3. Manager APIs (`/api/manager/*`)
*These endpoints require a user with `manager` or `sales_manager` role.*

### **Team Management**
- `GET /manager/team`: Fetch all executives reporting to this manager along with their performance stats.
- `POST /manager/team/executives`: Add a new sales executive to the team.
    ```json
    {
        "name": "Amit Kumar",
        "email": "amit@company.com",
        "phone": "9876543210",
        "role": "sales_executive",
        "password": "password123",
        "password_confirmation": "password123"
    }
    ```
- `PATCH /manager/team/executives/{id}/status`: Activate/Deactivate an executive.
- `GET /manager/team/leaves`: View pending leave applications from the team.
- `POST /manager/team/leaves/{id}/approve`: Approve or reject team member leaves (`{"status": "approved"}`).

### **Managerial Lead Operations**
- `GET /manager/leads`: View all leads across the manager's team.
- `POST /manager/leads/{id}/assign`: Reassign a lead to a different executive in the team.
    ```json
    {
        "assigned_to_user_id": 12
    }
    ```

### **Manager HRMS & Attendance**
- `GET /manager/attendance`: View own attendance summary.
- `POST /manager/attendance/clock-in`: Clock in for the day.
- `POST /manager/attendance/clock-out`: Clock out.
- `GET /manager/attendance/leaves`: Fetch own applied leaves.
- `POST /manager/attendance/leaves`: Apply for a new leave.

### **Team Follow-Ups**
- `GET /manager/follow-ups`: View team's upcoming follow-ups.
- `PATCH /manager/follow-ups/{id}/status`: Update a follow-up status.

### **Lead Distribution Automation**
- `GET /manager/distribution-rules`: List auto-assignment rules.

---

## 4. Broker / Channel Partner APIs (`/api/broker/*`)
*These endpoints require a user with `broker` role.*

### **Profile & Dashboard**
- `GET /broker/dashboard`: High-level metrics (Total submitted leads, Site Visits, Total Commission Earned).
- `GET /broker/profile`: Get broker profile data.
- `POST /broker/bank-details`: Update bank details for payouts.

### **Lead Submission & Tracking**
- `POST /broker/leads`: Submit a new lead.
    ```json
    {
        "first_name": "Suresh",
        "last_name": "Patel",
        "phone": "9988776655",
        "project_id": 2,
        "budget_min": 5000000,
        "budget_max": 8000000
    }
    ```
- `GET /broker/leads`: Track status of submitted leads.
- `GET /broker/leads/{id}/timeline`: View transparency timeline for a specific lead.
- `GET /broker/leads/{id}/site-visits`: Track site visit status for the submitted lead.

### **Payouts & Commissions**
- `GET /broker/commissions`: View calculated commissions on converted leads.
- `POST /broker/payout-request`: Request withdrawal of eligible commission balance.

---

## 5. Common & Utility APIs

### **FCM Push Notifications**
- `POST /fcm-token`: Register mobile device token for push notifications.
    ```json
    { "fcm_token": "fcm_device_token_string_here" }
    ```



### **Team Chat (React Native Gifted Chat integration)**
- `GET /chat/conversations`: List active chat threads.
- `GET /chat/{chat}/messages`: Fetch messages for a conversation (paginated).
- `POST /chat/{chat}/messages`: Send a message.
    ```json
    { "message": "Hi team, anyone at site?" }
    ```

---
**Note for React Native Developers**: 
- Always ensure you handle `401 Unauthorized` by logging the user out and redirecting to the Login screen.
- Standard Validation errors return a `422 Unprocessable Entity` status with an `errors` object mapping fields to array of error strings. Handle these gracefully in UI forms.
