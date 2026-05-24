# Feature Delivered: File Upload & Landlord Verification System

I have successfully implemented the file upload architecture and connected the Landlord Verification flow to your backend MySQL database!

## 1. Database & ERD Updates
To keep your ERD and project report accurate, please make the following updates:

- **New Table (16th Table): `verifications`**
  I have appended this to `database/schema.sql`. It tracks Landlord verification requests.
  ```sql
  CREATE TABLE verifications (
      verification_id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT,
      nid_type ENUM('National ID', 'Passport', 'Driving License') NOT NULL,
      document_path VARCHAR(255) NOT NULL,
      description TEXT NULL,
      status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
      submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
  );
  ```

- **New Table (17th Table): `watchlists`**
  This was added to replace the localStorage "saved properties" feature.
  ```sql
  CREATE TABLE watchlists (
      watchlist_id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT,
      listing_id INT,
      added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      UNIQUE(user_id, listing_id),
      FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
      FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE
  );
  ```
- **Relationship Note for ERD**: `users` has a 1-to-many relationship with `verifications`. `users` and `listings` both have a 1-to-many relationship with `watchlists`.

## 2. File Upload Architecture
- I created a new directory structure in your project folder: `uploads/verifications`, `uploads/listings`, and `uploads/items`.
- Created **`api/upload.php`**: This securely receives `multipart/form-data`, validates that it is an image or PDF, generates a unique random file name to prevent overwriting, saves it to the correct folder, and returns the relative path to be saved in the database.

## 3. Frontend & API Integrations

### Profile Page (Verifications)
- Updated `profile.html` with an `<input type="file">`.
- When a user submits their verification, the form first calls `api/upload.php` to save the file.
- It then calls the new **`api/verify.php`** to save the verification request and document path to the database.
- Added **`api/verif_status.php`** which dynamically updates the "Pending Review" / "Approved" badge based on actual DB status.

### Admin Dashboard
- Created **`api/admin_verifications.php`** to fetch all pending verifications from the DB.
- Updated `admin.html` to pull this data dynamically.
- The Admin table now features a **"View Document"** link that directly opens the uploaded file (e.g., `uploads/verifications/12345_abc.jpg`) in a new tab.
- Created **`api/admin_action_verif.php`**: When the Admin clicks Approve, it sets the verification status to `approved` AND automatically executes an SQL query to set `is_verified = 1` for all listings owned by that user!

### Property Listings
- Updated `listings.html` so that the "Photo URL" field is now a standard File Upload field.
- The form now seamlessly uploads the property image to `uploads/listings/` using the same robust API before saving the new listing to the database.

## How to Test
1. Make sure XAMPP is running. Log in as a Landlord.
2. Go to your **Profile** and upload a dummy image in the Verification section. Click Submit.
3. You will see a "Pending Review" badge.
4. Log out and log in as the **Admin**.
5. You will see the new verification request on the Dashboard. Click **View Document** to verify the image successfully uploaded!
6. Click **Approve**.
7. Log back in as the Landlord, and try listing a property—you'll now be able to upload a photo for your property!
