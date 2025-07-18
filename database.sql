CREATE DATABASE formule1 ;

USE formule1 ;

CREATE TABLE news (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    news_content TEXT NOT NULL,
    image_url VARCHAR(255)
);

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT, 
    username VARCHAR(255) NOT NULL, 
    password_hash VARCHAR(255) NOT NULL 
);

CREATE TABLE drivers (
    driver_id INT PRIMARY KEY AUTO_INCREMENT, 
    first_name VARCHAR(50) NOT NULL,          
    last_name VARCHAR(50) NOT NULL,           
    nationality VARCHAR(50) NOT NULL,         
    date_of_birth DATE,                   
    driver_number INT UNIQUE,                 
    team_name VARCHAR(100),                   
    championships_won INT DEFAULT 0,          
    career_points DECIMAL(10, 2) DEFAULT 0.00,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE            
);

ALTER TABLE drivers
ADD COLUMN image VARCHAR(255); 

ALTER TABLE drivers
ADD COLUMN flag_url VARCHAR(255) NULL;

ALTER TABLE drivers
ADD COLUMN driver_color VARCHAR(50) NULL;

--alles hierboven gereed in beide databases--
CREATE TABLE teams (
    team_id INT PRIMARY KEY AUTO_INCREMENT,
    team_name VARCHAR(100) NOT NULL UNIQUE,
    team_color VARCHAR(7) NOT NULL,
    full_team_name VARCHAR(255),
    base_location VARCHAR(100),
    team_principal VARCHAR(100),
    technical_director VARCHAR(100),
    championships_won INT DEFAULT 0,
    first_entry_year INT,
    website_url VARCHAR(255),
    logo_url VARCHAR(255),
    current_engine_supplier VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE
);

-- RED BULL RACING
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Red Bull Racing', '#0600EF', 'Oracle Red Bull Racing', 'Milton Keynes, UK', 'Christian Horner', 'Pierre Waché', 7, 2005, 'https://www.redbullracing.com/', 'url_naar_redbull_logo.png', 'Honda RBPT', TRUE);

-- MERCEDES
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Mercedes', '#00D2BE', 'Mercedes-AMG Petronas Formula One Team', 'Brackley, UK', 'Toto Wolff', 'James Allison', 8, 2010, 'https://www.mercedesamgf1.com/', 'url_naar_mercedes_logo.png', 'Mercedes', TRUE);

-- FERRARI
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Ferrari', '#DC0000', 'Scuderia Ferrari', 'Maranello, Italy', 'Frédéric Vasseur', 'Enrico Cardile', 16, 1950, 'https://www.ferrari.com/en-EN/formula1', 'url_naar_ferrari_logo.png', 'Ferrari', TRUE);

-- MCLAREN
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('McLaren', '#FF8700', 'McLaren Formula 1 Team', 'Woking, UK', 'Andrea Stella', 'Peter Prodromou', 8, 1966, 'https://www.mclaren.com/racing/formula-1/', 'url_naar_mclaren_logo.png', 'Mercedes', TRUE);

-- ASTON MARTIN
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Aston Martin', '#006F62', 'Aston Martin Aramco Formula One Team', 'Silverstone, UK', 'Mike Krack', 'Dan Fallows', 0, 2021, 'https://www.astonmartinf1.com/', 'url_naar_aston_martin_logo.png', 'Mercedes', TRUE);

-- ALPINE
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Alpine', '#0090FF', 'BWT Alpine F1 Team', 'Enstone, UK', 'Bruno Famin', 'Matt Harman', 0, 2021, 'https://www.alpinecars.com/en/f1/', 'url_naar_alpine_logo.png', 'Renault', TRUE);

-- WILLIAMS
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Williams', '#64C4FF', 'Williams Racing', 'Grove, UK', 'James Vowles', 'Pat Fry', 9, 1977, 'https://www.williamsf1.com/', 'url_naar_williams_logo.png', 'Mercedes', TRUE);

-- HAAS
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Haas', '#B6BABC', 'MoneyGram Haas F1 Team', 'Kannapolis, USA', 'Ayao Komatsu', 'Simone Resta', 0, 2016, 'https://www.haasf1team.com/', 'url_naar_haas_logo.png', 'Ferrari', TRUE);

-- RB (Racing Bulls)
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('RB', '#6692FF', 'Visa Cash App RB Formula 1 Team', 'Faenza, Italy', 'Laurent Mekies', 'Jody Egginton', 0, 2006, 'https://www.visacashapprb.com/', 'url_naar_rb_logo.png', 'Honda RBPT', TRUE);

-- SAUBER (Kick Sauber)
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Sauber', '#52E252', 'Stake F1 Team Kick Sauber', 'Hinwil, Switzerland', 'Alessandro Alunni Bravi', 'James Key', 0, 1993, 'https://www.sauber-group.com/motorsport/formula-1/', 'url_naar_sauber_logo.png', 'Ferrari', TRUE);

ALTER TABLE drivers
ADD COLUMN team_id INT,
ADD CONSTRAINT fk_team
FOREIGN KEY (team_id) REFERENCES teams(team_id);

ALTER TABLE drivers
ADD COLUMN place_of_birth VARCHAR(255);

ALTER TABLE drivers
ADD COLUMN description VARCHAR(255);