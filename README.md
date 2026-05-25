# UIUNest

UIUNest is a comprehensive platform designed for UIU (United International University) students to find verified housing, match with compatible flatmates, and safely buy or sell items in a local marketplace.

## Features
- **Housing Listings:** Browse and list verified properties around the UIU campus with integrated Leaflet maps.
- **Flatmate Matching:** Find flatmates based on compatibility scores (study habits, sleep schedules, cleanliness, etc.).
- **Exchange Marketplace:** Buy and sell furniture, appliances, and study materials safely with direct offer negotiation.
- **Mess Bill Manager:** Easily track and split monthly utility and rent bills among residents.
- **Landlord Verification:** Admin verification for landlords and property owners to ensure safety and authenticity.

## Tech Stack
- **Frontend:** HTML, CSS, JavaScript (Vanilla)
- **Backend:** PHP
- **Database:** MySQL
- **Icons:** [Lucide Icons](https://lucide.dev)
- **Maps:** [Leaflet.js](https://leafletjs.com)
- **Charts:** [Chart.js](https://www.chartjs.org/)

## Local Development & Setup
Because this project uses PHP and MySQL, **you must use a local server environment like XAMPP** to run the application.

### 1. Database Setup
1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Open a browser and navigate to `http://localhost/phpmyadmin`.
3. Create a new database named **`uiunest`**.
4. Click on the `uiunest` database, go to the **Import** tab.
5. Upload the `database/schema.sql` file located in this repository and click Import. This will generate all the necessary tables (users, listings, verifications, etc.).

### 2. Codebase Setup
You have two options to serve the codebase via XAMPP:

#### Option A: Direct Folder (Recommended)
Simply clone or move this entire repository directly into your XAMPP `htdocs` directory.
- Windows: `C:\xampp\htdocs\UIU-Nest`
- Mac: `/Applications/XAMPP/htdocs/UIU-Nest`

#### Option B: The "Shortcut" Method (Symlink)
If you prefer to keep the repository in your Documents or Downloads folder, you can create a symbolic link (shortcut) inside `htdocs` that points to your folder.

**On Windows:**
1. Open Command Prompt as **Administrator**.
2. Run the following command (replace the paths with your actual paths):
   ```cmd
   mklink /D "C:\xampp\htdocs\UIU-Nest" "C:\Users\YourName\Downloads\UIU-Nest"
   ```

### 3. Run the App
Once the database is imported and the folder is in `htdocs` (or symlinked), open your browser and go to:
`http://localhost/UIU-Nest`

#### Master Admin Account
The database comes pre-configured with a master admin account to access the dashboard.
- **Login ID**: `ADM-MASTER` (or `master@admin.com`)
- **Password**: `1265Master`

### 4. Developer Documentation
For teammates and future developers, check the `docs/` folder for up-to-date architectural notes:
- `docs/system_architecture.md`: A full technical walkthrough of the frontend Chrome system, database structure, API flow, and feature algorithms.
- `docs/walkthrough.md`: A log of recently completed technical implementations.
- `docs/changelog_vs_report.md`: Tracks architectural changes and deviations from the original UIUNest academic report and ERD.

### Note for AI Assistants
If you are using an AI assistant to help build this project, instruct it to read `docs/system_architecture.md`, `docs/walkthrough.md`, and `docs/task.md` to instantly catch up on the backend architecture and what has already been built.
