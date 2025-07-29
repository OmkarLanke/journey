CREATE DATABASE journey;
     USE journey;

     CREATE TABLE memories (
         id INT AUTO_INCREMENT PRIMARY KEY,
         photo_path VARCHAR(255) NOT NULL,
         description TEXT NOT NULL,
         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
     );