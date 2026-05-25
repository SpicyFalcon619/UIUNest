# UIU-Nest: System Architecture & Walkthrough

This document provides a complete technical walkthrough of the **UIU-Nest** platform. It explains the core technologies, how the frontend and backend communicate, and how the major features function under the hood.

---

## 1. Technology Stack

UIU-Nest is built using a lightweight, highly-performant stack designed to be easy to deploy and maintain without heavy build steps (no Webpack, Node.js, or React required).

*   **Frontend**: Pure HTML5, CSS3, and Vanilla JavaScript (ES6+).
*   **Backend**: PHP 8+ using PDO (PHP Data Objects) for secure database queries.
*   **Database**: MySQL Relational Database.
*   **Server**: XAMPP (Apache + MySQL).
*   **Libraries**:
    *   **Leaflet.js**: Renders the interactive map on the Listings page.
    *   **Chart.js**: Renders the visual bar charts on the Admin Dashboard.
    *   **Lucide**: Provides the clean, modern SVG icons used throughout the UI.

---

## 2. Frontend Architecture (The User Interface)

The frontend uses a **Multi-Page Application (MPA)** pattern. Instead of a single JavaScript bundle rendering everything, the app uses separate HTML files (`listings.html`, `dashboard.html`, `admin.html`).

### The "Chrome" System (`app.js`)
To avoid duplicating the Navigation Bar and Footer in every single HTML file, UIU-Nest uses a custom JavaScript mounting system. 
1. Every HTML file has empty placeholder `<div>` tags (e.g., `<div id="nav-mount"></div>`).
2. At the bottom of the HTML, a script calls `mountChrome('pageName')`.
3. `app.js` runs this function, dynamically generating the HTML for the navbar (checking if the user is logged in to show the correct links) and injecting it into the placeholders.

### State Management & Authentication
*   **Backend Truth**: Actual security is handled by PHP Sessions (`$_SESSION`). When a user logs in, an encrypted cookie is sent to the browser. Every API request automatically includes this cookie, proving the user's identity to the server.
*   **Frontend Flags**: To make the UI feel instantaneous, the frontend stores a lightweight flag in the browser's `localStorage` (`uiunest_logged_in_v4`). The UI checks this flag to instantly know whether to show a "Login" button or a "Profile" menu without waiting for a server response.

### Custom Component Ecosystem
UIU-Nest completely avoids native browser `window.confirm()` or `window.alert()` dialogues. Instead, it relies on a standardized, visually-integrated **Custom Confirm Modal** (`#confirmModal`) built with Vanilla CSS and JS. Destructive actions (like deleting listings or requests) pass a callback function to `showConfirm(title, text, callback)`, which asynchronously executes the API fetch only upon explicit user confirmation, ensuring a seamless, native-app feel.

---

## 3. Backend & Database Architecture

The backend consists of standalone REST-like API endpoints located in the `/api/` directory.

### How an API Call Works (Example: The Watchlist)
1. **User Action**: The user clicks the heart icon on a listing.
2. **Frontend Request**: The JavaScript catches the click and fires an asynchronous `fetch()` request to `api/watchlist.php`, sending the `listing_id` in JSON format.
3. **Backend Processing**: 
   - `watchlist.php` wakes up and immediately checks `$_SESSION['user_id']`. If the user isn't logged in, it halts and returns an error.
   - It connects to the MySQL database using `db.php`.
   - It checks if the listing is already in the `watchlists` table. If it is, it executes a `DELETE` query. If it isn't, it executes an `INSERT` query.
4. **Response**: The PHP script sends a JSON response back to the browser (`{"success": true}`).
5. **UI Update**: The JavaScript reads the `true` response and physically turns the heart icon red.

---

## 4. Core Features Deep Dive

### A. The Smart Match Calculator
When a student logs in, the platform calculates a **Match Percentage** for every listing.
*   **How it happens**: The `calculateCompatibility()` function in `app.js` pulls the user's personal preferences (Sleep Schedule, Diet, Cleanliness Score) from their profile. It mathematically compares these against the "Resident Preferences" set by the landlord when they created the listing. Perfect matches score 100%, and penalties are deducted for mismatches (e.g., Night Owl vs Early Bird). If a hard limit is violated (e.g., Gender mismatch), the score instantly drops to 0%.

### B. Mess Bill Manager (`api/bills.php`)
Managing shared expenses is notoriously difficult for students.
*   **How it works**: The Dashboard allows students living in a shared room to log their monthly utility bills (Electricity, Gas, Water, Internet). 
*   **The Math**: The PHP script takes the total amounts, adds them up, and executes an `INSERT` query into the `monthly_bills` table, dividing the total by the `current_occupancy` of the listing to give an exact "Per Person" cost.

### C. The Admin Dashboard
The admin panel is a heavily optimized control center.
*   **Data Aggregation (`api/admin_stats.php`)**: Instead of fetching thousands of individual listings and counting them in JavaScript, the PHP backend uses complex SQL queries (like `COUNT(*)` and `AVG(total_monthly) GROUP BY zone_id`) to perform the heavy lifting directly on the database engine.
*   **Demand vs Supply Intelligence**: The backend compares the number of active "Seeking" posts in a specific zone against the number of available "Listings". If seekers outnumber listings, it triggers a warning alert on the admin dashboard, advising admins to recruit more landlords in that specific area.

---

## 5. Security Measures

1. **SQL Injection Prevention**: Every single database query in the `/api/` folder uses **Prepared Statements** via PDO. This means user input is strictly treated as text, making SQL injection mathematically impossible.
2. **Password Hashing**: User passwords are encrypted using `password_hash()` (bcrypt) before being stored in the `users` table. Even if the database were compromised, the passwords cannot be read.
3. **Session Hijacking Protection**: Critical actions (like deleting a listing) check the `$_SESSION['role']` directly on the server. A malicious user cannot bypass this by simply editing their frontend JavaScript role to "admin".
