# UIUNest — Full Project Context File
> Generated for continuity across AI sessions. Read this entirely before responding to any follow-up.

---

## 1. Who You Are Helping

A university student at **United International University (UIU)**, Dhaka, Bangladesh. They are working on a **DBMS Lab project** (Course: Database Management Systems Laboratory, Code: CSE 3522 / CSI 222) assigned by their course instructor. The team has **4–5 members**. The student is the one coordinating the project and report writing.

---

## 2. The Project — UIUNest

### What It Is
**UIUNest** is a web-based housing and community marketplace portal built exclusively for UIU students and faculty. It solves three real problems in one platform:
1. Finding transparent, verified rental housing near the UIU campus
2. Finding compatible flatmates for shared messes
3. Buying and selling household goods within the campus community

### The Name
**UIU** (the university name) + **Nest** (a home). In the report, the logo renders "UIU" in navy and "Nest" in gold — UIU's colors.

### Inspiration
Architecturally inspired by **CareTutors (caretutors.com)** — a Bangladeshi two-sided marketplace connecting tutors with students. UIUNest applies the same database-driven matching philosophy to student housing.

### Tech Stack
- **Backend:** PHP (framework optional but not required)
- **Database:** MySQL
- **Map:** Leaflet.js (interactive map with property pins) — decided to include but NOT as a graded feature
- **Frontend:** Standard HTML/CSS/JS (or any PHP framework)

### Course Requirements (must be demonstrated)
- At least one: SELECT, INSERT, UPDATE, DELETE
- At least one: aggregate function, JOIN query, subquery

---

## 3. The Problem Being Solved

Students near UIU face:
- **Hidden costs:** Landlords advertise base rent (e.g. 3,500 BDT) but don't disclose electricity, gas, water, maintenance, caretaker fees, or advance payments
- **Compatibility issues:** No way to find out if flatmates are compatible (sleep schedule, diet, noise, guests) before moving in
- **Trust deficit:** Landlords misrepresent properties, keep deposits, or charge extra after move-in
- **No structured search:** Everything happens via Facebook groups or door-to-door searching — no search, filter, or verification
- **Goods problem:** Students accumulate fans, tables, chairs, fridges, mattresses — and when they want to sell or buy, they have no campus-specific, zone-filtered platform to do it

---

## 4. User Roles

### Three Roles (single users table, differentiated by a `role` enum):

| Role | Can Do |
|---|---|
| **Student/Teacher** | Dual-purpose: can SEARCH for housing AND LIST a spare seat/room in their current mess (peer listing). Can also buy/sell on Exchange. |
| **Landlord/Mess Owner** | Creates full property listings with complete cost breakdowns. Can list items on Exchange. |
| **Admin** | Manages all users, listings, complaints, verifications, and analytics. |

**Important:** Students are BOTH searchers AND listers. A student who has a spare seat in their mess can post a peer listing — this is NOT only for landlords.

---

## 5. Complete Feature List (15 Feature Groups)

### 5.1 User Account Management
- Registration and login for all three roles
- Dual-purpose student role (searcher + peer lister)
- Profile: name, email, UIU student/faculty status, university ID, gender, phone
- Password reset, profile editing, soft-delete deactivation
- Session management with role-based access control

### 5.2 Property Listing System (Owners AND Student Listers)
Two types of listings:
- **Full property listings** (by landlords): property type (single room, shared room, full mess, sublet), address, GPS coordinates, room count, current occupancy, gender preference, photos, mandatory itemized cost form
- **Peer listings** (by students): spare seat/room in their mess, simplified form, displayed with a "Student Listed" badge in search results

Both types include:
- Mandatory itemized cost form: rent, electricity (individual or shared meter), gas, water, internet, building maintenance, caretaker contribution, other fees → system computes **total true monthly cost** automatically
- Availability status: Available / Occupied / Soon-to-be-vacant (with expected vacate date)
- Amenity tags: attached bathroom, kitchen, furnished, rooftop, parking, power backup, lift

### 5.3 Property Search and Discovery
- Filter by zone, rent range, room type, gender preference, amenities
- Sort by total monthly cost, composite rating, newest, compatibility score
- Leaflet.js interactive map with listing pins (UIU campus as default center)
- Personal watchlist (save listings across sessions)

