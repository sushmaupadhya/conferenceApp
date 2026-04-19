-- Database schema for conference_app

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    designation ENUM('Professor/Scientist','Post-Doc','Graduate Student','MS/MSc/UG') NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    organization VARCHAR(255) NOT NULL,
    country_residence VARCHAR(100) NOT NULL,
    country_citizenship VARCHAR(100) NOT NULL,
    gender ENUM('Male','Female','Prefer not to disclose') NOT NULL,
    need_accommodation TINYINT(1) NOT NULL DEFAULT 0,
    accompanying_person TINYINT(1) NOT NULL DEFAULT 0,
    span_of_stay VARCHAR(255) NOT NULL,
    dietary_restrictions VARCHAR(255) DEFAULT NULL,
    payment_ack_file VARCHAR(255) NOT NULL,
    mysore_trip TINYINT(1) NOT NULL DEFAULT 0,
    passport_country ENUM('India','Other') NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('registered','active','blocked') NOT NULL DEFAULT 'registered',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL
);

CREATE TABLE abstracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    institute VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    reg_proof_file VARCHAR(255) NOT NULL,
    abstract_file VARCHAR(255) NOT NULL,
    presentation_choice ENUM('Oral presentation','Poster presentation') NOT NULL,
    travel_award_applied TINYINT(1) NOT NULL DEFAULT 0,
    award_pdf VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
    admin_comment TEXT DEFAULT NULL,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);
