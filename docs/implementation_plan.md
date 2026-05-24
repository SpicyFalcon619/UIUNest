# Implement File Upload & Admin Verification System

This plan details how we will introduce secure file uploading to the platform, starting with landlord verification documents and laying the groundwork for listing photos and exchange item photos.

## Proposed Changes

### 1. File Storage Architecture
We need a place to actually store the images.
- [NEW] Create an `uploads/` directory with subdirectories:
  - `uploads/verifications/` (For NID, Passports, etc.)
  - `uploads/listings/` (For property photos)
  - `uploads/items/` (For exchange feature photos)
- [NEW] `api/upload.php`: A universal endpoint to receive `multipart/form-data`. It will securely move the file to the correct subdirectory based on the request and return the relative path (e.g., `uploads/verifications/123456_nid.jpg`).

---

### 2. Database Schema Updates
We need a dedicated table to track verification requests and their associated documents.

- [MODIFY] `database/schema.sql` (and execute the following SQL):
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

---

### 3. Frontend & API Integration: Profile (Submitting Verification)
- [NEW] `api/verify.php`: API endpoint to insert the verification request into the database.
- [MODIFY] `profile.html`: 
  - Add a file input field: `<input type="file" id="vDoc" accept="image/*,.pdf" required>` to the Landlord Verification form.
  - Modify the JavaScript so it first posts the file to `api/upload.php` to get the image path, and then posts that path along with the other form details to `api/verify.php`.

---

### 4. Frontend & API Integration: Admin (Reviewing Verification)
- [NEW] `api/admin_verifications.php`: API to fetch all pending verifications from the database.
- [NEW] `api/admin_action_verif.php`: API to approve or reject a verification (which updates the `verifications` table and also sets `is_verified` to true on all listings owned by that user).
- [MODIFY] `admin.html`:
  - Fetch pending verifications via PHP.
  - Update the table UI to include a "View Document" link that opens the uploaded file (e.g., NID image) so the Admin can actually inspect it before clicking Approve.

---

### 5. Future-Proofing Listings
- [MODIFY] `listings.html`:
  - Replace the "Photo URL (Optional)" text input with an actual file upload input `<input type="file" id="lstPhoto" accept="image/*">`.
  - Update `saveNewListing()` to upload the file first via `api/upload.php`, and then pass the resulting path to `api/listings.php`.

## User Review Required

> [!IMPORTANT]
> Because PHP handles file uploads, we need to make sure your XAMPP environment is configured to allow `file_uploads` (it is by default). Does this flow (uploading to an `uploads/` folder, saving the path to the DB, and showing the path in the admin panel) sound good to you? Once you approve, I'll build the upload API and wire up the UI!
