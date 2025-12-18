#!/bin/bash

set -e  # Exit on error

echo "=== Cinema REST API Setup ==="
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Install dependencies
echo "1. Installing dependencies..."
composer install

# Start Docker containers
echo ""
echo "2. Starting Docker containers..."
docker compose up -d

# Wait for MySQL to be ready
echo ""
echo "3. Waiting for MySQL to be ready..."
sleep 5

# Check if MySQL is ready
echo "Checking MySQL connection..."
until docker compose exec -T database mysql -uroot -proot -e "SELECT 1" &> /dev/null; do
    echo "Waiting for MySQL..."
    sleep 2
done
echo "✓ MySQL is ready!"

# Create database
echo ""
echo "4. Creating database..."
php bin/console doctrine:database:create --if-not-exists

# Run migrations
echo ""
echo "5. Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Generate JWT keys
echo ""
echo "6. Generating JWT keys..."
mkdir -p config/jwt

if [ ! -f config/jwt/private.pem ]; then
    echo "Generating JWT keys with passphrase 'changeme'..."
    openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:changeme
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:changeme
    echo "✓ JWT keys generated successfully!"
else
    echo "✓ JWT keys already exist, skipping..."
fi

# Create employee user
echo ""
echo "7. Creating employee user..."
php bin/console app:create-employee employee@cinema.com password123 || echo "✓ Employee user already exists"

echo ""
echo "=== Setup Complete! ==="
echo ""
echo "Docker containers are running:"
echo "  - MySQL: localhost:3306"
echo ""
echo "You can now start the server with:"
echo "  php -S localhost:8000 -t public"
echo ""
echo "Or use the quick start script:"
echo "  ./reset-db.sh && php -S localhost:8000 -t public"
echo ""
echo "Employee credentials:"
echo "  Email: employee@cinema.com"
echo "  Password: password123"
echo ""
echo "To stop Docker containers:"
echo "  docker compose down"
echo ""
