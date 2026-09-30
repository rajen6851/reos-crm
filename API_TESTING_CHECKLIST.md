# REOS API Testing Checklist

This document contains every single API endpoint defined in `routes/api.php`. Use this as a checklist while testing in Postman to ensure nothing is missed.

## 1. Authentication & Common
- [ ] `POST /api/auth/login` - Login
- [ ] `POST /api/auth/otp/verify` - Verify OTP
- [ ] `GET /api/me` - Get current user profile
- [ ] `PUT /api/auth/profile` - Update profile
- [ ] `POST /api/auth/logout` - Logout
- [ ] `POST /api/fcm-token` - Update FCM Push Token
- [ ] `DELETE /api/fcm-token` - Remove FCM Push Token

## 2. Webhooks & Support
- [ ] `GET /api/webhooks/lead-sources/{type}/{token}` - Verify Webhook
- [ ] `POST /api/webhooks/lead-sources/{type}/{token}` - Handle Webhook
- [ ] `GET /api/reports/summary` - General Reports Summary
- [ ] `GET /api/notifications` - Get Notifications
- [ ] `POST /api/notifications/{id}/read` - Mark Notification Read
- [ ] `GET /api/support/tickets` - List Support Tickets
- [ ] `POST /api/support/tickets` - Create Support Ticket
- [ ] `GET /api/support/tickets/{id}` - View Ticket Details
- [ ] `POST /api/support/tickets/{id}/reply` - Reply to Ticket
- [ ] `PATCH /api/support/tickets/{id}/status` - Update Ticket Status

## 3. Team Chat
- [ ] `GET /api/chat/conversations` - Fetch Conversations
- [ ] `GET /api/chat/{chat}/messages` - Fetch Messages
- [ ] `POST /api/chat/{chat}/messages` - Send Message
- [ ] `POST /api/chat/direct` - Start Direct Chat
- [ ] `POST /api/chat/group` - Create Group Chat

## 4. Sales Executive (`/api/executive/*`)
- [ ] `POST /api/executive/google-calendar/connect` - Connect Calendar
- [ ] `GET /api/executive/attendance` - Sales Attendance Index
- [ ] `POST /api/executive/attendance/clock-in` - Sales Clock-in
- [ ] `POST /api/executive/attendance/clock-out` - Sales Clock-out
- [ ] `GET /api/executive/attendance/leaves` - Sales Applied Leaves
- [ ] `POST /api/executive/attendance/leaves` - Sales Apply Leave
- [ ] `GET /api/executive/dashboard` - Sales Dashboard
- [ ] `GET /api/executive/follow-ups` - All Upcoming Follow-ups
- [ ] `PATCH /api/executive/follow-ups/{id}/status` - Update Follow-up Status
- [ ] `GET /api/executive/leads` - List Assigned Leads
- [ ] `POST /api/executive/leads` - Create Lead
- [ ] `POST /api/executive/leads/check-duplicate` - Check Duplicate Lead
- [ ] `GET /api/executive/leads/{id}` - Lead Details
- [ ] `PUT /api/executive/leads/{id}` - Update Lead
- [ ] `GET /api/executive/leads/{id}/timeline` - Lead Timeline
- [ ] `POST /api/executive/leads/{id}/status` - Update Lead Status
- [ ] `POST /api/executive/leads/{id}/assign` - Re-assign Lead
- [ ] `POST /api/executive/leads/{id}/notes` - Add Note to Lead
- [ ] `POST /api/executive/leads/{id}/calls` - Log Call
- [ ] `GET /api/executive/leads/{id}/follow-ups` - Lead Specific Follow-ups
- [ ] `POST /api/executive/leads/{id}/follow-ups` - Schedule Lead Follow-up
- [ ] `GET /api/executive/site-visits` - List Site Visits
- [ ] `POST /api/executive/site-visits` - Schedule Site Visit
- [ ] `POST /api/executive/site-visits/{id}/status` - Update Site Visit Status
- [ ] `POST /api/executive/site-visits/{id}/verify-visit` - Verify Site Visit
- [ ] `GET /api/executive/projects` - List Active Projects
- [ ] `GET /api/executive/projects/{id}/units` - Check Unit Availability
- [ ] `GET /api/executive/bookings` - List Own Bookings
- [ ] `POST /api/executive/bookings` - Create Booking
- [ ] `POST /api/executive/bookings/{id}/payments` - Record Booking Payment
- [ ] `POST /api/executive/bookings/{id}/skip-agreement-request` - Request Agreement Skip

## 5. Manager (`/api/manager/*`)
- [ ] `GET /api/manager/attendance` - Manager Attendance Index
- [ ] `POST /api/manager/attendance/clock-in` - Manager Clock-in
- [ ] `POST /api/manager/attendance/clock-out` - Manager Clock-out
- [ ] `GET /api/manager/attendance/leaves` - Manager Own Leaves
- [ ] `POST /api/manager/attendance/leaves` - Manager Apply Leave
- [ ] `GET /api/manager/dashboard` - Manager Dashboard
- [ ] `GET /api/manager/follow-ups` - Team Follow-ups
- [ ] `PATCH /api/manager/follow-ups/{id}/status` - Update Team Follow-up Status
- [ ] `GET /api/manager/team` - List Team Members
- [ ] `POST /api/manager/team/executives` - Add Team Executive
- [ ] `PUT /api/manager/team/executives/{id}` - Edit Team Executive
- [ ] `PATCH /api/manager/team/executives/{id}/status` - Update Exec Status
- [ ] `GET /api/manager/team/leaves` - List Team Leave Requests
- [ ] `POST /api/manager/team/leaves/{id}/approve` - Approve Team Leave
- [ ] `GET /api/manager/leads` - List Team Leads
- [ ] `GET /api/manager/leads/{id}` - Lead Details (Team)
- [ ] `POST /api/manager/leads/{id}/status` - Update Lead Status
- [ ] `POST /api/manager/leads/{id}/assign` - Re-assign Lead within Team
- [ ] `GET /api/manager/site-visits` - Team Site Visits
- [ ] `POST /api/manager/site-visits/{id}/status` - Update Team Site Visit Status
- [ ] `GET /api/manager/projects` - List Projects
- [ ] `GET /api/manager/projects/{id}/units` - List Project Units
- [ ] `GET /api/manager/bookings` - Team Bookings
- [ ] `GET /api/manager/distribution-rules` - Auto Assignment Rules
- [ ] `POST /api/manager/distribution-rules` - Create Rule

## 6. Broker (`/api/broker/*`)
- [ ] `GET /api/broker/dashboard` - Broker Dashboard
- [ ] `GET /api/broker/profile` - Broker Profile
- [ ] `POST /api/broker/bank-details` - Update Bank Details
- [ ] `POST /api/broker/payout-request` - Request Payout
- [ ] `POST /api/broker/leads` - Submit New Lead
- [ ] `GET /api/broker/leads` - List Submitted Leads
- [ ] `GET /api/broker/leads/{id}` - Submitted Lead Details
- [ ] `GET /api/broker/leads/{id}/timeline` - Lead Timeline for Broker
- [ ] `GET /api/broker/leads/{id}/site-visits` - Lead Site Visits
- [ ] `GET /api/broker/leads/{id}/booking` - Lead Booking Details
- [ ] `GET /api/broker/commissions` - Broker Commissions
- [ ] `GET /api/broker/payouts` - Broker Payouts History
- [ ] `GET /api/broker/projects` - View Projects
- [ ] `GET /api/broker/notifications` - Broker Notifications
- [ ] `POST /api/broker/notifications/{id}/read` - Mark Notification Read
