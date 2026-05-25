# Unseed Database & Implement ID-Based Login

The user has requested to clear the database (leaving only the master admin), and implement a feature allowing users to log in with either their Email or their ID (Student ID, Landlord ID, or Admin ID).

## User Review Required

Please review the following proposed changes and confirm if this aligns with your expectations.

## Proposed Changes

### 1. Database Wipe & Master Admin Creation
I will create a script (`api/reset_db.php`) that will:
- Temporarily disable foreign key checks.
- `TRUNCATE` all user-generated tables (`users`, `listings`, `complaints`, `monthly_bills`, `watchlists`, etc.). 
- **Note**: The `zones` table will NOT be truncated, as those are static locations needed for the map to work.
- Insert the master admin account:
  - Email: `master@admin.com`
  - Password: `password` (hashed securely)
  - ID: `ADM-MASTER`
  - Role: `admin`
- Run an `ALTER TABLE` query to make the `university_id` column `UNIQUE` to ensure two users cannot register with the exact same Student/Landlord ID, which is critical if they use it to log in.

### 2. Auto-Generating & Assigning IDs
I will modify `api/register.php` to handle IDs based on the user's role:
- **Students**: Will explicitly provide their Student ID during registration. The script will save this directly to `university_id`.
- **Landlords**: The script will automatically generate a random ID starting with `LND-` (e.g., `LND-837492`) and save it to `university_id`.
- **Admins**: If additional admins are ever created in the future, they will automatically be assigned an `ADM-` prefix.

### 3. Login Updates
- **Frontend (`login.html`)**: Change the "Email" input field to "Email or ID" and change the `type="email"` to `type="text"` so the browser allows non-email inputs.
- **Backend (`api/login.php`)**: Update the SQL query from `SELECT * FROM users WHERE email = ?` to `SELECT * FROM users WHERE email = ? OR university_id = ?`. This allows the exact same login form to seamlessly accept either credential.

## Open Questions
1. When generating the `LND-` IDs for landlords, should it be a random string (e.g. `LND-A7X9`) or a sequential number based on their database row (e.g. `LND-1001`)?
2. Do you want the password for `master@admin.com` to be exactly `password`, or something else?

## Verification Plan
1. Run the wipe script.
2. Verify I can log in using `master@admin.com` and `ADM-MASTER`.
3. Register a new student and log in using their custom Student ID.
4. Register a landlord and verify they are correctly assigned an `LND-` ID, and log in using it.
