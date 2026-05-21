# UIUNest Prototype Functionality Implementation Plan

This plan details the steps to transition the static UIUNest prototype into a fully workable and interactive web application using `localStorage` for client-side state persistence.

## User Review Required

> [!IMPORTANT]
> **No Backend Required:** As specified in the context, this is a DBMS lab frontend prototype. We are implementing persistence exclusively via `localStorage` on the client side. This will make the app fully workable, enabling product creation, detail updates, billing tracking, and admin actions that persist across page reloads.
> 
> **Tailwind vs Custom CSS:** The existing codebase relies on `style.css` which provides a custom utility system. We will continue using `style.css` variables and classes for styling new components (like modals) to maintain design consistency.

## Open Questions
No open questions at this stage. The planned scope covers all pages and requested functionality.

---

## Proposed Changes

We will modify several frontend pages to hook them into a central `localStorage` database, and add new modals/logic to support listing properties and items.

### Core Persistence Component

#### [MODIFY] [app.js](file:///C:/Users/hossa/Downloads/pixel-perfect-main/app.js)
- Add database initialization on page load: check if `uiunest_db` exists in `localStorage`. If not, seed it with `window.mockData` (loaded from `data.js`).
- Overwrite `window.mockData` with the parsed object from `localStorage` so that every file accessing `mockData` automatically receives the latest persistent state.
- Add `saveMockData()` helper to stringify and write `window.mockData` back to `localStorage`.
- Add a helper to compute dynamic room compatibility based on 8 criteria (sleep, diet, guest policy, smoking, noise, cleanliness, gender, and budget).

---

### User Dashboard and Profiles

#### [MODIFY] [profile.html](file:///C:/Users/hossa/Downloads/pixel-perfect-main/profile.html)
- Populate inputs on page load with data from `mockData.currentUser` and `mockData.currentUser.preferences`.
- Make "Save Changes" persist user personal info to `localStorage`.
- Make "Save Preferences" persist housing preferences to `localStorage`.
- Make "Submit Verification Request" create a verification record in `mockData.verifs` and update status.

#### [MODIFY] [dashboard.html](file:///C:/Users/hossa/Downloads/pixel-perfect-main/dashboard.html)
- Dynamically display currentUser's active listings (`ownerId === currentUser.id`).
- Add a **My Items** tab to list the user's exchange items.
- Dynamically load the **My Offers** tab (offers made by currentUser).
- Make the **Delete** button for listings actually remove the listing from `mockData.listings` and save.
- Add an **Edit** modal for listings, allowing users to modify details (rent, bills, amenities) and persist changes.

---

### Marketplace and Listings

#### [MODIFY] [listings.html](file:///C:/Users/hossa/Downloads/pixel-perfect-main/listings.html)
- If logged in, display a **+ List Property** button.
- Implement a **List Property Modal** containing fields: Title, Zone, Address, Property Type, Gender Preference, Rooms, Occupancy, Rent, detailed utility bills (electricity, water, gas, internet, maintenance, caretaker, other), and checkboxes for amenities.
- Save new listings with `ownerId = currentUser.id`, default lat/lng near the selected zone, and default photo.

#### [MODIFY] [listing-detail.html](file:///C:/Users/hossa/Downloads/pixel-perfect-main/listing-detail.html)
- Calculate the compatibility score and matched/mismatched list dynamically by comparing `mockData.currentUser.preferences` with the listing's `residentPreferences`.
- Make **Submit Review** functional: append a review to `mockData.reviews`, update the listing's `compositeScore` and `reviewCount`, save, and re-render.
- Make **Submit Report / Complaint** functional: append a complaint record to `mockData.complaints` and save.

#### [MODIFY] [exchange.html](file:///C:/Users/hossa/Downloads/pixel-perfect-main/exchange.html)
- If logged in, display a **+ Sell an Item** button.
- Implement a **Sell Item Modal** with fields: Title, Category, Condition, Price, Reason, Photo URL, and optional linked listing ID.
- Save new items to `mockData.exchangeItems` with `seller = currentUser.name` and save.

#### [MODIFY] [item-detail.html](file:///C:/Users/hossa/Downloads/pixel-perfect-main/item-detail.html)
- Pull and push offers directly from/to `mockData.offers` in `localStorage`.
- Make buyer's **Submit Offer** functional.
- Make seller's action buttons (**Accept**, **Counter**, **Reject**, **Withdraw**) functional, updating status in `localStorage` and marking items as sold when accepted.

---

### Seeking and Bills

#### [MODIFY] [seeking.html](file:///C:/Users/hossa/Downloads/pixel-perfect-main/seeking.html)
- Update `addPost(e)` to write the new seeking post directly to `mockData.seekingPosts` in `localStorage`.
- Make the **Respond** button show a dynamic confirmation dialog or log responses.

#### [MODIFY] [bills.html](file:///C:/Users/hossa/Downloads/pixel-perfect-main/bills.html)
- Hook "Enter this month's bills" fields into a new bill save function that inserts/updates the bill in `mockData.monthlyBills`.
- Make "Mark Paid" update the payment status in `mockData.monthlyBills` payments list.
- Enable the month selection dropdown to switch between saved bill records.

---

### Admin Portal

#### [MODIFY] [admin.html](file:///C:/Users/hossa/Downloads/pixel-perfect-main/admin.html)
- Replace static complaint and verification arrays with `mockData.complaints` and `mockData.verifs`.
- Hook up status update actions ("Review", "Resolve" for complaints; "Approve", "Reject" for verifications) to write back to `localStorage`.
- Make top stats cards dynamic based on actual length of listings, users, open complaints, and pending verifications.

---

## Verification Plan

### Automated/Manual Validation
1. **User Sign Up / Login**: Register a user, check that they are logged in, and verify their profile details load correctly.
2. **Profile & Preferences**: Save preferences, visit a listing, and confirm the compatibility score adapts to the new settings.
3. **List a Property**: Add a listing via listings page, check that it appears in browse view, displays on the Leaflet map, and is visible in the user's dashboard under "My Listings".
4. **Exchange Market**: List an item, verify it shows in exchange view. Switch users or submit an offer, verify the offer is visible on the dashboard and details pages. Use the seller account to accept/counter, confirming the item updates to sold.
5. **Bill Manager**: Add a bill, mark a resident's share as paid, reload, and verify the payment tracker is up to date.
6. **Admin Panel**: File a complaint on a listing, go to `admin.html`, verify the complaint appears, and click "Resolve" to see the "Open Complaints" count decrement.
