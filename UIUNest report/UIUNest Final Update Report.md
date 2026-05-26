# UIUNest: Final Project Check-Up & Update Report

This report outlines exactly what we built, how the final application differs from the original `UIUNest_Project_Context.md`, and what needs to be updated in your official `UIUNest Report.docx` and ERD files before your final submission.

---

## 1. Major Architectural & Deployment Additions

These are significant technical achievements we completed that were not in the original plan. You should proudly highlight these in your final report!

### What We Added:

- **Live Cloud Deployment:** Successfully deployed the entire platform to **Railway**.
- **Dynamic Database Routing (`db.php`):** Implemented an environment-aware database connection. It connects to your local XAMPP database when developing locally, but automatically switches to Railway's cloud MySQL database in production using environment variables.
- **Nixpacks Configuration (`composer.json`):** Added a PHP package config to explicitly instruct Railway's Nixpacks builder to provision a PHP Apache server with the `pdo_mysql` extension.
- **Auto-Installer Script (`reset_db.php`):** Engineered a script that automatically parses and executes `schema.sql` to build the database from scratch if the tables are missing.

---

## 2. Database Schema Expansion (15 Tables → 19 Tables)

The original context planned for 15 tables. During development, we realized we needed 4 additional tables to properly normalize the data and support the features.

> [!IMPORTANT]
> **Update your Report:** You must update Section 8 (Schema Diagram + all 15 table schemas) to reflect **19 tables**.

### The 4 New Tables:

1.  **`verifications` (Table 16):** Moved out of the `users` table to properly handle document uploads, NID types, and verification status (`pending`, `approved`, `rejected`).
2.  **`watchlists` (Table 17):** Created a dedicated many-to-many junction table to allow users to save listings to their personal watchlist.
3.  **`seeking_responses` (Table 18):** Instead of handling "Looking For" replies via external messaging, we built a dedicated table to track responses to seeking posts.
4.  **`notifications` (Table 19):** Built a scalable notification system to alert users when verifications are approved, offers are made, or applications are accepted.

---

## 3. Specific Table Modifications

Several tables from the original plan were modified to fix SQL errors or add necessary functionality.

> [!WARNING]
> **Update your ERD:** Make sure your final ERD (`Untitled.png` / `Untitled.svg`) reflects these column changes.

- **`users`:** Added `profile_pic VARCHAR(255) NULL` to support user avatars (and the ability to remove them).
- **`listings`:** Added `photos TEXT NULL` to store comma-separated paths for property images.
- **`items` (Exchange):**
  - Renamed the `condition` column to `item_condition` (since `condition` is a reserved SQL keyword).
  - Added `photo_url VARCHAR(255) NULL`.
- **`bill_payments`:** We discovered that mess managers often need to add people to the bill who aren't registered on the app. We changed `resident_user_id` to allow `NULL` and added `resident_label VARCHAR(50) NULL` (e.g., "Guest" or "Rahim's Brother").
- **`seeking_posts`:** Changed `room_type` to `property_type` to maintain ENUM consistency with the `listings` table.

---

## 4. What to UPDATE in your Official Report (`UIUNest Report.docx`)

### Additions to Make:

1.  **Section 5 (Features):** Add a bullet point about the "Notification System" and "Live Cloud Deployment".
2.  **Section 8 (Schema):** Add the 4 new tables (`verifications`, `watchlists`, `seeking_responses`, `notifications`).
3.  **Section 9 (SQL Queries):** You now have excellent complex queries to showcase! For example, the weighted compatibility matching query in `api/match_flatmate.php` is a massive `JOIN` with complex math. Include that!
4.  **Cover Page / Intro:** Paste your live Railway URL (`https://uiunest-production.up.railway.app/`) and the GitHub repository link.

### Removals/Changes to Make:

1.  **Neighborhood Rating System:** In the original context, this was marked as "may be deprioritized". We did not build the `neighborhood_ratings` table to save time. Remove mentions of this specific feature from the report so the professor doesn't look for it.
2.  **Move-In Notification System:** We built standard notifications, but the specific "Notify Me when this room is vacant" feature was skipped. Remove this bullet point from the report.

---

## 5. Next Steps for the ERD

Your current ERD files (`Untitled.png` / `Untitled.svg`) are outdated. You need to generate a new one.

**How to generate the latest ERD in 2 minutes:**

1.  Open your local **phpMyAdmin** (XAMPP).
2.  Click on the `uiunest` database.
3.  Click on the **Designer** tab at the top.
4.  phpMyAdmin will automatically draw the entire 19-table ERD for you with all the relationship lines!
5.  Rearrange the boxes so they look clean, take a screenshot (or click Export), and replace the old image in your Word document.
