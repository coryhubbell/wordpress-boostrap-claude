#!/bin/bash

# WordPress Bootstrap Claude - Development Startup Script
# This script starts the complete development environment

echo "🚀 Starting WordPress Bootstrap Claude Development Environment..."
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running!"
    echo "Please start Docker Desktop and try again."
    exit 1
fi

# Start Docker containers
echo "📦 Starting WordPress & MySQL containers..."
docker-compose up -d

# Wait for WordPress to be ready
echo "⏳ Waiting for WordPress to be ready..."
sleep 10

# Check if WordPress is responding
until curl -s http://localhost:8080 > /dev/null; do
    echo "   Still waiting for WordPress..."
    sleep 2
done

echo "✅ WordPress is ready at http://localhost:8080"
echo ""

# Check if node_modules exists in admin/
if [ ! -d "admin/node_modules" ]; then
    echo "📦 Installing npm dependencies..."
    cd admin && npm install && cd ..
fi

echo ""
echo "🎯 Development Environment Status:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ WordPress:      http://localhost:8080"
echo "✅ WP Admin:       http://localhost:8080/wp-admin"
echo "✅ phpMyAdmin:     http://localhost:8081"
echo "✅ REST API:       http://localhost:8080/wp-json/wpbc/v2/"
echo ""
echo "📝 WordPress Credentials:"
echo "   Username: admin (set during first install)"
echo "   Password: (you will set this during install)"
echo ""
echo "🔧 Next Steps:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1. If first time: Install WordPress at http://localhost:8080"
echo "2. Activate 'WordPress Bootstrap Claude' theme"
echo "3. Start Visual Interface dev server:"
echo "   cd admin && npm run dev"
echo "4. Access 'Visual Interface' in WordPress admin menu"
echo ""
echo "📚 Documentation: See DOCKER_SETUP.md"
echo ""
echo "Happy coding! 🎉"
