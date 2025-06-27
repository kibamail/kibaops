-- ==============================================================================
--                     PostgreSQL Initialization Script
-- ==============================================================================
--
-- This script creates the necessary users and databases for KibaOps
-- It runs automatically when the PostgreSQL container starts for the first time
--
-- ==============================================================================

-- Create the main application user if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'kibaops_user') THEN
        CREATE ROLE kibaops_user WITH LOGIN PASSWORD 'kibaops_password';
        RAISE NOTICE 'Created user: kibaops_user';
    ELSE
        RAISE NOTICE 'User kibaops_user already exists';
    END IF;
END
$$;

-- Create the queue worker user if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'kibaops_queue') THEN
        CREATE ROLE kibaops_queue WITH LOGIN PASSWORD 'kibaops_queue_password';
        RAISE NOTICE 'Created user: kibaops_queue';
    ELSE
        RAISE NOTICE 'User kibaops_queue already exists';
    END IF;
END
$$;

-- Create the main application database if it doesn't exist
SELECT 'CREATE DATABASE kibaops OWNER kibaops_user'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'kibaops')\gexec

-- Grant necessary privileges to the application user
GRANT ALL PRIVILEGES ON DATABASE kibaops TO kibaops_user;
GRANT ALL PRIVILEGES ON DATABASE kibaops TO kibaops_queue;

-- Create testing database for CI/testing environments
SELECT 'CREATE DATABASE kibaops_test OWNER kibaops_user'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'kibaops_test')\gexec

GRANT ALL PRIVILEGES ON DATABASE kibaops_test TO kibaops_user;
GRANT ALL PRIVILEGES ON DATABASE kibaops_test TO kibaops_queue;

-- Create development database (optional, for local development variations)
SELECT 'CREATE DATABASE kibaops_dev OWNER kibaops_user'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'kibaops_dev')\gexec

GRANT ALL PRIVILEGES ON DATABASE kibaops_dev TO kibaops_user;
GRANT ALL PRIVILEGES ON DATABASE kibaops_dev TO kibaops_queue;

-- Ensure the users can create databases (needed for testing)
ALTER ROLE kibaops_user CREATEDB;
ALTER ROLE kibaops_queue CREATEDB;

-- Connect to the main database and set up schema permissions
\c kibaops

-- Grant schema permissions
GRANT ALL ON SCHEMA public TO kibaops_user;
GRANT ALL ON SCHEMA public TO kibaops_queue;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO kibaops_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO kibaops_queue;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO kibaops_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO kibaops_queue;

-- Set default privileges for future objects
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO kibaops_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO kibaops_queue;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO kibaops_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO kibaops_queue;

-- Connect to test database and set up schema permissions
\c kibaops_test

GRANT ALL ON SCHEMA public TO kibaops_user;
GRANT ALL ON SCHEMA public TO kibaops_queue;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO kibaops_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO kibaops_queue;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO kibaops_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO kibaops_queue;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO kibaops_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO kibaops_queue;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO kibaops_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO kibaops_queue;

-- Connect to dev database and set up schema permissions
\c kibaops_dev

GRANT ALL ON SCHEMA public TO kibaops_user;
GRANT ALL ON SCHEMA public TO kibaops_queue;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO kibaops_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO kibaops_queue;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO kibaops_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO kibaops_queue;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO kibaops_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO kibaops_queue;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO kibaops_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO kibaops_queue;

-- Switch back to postgres database for final setup
\c postgres

-- Display created databases and users for verification
\echo '==============================================================================';
\echo '                     PostgreSQL Initialization Complete';
\echo '==============================================================================';
\echo '';
\echo 'Created databases:';
SELECT datname as "Database Name", 
       pg_catalog.pg_get_userbyid(datdba) as "Owner"
FROM pg_catalog.pg_database 
WHERE datname IN ('kibaops', 'kibaops_test', 'kibaops_dev')
ORDER BY datname;

\echo '';
\echo 'Created users:';
SELECT rolname as "Username",
       CASE WHEN rolcreatedb THEN 'Yes' ELSE 'No' END as "Can Create DB",
       CASE WHEN rolcanlogin THEN 'Yes' ELSE 'No' END as "Can Login"
FROM pg_catalog.pg_roles
WHERE rolname IN ('kibaops_user', 'kibaops_queue')
ORDER BY rolname;

\echo '';
\echo 'Setup completed successfully!';
\echo '==============================================================================';
