# UIUNest v1.0.0 — Feature Walkthrough

This document summarizes all features and technical implementations completed for the v1.0.0 release.

---

## Authentication & User Roles

- **Login / Register**: Students, Landlords, and Admin can all log in via email or university ID. Passwords are hashed with `bcrypt` (PHP `password_hash`).
- **Role-Based UI**: Navigation links, forms, and dashboard tabs are conditionally rendered based on the user's role (`student`, `landlord`, `admin`). Landlords do not see the "Looking For" section. Admins do not see marketplace/bills/notifications.
- **Session Management**: PHP sessions (`$_SESSION`) handle server-side auth. The client stores user data in `localStorage` under `uiunest_current_user_v4`.

---

## Profile & Profile Picture

- Users can edit their name, email, phone, and university ID from `profile.html`.
- **Profile Picture Upload**: A file input with an instant circular preview allows any user to upload a profile photo. The image is securely uploaded to `uploads/profiles/` via `api/upload.php`. The path is saved to the `profile_pic` column in the `users` table.
- After saving, the top-right navbar avatar immediately replaces the initials badge with the uploaded photo (via `mountChrome()` re-render).
- Profile pictures persist across sessions and page refreshes via `localStorage` + PHP session.

---

## Housing Listings

- Browse listings with Leaflet map integration, zone filters, and pagination.
- Listings can be created by landlords with multiple images, amenities, and pricing.
- Verified landlords get a **Verified badge** on all their listings.
- Students can apply directly to listings. Landlords can approve/reject applications.
- A **Watchlist** (heart icon) lets students save favourites, synced to the server.

---

## Flatmate Compatibility Matching

- Students fill in a preference profile: sleep schedule, diet, guest policy, smoking tolerance, cleanliness score, noise level.
- A weighted compatibility algorithm compares any two users' preferences and outputs a 0–100 score.
- Listings show a colour-coded compatibility badge when viewed while logged in as a student.

---

## Exchange Marketplace

- Any user can list items for sale (furniture, appliances, books, etc.) with photos and pricing.
- Buyers can make offers; sellers can accept, counter, or reject.
- A dedicated **Offers** tab in the Dashboard shows all incoming/outgoing offers and their status history.
- Both buyers and sellers can **delete** a completed or cancelled offer from their history.

---

## Mess Bill Manager

- Landlords can create monthly bills per listing, specifying electricity, gas, water, internet, and custom fee entries.
- Bills are split per resident and shown to tenants in their `bills.html` view.
- Tenants can mark their share as paid.

---

## Looking For (Seeking Posts)

- Students can post "Looking For" ads specifying budget, zone, property type, and preferred gender.
- Other users (landlords/students) can respond to these posts.
- **Hidden from Landlords** — the "Looking For" nav link is not shown to landlord accounts.

---

## Notifications

- An in-app notification bell appears in the navbar for non-admin users.
- Notifications are created server-side and displayed in a dropdown with unread count badge.
- Clicking a notification marks it as read and navigates to the relevant page.
- **Triggered automatically for**:
  - Verification approved → user notified, redirected to profile
  - Verification rejected → user notified, redirected to profile
  - Verification revoked by admin → user notified, redirected to profile

---

## Admin Dashboard

- Accessible only to users with `role = 'admin'`. Non-admin access is blocked server-side.
- **Stats Cards**: Total users, active listings, pending verifications, open complaints.
- **Users Tab**: View all users with role, status, and verification badges. Admin can:
  - **Suspend / Activate** accounts
  - **Revoke Verification** — removes verified status, un-verifies all listings, notifies the user, and allows them to re-apply
- **Verifications Tab**: Review pending verification document submissions. Admin can:
  - **Approve** — marks user as verified, verifies all their listings, sends approval notification
  - **Reject** — sends rejection notification
  - Previously revoked requests display a "Revoked" badge (not re-actioned)
- **Listings Tab**: View and permanently delete any listing platform-wide.
- **Complaints Tab**: Review, escalate to "Under Review", resolve, or delete the associated listing.

---

## User Verification Flow

1. Landlord/student visits `profile.html` and submits documents via the Verification form.
2. Admin reviews in the Admin Dashboard and approves or rejects.
3. On approval: all listings owned by the user get `is_verified = 1`. A green **Verified** badge appears on those listings.
4. On the profile page, if verification is approved/pending, the upload form hides and a status badge is shown instead.
5. If revoked: the form reappears so the user can re-submit fresh documents.

---

## File Uploads

All uploads are handled by `api/upload.php`, which:
- Accepts types: `listing`, `item`, `verification`, `profile`
- Saves to `uploads/{type}s/` with a unique randomised filename
- Only allows `jpg`, `jpeg`, `png`, `pdf` extensions
- Returns a relative path for storage in the database

