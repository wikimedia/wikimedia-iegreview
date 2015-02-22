-- Create a new reviews and review_questions table to make them customizable

RENAME TABLE reviews to reviews_legacy;


DROP TABLE IF EXISTS review_questions;
CREATE TABLE IF NOT EXISTS review_questions (
    id              INT(11) NOT NULL
  , campaign        INT(11) NOT NULL
  , question        VARCHAR(255)
  , added_by        INT(11)
  , created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  , PRIMARY KEY (id)
  , CONSTRAINT FOREIGN KEY (campaign) REFERENCES campaigns(id) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 ;


CREATE TABLE reviews (
    id              INT(11) NOT NULL AUTO_INCREMENT
  , proposal        INT(11) NOT NULL
  , campaign        INT(11) NOT NULL
  , question        INT(11)
  , points          INT(11)
  , comments        VARCHAR(255)
  , added_by        INT(11)
  , created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  , PRIMARY KEY (id)
  , CONSTRAINT FOREIGN KEY (proposal) REFERENCES proposals (id) ON DELETE CASCADE
  , CONSTRAINT FOREIGN KEY (campaign) REFERENCES campaigns(id) ON DELETE CASCADE
  , CONSTRAINT FOREIGN KEY (question) REFERENCES review_questions(id) ON DELETE CASCADE
  , CONSTRAINT FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE RESTRICT
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 ;
