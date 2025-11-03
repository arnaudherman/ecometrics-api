#!/bin/bash

echo "🧹 EcoMetrics - Reset de la base de données"
echo "============================================"
echo ""

echo "⚠️  ATTENTION: Toutes les données vont être supprimées !"
echo ""
read -p "Continuer ? (y/n) " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]
then
    echo "🗑️  Suppression des données..."
    docker-compose exec -T app php artisan migrate:fresh
    
    echo ""
    echo "✅ Base de données réinitialisée !"
    echo ""
    
    # Vérification
    echo "📊 Vérification..."
    USERS=$(docker-compose exec -T db psql -U ecometrics -d ecometrics -t -c "SELECT COUNT(*) FROM users;" 2>/dev/null | xargs)
    APPS=$(docker-compose exec -T db psql -U ecometrics -d ecometrics -t -c "SELECT COUNT(*) FROM applications;" 2>/dev/null | xargs)
    METRICS=$(docker-compose exec -T db psql -U ecometrics -d ecometrics -t -c "SELECT COUNT(*) FROM metrics;" 2>/dev/null | xargs)
    CERTS=$(docker-compose exec -T db psql -U ecometrics -d ecometrics -t -c "SELECT COUNT(*) FROM certificates;" 2>/dev/null | xargs)
    
    echo "  • Users: ${USERS:-0}"
    echo "  • Applications: ${APPS:-0}"
    echo "  • Metrics: ${METRICS:-0}"
    echo "  • Certificates: ${CERTS:-0}"
    echo ""
    
    echo "============================================"
    echo "✅ Base propre ! Prêt pour la démo !"
    echo ""
    echo "Tu peux maintenant utiliser:"
    echo "  • Thunder Client pour une démo manuelle"
    echo "  • ./demo_complete.sh pour une démo automatique"
    echo "============================================"
else
    echo "❌ Annulé"
fi
