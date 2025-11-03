cat > demo_complete.sh << 'EOF'
#!/bin/bash

echo "🌱 EcoMetrics API - Démo complète"
echo "================================="
echo ""

# 1. Register
echo "1️⃣  REGISTER - Création d'un compte"
echo "──────────────────────────────────"
REGISTER=$(curl -s -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d "{
    \"name\": \"Demo Infomaniak\",
    \"email\": \"demo-$(date +%s)@infomaniak.com\",
    \"password\": \"password123\",
    \"password_confirmation\": \"password123\"
  }")

TOKEN=$(echo "$REGISTER" | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)
USER_ID=$(echo "$REGISTER" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
echo "✅ User créé: ID $USER_ID"
echo "🔑 Token: ${TOKEN:0:40}..."
echo ""
sleep 2

# 2. Create Application
echo "2️⃣  CREATE APPLICATION - Enregistrer une app"
echo "────────────────────────────────────────────"
APP=$(curl -s -X POST http://localhost:8000/api/applications \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Green Cloud Platform",
    "url": "https://greencloud.infomaniak.com",
    "description": "Plateforme cloud écologique hébergée en Suisse"
  }')

APP_ID=$(echo "$APP" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
echo "✅ Application créée: ID $APP_ID"
echo "📱 Name: Green Cloud Platform"
echo ""
sleep 2

# 3. Create Metrics (5 jours)
echo "3️⃣  CREATE METRICS - Tracking sur 5 jours"
echo "─────────────────────────────────────────"

for i in {0..4}; do
  DATE=$(date -v-${i}d +%Y-%m-%d 2>/dev/null || date -d "${i} days ago" +%Y-%m-%d)
  REQUESTS=$((8000 + RANDOM % 4000))
  STORAGE=$(awk -v min=4.5 -v max=7.5 'BEGIN{srand(); print min+rand()*(max-min)}')
  CPU=$(awk -v min=1.8 -v max=3.5 'BEGIN{srand(); print min+rand()*(max-min)}')
  
  METRIC=$(curl -s -X POST http://localhost:8000/api/applications/$APP_ID/metrics \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d "{
      \"date\": \"$DATE\",
      \"requests_count\": $REQUESTS,
      \"storage_gb\": $STORAGE,
      \"cpu_hours\": $CPU
    }")
  
  CARBON=$(echo "$METRIC" | grep -o '"carbon_footprint_kg":[0-9.]*' | cut -d':' -f2)
  printf "  📊 %s: %5d req, %.1f GB, %.1f h → %s kg CO₂\n" "$DATE" "$REQUESTS" "$STORAGE" "$CPU" "$CARBON"
  sleep 0.5
done
echo ""
sleep 2

# 4. Get Stats
echo "4️⃣  GET STATS - Statistiques agrégées"
echo "─────────────────────────────────────"
STATS=$(curl -s -X GET http://localhost:8000/api/applications/$APP_ID/metrics/stats \
  -H "Authorization: Bearer $TOKEN")

TOTAL_METRICS=$(echo "$STATS" | grep -o '"total_metrics":[0-9]*' | cut -d':' -f2)
TOTAL_CARBON=$(echo "$STATS" | grep -o '"total_carbon_footprint_kg":[0-9.]*' | cut -d':' -f2)
AVG_CARBON=$(echo "$STATS" | grep -o '"average_carbon_footprint_kg":[0-9.]*' | cut -d':' -f2)

echo "  📊 Total métriques: $TOTAL_METRICS"
echo "  🌍 Empreinte totale: $TOTAL_CARBON kg CO₂"
echo "  📈 Moyenne: $AVG_CARBON kg CO₂/jour"
echo ""
sleep 2

# 5. Issue Certificate
echo "5️⃣  ISSUE CERTIFICATE - Génération du badge écologique"
echo "──────────────────────────────────────────────────────"
CERT=$(curl -s -X POST http://localhost:8000/api/applications/$APP_ID/issue-certificate \
  -H "Authorization: Bearer $TOKEN")

BADGE=$(echo "$CERT" | grep -o '"badge_level":"[^"]*' | cut -d'"' -f4)
CERT_TOTAL=$(echo "$CERT" | grep -o '"total_carbon_kg":[0-9.]*' | cut -d':' -f2)
MONTHLY_AVG=$(echo "$CERT" | grep -o '"monthly_average_kg":[0-9.]*' | cut -d':' -f2)

echo "✅ Certificat généré"
echo "🏆 Badge: $(echo $BADGE | tr '[:lower:]' '[:upper:]')"
echo "📊 Total: ${CERT_TOTAL} kg CO₂"
echo "📈 Moyenne mensuelle: ${MONTHLY_AVG} kg CO₂/mois"
echo ""

# Résumé
echo "================================="
echo "✅ DÉMO TERMINÉE AVEC SUCCÈS !"
echo ""
echo "📊 Résumé:"
echo "  • User ID: $USER_ID"
echo "  • App ID: $APP_ID"
echo "  • Métriques: 5 jours"
echo "  • Total CO₂: ${CERT_TOTAL} kg"
echo "  • Badge: $(echo $BADGE | tr '[:lower:]' '[:upper:]')"
echo ""
echo "Token pour Thunder Client:"
echo "$TOKEN"
echo ""
echo "================================="
EOF

chmod +x demo_complete.sh