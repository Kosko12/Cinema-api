#!/bin/bash

echo "=== Resetting Cinema Database ==="
echo ""

# Drop database if exists
echo "1. Dropping existing database..."
php bin/console doctrine:database:drop --force --if-exists

# Create database
echo ""
echo "2. Creating fresh database..."
php bin/console doctrine:database:create

# Run migrations
echo ""
echo "3. Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Create employee user
echo ""
echo "4. Creating employee user..."
php bin/console app:create-employee employee@cinema.com password123

echo ""
echo "=== Database Reset Complete! ==="
echo ""
echo "You can now start the server with:"
echo "  php -S localhost:8000 -t public"
echo ""
