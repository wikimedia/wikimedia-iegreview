-- Create a campaigns table to hold a record of past and previous campaigns
-- Bug: T87299

DROP TABLE IF EXISTS campaigns;

CREATE TABLE IF NOT EXISTS campaigns (
    id              INT(11) NOT NULL AUTO_INCREMENT
  , name            VARCHAR(255) NOT NULL
  , created_by      INT(11)
  , created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  , campaign_status TINYINT(1) DEFAULT 0
  , start_date      TIMESTAMP NOT NULL
  , end_date        TIMESTAMP NOT NULL
  , PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 ;