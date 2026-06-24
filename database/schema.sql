DROP TABLE IF EXISTS order_cancellations;
DROP TABLE IF EXISTS order_status_history;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS menu_dishes;
DROP TABLE IF EXISTS dishes;
DROP TABLE IF EXISTS menu_images;
DROP TABLE IF EXISTS menus;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS opening_hours;
DROP TABLE IF EXISTS diets;
DROP TABLE IF EXISTS themes;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role ENUM('user','employee','admin') NOT NULL DEFAULT 'user',
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  address VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE themes (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE diets  (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menus (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  conditions TEXT NOT NULL,
  theme_id INT NOT NULL,
  diet_id INT NOT NULL,
  min_people INT NOT NULL,
  base_price DECIMAL(10,2) NOT NULL,
  stock_available INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (theme_id) REFERENCES themes(id),
  FOREIGN KEY (diet_id) REFERENCES diets(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menu_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  menu_id INT NOT NULL,
  url VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE dishes (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, description TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menu_dishes (
  menu_id INT NOT NULL,
  dish_id INT NOT NULL,
  category ENUM('entree','plat','dessert') NOT NULL,
  PRIMARY KEY (menu_id, dish_id, category),
  FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
  FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  menu_id INT NOT NULL,
  prestation_address VARCHAR(255) NOT NULL,
  prestation_city VARCHAR(120) NOT NULL,
  prestation_date DATE NOT NULL,
  prestation_time TIME NOT NULL,
  people_count INT NOT NULL,
  menu_price DECIMAL(10,2) NOT NULL,
  delivery_fee DECIMAL(10,2) NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  status ENUM('en_attente','acceptee','en_preparation','en_livraison','livree','attente_retour_materiel','terminee','annulee') NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (menu_id) REFERENCES menus(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_status_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  status VARCHAR(50) NOT NULL,
  changed_by_user_id INT NULL,
  changed_at DATETIME NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_cancellations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  cancelled_by_user_id INT NOT NULL,
  contact_mode ENUM('appel','mail') NOT NULL,
  contact_date DATE NOT NULL,
  reason VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (cancelled_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  user_id INT NOT NULL,
  rating TINYINT NOT NULL,
  comment TEXT NOT NULL,
  status ENUM('en_attente','valide','refuse') NOT NULL DEFAULT 'en_attente',
  created_at DATETIME NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE opening_hours (
  day_of_week TINYINT PRIMARY KEY,
  open_time TIME NOT NULL,
  close_time TIME NOT NULL,
  is_closed TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
