-- Create a new reviews and review_questions table to make them customizable

DROP TABLE IF EXISTS review_questions;
CREATE TABLE review_questions (
    id          INT(11) NOT NULL
  , campaign    INT(11) NOT NULL
  , question    BLOB
  , created_by  INT(11)
  , created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  , modified_by INT(11)
  , modified_at TIMESTAMP
  , PRIMARY KEY (id)
  , CONSTRAINT FOREIGN KEY (campaign) REFERENCES campaigns(id) ON DELETE CASCADE
  , CONSTRAINT FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
  , CONSTRAINT FOREIGN KEY (modified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 ;

DROP TABLE IF EXISTS review_answers;
CREATE TABLE IF NOT EXISTS review_answers (
    proposal        INT(11) NOT NULL
  , question        INT(11) NOT NULL
  , reviewer        INT(11) NOT NULL
  , points          TINYINT UNSIGNED
  , comments        BLOB
  , created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  , PRIMARY KEY (proposal, question, reviewer)
  , CONSTRAINT FOREIGN KEY (proposal) REFERENCES proposals (id) ON DELETE CASCADE
  , CONSTRAINT FOREIGN KEY (question) REFERENCES review_questions(id) ON DELETE CASCADE
  , CONSTRAINT FOREIGN KEY (reviewer) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 ;
