Run this on the console myphpadmin

-- 1. Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    username VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    email VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    password VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Create songs table
CREATE TABLE IF NOT EXISTS songs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    artist VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Create listeninghistory table
CREATE TABLE IF NOT EXISTS listeninghistory (
    historyID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    songID INT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (songID) REFERENCES songs(id) ON DELETE CASCADE,
    INDEX idx_userID (userID),
    INDEX idx_songID (songID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Create playlists table
CREATE TABLE IF NOT EXISTS playlists (
    playlistID INT AUTO_INCREMENT PRIMARY KEY,  
    userID INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_userID (userID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

<<<<<<< HEAD
--5. CREATE TABLE IF NOT EXISTS playlists (
    playlist_id INT AUTO_INCREMENT PRIMARY KEY,  
    userID INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_userID (userID)
=======
-- 5. Create playlist_songs table
CREATE TABLE IF NOT EXISTS playlist_songs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    playlistID INT NOT NULL,
    songID INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (playlistID) REFERENCES playlists(playlistID) ON DELETE CASCADE,
    FOREIGN KEY (songID) REFERENCES songs(id) ON DELETE CASCADE,
    INDEX idx_playlistID (playlistID),
    INDEX idx_songID (songID),
    UNIQUE KEY unique_playlist_song (playlistID, songID)
>>>>>>> 396aca0ea2a4b5a5668b04d48d6503c48b6c912e
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Insert sample songs data
INSERT INTO songs (title, artist, file_path) VALUES
('3005', 'Unknown Artist', '/midtermProject/assets/audio/3005.mp3'),
('agora hills', 'Unknown Artist', '/midtermProject/assets/audio/agora_hills.mp3'),
('allforu passionfruit', 'Unknown Artist', '/midtermProject/assets/audio/allforu_passionfruit.mp3'),
('a keeper', 'Unknown Artist', '/midtermProject/assets/audio/a_keeper.mp3'),
('beautyand abeat', 'Unknown Artist', '/midtermProject/assets/audio/beautyand_abeat.mp3'),
('be your', 'Unknown Artist', '/midtermProject/assets/audio/be_your.mp3'),
('be yourkaytra', 'Unknown Artist', '/midtermProject/assets/audio/be_yourkaytra.mp3'),
('blaxian untitled', 'Unknown Artist', '/midtermProject/assets/audio/blaxian_untitled.mp3'),
('bon appetit', 'Unknown Artist', '/midtermProject/assets/audio/bon_appetit.mp3'),
('bym riddim', 'Unknown Artist', '/midtermProject/assets/audio/bym_riddim.mp3'),
('chanel', 'Unknown Artist', '/midtermProject/assets/audio/chanel.mp3'),
('controlla', 'Unknown Artist', '/midtermProject/assets/audio/controlla.mp3'),
('crazy frog', 'Unknown Artist', '/midtermProject/assets/audio/crazy_frog.mp3'),
('daisies flip', 'Unknown Artist', '/midtermProject/assets/audio/daisies_flip.mp3'),
('de samba', 'Unknown Artist', '/midtermProject/assets/audio/de_samba.mp3'),
('duplade bandido', 'Unknown Artist', '/midtermProject/assets/audio/duplade_bandido.mp3'),
('gasolina', 'Unknown Artist', '/midtermProject/assets/audio/gasolina.mp3'),
('let her go', 'Unknown Artist', '/midtermProject/assets/audio/let_her_go.mp3'),
('lost', 'Unknown Artist', '/midtermProject/assets/audio/lost.mp3'),
('marvins room', 'Unknown Artist', '/midtermProject/assets/audio/marvins_room.mp3'),
('monaco edit', 'Unknown Artist', '/midtermProject/assets/audio/monaco_edit.mp3'),
('nights', 'Unknown Artist', '/midtermProject/assets/audio/nights.mp3'),
('no idea', 'Unknown Artist', '/midtermProject/assets/audio/no_idea.mp3'),
('redbone edit', 'Unknown Artist', '/midtermProject/assets/audio/redbone_edit.mp3'),
('slide edit', 'Unknown Artist', '/midtermProject/assets/audio/slide_edit.mp3'),
('somebodyloves me', 'Unknown Artist', '/midtermProject/assets/audio/somebodyloves_me.mp3'),
('sprinter edit', 'Unknown Artist', '/midtermProject/assets/audio/sprinter_edit.mp3'),
('supermean spiderman', 'Unknown Artist', '/midtermProject/assets/audio/supermean_spiderman.mp3'),
('victory lap', 'Unknown Artist', '/midtermProject/assets/audio/victory_lap.mp3'),
('wasteno time', 'Unknown Artist', '/midtermProject/assets/audio/wasteno_time.mp3'),
('which one', 'Unknown Artist', '/midtermProject/assets/audio/which_one.mp3');