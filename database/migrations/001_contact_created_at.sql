-- Adds a received-at timestamp to contact messages (shown in the admin inbox).
ALTER TABLE contact ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
