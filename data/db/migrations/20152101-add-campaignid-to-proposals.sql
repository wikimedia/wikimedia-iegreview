-- Add a campaign id to proposals table.
-- Bug: T87306

ALTER TABLE proposals
  ADD COLUMN campaign_id INT(11) NOT NULL
  AFTER id;
