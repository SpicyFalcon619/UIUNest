# UIUNest

![PHP](https://img.shields.io/badge/PHP-Backend-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=flat-square&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-Frontend-E34F26?style=flat-square&logo=html5&logoColor=white)
![Railway](https://img.shields.io/badge/Deployed_on-Railway-0B0D0E?style=flat-square&logo=railway&logoColor=white)

**Live Platform:** [https://uiunest-production.up.railway.app/](https://uiunest-production.up.railway.app/)

UIUNest is a comprehensive platform designed for UIU (United International University) students, landlords, and administrators. It covers verified housing discovery, flatmate compatibility matching, a peer-to-peer exchange marketplace, mess bill management, and a full admin dashboard — all in a single, mobile-friendly web application.

### What's New in v1.2.0 (Final Polish Release)
- **Responsive Mobile Perfection**: Fixed overflow issues on the Mess Bill Manager and Profile pages, ensuring custom dropdowns and file uploads flawlessly adapt to small screens.
- **Sleek Navbar Behavior**: Engineered a smart navbar that remains permanently pinned on the homepage, but smoothly auto-hides with a graceful transition on sub-pages to maximize vertical screen real estate. The mobile floating pill remains safely anchored to the bottom.
- **Natural Copywriting**: Completely rewrote the homepage marketing copy to sound conversational, warm, and distinctly human (saying goodbye to robotic AI phrasing and em dashes).
- **Refined Aesthetics**: Softened the active navigation pill radiuses to perfect the "squircle" look, and precisely balanced the white space and vertical margins across the hero section for a premium, airy feel.

## Features

### For Students
- **Housing Listings:** Browse verified properties around the UIU campus with Leaflet maps, zone filters, and price ranges.
- **Flatmate Matching:** Find roommates with a weighted compatibility score based on sleep schedule, diet, noise tolerance, cleanliness, and guest policy.
- **Exchange Marketplace:** Buy and sell furniture, appliances, and study materials with direct offer/counter-offer negotiation.
- **Mess Bill Manager:** Track, split, and pay monthly utility and rent bills among housemates.
- **Looking For:** Post or respond to "Looking For" ads to find accommodation quickly.
- **Watchlist:** Save favourite listings for later.
- **Notifications:** Real-time in-app notifications for verification updates, offer responses, and more.

### For Landlords
- **Property Listings:** Create and manage rental listings with photos, amenities, and pricing.
- **Tenant Applications:** Review and accept or reject tenancy applications.
- **Verification:** Upload identity documents for admin verification to earn a verified badge on all listings.
- **Bills Management:** Create and manage monthly bills for tenants.

### For Master Admin
- **User Management:** View all users, suspend/activate accounts, and revoke verifications.
- **Verification Review:** Approve or reject landlord/user verification document submissions with one click, automatically notifying the user.
- **Complaints Handling:** Review, escalate, and resolve complaints between users.
- **Admin Stats:** Real-time dashboard with total users, listings, pending verifications, and open complaints.

## Tech Stack
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Backend:** PHP 8+
- **Database:** MySQL
- **Icons:** [Lucide Icons](https://lucide.dev)
- **Maps:** [Leaflet.js](https://leafletjs.com)
- **Charts:** [Chart.js](https://www.chartjs.org/)

## Local Development & Setup

This project requires a PHP + MySQL server environment. [XAMPP](https://www.apachefriends.org/) is recommended.

### 1. Start Your Server
1. Open the XAMPP Control Panel and start **Apache** and **MySQL**.

### 2. Database Setup
1. Open `http://localhost/phpmyadmin` in your browser.
2. Create a new database named **`uiunest`**.
3. Select the `uiunest` database → go to the **Import** tab → upload `database/schema.sql` → click **Import**.

This creates all 19 tables including users, listings, verifications, notifications, marketplace items, bills, and more.

### 3. Place the Project in htdocs

#### Option A: Direct Folder (Recommended)
Clone or move the repository directly into XAMPP's `htdocs` directory:
- **Windows:** `C:\xampp\htdocs\UIU-Nest`
- **Mac:** `/Applications/XAMPP/htdocs/UIU-Nest`

#### Option B: Symbolic Link
Keep the repo anywhere and create a shortcut into `htdocs`.

**Windows (run as Administrator):**
```cmd
mklink /D "C:\xampp\htdocs\UIU-Nest" "C:\Users\YourName\Downloads\UIU-Nest"
```

### 4. Open the App
Navigate to: `http://localhost/UIU-Nest`

### 5. Master Admin Account
The schema includes a `reset_db.php` utility that seeds the master admin:
- **Login:** `ADM-MASTER` or `master@admin.com`
- **Password:** `1265Master`

> **Security Note:** Delete or password-protect `api/reset_db.php` before deploying to production.

## Project Structure

```
UIU-Nest/
├── api/                         # All PHP backend endpoints
│   ├── db.php                   # PDO database connection
│   ├── login.php                # Authentication + session creation
│   ├── logout.php               # Session teardown
│   ├── register.php             # New user registration
│   ├── me.php                   # Fetch current session user
│   ├── profile.php              # Profile read/update (incl. profile_pic)
│   ├── upload.php               # Secure file upload handler (images, PDFs)
│   ├── listings.php             # Listing CRUD
│   ├── applications.php         # Tenancy applications
│   ├── watchlist.php            # User watchlist (save/remove listings)
│   ├── bills.php                # Mess bill create/read/pay
│   ├── exchange.php             # Marketplace item CRUD
│   ├── offers.php               # Offer/counter-offer management
│   ├── complaint.php            # Submit complaints
│   ├── seeking.php              # Looking-For post CRUD
│   ├── seeking_responses.php    # Responses to seeking posts
│   ├── verify.php               # Submit verification request
│   ├── verif_status.php         # Check own verification status
│   ├── notifications.php        # Fetch + mark-read notifications
│   ├── dashboard.php            # Dashboard data aggregation
│   ├── update_status.php        # Update listing/offer/application status
│   ├── check.php                # Auth check helper
│   ├── admin_stats.php          # Admin dashboard stats
│   ├── admin_users.php          # List all users for admin
│   ├── admin_action_user.php    # Admin suspend/activate user
│   ├── admin_verifications.php  # List all verification requests
│   ├── admin_action_verif.php   # Admin approve/reject verification
│   ├── admin_revoke_verif.php   # Admin revoke verification
│   ├── admin_complaints.php     # List all complaints for admin
│   ├── admin_action_complaint.php # Admin resolve/escalate complaint
│   ├── admin_action_listing.php # Admin delete listing
│   ├── alter_db_interactions.php # One-time DB migration (seeking_responses + notifications tables)
│   └── reset_db.php             # Dev only — truncates all tables + seeds master admin
├── database/
│   ├── schema.sql               # Full MySQL schema (19 tables)
│   ├── seed.sql                 # Optional sample data
│   └── seed.php                 # PHP seeder script
├── docs/
│   ├── system_architecture.md   # Technical architecture overview
│   ├── walkthrough.md           # Feature implementation log
│   ├── changelog_vs_report.md   # Deviations from original academic ERD
│   ├── implementation_plan.md   # Current/past dev plan
│   └── task.md                  # Dev task checklist
├── uploads/                     # User-uploaded files (gitignored)
│   ├── listings/
│   ├── items/
│   ├── verifications/
│   └── profiles/
├── admin.html                   # Admin dashboard (admin-only)
├── bills.html                   # Mess bill manager
├── dashboard.html               # User dashboard (listings, offers, watchlist, bills)
├── exchange.html                # Marketplace browse
├── index.html                   # Home / landing page
├── item-detail.html             # Single marketplace item view
├── listing-detail.html          # Single housing listing view
├── listings.html                # Housing listings browse
├── login.html                   # Login page
├── profile.html                 # Profile + preferences + verification
├── register.html                # Registration page
├── seeking.html                 # Looking For posts
├── app.js                       # Core JS: nav, auth, localStorage, utilities
├── data.js                      # Static seed/fallback data
├── style.css                    # Global design system
└── .gitignore
```

## Developer Documentation
For teammates and future developers, the `docs/` folder contains up-to-date architectural notes:
- `docs/system_architecture.md` — Technical overview of the frontend Chrome system, database schema, API flows, and feature algorithms.
- `docs/walkthrough.md` — Log of all major completed implementations and what changed.

### Note for AI Assistants
If you are using an AI assistant to help build this project, instruct it to read `docs/system_architecture.md` and `docs/walkthrough.md` to instantly understand the backend architecture and history of changes.
