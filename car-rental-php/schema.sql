
-- schema.sql (MySQL)
CREATE TABLE IF NOT EXISTS users(
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cars(
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  brand VARCHAR(120) NOT NULL,
  seats TINYINT NOT NULL DEFAULT 4,
  type ENUM('Sedan','SUV','Hatchback','Pickup','EV') NOT NULL,
  price_per_day DECIMAL(10,2) NOT NULL,
  img_path VARCHAR(255) DEFAULT 'assets/img/placeholder-car.svg',
  status ENUM('available','maintenance') DEFAULT 'available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bookings(
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  car_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  days INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('pending','approved','rejected','cancelled','returned') DEFAULT 'pending',
  payment_status ENUM('unpaid','paid','refunded') DEFAULT 'unpaid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed admin & sample cars
INSERT INTO users(name,email,password_hash,role) VALUES
('Admin','admin@carrent.local', SHA2('admin123',256), 'admin')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO cars(title,brand,seats,type,price_per_day) VALUES
('City ZX 2023','Honda',5,'Sedan',1200.00),
('Corolla Cross','Toyota',5,'SUV',1600.00),
('MG4 Electric','MG',5,'EV',1800.00),
('Mazda 2','Mazda',5,'Hatchback',1100.00);
