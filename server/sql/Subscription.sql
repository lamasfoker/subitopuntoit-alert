CREATE TABLE Subscription (
    endpoint VARCHAR(500) NOT NULL PRIMARY KEY,
    contentEncoding VARCHAR(500) NOT NULL,
    authToken VARCHAR(500) NOT NULL,
    publicKey VARCHAR(500) NOT NULL
);