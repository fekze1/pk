-- Таблица Passport (Паспорт)
CREATE TABLE Passport (
    passport_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    fullname VARCHAR(255) NOT NULL,
    issue_date DATE NOT NULL,
    series_and_number VARCHAR(255) NOT NULL
);

-- Таблица Employee (Сотрудник)
CREATE TABLE Employee (
    employee_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    login_employee VARCHAR(255) NOT NULL,
    password_employee VARCHAR(255) NOT NULL
);

-- Таблица Faculty (Факультет)
CREATE TABLE Faculty (
    faculty_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name_faculty VARCHAR(255) NOT NULL,
	avggrade_for_budget NUMERIC(5, 2) NOT NULL,
    avggrade_for_paid NUMERIC(5, 2) NOT NULL,
	examgrade_for_budget NUMERIC(5, 2) NOT NULL,
    examgrade_for_paid NUMERIC(5, 2) NOT NULL
);

-- Таблица Certificate (Аттестат)
CREATE TABLE Certificate (
    certificate_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    number_certificate VARCHAR(255) NOT NULL,
    issue_date DATE NOT NULL,
	average_grade NUMERIC(5, 2) NOT NULL,
	average_exam_grade NUMERIC(5, 2) NOT NULL
);

-- Таблица Applicant (Абитуриент)
CREATE TABLE Applicant (
    applicant_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    login_applicant VARCHAR(255) NOT NULL,
    password_applicant VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    fullname VARCHAR(255) NOT NULL,
    priority_applicant INT DEFAULT NULL,
    passport_id BIGINT DEFAULT NULL,
    certificate_id BIGINT DEFAULT NULL,
    benefit_id BIGINT DEFAULT NULL,
    FOREIGN KEY (passport_id) REFERENCES Passport(passport_id),
    FOREIGN KEY (certificate_id) REFERENCES Certificate(certificate_id)
);

-- Таблица Benefit (Льгота)
CREATE TABLE Benefit (
    benefit_id BIGINT PRIMARY KEY AUTO_INCREMENT,
	applicant_id BIGINT,
    name_benefit VARCHAR(255) NOT NULL,
    number_benefit VARCHAR(255) NOT NULL,
	FOREIGN KEY (applicant_id) REFERENCES Applicant(applicant_id)
);


-- Таблица Application (Заявка)
CREATE TABLE Application (
    application_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    type_application VARCHAR(255) NOT NULL,
    status_application VARCHAR(255) NOT NULL,
    applicant_id BIGINT,
    employee_id BIGINT,
    faculty_id BIGINT,
    FOREIGN KEY (applicant_id) REFERENCES Applicant(applicant_id),
    FOREIGN KEY (employee_id) REFERENCES Employee(employee_id),
    FOREIGN KEY (faculty_id) REFERENCES Faculty(faculty_id)
);

CREATE TABLE Faculty_Employee (
	facultyemployee_id BIGINT PRIMARY KEY AUTO_INCREMENT,
	faculty_id BIGINT,
	employee_id BIGINT,
	FOREIGN KEY (faculty_id) REFERENCES Faculty(faculty_id),
	FOREIGN KEY (employee_id) REFERENCES Employee(employee_id)
);