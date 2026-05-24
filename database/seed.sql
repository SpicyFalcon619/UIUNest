USE uiunest;

-- Insert Zones
INSERT INTO zones (zone_name, description, center_lat, center_lng, radius_km) VALUES
('UIU Campus Area', 'Immediate surroundings of United International University', 23.7979, 90.4497, 1.5),
('Sayed Nagar', 'Residential area close to campus', 23.7950, 90.4440, 2.0),
('Shatarkul', 'Quiet neighborhood', 23.7910, 90.4350, 2.5),
('Nurer Chala', 'Bustling commercial and residential area', 23.8050, 90.4380, 2.0),
('Aftabnagar', 'Planned residential sector', 23.7660, 90.4340, 3.0),
('Notun Bazar', 'Major transit and shopping hub', 23.7970, 90.4220, 2.5);

-- Insert Users
-- Note: The password_hash here is for the word 'password' using BCRYPT.
-- Login to all accounts using 'password' instead of 'student123' during this phase until register is implemented.
INSERT INTO users (name, email, password_hash, role, gender, university_id) VALUES
('Student User', 'student@uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'male', '011202001'),
('Student Two', 'student2@uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'female', '011202002'),
('Landlord User', 'landlord@uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'landlord', 'male', NULL),
('Master Admin', 'admin@uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'male', NULL);

-- Insert Listings
INSERT INTO listings (user_id, zone_id, listing_type, property_type, title, address, lat, lng, gender_pref, total_rooms, current_occupancy, status, is_verified) VALUES
(3, 1, 'full_property', 'single_room', 'Cozy Single Room near UIU', 'House 42, Road 3, UIU Campus Area', 23.7980, 90.4500, 'any', 3, 2, 'available', TRUE),
(1, 2, 'peer_listing', 'shared_room', 'Need 1 roommate for Shared Room', 'Sayed Nagar Main Road', 23.7955, 90.4445, 'male', 2, 1, 'available', TRUE);

-- Insert Utility Costs
INSERT INTO utility_costs (listing_id, base_rent, electricity_amount, electricity_type, gas_bill, water_bill, internet_cost, total_monthly) VALUES
(1, 6500, 800, 'individual', 1080, 500, 500, 9380),
(2, 4500, 600, 'shared', 540, 300, 250, 6190);

-- Insert Amenities
INSERT INTO listing_amenities (listing_id, attached_bathroom, is_furnished, rooftop_access, power_backup, parking) VALUES
(1, TRUE, TRUE, TRUE, TRUE, TRUE),
(2, FALSE, FALSE, TRUE, FALSE, FALSE);
