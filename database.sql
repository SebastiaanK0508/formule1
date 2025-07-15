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