### 5.4 Flatmate Compatibility Matching
- Every user fills a preference profile: sleep schedule, study hours, diet (vegetarian/non-veg/halal strict), guest policy, smoking tolerance, preferred flatmate gender, cleanliness (1–5), noise tolerance
- When a searcher views a shared mess, system computes a **compatibility percentage** by JOIN-querying their preferences against all current residents' profiles
- Compatibility tiers shown on listing cards: High Match (80%+), Good Match (60–79%), Partial Match (40–59%), Low Match (<40%)

### 5.5 Seeking Posts Board
- Searchers post "Looking For" requests: zone, budget range, room type, gender preference, move-in date, special requirements
- Others (landlords, mess members, fellow searchers) respond via internal messages
- Status: Active or Fulfilled; fulfilled posts archived for trend analysis

### 5.6 Multi-Dimensional Review System
- 5 separate score dimensions: Value for Money, Accuracy of Listing, Landlord Responsiveness, Cleanliness, Safety (each 1–5)
- Weighted composite score computed and stored
- UNIQUE(listing_id, reviewer_id) — one review per user per listing
- Optional text comment (500 chars)

### 5.7 Complaint and Moderation System
- Categories: Hidden costs charged, Harassment, Deposit not returned, Listing misrepresentation, Other
- Status: Submitted → Under Review → Resolved (admin-managed)
- 3+ unresolved complaints → automatic warning flag on all of landlord's listings
- Admin can suspend accounts

### 5.8 Landlord Verification System
- Landlords submit NID type, document description, submission date (metadata only for prototype)
- Admin approves/rejects → verified badge appears on all their listings
- Searchers can filter to show only verified listings

### 5.9 Rent History and Price Trend Tracker
- Every rent update archives old value + date into `rent_history` table (audit trail pattern — never modify historical records)
- Listing detail page shows price change timeline
- Admin can query average rent trends by zone and time range

### 5.10 Neighborhood Rating System
- Users rate zones (not individual properties) on: Safety, Transport Access, Market Proximity, Noise Level, Overall Satisfaction (1–5 each)
- Stored in `neighborhood_ratings` table (separate from property reviews)
- Each listing shows composite zone score alongside property score
- **Note:** This feature is designed but may be deprioritized if time is tight

### 5.11 Mess Committee Bill Management Module
- Designated mess manager enters monthly utility readings: electricity units, gas, water, internet, miscellaneous
- System divides total automatically among current residents
- Per-resident payment status tracked (Paid/Unpaid) individually
- Full monthly bill history stored → enables per-resident expense queries over time

### 5.12 Move-In Availability and Notification System
- Each room has its own availability status and expected vacate date
- "Notify Me" flag on any listing/room → system logs notification events when status changes to Available
- **Note:** This feature is designed but may be deprioritized if time is tight

### 5.13 UIUNest Exchange — Community Household Marketplace
**Critical design decision:** Exchange is open to ALL registered users at ANY time for ANY reason — not just people moving out. Reasons include: upgrading, clearing space, no longer using something, moving mess, etc.

Key features:
- List any household item: fans, study tables, chairs, mattresses, mini-fridges, bookshelves, kitchen items, lamps, curtains
- Item listing fields: category, condition (New/Like New/Good/Fair), asking price, description, zone location, optional reason for selling, up to 4 photos
- **Zone-filtered** — all Exchange items shown to a user are within their browsed zone, keeping buyers/sellers physically close
- **Optional housing link:** When a user marks a property as "Soon to Vacate," system prompts them to list items. When a searcher views a listing, items from current tenants are shown in a dedicated section
- **Structured Offer System:** Buyer submits a formal offer (stored in `offers` table with price, message, timestamp). Seller can Accept, Reject, or Counter. All states tracked with timestamps: Pending → Countered → Accepted/Rejected/Withdrawn
- Post-transaction reviews: Item Accuracy and Communication Quality
- Sold items archived for price history analysis

### 5.14 Admin Dashboard and Analytics
- Manage all users, listings, Exchange items, complaints, verifications
- Analytics: listings by zone, average rent by zone, complaint resolution time, most-searched zones, Exchange transaction volume by category, demand vs. supply ratio
- Demand gap identification: zones where seeking posts > available listings

### 5.15 Database-Level DBMS Requirements Coverage
- SELECT with multi-table JOINs: listings + owners + utility costs + reviews + zones + Exchange items
- INSERT: registration, listing, offer, review, complaint, bill entry
- UPDATE: listing status, rent update (triggers rent_history insert), offer state transitions, payment status
- DELETE: listing removal, item withdrawal, soft-delete accounts
- Aggregate functions: AVG (ratings, rent, offer prices), SUM (utility costs), COUNT (occupancy, complaints), MAX/MIN (rent/price ranges)
- Subqueries: listings cheaper than zone average; items with 2+ offer rounds; users with unresolved complaints; zones ranked by demand
- GROUP BY + HAVING: zone-wise rent aggregation; category transaction volume

