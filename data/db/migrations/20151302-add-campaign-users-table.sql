-- Bug: T89455
-- Add campaign-users table to specify which users have access to which campaigns

DROP TABLE IF EXISTS campaign_users;

CREATE TABLE IF NOT EXISTS campaign_users (
    campaign_id     INT(11) NOT NULL
  , user_id         INT(11) NOT NULL
  , added_by        INT(11)
  , created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  , PRIMARY KEY (campaign_id, user_id)
  , CONSTRAINT FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
  , CONSTRAINT FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 ;