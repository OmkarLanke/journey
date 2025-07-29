CREATE DATABASE IF NOT EXISTS journey;
     USE journey;
     ALTER TABLE memories ADD COLUMN title VARCHAR(100) NOT NULL AFTER photo_path;

     CREATE TABLE memories (
         id INT AUTO_INCREMENT PRIMARY KEY,
         photo_path VARCHAR(255) NOT NULL,
         title VARCHAR(100) NOT NULL,
         description TEXT NOT NULL,
         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
     );