---

## 6. Database Schema — All 14 Tables

### Table 1: users
| Column | Type | Constraint |
|---|---|---|
| user_id | INT | PK, AUTO_INCREMENT |
| name | VARCHAR(100) | NOT NULL |
| email | VARCHAR(150) | UNIQUE, NOT NULL |
| password_hash | VARCHAR(255) | NOT NULL |
| role | ENUM('student','landlord','admin') | NOT NULL |
| gender | ENUM('male','female','other') | NOT NULL |
| phone | VARCHAR(20) | NULL |
| university_id | VARCHAR(50) | NULL |
| status | ENUM('active','suspended') | DEFAULT 'active' |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |

### Table 2: zones
| Column | Type | Constraint |
|---|---|---|
| zone_id | INT | PK, AUTO_INCREMENT |
| zone_name | VARCHAR(100) | NOT NULL |
| description | TEXT | NULL |
| center_lat | DECIMAL(9,6) | NOT NULL |
| center_lng | DECIMAL(9,6) | NOT NULL |
| radius_km | DECIMAL(4,2) | NOT NULL |

### Table 3: listings
| Column | Type | Constraint |
|---|---|---|
| listing_id | INT | PK, AUTO_INCREMENT |
| user_id | INT | FK → users.user_id |
| zone_id | INT | FK → zones.zone_id |
| listing_type | ENUM('full_property','peer_listing') | NOT NULL |
| property_type | ENUM('single_room','shared_room','full_mess','sublet') | NOT NULL |
| title | VARCHAR(200) | NOT NULL |
| address | TEXT | NOT NULL |
| lat | DECIMAL(9,6) | NOT NULL |
| lng | DECIMAL(9,6) | NOT NULL |
| gender_pref | ENUM('male','female','any') | NOT NULL |
| total_rooms | INT | NOT NULL |
| current_occupancy | INT | DEFAULT 0 |
| status | ENUM('available','occupied','soon_vacant') | DEFAULT 'available' |
| expected_vacate_date | DATE | NULL |
| is_verified | BOOLEAN | DEFAULT FALSE |
| description | TEXT | NULL |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| updated_at | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

### Table 4: utility_costs
| Column | Type | Constraint |
|---|---|---|
| cost_id | INT | PK, AUTO_INCREMENT |
| listing_id | INT | FK → listings, UNIQUE (1-to-1) |
| base_rent | DECIMAL(10,2) | NOT NULL |
| electricity_amount | DECIMAL(8,2) | DEFAULT 0 |
| electricity_type | ENUM('individual','shared') | NOT NULL |
| gas_bill | DECIMAL(8,2) | DEFAULT 0 |
| water_bill | DECIMAL(8,2) | DEFAULT 0 |
| internet_cost | DECIMAL(8,2) | DEFAULT 0 |
| maintenance_fee | DECIMAL(8,2) | DEFAULT 0 |
| caretaker_fee | DECIMAL(8,2) | DEFAULT 0 |
| other_fees | DECIMAL(8,2) | DEFAULT 0 |
| total_monthly | DECIMAL(10,2) | NOT NULL (computed and stored) |
| updated_at | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

**Design note:** `total_monthly` is a stored computed field (not live-computed on query) because it is the most-queried field. Application layer updates it whenever any component cost changes.

### Table 5: listing_amenities
| Column | Type | Constraint |
|---|---|---|
| amenity_id | INT | PK, AUTO_INCREMENT |
| listing_id | INT | FK → listings, UNIQUE (1-to-1) |
| attached_bathroom | BOOLEAN | DEFAULT FALSE |
| attached_kitchen | BOOLEAN | DEFAULT FALSE |
| is_furnished | BOOLEAN | DEFAULT FALSE |
| rooftop_access | BOOLEAN | DEFAULT FALSE |
| parking | BOOLEAN | DEFAULT FALSE |
| power_backup | BOOLEAN | DEFAULT FALSE |
| lift_access | BOOLEAN | DEFAULT FALSE |