---

## Key Files Reference

| File | Purpose |
|------|---------|
| `app.js` | Core JS: nav rendering, auth, localStorage, toast, utilities |
| `data.js` | Static seed/fallback data for offline/demo use |
| `style.css` | Global design system: variables, components, layout |
| `api/db.php` | PDO database connection |
| `api/login.php` | Login + session creation, returns full user object incl. `profile_pic` |
| `api/profile.php` | GET/POST profile info + preferences + profile_pic |
| `api/upload.php` | Secure file upload handler |
| `api/admin_action_verif.php` | Approve/reject verifications + send notifications |
| `api/admin_revoke_verif.php` | Revoke verification + notify user |
| `api/notifications.php` | Fetch and mark-as-read notifications |
| `api/reset_db.php` | Truncates all tables + seeds master admin (dev use only) |
| `database/schema.sql` | Full MySQL schema — 19 tables |

---

## v1.0.0 Release Notes

**Release Date:** May 2026  
**Tag:** `v1.0.0`

This is the first stable release of UIUNest. All core features across student housing, marketplace, mess bills, notifications, admin dashboard, user verification, and profile management are fully implemented and tested.

---

## v1.1.0 — UI Overhaul, Bug Fixes & Mobile UX Native Overhaul

**Release Date:** May 2026  
**Tag:** `v1.1.0`

This update focuses on visual polish, consistency, and critical bug fixes regarding compatibility scoring. It also completely revamps the mobile experience to feel like a native application.

### Mobile App-Like UX Enhancements
- **Bottom Navigation Bar:** Removed the clunky top hamburger menu and implemented a sticky Bottom Tab Bar for all screens under 600px. Links now feature native-style icons stacked above text.
- **Swipeable Bottom Sheets:** Re-engineered all floating modals to snap to the bottom of the screen with a drag pill indicator, perfectly mimicking iOS/Android bottom sheets.
- **Continuous Feeds:** Eliminated heavy outer borders and radii from listing cards on mobile, creating an edge-to-edge vertical scrolling feed akin to modern social apps.
- **Native Touch Response:** Added `-webkit-tap-highlight-color: transparent` globally to remove the default blue highlight boxes when tapping elements on touch screens.

### Design Updates
- **Compatibility Scoring Fixed**: Resolved a scoping bug in `listings.html` that caused the compatibility calculator in `app.js` to rely on stale `localStorage` user preferences. Match scores now instantly and accurately update when a user changes their profile preferences.
- **Match Badge UI Revamped**: Replaced the previous basic text with clean, solid gradient background blocks (Emerald, Blue, Amber) and white text for High, Good, and Low matches.
- **Dashboard Scalability**: Increased font and icon sizes for "Verification" and "Compatibility" status cards to align visually with the numerical statistic cards.
- **Navbar Width & Sticky Fix**: Removed the `1440px` max-width on `.nav-inner` (and subsequently reverted it to properly match `.container` alignment) and fixed the `position: sticky` bug on `index.html` by removing an offending `overflow-x: hidden` constraint from the global `body` tag.
- **Verified Filter Removed**: Removed the redundant "Verified Only" filter switch from `listings.html` as the platform currently assumes all valid landlord listings to be trustworthy or verified in this scope.
# # #   F i n a l   P o l i s h   P h a s e   ( v 1 . 2 . 0 )  
 -   P e r f e c t e d   r e s p o n s i v e   m o b i l e   l a y o u t s   o n   c o m p l e x   p a g e s   ( M e s s   B i l l s ,   P r o f i l e )   b y   a p p l y i n g   f l e x - w r a p   a n d   m a x - w i d t h   c o n s t r a i n t s   t o   p r e v e n t   h o r i z o n t a l   o v e r f l o w .  
 -   R e f i n e d   n a v b a r   l o g i c :   P e r m a n e n t   s t i c k y   b e h a v i o r   o n   t h e   h o m e p a g e ,   e l e g a n t   s c r o l l - t o - h i d e   t r a n s i t i o n s   o n   s u b - p a g e s ,   a n d   p r o t e c t e d   b o t t o m - f i x e d   p o s i t i o n i n g   o n   m o b i l e   d e v i c e s .  
 -   R e w r o t e   h o m e p a g e   m a r k e t i n g   c o p y   t o   s o u n d   s i g n i f i c a n t l y   m o r e   n a t u r a l   a n d   h u m a n ,   s t r i p p i n g   o u t   A I - i s m s .  
 -   A d j u s t e d   v e r t i c a l   s p a c i n g ,   m a r g i n s ,   a n d   b o r d e r   r a d i i   a c r o s s   t h e   U I   t o   e s t a b l i s h   a   p r e m i u m ,   b a l a n c e d   a e s t h e t i c .  
 