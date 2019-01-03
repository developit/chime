-- USE d2nc509t02ubta;

CREATE OR REPLACE FUNCTION update_modified_column() 
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = now();
    RETURN NEW; 
END;
$$ language 'plpgsql';

CREATE TABLE users (
	id SERIAL PRIMARY KEY,
	username VARCHAR(32) NOT NULL,
	name VARCHAR(100) NOT NULL,
	email VARCHAR(256) NOT NULL,
	password TEXT NOT NULL,
	verified smallint NOT NULL DEFAULT 0,
	suspended smallint NOT NULL DEFAULT 0,
	bio VARCHAR(200),
	website VARCHAR(256), 
	location VARCHAR(32),
	color VARCHAR(6),
	two_factor_enabled VARCHAR(32),
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Access Levels: (1) Read (2) Read/Write (3) Read/Write/Account

CREATE TABLE tokens (
	id VARCHAR(128) PRIMARY KEY,
	token VARCHAR(32) NOT NULL,
	user_id INT NOT NULL,
	description VARCHAR(256),
	access_level smallint NOT NULL DEFAULT 1,
    created_ip VARCHAR(32),
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
	id SERIAL PRIMARY KEY,
	user_id INT NOT NULL,
	body VARCHAR(200),
	mentions VARCHAR(200),
	hashtags VARCHAR(200),
	urls VARCHAR(200),
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE comments (
	id SERIAL PRIMARY KEY,
	post_id INT NOT NULL,
	user_id INT NOT NULL,
	body VARCHAR(200),
	mentions VARCHAR(200),
	hashtags VARCHAR(200),
	urls VARCHAR(200),	
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE follows (
	id SERIAL PRIMARY KEY,
	user_id INT,
	follow_id INT,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE likes (
	id SERIAL PRIMARY KEY,
	user_id INT NOT NULL,
	likeable_id INT NOT NULL,
	likeable_type VARCHAR(32) NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notifications (
	id SERIAL PRIMARY KEY,
	user_id INT NOT NULL,
	notifier_id INT NOT NULL,
	notification_type VARCHAR(32) NOT NULL,
	object_id INT,
	object_type VARCHAR(32) NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_mod_users BEFORE UPDATE ON users FOR EACH ROW EXECUTE PROCEDURE  update_modified_column();
CREATE TRIGGER update_mod_tokens BEFORE UPDATE ON tokens FOR EACH ROW EXECUTE PROCEDURE  update_modified_column();
CREATE TRIGGER update_mod_posts BEFORE UPDATE ON posts FOR EACH ROW EXECUTE PROCEDURE  update_modified_column();
CREATE TRIGGER update_mod_comments BEFORE UPDATE ON comments FOR EACH ROW EXECUTE PROCEDURE  update_modified_column();
CREATE TRIGGER update_mod_follows BEFORE UPDATE ON follows FOR EACH ROW EXECUTE PROCEDURE  update_modified_column();
CREATE TRIGGER update_mod_likes BEFORE UPDATE ON likes FOR EACH ROW EXECUTE PROCEDURE  update_modified_column();
CREATE TRIGGER update_mod_notifications BEFORE UPDATE ON notifications FOR EACH ROW EXECUTE PROCEDURE  update_modified_column();
