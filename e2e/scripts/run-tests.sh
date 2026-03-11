#!/bin/bash

set -e

echo "Starting E2E Test Environment..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Check if database is accessible
echo "Checking database connection..."
php artisan tinker --execute="DB::connection()->getPdo();"

# Run migrations
echo "Running migrations..."
php artisan migrate:fresh --seed

# Start the server in background
echo "Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=8000 &
SERVER_PID=$!

# Wait for server to be ready
echo "Waiting for server to be ready..."
for i in {1..30}; do
    if curl -s http://localhost:8000 > /dev/null 2>&1; then
        echo "Server is ready!"
        break
    fi
    sleep 1
done

# Run tests
echo "Running E2E tests..."
cd e2e
npm install
npm test

# Cleanup
echo "Cleaning up..."
kill $SERVER_PID 2>/dev/null || true

echo "E2E tests completed!"
