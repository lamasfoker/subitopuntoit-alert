CREATE TABLE Research (
    id int PRIMARY KEY NOT NULL AUTO_INCREMENT,
    endpoint VARCHAR(500) NOT NULL,
    location VARCHAR(500) NOT NULL,
    locationParameters VARCHAR(500) NOT NULL,
    onlyInTitle BOOLEAN DEFAULT FALSE,
    query VARCHAR(500) NOT NULL,
    lastCheck VARCHAR(500) NOT NULL
);