### Table 6: user_preferences
| Column | Type | Constraint |
|---|---|---|
| pref_id | INT | PK, AUTO_INCREMENT |
| user_id | INT | FK → users, UNIQUE (1-to-1) |
| sleep_schedule | ENUM('early','late','flexible') | NOT NULL |
| study_hours | INT | DEFAULT 0 |
| diet | ENUM('vegetarian','non_veg','halal_strict') | NOT NULL |
| guest_policy | ENUM('allowed','restricted','not_allowed') | NOT NULL |
| smoking_tolerance | BOOLEAN | DEFAULT FALSE |
| preferred_gender | ENUM('male','female','any') | NOT NULL |
| cleanliness_score | INT | CHECK (1–5) |
| noise_tolerance | ENUM('quiet','moderate','noisy') | NOT NULL |

### Table 7: reviews
| Column | Type | Constraint |
|---|---|---|
| review_id | INT | PK, AUTO_INCREMENT |
| listing_id | INT | FK → listings |
| reviewer_id | INT | FK → users |
| value_for_money | INT | CHECK (1–5) |
| listing_accuracy | INT | CHECK (1–5) |
| landlord_response | INT | CHECK (1–5) |
| cleanliness | INT | CHECK (1–5) |
| safety | INT | CHECK (1–5) |
| composite_score | DECIMAL(3,2) | NOT NULL |
| comment | VARCHAR(500) | NULL |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| | | UNIQUE(listing_id, reviewer_id) |

### Table 8: complaints
| Column | Type | Constraint |
|---|---|---|
| complaint_id | INT | PK, AUTO_INCREMENT |
| complainant_id | INT | FK → users |
| against_user_id | INT | FK → users |
| listing_id | INT | FK → listings, NULL |
| category | ENUM('hidden_costs','harassment','deposit_not_returned','misrepresentation','other') | NOT NULL |
| description | TEXT | NOT NULL |
| status | ENUM('submitted','under_review','resolved') | DEFAULT 'submitted' |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| resolved_at | DATETIME | NULL |

### Table 9: seeking_posts
| Column | Type | Constraint |
|---|---|---|
| post_id | INT | PK, AUTO_INCREMENT |
| user_id | INT | FK → users |
| zone_id | INT | FK → zones |
| budget_min | DECIMAL(10,2) | NOT NULL |
| budget_max | DECIMAL(10,2) | NOT NULL |
| room_type | ENUM('single','shared','any') | NOT NULL |
| preferred_gender | ENUM('male','female','any') | NOT NULL |
| move_in_date | DATE | NULL |
| requirements | TEXT | NULL |
| status | ENUM('active','fulfilled') | DEFAULT 'active' |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |

### Table 10: monthly_bills
| Column | Type | Constraint |
|---|---|---|
| bill_id | INT | PK, AUTO_INCREMENT |
| listing_id | INT | FK → listings |
| bill_month | DATE | NOT NULL (first day of month) |
| electricity_amount | DECIMAL(8,2) | DEFAULT 0 |
| gas_amount | DECIMAL(8,2) | DEFAULT 0 |
| water_amount | DECIMAL(8,2) | DEFAULT 0 |
| internet_amount | DECIMAL(8,2) | DEFAULT 0 |
| other_amount | DECIMAL(8,2) | DEFAULT 0 |
| total_amount | DECIMAL(10,2) | NOT NULL |
| per_person_amount | DECIMAL(10,2) | NOT NULL |
| created_by | INT | FK → users (mess manager) |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |

### Table 11: bill_payments
| Column | Type | Constraint |
|---|---|---|
| payment_id | INT | PK, AUTO_INCREMENT |
| bill_id | INT | FK → monthly_bills |
| resident_user_id | INT | FK → users |
| status | ENUM('paid','unpaid') | DEFAULT 'unpaid' |
| paid_at | DATETIME | NULL |

### Table 12: rent_history
| Column | Type | Constraint |
|---|---|---|
| history_id | INT | PK, AUTO_INCREMENT |
| listing_id | INT | FK → listings |
| old_rent | DECIMAL(10,2) | NOT NULL |
| new_rent | DECIMAL(10,2) | NOT NULL |
| changed_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| changed_by | INT | FK → users |

**Design note:** Audit trail pattern — every UPDATE to base_rent in utility_costs triggers an INSERT here. Old records are never modified.

### Table 13: items (UIUNest Exchange)
| Column | Type | Constraint |
|---|---|---|
| item_id | INT | PK, AUTO_INCREMENT |
| seller_id | INT | FK → users |
| zone_id | INT | FK → zones |
| listing_id | INT | FK → listings, NULL (optional link) |
| category | ENUM('furniture','appliances','electronics','kitchen','study','other') | NOT NULL |
| title | VARCHAR(200) | NOT NULL |
| description | TEXT | NULL |
| condition | ENUM('new','like_new','good','fair') | NOT NULL |
| asking_price | DECIMAL(10,2) | NOT NULL |
| reason_for_selling | VARCHAR(300) | NULL |
| status | ENUM('available','sold','withdrawn') | DEFAULT 'available' |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |

