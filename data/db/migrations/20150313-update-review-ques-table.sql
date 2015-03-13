-- Make points field in review_answers SIGNED
ALTER TABLE review_answers CHANGE points points TINYINT(3);

-- Add type field to identify question type recommend/score
ALTER TABLE review_questions
  ADD COLUMN type ENUM( 'score', 'recommend' ) DEFAULT 'score'
  AFTER question;

-- Add question_title, question_footer strings; Update question -> question_body
ALTER TABLE review_questions
  CHANGE question question_body BLOB DEFAULT NULL,
  ADD COLUMN question_title VARCHAR(255) AFTER campaign,
  ADD COLUMN question_footer VARCHAR(255) AFTER question_body;
