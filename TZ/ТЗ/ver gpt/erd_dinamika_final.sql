-- ERD (Dinamika final) - основные таблицы (черновик)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS pg_trgm;

CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    fio VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    organization VARCHAR(255),
    position VARCHAR(255),
    avatar_path VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    deleted_at TIMESTAMP
);

-- Spatie tables (permissions & roles) will be created by package migrations

CREATE TABLE aircrafts (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    path TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

CREATE TABLE courses (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    short_description TEXT,
    long_description TEXT,
    aircraft_id BIGINT REFERENCES aircrafts(id) ON DELETE SET NULL,
    visible BOOLEAN DEFAULT true,
    price NUMERIC(10,2),
    created_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

CREATE INDEX idx_courses_title ON courses USING gin (to_tsvector('russian', coalesce(title,'') || ' ' || coalesce(short_description,'')));

CREATE TABLE aukstructures (
    id BIGSERIAL PRIMARY KEY,
    course_id BIGINT REFERENCES courses(id) ON DELETE CASCADE,
    parent_id BIGINT REFERENCES aukstructures(id) ON DELETE CASCADE,
    title VARCHAR(255),
    type VARCHAR(50),
    identifier VARCHAR(100),
    position INT DEFAULT 0,
    meta JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

CREATE TABLE lessons (
    id BIGSERIAL PRIMARY KEY,
    aukstructure_id BIGINT REFERENCES aukstructures(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    content_url TEXT,
    duration INT,
    is_visible BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

CREATE TABLE questions (
    id BIGSERIAL PRIMARY KEY,
    aukstructure_id BIGINT REFERENCES aukstructures(id) ON DELETE SET NULL,
    question_text TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    created_by BIGINT REFERENCES users(id),
    meta JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

CREATE TABLE answers (
    id BIGSERIAL PRIMARY KEY,
    question_id BIGINT REFERENCES questions(id) ON DELETE CASCADE,
    answer_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT false
);

CREATE TABLE tests (
    id BIGSERIAL PRIMARY KEY,
    course_id BIGINT REFERENCES courses(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    settings JSONB,
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

CREATE TABLE test_results (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    test_id BIGINT REFERENCES tests(id) ON DELETE CASCADE,
    score INT,
    total INT,
    passed BOOLEAN,
    details JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

CREATE TABLE assignments (
    id BIGSERIAL PRIMARY KEY,
    course_id BIGINT REFERENCES courses(id),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date TIMESTAMP WITH TIME ZONE,
    created_by BIGINT REFERENCES users(id)
);

CREATE TABLE submissions (
    id BIGSERIAL PRIMARY KEY,
    assignment_id BIGINT REFERENCES assignments(id),
    user_id BIGINT REFERENCES users(id),
    file_path TEXT,
    comment TEXT,
    grade INT,
    status VARCHAR(50),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

CREATE TABLE notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    type VARCHAR(100),
    payload JSONB,
    is_read BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

CREATE TABLE audits (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    action VARCHAR(255),
    target_type VARCHAR(100),
    target_id BIGINT,
    ip INET,
    user_agent TEXT,
    meta JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_lessons_aukstructure ON lessons(aukstructure_id);
CREATE INDEX idx_test_results_user ON test_results(user_id);
CREATE INDEX idx_assignments_course ON assignments(course_id);

-- End of DDL (черновик)