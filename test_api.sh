#!/bin/bash

echo "🧪 ECOMETRICS API - TEST COMPLET"
echo "================================="

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 1. Tests automatisés
echo ""
echo "📋 1. Tests automatisés..."
docker-compose exec app php artisan test
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Tests automatisés OK${NC}"
else
    echo -e "${RED}❌ Tests automatisés FAILED${NC}"
    exit 1
fi

# 2. Reset base
echo ""
echo "🔄 2. Reset base de données..."
docker-compose exec app php artisan migrate:fresh --seed

# 3. Register
echo ""
echo "👤 3. Test Register..."
REGISTER_RESPONSE=$(curl -s -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@test.com",
    "password": "password123",
    "password_confirmation": "password123"
  }')

TOKEN=$(echo $REGISTER_RESPONSE | jq -r '.access_token')

if [ "$TOKEN" != "null" ] && [ "$TOKEN" != "" ]; then
    echo -e "${GREEN}✅ Register OK - Token reçu${NC}"
else
    echo -e "${RED}❌ Register FAILED${NC}"
    echo $REGISTER_RESPONSE
    exit 1
fi

# 4. Create Application
echo ""
echo "📱 4. Test Create Application..."
APP_RESPONSE=$(curl -s -X POST http://localhost:8000/api/applications \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name": "Test App",
    "url": "https://test.com",
    "description": "Test"
  }')

APP_ID=$(echo $APP_RESPONSE | jq -r '.application.id')

if [ "$APP_ID" != "null" ] && [ "$APP_ID" != "" ]; then
    echo -e "${GREEN}✅ Create Application OK - ID: $APP_ID${NC}"
else
    echo -e "${RED}❌ Create Application FAILED${NC}"
    echo $APP_RESPONSE
    exit 1
fi

# 5. Create Metrics
echo ""
echo "📊 5. Test Create Metrics..."
METRIC_RESPONSE=$(curl -s -X POST http://localhost:8000/api/applications/$APP_ID/metrics \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "date": "2025-11-01",
    "requests_count": 1000,
    "storage_gb": 1.0,
    "cpu_hours": 0.5
  }')

CARBON=$(echo $METRIC_RESPONSE | jq -r '.metric.carbon_footprint_kg')

if [ "$CARBON" != "null" ] && [ "$CARBON" != "" ]; then
    echo -e "${GREEN}✅ Create Metric OK - Carbon: $CARBON kg${NC}"
else
    echo -e "${RED}❌ Create Metric FAILED${NC}"
    echo $METRIC_RESPONSE
    exit 1
fi

# 6. Issue Certificate
echo ""
echo "🏆 6. Test Issue Certificate..."
CERT_RESPONSE=$(curl -s -X POST http://localhost:8000/api/applications/$APP_ID/issue-certificate \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN")

BADGE=$(echo $CERT_RESPONSE | jq -r '.certificate.badge_level')

if [ "$BADGE" != "null" ] && [ "$BADGE" != "" ]; then
    echo -e "${GREEN}✅ Issue Certificate OK - Badge: $BADGE${NC}"
else
    echo -e "${RED}❌ Issue Certificate FAILED${NC}"
    echo $CERT_RESPONSE
    exit 1
fi

# Success
echo ""
echo "================================="
echo -e "${GREEN}🎉 TOUS LES TESTS PASSENT !${NC}"
echo "================================="