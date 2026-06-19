-- ============================================================
--  TUP-LMS — Course Content Schema Additions
--  Adds: Activities as a course_modules variant, and a fully
--  separate Exams structure (kept independent from Quizzes).
--  Run this against the existing tup_lms database.
-- ============================================================

USE tup_lms;

-- ------------------------------------------------------------
-- 1. ACTIVITIES live inside course_modules as a variant row.
--    item_type distinguishes a regular lesson/topic from an
--    activity (assignment/discussion/project/reflection).
-- ------------------------------------------------------------
ALTER TABLE course_modules
    ADD COLUMN item_type ENUM('lesson','activity') NOT NULL DEFAULT 'lesson' AFTER course_id,
    ADD COLUMN activity_type ENUM('assignment','discussion','project','reflection') DEFAULT NULL AFTER item_type,
    ADD COLUMN points SMALLINT UNSIGNED DEFAULT NULL AFTER content,
    ADD COLUMN due_at DATETIME DEFAULT NULL AFTER points;

CREATE INDEX idx_modules_item_type ON course_modules(course_id, item_type);

-- ------------------------------------------------------------
-- 2. EXAMS — intentionally separate from quizzes/quiz_questions.
-- ------------------------------------------------------------
CREATE TABLE exams (
    exam_id      INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    course_id    INT UNSIGNED  NOT NULL,
    module_id    INT UNSIGNED  DEFAULT NULL,
    title        VARCHAR(200)  NOT NULL,
    description  TEXT          DEFAULT NULL,
    ai_generated TINYINT(1)   NOT NULL DEFAULT 0,
    is_published TINYINT(1)   NOT NULL DEFAULT 0,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                       ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_exam_course
        FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    CONSTRAINT fk_exam_module
        FOREIGN KEY (module_id) REFERENCES course_modules(module_id) ON DELETE SET NULL
);

CREATE TABLE exam_questions (
    question_id    INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    exam_id        INT UNSIGNED  NOT NULL,
    question_text  TEXT          NOT NULL,
    question_type  ENUM('multiple_choice','open_ended') NOT NULL,
    correct_answer TEXT          DEFAULT NULL,
    points         SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    order_index    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_examq_exam
        FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE CASCADE
);

CREATE TABLE exam_question_choices (
    choice_id    INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    question_id  INT UNSIGNED  NOT NULL,
    choice_label CHAR(1)       NOT NULL,
    choice_text  TEXT          NOT NULL,
    is_correct   TINYINT(1)   NOT NULL DEFAULT 0,
    CONSTRAINT fk_examqc_question
        FOREIGN KEY (question_id) REFERENCES exam_questions(question_id) ON DELETE CASCADE
);

CREATE INDEX idx_exams_course ON exams(course_id);
CREATE INDEX idx_examq_exam   ON exam_questions(exam_id);
