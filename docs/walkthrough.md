# Walkthrough: Backend Migration (Phase 1)

I've completed the heavy lifting for **Phase 1: Backend Migration**. The app's "split brain" has been resolved for three major features: The Exchange, Seeking Posts, and Mess Bills. They are now fully wired to the live MySQL database via PHP APIs!

## What was accomplished

### 1. The Exchange (Marketplace)
- Added a new `photo_url` column to the `items` database table so users can post photos of their items.
- Built `api/exchange.php` to handle fetching all active items and posting new items securely.
- Updated `exchange.html` so the grid loads items from the live database. When a user fills out the "Sell an Item" form, it instantly posts to the database and reloads the grid.

### 2. Looking For (Seeking Posts)
- Built `api/seeking.php` to handle roommate requests.
- Updated `seeking.html` to load live posts.
- The "+ Post a Request" form now validates input and correctly saves it to the `seeking_posts` table linked to the user's account.

### 3. Mess Bill Manager
- Added a `resident_label` column to the `bill_payments` table to easily track splits like "Resident 1", "Resident 2" without requiring every resident to have a registered account.
- Built `api/bills.php` with complex logic:
  - `GET`: Fetches all bills and their payment tracker status for a specific property.
  - `POST`: Logs a new month's bill, calculates the per-person split, and auto-generates the `bill_payments` tracker rows. If you submit the same month twice, it cleverly updates the existing bill instead of creating a duplicate.
  - `PUT`: Marks a resident's payment as "paid" securely.
- Updated `bills.html` to fetch the logged-in user's properties from the live database, allowing them to manage and track real bills.

## Verification
- XAMPP was restarted and tested.
- Schema alterations executed perfectly.
- All three APIs (`exchange.php`, `seeking.php`, `bills.php`) were wired into the frontend Javascript controllers.
- Watchlist and Profile preferences are successfully saving to the database and correctly persisting across page loads.
- Global event delegation fixed the "double fire" bug for the watchlist heart icon.
- Leaflet map rendering fixed to prevent unloaded gray tiles.

# Phase 3: Admin Dashboard Enhancements (Complete)
The Admin Dashboard (`admin.html`) is now fully powered by live data from the database, dropping all reliance on the frontend `mockData` array.

## Core Implementations
1. **System Analytics Pipeline (`api/admin_stats.php`)**
   - The backend now actively computes live system metrics: Total Listings, Open Complaints, and Average Rent aggregated by Zone using `GROUP BY`.
   - Demand vs. Supply intelligence is actively calculated by comparing the volume of active `seeking_posts` against available `listings` in each zone.
2. **Complaints Management (`api/admin_complaints.php` & `api/admin_action_complaint.php`)**
   - The dashboard dynamically pulls all user complaints with `JOIN` statements to resolve the Complainant and the associated Listing Owner.
   - The "Resolve" and "Review" action buttons issue POST requests to update the complaint state in the live database.
3. **Moderation Controls (`api/admin_action_listing.php`)**
   - Admins can now completely delete severe or fraudulent listings directly from the dashboard. This securely invokes a `DELETE` query that cascades through the database to clean up associated amenities, costs, and complaints.
4. **Session Security Fix**
   - Corrected authorization bug where admin actions were incorrectly reading the user's role from the session. All 8 admin APIs now securely check `$_SESSION['user']['role']`.

## Verification
- The top summary cards (Total Users, Total Listings, Open Complaints) accurately reflect database totals.
- Chart.js successfully consumes the JSON output from `admin_stats.php` to render the Bar Charts.
- Clicking the Action buttons (`Resolve`, `Delete Listing`) actively modifies the database and automatically refreshes the UI via the `loadAdminData()` flow.

---

# Walkthrough: Backend Migration (Phase 2)

Phase 2 tackled the Watchlist and User Profiles, moving them out of `localStorage` and into the real MySQL database.

## What was accomplished

### 1. Cloud Watchlist
- Built `api/watchlist.php` to act as a toggle switch (add/remove) for saved listings.
- Rewrote the global Javascript functions in `app.js` to automatically fetch your saved listings when you log in. 
- The heart buttons across the app now sync directly with the `watchlists` database table. If you favorite a property on your laptop, it will be favorited on your phone too!

### 2. User Profiles & Preferences
- Added the missing "Cleanliness Score" slider to the frontend `profile.html` page.
- Allowed users to edit their registered email address, as requested.
- Built `api/profile.php` to handle updates for both basic info (`users` table) and housing preferences (`user_preferences` table).
- Integrated the database preferences directly into the session payload at login, meaning the **Match Percentage** calculator now runs accurately against live data from the server instead of mock data!

> [!TIP]
> Try going to your Profile, changing your "Sleep Schedule" or "Cleanliness", and then browsing the Listings page. You'll see your Match Percentages against other students instantly recalculate!

---

# Phase 4: Dashboard Comprehensive Edit Listing & Image Overhaul

This phase radically upgraded the Edit Listing capability from the user Dashboard, and completely decoupled the frontend from mock data imagery.

## Core Implementations

1. **Expanded Edit Listing Modal**
   - Replaced the tiny, basic "Edit" modal in `dashboard.html` with a gigantic, comprehensive form mirroring the "Create Listing" interface.
   - Added full Address, Description, detailed Utility costs (Gas, Electricity, Water, Internet, Maintenance, Caretaker), and a full Amenities checklist.
   - Smart State Loading: Opening the modal now asynchronously queries `api/listings.php?id=X` to fetch the complete, live dataset for that property (including complex joined tables) and seamlessly populates every single input field in the modal.

2. **Backend PUT Integration (`api/listings.php`)**
   - Added a highly robust `PUT` HTTP method listener to handle the incoming comprehensive payload.
   - Updates `listings`, `utility_costs`, and `listing_amenities` inside a single secure database transaction (`pdo->beginTransaction()`).

3. **Complete Mock Data Image Decoupling**
   - Removed all hardcoded fallbacks to external Unsplash image URLs across the entire codebase (`dashboard.html`, `listing-detail.html`, `app.js`, `api/listings.php`, `api/exchange.php`, `data.js`).
   - Implemented dynamic database JSON parsing for the watchlist images (`l.photos`).
   - Replaced the external Unsplash fallback with a fully self-contained, inline SVG Graphic ("No Photo Available") that prevents broken image links natively without relying on external internet connections or testing data.
