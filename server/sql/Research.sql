CREATE TABLE Research (
    id int PRIMARY KEY NOT NULL AUTO_INCREMENT,
    endpoint VARCHAR(500) NOT NULL,
    region VARCHAR(500) NOT NULL,
    city VARCHAR(500) NOT NULL,
    query VARCHAR(500) NOT NULL
);