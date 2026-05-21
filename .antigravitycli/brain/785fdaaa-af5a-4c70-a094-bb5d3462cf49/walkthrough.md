# UIUNest Verification Walkthrough

This document outlines the login credentials for all pre-made accounts and provides step-by-step instructions to verify the dynamic flows in the UIUNest frontend prototype.

## Pre-made Demo Credentials

| Role | Email | Password | Additional Details |
| :--- | :--- | :--- | :--- |
| **Student** | `student@uiu.ac.bd` | `student123` | ID: `011202001`, Gender: Male |
| **Landlord** | `landlord@uiu.ac.bd` | `landlord123` | Role: landlord, Gender: Male |
| **Master Admin** | `admin@uiu.ac.bd` | `admin123` | Role: admin, Full dashboard access |

---

## Key Verification Steps

### 1. Clean Database Verification
- Open the application in your browser (`index.html` or `listings.html`).
- Navigate to **Listings**, **Exchange**, and **Looking For**.
- **Result**: Verify that there are zero pre-existing listings, exchange items, or seeking posts. The marketplace is a completely clean slate, ready for your first posts.

### 2. Login Flow
- Click **Login** in the navigation bar.
- Try entering invalid credentials (e.g., wrong password) to verify toast error feedback.
- Enter any of the pre-made credentials listed above (e.g., student or landlord) and click **Log In**.
- **Result**: You are logged in and redirected to the user dashboard page, showing dynamic initials in the nav avatar.

### 3. Landlord Listing Flow & Verification
- Log in as the Landlord: `landlord@uiu.ac.bd` / `landlord123`.
- Go to the **Listings** page.
- Click the **+ List Property** button.
- Fill out the form with mock data (e.g., Base Rent: `৳6,000`, Gas Bill: `৳800`, water, internet) and specify amenities and preferences.
- Click **Publish Listing**.
- **Result**: A success toast appears, the property card is rendered on the listings grid and marked on the Leaflet map. Since this landlord is not verified yet, the listing does *not* display a "Verified" badge.

### 4. Admin Verification Flow
- Click your profile avatar on the top right, select **Admin Panel** (or log in as Master Admin: `admin@uiu.ac.bd` / `admin123` if not already logged in).
- In the **Admin Dashboard**, notice that the "Total Listings" counter has increased to `1`.
- Submit a verification request as a landlord (via profile or simulation data), or inspect the **Verification Requests** table.
- Click **Approve** next to a verification request.
- Go back to the **Listings** page.
- **Result**: The landlord's property listing now dynamically displays a gold **✓ Verified** badge!

### 5. Roommate Compatibility & Match Score (Student Perspective)
- Log in as the Student: `student@uiu.ac.bd` / `student123`.
- Go to the **Listings** page and view the landlord's listing details.
- **Result**: The listing details display a dynamic compatibility match score based on the student's preferences (cleanliness, sleep schedules, guests, smoking). You can inspect the specific matched and unmatched preference items.

### 6. Exchange Marketplace and Bidding
- Navigate to the **Exchange** tab.
- Click **+ Sell Item** to list an item (e.g., a "Study Table" for `৳2,500`).
- Log in as another user (e.g., Student) and go to the listed item's page.
- Enter an offer price (e.g., `৳2,200`) and click **Submit Offer**.
- Log back in as the seller (Landlord/Student) and open your dashboard or the item page.
- **Result**: You can see the pending offer and click **Accept**, **Counter** (negotiate), or **Reject** to trigger live status updates.

### 7. Complaint Resolution
- As a logged-in student, open a listing detail page and locate the **Report / Complain** section.
- Select a category (e.g., "Mismatched Amenities") and submit a description.
- Go to the **Admin Panel** as Master Admin.
- Locate the **Complaints** section.
- **Result**: You will see the new complaint listed with status `Submitted`. Click **Review** to mark it as `Under Review`, and click **Resolve** to transition it to `Resolved`.
