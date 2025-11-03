#!/bin/bash

TOKEN="3|pXqkTQWxmV61J4biqVzaCwqDB84XzbzZ6yNm0fJN9350c8f0"

echo "🚀 Adding 27 light metrics (Oct 4-30)..."

for i in {4..30}; do
  DATE=$(printf "2025-10-%02d" $i)
  
  curl -X POST http://localhost:8000/api/applications/2/metrics \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer $TOKEN" \
    -d "{
      \"date\": \"$DATE\",
      \"requests_count\": 100,
      \"storage_gb\": 0.5,
      \"cpu_hours\": 0.1
    }" \
    -s > /dev/null
  
  echo "✅ Metric added for $DATE"
done

echo ""
echo "🎉 Done! 27 metrics added"
echo ""
echo "📊 New stats:"
echo "   - 27 days × 0.095 kg = 2.565 kg"
echo "   - 3 days × 3.495 kg = 10.485 kg"
echo "   - Total: 13.05 kg CO2"
echo "   - Monthly average: 13.05 kg"
echo "   - Expected badge: GOLD 🥇 (< 20 kg)"