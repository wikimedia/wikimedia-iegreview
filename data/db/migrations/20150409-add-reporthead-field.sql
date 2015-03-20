-- Add report_head field in Questions table which will hold report column string
ALTER TABLE review_questions
  ADD COLUMN report_head VARCHAR(255) AFTER question_footer;