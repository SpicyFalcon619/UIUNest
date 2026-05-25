# UIUNest: Architecture Deviations Changelog

This document tracks all system architecture and database schema changes that deviate from the original `UIUNest report` and ERD (Entity Relationship Diagrams) provided at the project's inception. It ensures the team stays aligned on the current source of truth.

## 1. Complete Backend Migration (Mock to Live)
**Original Report Assumption:** The initial prototype and documentation heavily relied on frontend mock arrays (`mockData` in `data.js`) for rapid prototyping.
**Current Architecture:** The system has been fully migrated to a robust backend using PHP 8+ and MySQL. All data flows securely through standalone REST-like API endpoints located in the `/api/` directory (e.g., `api/listings.php`, `api/exchange.php`, `api/admin_stats.php`). The `mockData` object is completely obsolete.

## 2. Match Percentage Calculator Optimization
**Original ERD:** The ERD likely lacked granular tracking of specific matching metrics beyond basic strings.
**Current Architecture:** The `user_preferences` table was heavily customized. It now tracks exact parameters used by the algorithmic matching engine (`app.js -> calculateCompatibility()`):
- `sleep_schedule` (early_bird, night_owl, flexible)
- `diet` (veg, non_veg, flexible)
- `guest_policy` (strict, restricted, flexible)
- `smoking_tolerance` (0 or 1 integer flag)
- `noise_tolerance` (quiet, moderate, loud)
- `cleanliness_score` (integer 1-5)

## 3. The Unified "Email or ID" Authentication System
**Original Report:** Standard Email/Password login.
**Current Architecture:** The login system was upgraded to accept **either** a registered Email **or** a UIU Student/Landlord ID. 
- A `UNIQUE` constraint was added to the `university_id` column in the database to prevent ID collisions.
- **Auto-Generation:** If a Landlord registers, they do not input an ID. The system securely auto-generates a highly random 6-character ID starting with `LND-` (e.g., `LND-X7A4K1`). Master Admins use `ADM-MASTER`.

## 4. Admin Dashboard Analytics Engine
**Original Concept:** Simple counting of rows for the admin dashboard.
**Current Architecture:** The `api/admin_stats.php` endpoint was developed as an advanced analytics engine. Rather than fetching huge datasets into JavaScript, the backend performs the mathematical heavy lifting using SQL `GROUP BY`, `COUNT()`, and `AVG()` to calculate:
- Average Rent Aggregation by Zone
- Demand vs. Supply intelligence (comparing active `seeking_posts` against available `listings` per zone).

## 5. Global Event Delegation for Dynamic Elements
**Frontend Changes:** As elements like the Watchlist Heart Icon or the document viewer are injected asynchronously from the database, standard JavaScript `onclick` listeners attached on page load failed to work. The architecture shifted to **Global Event Delegation**, where events are caught at the `document.body` level to guarantee they trigger even on elements rendered a millisecond before the click.
