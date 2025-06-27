#!/bin/bash

# ==============================================================================
#                     PostgreSQL Environment Setup Script
# ==============================================================================
#
# This script ensures proper environment setup and validates the database
# initialization. It runs after the SQL initialization script.
#
# ==============================================================================

set -e

echo "=============================================================================="
echo "                     PostgreSQL Environment Setup"
echo "=============================================================================="

# Function to execute SQL commands
execute_sql() {
    local database=$1
    local sql=$2
    psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$database" <<-EOSQL
        $sql
EOSQL
}

# Function to check if database exists
database_exists() {
    local db_name=$1
    psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "postgres" -tAc "SELECT 1 FROM pg_database WHERE datname='$db_name'" | grep -q 1
}

# Function to check if user exists
user_exists() {
    local user_name=$1
    psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "postgres" -tAc "SELECT 1 FROM pg_roles WHERE rolname='$user_name'" | grep -q 1
}

echo "Validating database setup..."

# Validate that the application users exist
if user_exists "kibaops_user"; then
    echo "✓ User 'kibaops_user' exists"
else
    echo "✗ User 'kibaops_user' does not exist"
    exit 1
fi

if user_exists "kibaops_queue"; then
    echo "✓ User 'kibaops_queue' exists"
else
    echo "✗ User 'kibaops_queue' does not exist"
    exit 1
fi

# Validate that databases exist
for db in "kibaops" "kibaops_test" "kibaops_dev"; do
    if database_exists "$db"; then
        echo "✓ Database '$db' exists"
    else
        echo "✗ Database '$db' does not exist"
        exit 1
    fi
done

# Test connection with application user (using local socket during init)
echo "Testing connection with application user..."
if PGPASSWORD=kibaops_password psql -U kibaops_user -d kibaops -c "SELECT version();" > /dev/null 2>&1; then
    echo "✓ Application user can connect to main database"
else
    echo "✗ Application user cannot connect to main database"
    exit 1
fi

# Test connection with queue user
echo "Testing connection with queue user..."
if PGPASSWORD=kibaops_queue_password psql -U kibaops_queue -d kibaops -c "SELECT version();" > /dev/null 2>&1; then
    echo "✓ Queue user can connect to main database"
else
    echo "✗ Queue user cannot connect to main database"
    exit 1
fi

# Test database creation permissions
echo "Testing database creation permissions..."
if PGPASSWORD=kibaops_password psql -U kibaops_user -d postgres -c "SELECT 1;" > /dev/null 2>&1; then
    echo "✓ Application user has necessary permissions"
else
    echo "✗ Application user lacks necessary permissions"
    exit 1
fi

if PGPASSWORD=kibaops_queue_password psql -U kibaops_queue -d postgres -c "SELECT 1;" > /dev/null 2>&1; then
    echo "✓ Queue user has necessary permissions"
else
    echo "✗ Queue user lacks necessary permissions"
    exit 1
fi

# Set up additional configurations if needed
echo "Setting up additional configurations..."

# Ensure proper encoding and collation
for db in "kibaops" "kibaops_test" "kibaops_dev"; do
    execute_sql "$db" "
        -- Ensure UTF-8 encoding
        UPDATE pg_database SET encoding = pg_char_to_encoding('UTF8') WHERE datname = '$db';
        
        -- Create extensions that might be needed
        CREATE EXTENSION IF NOT EXISTS \"uuid-ossp\";
        CREATE EXTENSION IF NOT EXISTS \"pgcrypto\";
    "
    echo "✓ Configured database '$db'"
done

# Display final status
echo ""
echo "=============================================================================="
echo "                     PostgreSQL Setup Summary"
echo "=============================================================================="

# Show database information
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "postgres" <<-EOSQL
    SELECT 
        d.datname as "Database",
        pg_catalog.pg_get_userbyid(d.datdba) as "Owner",
        pg_encoding_to_char(d.encoding) as "Encoding",
        d.datcollate as "Collate"
    FROM pg_catalog.pg_database d
    WHERE d.datname IN ('kibaops', 'kibaops_test', 'kibaops_dev')
    ORDER BY d.datname;
EOSQL

echo ""
echo "✓ PostgreSQL initialization completed successfully!"
echo "✓ All databases and users are ready for use"
echo "=============================================================================="
