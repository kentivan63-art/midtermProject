# Groovify Music App - Database Setup

## Database Configuration

The database structure is defined in `config/database.sql`. Run this SQL file in phpMyAdmin to set up the complete database schema.

## Database Structure

The database consists of 5 connected tables:

1. **users** - Stores user account information
   - userID (primary key)
   - fullname
   - email (unique)
   - password
   - created_at

2. **songs** - Stores music track information
   - songID (primary key)
   - title
   - artist
   - file_path

3. **listeninghistory** - Tracks user listening activity
   - historyID (primary key)
   - userID (foreign key to users)
   - songID (foreign key to songs)
   - timestamp

4. **playlists** - Stores user-created playlists
   - playlistID (primary key)
   - userID (foreign key to users)
   - name
   - created_at

5. **playlist_songs** - Links songs to playlists
   - id (primary key)
   - playlistID (foreign key to playlists)
   - songID (foreign key to songs)
   - added_at

## Installation

1. Open phpMyAdmin
2. Create a new database named `midtermProject`
3. Import the SQL file: `config/database.sql`
4. The SQL will create all tables and insert sample song data

## Notes

- Foreign key relationships ensure data integrity
- ON DELETE CASCADE keeps tables clean when users or songs are removed
- Indexes on foreign keys improve query performance
- The database is configured to use UTF-8 character encoding
- Playlist functionality requires both playlists and playlist_songs tables
