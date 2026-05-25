# Backend Migration Tasks (Priority 2)

- [x] **1. Cloud Watchlist**
  - [x] Create `api/watchlist.php` (GET/POST).
  - [x] Update `app.js` `toggleWatchlist()` to POST to the API.
  - [x] Update `app.js` `loadWatchlist()` to GET from the API.
- [x] **2. User Profiles & Preferences**
  - [x] Create `api/profile.php` (GET/POST).
  - [x] Update `profile.html` UI (Add Cleanliness Score 1-5).
  - [x] Update `profile.html` scripts to fetch/save preferences.
  - [x] Integrate live preferences into Match % calculator.

- [x] **3. Admin Dashboard Enhancements**
  - [x] Create `api/admin_stats.php` to fetch system metrics (totals, rent by zone, demand vs supply).
  - [x] Create `api/admin_complaints.php` and `api/admin_action_complaint.php` for fetching and resolving complaints.
  - [x] Create `api/admin_action_listing.php` to handle listing deletion.
  - [x] Update `admin.html` JS to replace `mockData` and wire up the UI buttons and charts to live API endpoints.
