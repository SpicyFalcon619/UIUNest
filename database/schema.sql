CREATE DATABASE IF NOT EXISTS uiunest;
USE uiunest;

-- 1. users
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'landlord', 'admin') NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    phone VARCHAR(20) NULL,
    university_id VARCHAR(50) NULL,
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. zones
CREATE TABLE zones (
    zone_id INT AUTO_INCREMENT PRIMARY KEY,
    zone_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    center_lat DECIMAL(9,6) NOT NULL,
    center_lng DECIMAL(9,6) NOT NULL,
    radius_km DECIMAL(4,2) NOT NULL
);

-- 3. listings
CREATE TABLE listings (
    listing_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    zone_id INT,
    listing_type ENUM('full_property', 'peer_listing') NOT NULL,
    property_type ENUM('single_room', 'shared_room', 'full_mess', 'sublet') NOT NULL,
    title VARCHAR(200) NOT NULL,
    address TEXT NOT NULL,
    lat DECIMAL(9,6) NOT NULL,
    lng DECIMAL(9,6) NOT NULL,
    gender_pref ENUM('male', 'female', 'any') NOT NULL,
    total_rooms INT NOT NULL,
    current_occupancy INT DEFAULT 0,
    status ENUM('available', 'occupied', 'soon_vacant') DEFAULT 'available',
    expected_vacate_date DATE NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    description TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(zone_id)
);

-- 4. utility_costs
CREATE TABLE utility_costs (
    cost_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT UNIQUE,
    base_rent DECIMAL(10,2) NOT NULL,
    electricity_amount DECIMAL(8,2) DEFAULT 0,
    electricity_type ENUM('individual', 'shared') NOT NULL,
    gas_bill DECIMAL(8,2) DEFAULT 0,
    water_bill DECIMAL(8,2) DEFAULT 0,
    internet_cost DECIMAL(8,2) DEFAULT 0,
    maintenance_fee DECIMAL(8,2) DEFAULT 0,
    caretaker_fee DECIMAL(8,2) DEFAULT 0,
    other_fees DECIMAL(8,2) DEFAULT 0,
    total_monthly DECIMAL(10,2) NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE
);

-- 5. listing_amenities
CREATE TABLE listing_amenities (
    amenity_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT UNIQUE,
    attached_bathroom BOOLEAN DEFAULT FALSE,
    attached_kitchen BOOLEAN DEFAULT FALSE,
    is_furnished BOOLEAN DEFAULT FALSE,
    rooftop_access BOOLEAN DEFAULT FALSE,
    parking BOOLEAN DEFAULT FALSE,
    power_backup BOOLEAN DEFAULT FALSE,
    lift_access BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE
);

-- 6. user_preferences
CREATE TABLE user_preferences (
    pref_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    sleep_schedule ENUM('early', 'late', 'flexible') NOT NULL,
    study_hours INT DEFAULT 0,
    diet ENUM('vegetarian', 'non_veg', 'halal_strict') NOT NULL,
    guest_policy ENUM('allowed', 'restricted', 'not_allowed') NOT NULL,
    smoking_tolerance BOOLEAN DEFAULT FALSE,
    preferred_gender ENUM('male', 'female', 'any') NOT NULL,
    cleanliness_score INT CHECK (cleanliness_score BETWEEN 1 AND 5),
    noise_tolerance ENUM('quiet', 'moderate', 'noisy') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 7. reviews
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT,
    reviewer_id INT,
    value_for_money INT CHECK (value_for_money BETWEEN 1 AND 5),
    listing_accuracy INT CHECK (listing_accuracy BETWEEN 1 AND 5),
    landlord_response INT CHECK (landlord_response BETWEEN 1 AND 5),
    cleanliness INT CHECK (cleanliness BETWEEN 1 AND 5),
    safety INT CHECK (safety BETWEEN 1 AND 5),
    composite_score DECIMAL(3,2) NOT NULL,
    comment VARCHAR(500) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(listing_id, reviewer_id),
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 8. complaints
CREATE TABLE complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    complainant_id INT,
    against_user_id INT,
    listing_id INT NULL,
    category ENUM('hidden_costs', 'harassment', 'deposit_not_returned', 'misrepresentation', 'other') NOT NULL,
    description TEXT NOT NULL,
    status ENUM('submitted', 'under_review', 'resolved') DEFAULT 'submitted',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    FOREIGN KEY (complainant_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (against_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE
);

-- 9. seeking_posts
CREATE TABLE seeking_posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    zone_id INT,
    budget_min DECIMAL(10,2) NOT NULL,
    budget_max DECIMAL(10,2) NOT NULL,
    room_type ENUM('single', 'shared', 'any') NOT NULL,
    preferred_gender ENUM('male', 'female', 'any') NOT NULL,
    move_in_date DATE NULL,
    requirements TEXT NULL,
    status ENUM('active', 'fulfilled') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(zone_id)
);

-- 10. monthly_bills
CREATE TABLE monthly_bills (
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT,
    bill_month DATE NOT NULL,
    electricity_amount DECIMAL(8,2) DEFAULT 0,
    gas_amount DECIMAL(8,2) DEFAULT 0,
    water_amount DECIMAL(8,2) DEFAULT 0,
    internet_amount DECIMAL(8,2) DEFAULT 0,
    other_amount DECIMAL(8,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    per_person_amount DECIMAL(10,2) NOT NULL,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 11. bill_payments
CREATE TABLE bill_payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id INT,
    resident_user_id INT,
    status ENUM('paid', 'unpaid') DEFAULT 'unpaid',
    paid_at DATETIME NULL,
    FOREIGN KEY (bill_id) REFERENCES monthly_bills(bill_id) ON DELETE CASCADE,
    FOREIGN KEY (resident_user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 12. rent_history
CREATE TABLE rent_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT,
    old_rent DECIMAL(10,2) NOT NULL,
    new_rent DECIMAL(10,2) NOT NULL,
    changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    changed_by INT,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 13. items
CREATE TABLE items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT,
    zone_id INT,
    listing_id INT NULL,
    category ENUM('furniture', 'appliances', 'electronics', 'kitchen', 'study', 'other') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    item_condition ENUM('new', 'like_new', 'good', 'fair') NOT NULL,
    asking_price DECIMAL(10,2) NOT NULL,
    reason_for_selling VARCHAR(300) NULL,
    status ENUM('available', 'sold', 'withdrawn') DEFAULT 'available',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(zone_id),
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE SET NULL
);

-- 14. offers
CREATE TABLE offers (
    offer_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT,
    buyer_id INT,
    offer_price DECIMAL(10,2) NOT NULL,
    message VARCHAR(300) NULL,
    status ENUM('pending', 'countered', 'accepted', 'rejected', 'withdrawn') DEFAULT 'pending',
    counter_price DECIMAL(10,2) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 15. applications
CREATE TABLE applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT,
    applicant_id INT,
    message TEXT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE,
    FOREIGN KEY (applicant_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 16. verifications
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
