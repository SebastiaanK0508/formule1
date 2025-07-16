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
    is_active BOOLEAN DEFAULT TRUE            
);