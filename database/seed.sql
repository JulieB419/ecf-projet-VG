INSERT INTO themes (name) VALUES ('Classique'), ('Noël'), ('Pâques'), ('Évènement');
INSERT INTO diets (name) VALUES ('Classique'), ('Végétarien'), ('Vegan');

INSERT INTO menus (title,description,conditions,theme_id,diet_id,min_people,base_price,stock_available,is_active,created_at) VALUES
('Menu Classique','Un menu simple et gourmand pour toutes les occasions.','Commander au moins 48h à l\'avance.',1,1,4,45.00,10,1,NOW()),
('Menu Noël','Un menu festif pour vos repas de fin d\'année.','Commander 7 jours à l\'avance. Conservation au frais.',2,1,6,80.00,5,1,NOW()),
('Menu Vegan','Un menu 100% végétal, équilibré et savoureux.','Commander 72h à l\'avance.',1,3,4,55.00,7,1,NOW());

INSERT INTO menu_images (menu_id,url,sort_order) VALUES
(1,'https://picsum.photos/seed/vg1/900/600',0),
(1,'https://picsum.photos/seed/vg2/900/600',1),
(2,'https://picsum.photos/seed/vg3/900/600',0),
(3,'https://picsum.photos/seed/vg4/900/600',0);

INSERT INTO dishes (name,description) VALUES
('Velouté de saison','Entrée douce et réconfortante.'),
('Poulet rôti','Plat principal généreux.'),
('Tarte maison','Dessert du moment.'),
('Salade croquante','Entrée vegan pleine de fraîcheur.'),
('Curry de légumes','Plat vegan parfumé.'),
('Compote épicée','Dessert vegan simple.');

INSERT INTO menu_dishes (menu_id,dish_id,category) VALUES
(1,1,'entree'),(1,2,'plat'),(1,3,'dessert'),
(3,4,'entree'),(3,5,'plat'),(3,6,'dessert');

INSERT INTO opening_hours (day_of_week,open_time,close_time,is_closed) VALUES
(0,'09:00','18:00',0),
(1,'09:00','18:00',0),
(2,'09:00','18:00',0),
(3,'09:00','18:00',0),
(4,'09:00','18:00',0),
(5,'10:00','16:00',0),
(6,'10:00','14:00',0);
