#!/bin/bash

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
sleep 15

# Try to connect and create the user if it doesn't exist
mysql -h mysql -u root -p"${DB_ROOT_PASSWORD}" << EOF
-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\`;

-- Create the user if it doesn't exist
CREATE USER IF NOT EXISTS '${DB_USERNAME}'@'%' IDENTIFIED BY '${DB_PASSWORD}';

-- Grant privileges
GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'%';
GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost';

FLUSH PRIVILEGES;

-- Verify
SELECT User, Host FROM mysql.user WHERE User='${DB_USERNAME}';
EOF

echo "✓ MySQL user setup complete"
