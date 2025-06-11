USE AstroAllies_DB;

-- Creazione tabella Utente
CREATE TABLE IF NOT EXISTS Utente (
    Username VARCHAR(20) PRIMARY KEY,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(64) NOT NULL, -- SHA-256 bit in hex = 64 char
    Punteggio INT DEFAULT 0,
    CHECK (Email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$')
);

-- Creazione tabella Sessione
CREATE TABLE IF NOT EXISTS Sessione (
    Username VARCHAR(20),
    Token VARCHAR(128) NOT NULL UNIQUE,
    Scadenza DATETIME NOT NULL,
    PRIMARY KEY (Username),
    FOREIGN KEY (Username) REFERENCES Utente(Username) ON DELETE CASCADE
);

-- Evento per eliminare le sessioni scadute (ogni 5 minuti)
DELIMITER //
CREATE EVENT IF NOT EXISTS elimina_sessioni_scadute
ON SCHEDULE EVERY 8 HOUR
DO
BEGIN
    DELETE FROM Sessione WHERE Scadenza < NOW();
END;
//
DELIMITER ;

