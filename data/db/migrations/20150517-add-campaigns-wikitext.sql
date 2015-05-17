-- Add wikitext field to campaigns table for holding the wikitext template
ALTER TABLE campaigns
  ADD COLUMN wikitext BLOB
  AFTER end_date;