### Table 14: offers
| Column | Type | Constraint |
|---|---|---|
| offer_id | INT | PK, AUTO_INCREMENT |
| item_id | INT | FK → items |
| buyer_id | INT | FK → users |
| offer_price | DECIMAL(10,2) | NOT NULL |
| message | VARCHAR(300) | NULL |
| status | ENUM('pending','countered','accepted','rejected','withdrawn') | DEFAULT 'pending' |
| counter_price | DECIMAL(10,2) | NULL |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| updated_at | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

**Design note:** `counter_price` stores seller's counter without overwriting buyer's original `offer_price` — full negotiation trail preserved for analytics.

---

## 7. Key Relationships

| Relationship | Cardinality | Note |
|---|---|---|
| users → listings | 1 : many | Both landlords and students can create listings |
| users → user_preferences | 1 : 1 | Every user has exactly one preference profile |
| listings → utility_costs | 1 : 1 | Separated for independent updates |
| listings → listing_amenities | 1 : 1 | Separated to keep listings table lean |
| listings → reviews | 1 : many | UNIQUE(listing_id, reviewer_id) enforced |
| listings → rent_history | 1 : many | Audit trail, records never modified |
| listings → monthly_bills | 1 : many | One bill per month per mess |
| monthly_bills → bill_payments | 1 : many | One payment record per resident per bill |
| listings → items | 1 : 0/many | Optional — Exchange items may link to a property |
| items → offers | 1 : many | Full negotiation trail per item |
| zones → listings/seeking_posts/items | 1 : many | All three reference zones for consistent filtering |

---

## 8. Comparison With Existing Platforms

| Feature | UIUNest | Bproperty | Bikroy | Facebook Groups | SpareRoom |
|---|---|---|---|---|---|
| Student-focused housing | ● | ○ | ◑ | ◑ | ◑ |
| Mess / shared room support | ● | ○ | ○ | ◑ | ● |
| Itemized cost transparency | ● | ○ | ○ | ○ | ○ |
| Flatmate compatibility score | ● | ○ | ○ | ○ | ◑ |
| Verified landlord badge | ● | ◑ | ○ | ○ | ◑ |
| Multi-dimensional reviews | ● | ◑ | ○ | ○ | ◑ |
| Seeking posts board | ● | ○ | ○ | ● | ● |
| Live interactive map | ● | ● | ○ | ○ | ◑ |
| Rent history tracker | ● | ○ | ○ | ○ | ○ |
| Complaint mechanism | ● | ○ | ○ | ○ | ○ |
| Neighborhood safety score | ● | ○ | ○ | ○ | ○ |
| Student goods marketplace | ● | ○ | ● | ● | ○ |
| Zone-filtered item listings | ● | ○ | ○ | ○ | ○ |
| Offer & negotiation system | ● | ○ | ◑ | ○ | ○ |
| Mess bill management | ● | ○ | ○ | ○ | ○ |
| UIU campus context | ● | ○ | ○ | ○ | ○ |

● = full support, ◑ = partial, ○ = absent

---

## 9. Project Report — Current Status

### Report File
- **File name:** `UIUNest_Interim_Report.docx`
- **Format:** A4, Arial font, navy/gold UIU color scheme, header/footer on all pages
- **Generated using:** Node.js with the `docx` npm library

### Sections Completed (ready for submission)
| Section | Title | Status |
|---|---|---|
| Cover Page | UIUNest, team info, course, GitHub/YouTube links (placeholders) | ✅ Done |
| Table of Contents | All 13 sections listed | ✅ Done |
| Section 1 | Introduction / Overview | ✅ Done |
| Section 2 | Motivation (4 sub-problems) | ✅ Done |
| Section 3 | Similar Projects (6 platforms analyzed) | ✅ Done |
| Section 4 | Benchmark Analysis (20-feature table) | ✅ Done |
| Section 5 | Complete Feature List (15 feature groups) | ✅ Done |
| Section 6 | Database Design Approach + relationships table | ✅ Done |
| Section 7 | ERD (placeholder box for diagram image) | ✅ Done |
| Section 8 | Schema Diagram + all 14 table schemas | ✅ Done |

