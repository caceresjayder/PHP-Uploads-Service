-- Active: 1729370046229@@127.0.0.1@3306@uploads
CREATE DATABASE IF NOT EXISTS uploads;
USE uploads;
CREATE TABLE IF NOT EXISTS upload (
    id VARCHAR(32) primary key NOT NULL,
    name VARCHAR(255) NOT NULL, 
    type VARCHAR(255) NOT NULL, 
    size INT NOT NULL, 
    file VARCHAR(255) NOT NULL, 
    last_read datetime NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
)

CREATE INDEX IF NOT EXISTS idx_upload_last_read ON upload (last_read);