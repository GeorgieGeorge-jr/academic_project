-- Create database
CREATE DATABASE IF NOT EXISTS academic_project;
USE academic_project;

-- Students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    matric_number VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    blood_group VARCHAR(5),
    state_of_origin VARCHAR(50),
    phone VARCHAR(15),
    hobbies TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(10) UNIQUE NOT NULL,
    course_title VARCHAR(100) NOT NULL,
    credit_unit INT NOT NULL
);

-- Student courses junction table
CREATE TABLE student_courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    course_id INT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Employees table
CREATE TABLE employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(50) NOT NULL
);

-- Payroll records table
CREATE TABLE payroll_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT,
    hours_worked DECIMAL(5,2) NOT NULL,
    hourly_rate DECIMAL(10,2) NOT NULL,
    deduction DECIMAL(10,2) NOT NULL,
    gross_pay DECIMAL(10,2) NOT NULL,
    net_pay DECIMAL(10,2) NOT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- GPA records table
CREATE TABLE gpa_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    total_grade_points DECIMAL(10,2) NOT NULL,
    total_credit_units INT NOT NULL,
    gpa DECIMAL(3,2) NOT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Add this new table for course assessments
CREATE TABLE course_assessments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    course_id INT,
    quiz_1 DECIMAL(5,2) DEFAULT 0,
    quiz_2 DECIMAL(5,2) DEFAULT 0,
    quiz_3 DECIMAL(5,2) DEFAULT 0,
    mid_semester DECIMAL(5,2) DEFAULT 0,
    project DECIMAL(5,2) DEFAULT 0,
    attendance DECIMAL(5,2) DEFAULT 0,
    exam DECIMAL(5,2) DEFAULT 0,
    total_score DECIMAL(5,2) DEFAULT 0,
    grade VARCHAR(2),
    grade_point DECIMAL(3,2),
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_course (student_id, course_id)
);

CREATE TABLE gpa_records
(
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    total_grade_points DECIMAL(10,2) NOT NULL,
    total_credit_units INT NOT NULL,
    gpa DECIMAL(3,2) NOT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO students (matric_number, name, blood_group, state_of_origin, phone, hobbies) VALUES
('22/0097', 'George George', 'A+', 'AkwaIBom State', '09163037014', 'Playing Chess, Basketball'),
('22/0330', 'Olaifa Oladapo Fuad', 'O+', 'Ogun State', '09043447184', 'Reading, Coding'),
('22/0054', 'Ikhioya clarence', 'B+', 'Edo state', '09063380716', 'Watching movies, Music'),
('22/2900', 'okorie noble chibueze', 'O+', 'Imo state', '09130129226', 'traveling, Music'),
('22/0272', 'Kalejaye Abdulrahman Oluwatobi', 'O+', 'Ogun state', '08166625250', 'Reading, Music'),
('22/0115', 'Ezembaukwu Vincent', 'O+', 'Anambra State', '09068936312', 'Watching movies, Music'),
('22/0127', 'Kalu Joshua Uwaoma', 'O+', 'Abia state', '08064270255', 'Music'),
('22/0087', 'Eze Sandra', 'O-', 'Anambra state', '09075892154', 'Playing games, Seeing Movies'),
('22/0088', 'Eze-Bekee David', 'A+', 'Abia state', '08123199386', 'Playing games, Watching Series'),
('22/0299', 'Chidubem Iwuh', 'O+', 'Imo state', '08120787401', 'Books');

INSERT INTO courses (course_code, course_title, credit_unit) VALUES
('SENG412', 'Internet Technologies', 3),
('COSC430', 'Hands-on JAVA training', 1),
('SENG408', 'Modeling And Simulation', 3),
('SENG490', 'Research Project', 3),
('SENG406', 'Formal Methods', 3),
('SENG404', 'Human Computer Interaction', 3),
('SENG402', 'Software Quality Assurance', 3),
('GEDS420', 'Biblical Principles', 2);

-- This automatically creates all possible combinations
INSERT INTO student_courses (student_id, course_id)
SELECT s.id, c.id
FROM students s
CROSS JOIN courses c
ORDER BY s.id, c.id;

INSERT INTO employees (name, position) VALUES
('Dr. James Wilson', 'Professor'),
('Ms. Sarah Brown', 'Administrator'),
('Mr. David Lee', 'Accountant');