### Sections Remaining (for final report in Week 6)
| Section | Title | Status |
|---|---|---|
| Section 9 | SQL Queries for Feature Implementation | ⏳ Final report |
| Section 10 | Application Screenshots | ⏳ Final report |
| Section 11 | Limitations | ⏳ Final report |
| Section 12 | Future Work | ⏳ Final report |
| Section 13 | Conclusion | ⏳ Final report |

### Placeholders to Fill Before Submission
- Team member names and IDs on the cover page
- Section on cover page (e.g. Section B)
- Submission date on the cover page
- GitHub repository link
- YouTube demo video link
- ERD diagram image in Section 7 (export from dbdiagram.io or MySQL Workbench)
- Schema diagram image in Section 8

---

## 10. Project Timeline (6 Weeks Post-Midterm)

| Week | Task | Marks |
|---|---|---|
| 1 | Proposal presentation + interim report submission | Presentation: 10 (individual), Report: 10 (group) |
| 2 | Show 20–30% project completion | 5 marks (individual + group) |
| 3 | Show 40–50% project completion | 5 marks |
| 4 | Show 60–70% project completion | 5 marks |
| 5 | Show 80–90% completion + individual viva | 5 marks + Viva: 15 (individual) |
| 6 | Final presentation + final report + project demo | Presentation: 10, Report: 10, Project: 10 |

**Best 4 of weeks 2–6 are counted** (one week can be missed without penalty).

### Marks Distribution (out of 50% of total course marks)
- Presentation + Report average (2 presentations): 15
- Project Development Individual (viva, Week 5): 25
- Project Development Overall (weekly updates): 10

---

## 11. Important Design Decisions Already Made

1. **Single `users` table** for all roles — role differentiated by ENUM field, not separate tables
2. **`utility_costs` separated from `listings`** — independent update frequency justifies 1-to-1 separate table
3. **`total_monthly` stored (not computed on query)** — most-queried field; updated by application when any cost component changes
4. **Audit trail pattern for `rent_history`** — INSERT-only, old records never modified
5. **Student peer listings** use a simplified form and are clearly labeled "Student Listed" in search results — structurally the same `listings` table, differentiated by `listing_type` ENUM
6. **UIUNest Exchange is open to ALL users at ANY time for ANY reason** — not restricted to people who are moving out
7. **`listing_id` in `items` is nullable** — Exchange items may optionally link to a property listing but don't have to
8. **`counter_price` does not overwrite `offer_price`** in the `offers` table — both are preserved for the full negotiation trail
9. **`neighborhood_ratings`** is designed and documented but may be deprioritized during implementation if time is short
10. **Leaflet.js map is included** in the design but is NOT a graded feature milestone — implement after core features are stable
11. **3NF normalization throughout** — no repeating groups, no partial dependencies, no transitive dependencies

---

## 12. What to Help With Next

The most likely next tasks, in priority order:

1. **Generate the ERD diagram** — the student needs to produce the actual ERD image (either export from dbdiagram.io using the schema above, or generate it using MySQL Workbench). You can help write the dbdiagram.io DSL code for this if asked.

2. **Write SQL CREATE TABLE statements** — all 14 tables above need to be turned into MySQL-compatible `CREATE TABLE` SQL. This is what the team will use to actually build the database.

3. **Write sample SQL queries for Section 9** (final report) — examples using SELECT/INSERT/UPDATE/DELETE/JOIN/aggregate/subquery based on the features above.

4. **Build the PHP + MySQL application** — the actual website. Suggested build order:
   - Database setup (CREATE tables, seed data)
   - User registration and login (session management, role-based routing)
   - Listing creation form (landlord + student peer listing)
   - Search and filter page
   - Utility cost form + total computation
   - Review submission
   - Compatibility matching query
   - Seeking posts board
   - Mess bill management panel
   - UIUNest Exchange (item listing + offer system)
   - Admin dashboard
   - Complaint system

5. **Update or complete the final report** — add Sections 9–13 when the application is built.

---

## 13. Tone and Style Notes

- The student writes informally (uses "L99" at the end of messages as slang for respect/appreciation)
- They are technically capable and engaged — don't over-explain basics
- They appreciate concise, direct responses — not padded with filler
- When generating documents or code, just do it — don't ask excessive clarifying questions
- When they say "lets move on to the next stage" it means proceed with the most logical next step without asking what it is

---

*End of context file. This document covers everything discussed and decided about UIUNest as of the session it was generated. Start fresh responses from here.*
