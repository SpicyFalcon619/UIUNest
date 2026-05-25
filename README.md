# UIUNest

UIUNest is a comprehensive platform designed for UIU (United International University) students, landlords, and administrators. It covers verified housing discovery, flatmate compatibility matching, a peer-to-peer exchange marketplace, mess bill management, and a full admin dashboard — all in a single, mobile-friendly web application.

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

> ⚠️ **Security Note:** Delete or password-protect `api/reset_db.php` before deploying to production.

## Project Structure

```
UIU-Nest/
├── api/                   # All PHP backend endpoints
│   ├── db.php             # Database connection
│   ├── login.php          # Authentication
│   ├── profile.php        # Profile read/update (incl. profile picture)
│   ├── listings.php       # Listing CRUD
│   ├── exchange.php       # Marketplace items & offers
│   ├── bills.php          # Mess bill management
│   ├── notifications.php  # In-app notifications
│   ├── admin_*.php        # Admin-only action endpoints
│   ├── upload.php         # File upload handler (images, docs)
│   └── reset_db.php       # DB reset + master admin seeder
├── database/
│   └── schema.sql         # Full MySQL schema (19 tables)
├── uploads/               # User-uploaded files (gitignored)
├── docs/                  # Developer documentation
├── *.html                 # Page files (index, listings, dashboard, etc.)
├── app.js                 # Core JS: nav, auth, utilities, shared state
├── style.css              # Global stylesheet
└── data.js                # Static seed/fallback data
```

## Developer Documentation
For teammates and future developers, the `docs/` folder contains up-to-date architectural notes:
- `docs/system_architecture.md` — Technical overview of the frontend Chrome system, database schema, API flows, and feature algorithms.
- `docs/walkthrough.md` — Log of all major completed implementations and what changed.

### Note for AI Assistants
If you are using an AI assistant to help build this project, instruct it to read `docs/system_architecture.md` and `docs/walkthrough.md` to instantly understand the backend architecture and history of changes.
