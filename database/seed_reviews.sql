-- Seed : 5 avis validés pour la page d'accueil
-- Ce seed crée aussi 5 commandes terminées pour satisfaire les clés étrangères.

SET FOREIGN_KEY_CHECKS=0;

-- 1) Utilisateurs (clients) de test
-- Hash bcrypt (valeur connue) : mot de passe "password".
INSERT INTO users (role, email, password_hash, first_name, last_name, phone, address, is_active, created_at) VALUES
  ('user','client1@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Sophie','Martin','0600000001','Bordeaux',1,NOW()),
  ('user','client2@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Karim','Benali','0600000002','Mérignac',1,NOW()),
  ('user','client3@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Laura','Dupont','0600000003','Pessac',1,NOW()),
  ('user','client4@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Nicolas','Roux','0600000004','Talence',1,NOW()),
  ('user','client5@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Camille','Moreau','0600000005','Bègles',1,NOW());

-- 2) Commandes terminées (pour pouvoir attacher un avis)
-- On utilise un menu existant (id=1) ; si besoin, change menu_id vers un id présent dans ta base.
INSERT INTO orders (
  user_id, menu_id, prestation_address, prestation_city, prestation_date, prestation_time,
  people_count, menu_price, delivery_fee, total_amount, status, created_at
) VALUES
  ((SELECT id FROM users WHERE email='client1@example.com' LIMIT 1), 1, '1 rue de la Paix', 'Bordeaux', DATE_ADD(CURDATE(), INTERVAL 10 DAY), '19:30:00', 12, 384.00, 0.00, 384.00, 'terminee', NOW()),
  ((SELECT id FROM users WHERE email='client2@example.com' LIMIT 1), 1, '12 avenue des Fleurs', 'Mérignac', DATE_ADD(CURDATE(), INTERVAL 12 DAY), '20:00:00', 10, 320.00, 8.00, 328.00, 'terminee', NOW()),
  ((SELECT id FROM users WHERE email='client3@example.com' LIMIT 1), 1, '5 cours Victor Hugo', 'Pessac', DATE_ADD(CURDATE(), INTERVAL 14 DAY), '12:30:00', 15, 432.00, 10.00, 442.00, 'terminee', NOW()),
  ((SELECT id FROM users WHERE email='client4@example.com' LIMIT 1), 1, '8 rue Sainte-Catherine', 'Talence', DATE_ADD(CURDATE(), INTERVAL 16 DAY), '19:00:00', 20, 576.00, 12.00, 588.00, 'terminee', NOW()),
  ((SELECT id FROM users WHERE email='client5@example.com' LIMIT 1), 1, '2 place de la Bourse', 'Bègles', DATE_ADD(CURDATE(), INTERVAL 18 DAY), '18:30:00', 8, 256.00, 6.00, 262.00, 'terminee', NOW());

-- 3) Avis (validés)
INSERT INTO reviews (order_id, user_id, rating, message, status, created_at) VALUES
  ((SELECT o.id FROM orders o JOIN users u ON u.id=o.user_id WHERE u.email='client1@example.com' ORDER BY o.id DESC LIMIT 1), (SELECT id FROM users WHERE email='client1@example.com' LIMIT 1), 5, 'Livraison à l\'heure, portions généreuses et tout le monde a adoré. Merci !', 'valide', NOW()),
  ((SELECT o.id FROM orders o JOIN users u ON u.id=o.user_id WHERE u.email='client2@example.com' ORDER BY o.id DESC LIMIT 1), (SELECT id FROM users WHERE email='client2@example.com' LIMIT 1), 4, 'Très bon et super simple à organiser. Les conditions étaient claires.', 'valide', NOW()),
  ((SELECT o.id FROM orders o JOIN users u ON u.id=o.user_id WHERE u.email='client3@example.com' ORDER BY o.id DESC LIMIT 1), (SELECT id FROM users WHERE email='client3@example.com' LIMIT 1), 5, 'Menu top pour notre événement : prix cohérent et qualité au rendez-vous.', 'valide', NOW()),
  ((SELECT o.id FROM orders o JOIN users u ON u.id=o.user_id WHERE u.email='client4@example.com' ORDER BY o.id DESC LIMIT 1), (SELECT id FROM users WHERE email='client4@example.com' LIMIT 1), 5, 'Service impeccable. On refera appel à vous sans hésiter.', 'valide', NOW()),
  ((SELECT o.id FROM orders o JOIN users u ON u.id=o.user_id WHERE u.email='client5@example.com' ORDER BY o.id DESC LIMIT 1), (SELECT id FROM users WHERE email='client5@example.com' LIMIT 1), 4, 'Très bonne expérience. Mention spéciale au dessert !', 'valide', NOW());

SET FOREIGN_KEY_CHECKS=1;
