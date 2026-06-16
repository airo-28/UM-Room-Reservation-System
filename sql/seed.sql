INSERT INTO users(full_name,email,password_hash,role) VALUES
('Admin User','admin@example.com', '$2y$10$FeQUvKQV.eP6kk45OxHr0.ezoq/B97csvyCPR61VwSdyAqvhCkz0W','admin'); -- password: admin123

INSERT INTO rooms(name,location,capacity,type,is_active,open_time,close_time) VALUES
('Collab Room A','Library 2F',6,'collab',1,'08:00:00','21:00:00'),
('Computer Lab 1','IT Building 3F',30,'lab',1,'08:00:00','21:00:00'),
('Classroom 101','Main Bldg',40,'classroom',1,'08:00:00','21:00:00');

INSERT INTO resources(name,is_active) VALUES
('Projector',1),('Whiteboard Markers',1),('HDMI Cable',1);

INSERT INTO reservations(user_id,room_id,date,start_time,end_time,purpose,status)
VALUES (1,1,CURDATE(), '10:00','11:00','Demo','approved');
