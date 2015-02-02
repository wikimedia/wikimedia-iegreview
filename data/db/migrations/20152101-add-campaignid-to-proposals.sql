-- Add a campaign id to proposals table.
-- Bug: T87306

SET foreign_key_checks = 0;
ALTER TABLE proposals
  ADD COLUMN campaign INT(11) NOT NULL AFTER id,
  ADD FOREIGN KEY (campaign) REFERENCES campaigns(id) ON DELETE CASCADE;
SET foreign_key_checks = 1;