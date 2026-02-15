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

-- Insert sample data
INSERT INTO students (matric_number, name, blood_group, state_of_origin, phone, hobbies) VALUES
('CS001', 'John Doe', 'O+', 'Lagos', '08012345678', 'Reading, Football'),
('CS002', 'Jane Smith', 'A+', 'Abuja', '08087654321', 'Swimming, Coding'),
('CS003', 'Mike Johnson', 'B+', 'Kano', '08011223344', 'Gaming, Music');

INSERT INTO courses (course_code, course_title, credit_unit) VALUES
('CS101', 'Introduction to Programming', 3),
('CS102', 'Database Systems', 3),
('CS103', 'Web Development', 2),
('CS104', 'Mathematics for Computing', 3);

INSERT INTO student_courses (student_id, course_id) VALUES
(1, 1), (1, 2), (1, 3),
(2, 1), (2, 3), (2, 4),
(3, 2), (3, 4);

INSERT INTO employees (name, position) VALUES
('Dr. James Wilson', 'Professor'),
('Ms. Sarah Brown', 'Administrator'),
('Mr. David Lee', 'Accountant');