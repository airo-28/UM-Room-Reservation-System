DROP TABLE IF EXISTS reservation_logs;
DROP TABLE IF EXISTS reservation_resources;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS resources;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS announcements;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  location VARCHAR(120) NOT NULL,
  capacity INT NOT NULL,
  type ENUM('collab','lab','classroom','other') NOT NULL DEFAULT 'collab',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  open_time TIME NOT NULL DEFAULT '08:00:00',
  close_time TIME NOT NULL DEFAULT '21:00:00',
  amenities TEXT,
  description TEXT,
  image_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE resources (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  room_id INT NOT NULL,
  date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  purpose VARCHAR(255) NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_res_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_res_room FOREIGN KEY(room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  INDEX idx_room_date (room_id, date, start_time, end_time)
) ENGINE=InnoDB;

CREATE TABLE reservation_resources (
  reservation_id INT NOT NULL,
  resource_id INT NOT NULL,
  PRIMARY KEY (reservation_id, resource_id),
  CONSTRAINT fk_rr_res FOREIGN KEY(reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
  CONSTRAINT fk_rr_resource FOREIGN KEY(resource_id) REFERENCES resources(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  body TEXT NOT NULL,
  severity ENUM('update','info','notice','important') NOT NULL DEFAULT 'info',
  starts_at TIMESTAMP NULL DEFAULT NULL,
  ends_at TIMESTAMP NULL DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE reservation_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reservation_id INT NOT NULL,
  action VARCHAR(50) NOT NULL,
  from_status VARCHAR(50),
  to_status VARCHAR(50) NOT NULL,
  actor_user_id INT NOT NULL,
  actor_role VARCHAR(20) NOT NULL,
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_rl_res FOREIGN KEY(reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
  CONSTRAINT fk_rl_user FOREIGN KEY(actor_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
