CREATE TABLE locks (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    owner VARCHAR(100),
    timeout INTEGER UNSIGNED,
    created INTEGER,
    token VARCHAR(100),
    scope TINYINT,
    depth TINYINT,
    uri text
);
