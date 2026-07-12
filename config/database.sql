-- =============================================
-- Groovify Music App Database Schema
-- =============================================
-- This file creates the complete database structure
-- for the Groovify music application with 5 connected tables:
-- 1. users - stores user account information
-- 2. songs - stores music track information
-- 3. listeninghistory - tracks user listening activity
-- 4. playlists - stores user-created playlists
-- 5. playlist_songs - links songs to playlists
-- =============================================

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    userID INT(11) NOT NULL AUTO_INCREMENT,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (userID),
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create songs table
CREATE TABLE IF NOT EXISTS songs (
    songID INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    artist VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create listeninghistory table
CREATE TABLE IF NOT EXISTS listeninghistory (
    historyID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT(11) NOT NULL,
    songID INT(11) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES users(userID) ON DELETE CASCADE,
    FOREIGN KEY (songID) REFERENCES songs(songID) ON DELETE CASCADE,
    INDEX idx_userID (userID),
    INDEX idx_songID (songID),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create playlists table
CREATE TABLE IF NOT EXISTS playlists (
    playlistID INT(11) AUTO_INCREMENT PRIMARY KEY,
    userID INT(11) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES users(userID) ON DELETE CASCADE,
    INDEX idx_userID (userID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create playlist_songs table
CREATE TABLE IF NOT EXISTS playlist_songs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    playlistID INT(11) NOT NULL,
    songID INT(11) NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (playlistID) REFERENCES playlists(playlistID) ON DELETE CASCADE,
    FOREIGN KEY (songID) REFERENCES songs(songID) ON DELETE CASCADE,
    INDEX idx_playlistID (playlistID),
    INDEX idx_songID (songID),
    UNIQUE KEY unique_playlist_song (playlistID, songID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4_general_ci;

-- Insert sample songs data
INSERT INTO songs (title, artist, file_path) VALUES
('3005', 'Unknown Artist', '../assets/audio/3005.mp3'),
('agora hills', 'Unknown Artist', '../assets/audio/agora_hills.mp3'),
('allforu passionfruit', 'Unknown Artist', '../assets/audio/allforu_passionfruit.mp3'),
('a keeper', 'Unknown Artist', '../assets/audio/a_keeper.mp3'),
('beautyand abeat', 'Unknown Artist', '../assets/audio/beautyand_abeat.mp3'),
('be your', 'Unknown Artist', '../assets/audio/be_your.mp3'),
('be yourkaytra', 'Unknown Artist', '../assets/audio/be_yourkaytra.mp3'),
('blaxian untitled', 'Unknown Artist', '../assets/audio/blaxian_untitled.mp3'),
('bon appetit', 'Unknown Artist', '../assets/audio/bon_appetit.mp3'),
('bym riddim', 'Unknown Artist', '../assets/audio/bym_riddim.mp3'),
('chanel', 'Unknown Artist', '../assets/audio/chanel.mp3'),
('controlla', 'Unknown Artist', '../assets/audio/controlla.mp3'),
('crazy frog', 'Unknown Artist', '../assets/audio/crazy_frog.mp3'),
('daisies flip', 'Unknown Artist', '../assets/audio/daisies_flip.mp3'),
('de samba', 'Unknown Artist', '../assets/audio/de_samba.mp3'),
('duplade bandido', 'Unknown Artist', '../assets/audio/duplade_bandido.mp3'),
('gasolina', 'Unknown Artist', '../assets/audio/gasolina.mp3'),
('let her go', 'Unknown Artist', '../assets/audio/let_her_go.mp3'),
('lost', 'Unknown Artist', '../assets/audio/lost.mp3'),
('marvins room', 'Unknown Artist', '../assets/audio/marvins_room.mp3'),
('monaco edit', 'Unknown Artist', '../assets/audio/monaco_edit.mp3'),
('nights', 'Unknown Artist', '../assets/audio/nights.mp3'),
('no idea', 'Unknown Artist', '../assets/audio/no_idea.mp3'),
('redbone edit', 'Unknown Artist', '../assets/audio/redbone_edit.mp3'),
('slide edit', 'Unknown Artist', '../assets/audio/slide_edit.mp3'),
('somebodyloves me', 'Unknown Artist', '../assets/audio/somebodyloves_me.mp3'),
('sprinter edit', 'Unknown Artist', '../assets/audio/sprinter_edit.mp3'),
('supermean spiderman', 'Unknown Artist', '../assets/audio/supermean_spiderman.mp3'),
('victory lap', 'Unknown Artist', '../assets/audio/victory_lap.mp3'),
('wasteno time', 'Unknown Artist', '../assets/audio/wasteno_time.mp3'),
('which one', 'Unknown Artist', '../assets/audio/which_one.mp3');
