ALTER TABLE users
  ADD COLUMN reset_date TIMESTAMP NULL DEFAULT NULL
  AFTER reset_hash;
