-- 1. Settings
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255),
    facebook_url VARCHAR(255),
    instagram_url VARCHAR(255),
    linkedin_url VARCHAR(255),
    twitter_url VARCHAR(255),
    whatsapp_url VARCHAR(255),
    phone_number VARCHAR(20)
);

-- 2. Services
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_name VARCHAR(255),
    service_description TEXT,
    image_url VARCHAR(255)
);

-- 3. Service Details
CREATE TABLE service_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_id INT,
    detail_description TEXT,
    FOREIGN KEY (service_id) REFERENCES services(id)
);

-- 4. Service Features
CREATE TABLE service_features (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_id INT,
    FOREIGN KEY (service_id) REFERENCES services(id)
);


-- 5. About Us
CREATE TABLE about_us (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    subtitle VARCHAR(255),
    main_description TEXT,
    additional_description TEXT,
    image_url VARCHAR(255)
);

-- 6. Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    role VARCHAR(50),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    phone_number VARCHAR(20)
);

-- 7. Projects
CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT,
    member_id INT,
    project_name VARCHAR(255),
    project_description TEXT,
    image_url VARCHAR(255),
    project_cost DECIMAL(10,2),
    start_date DATE,
    end_date DATE,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (member_id) REFERENCES members(id)
);

-- 8. Project Details
CREATE TABLE project_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    detail_description TEXT,
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

-- 9. Project Views
CREATE TABLE project_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    view_title VARCHAR(255),
    view_description TEXT,
    image_url VARCHAR(255),
    create_time DATETIME,
    view_link VARCHAR(255)
);

-- 10. Pricing Plans
CREATE TABLE pricing_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_title VARCHAR(255),
    plan_description TEXT,
    price DECIMAL(10,2),
    billing_period VARCHAR(50)
);

CREATE TABLE pricing_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pricing_id INT,
    pricing_detail TEXT,
    FOREIGN KEY (pricing_id) REFERENCES pricing_plans(id)
);

-- 11. Members
CREATE TABLE members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(255),
    phone_number VARCHAR(20)
);

-- 12. Clients
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    country_of_origin VARCHAR(100)
);
