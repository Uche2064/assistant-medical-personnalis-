# Documentation - Statistiques des Personnels (Gestionnaire)

## 📊 Vue d'ensemble

Le gestionnaire dispose maintenant de statistiques complètes et détaillées sur tous les personnels qu'il gère, avec des graphiques, évolutions mensuelles et classements.

## 🔗 Endpoint

```
GET /v1/gestionnaire/personnels/stats
```

**Authentification** : Requise (Token JWT)  
**Rôle requis** : `gestionnaire`

---

## 📋 Structure de la Réponse Complète

```json
{
    "success": true,
    "message": "Statistiques des personnels récupérées avec succès",
    "data": {
        "vue_ensemble": { /* ... */ },
        "repartitions": { /* ... */ },
        "evolution_mensuelle": [ /* ... */ ],
        "derniers_personnels": [ /* ... */ ],
        "top_par_role": { /* ... */ }
    }
}
```

---

## 1️⃣ Vue d'Ensemble (vue_ensemble)

### Statistiques clés globales

```json
{
    "vue_ensemble": {
        "total": 45,
        "actifs": 38,
        "inactifs": 7,
        "taux_activation": 84.44,
        "nouveaux_ce_mois": 5
    }
}
```

### Métriques incluses
- ✅ **Total** : Nombre total de personnels gérés
- ✅ **Actifs** : Personnels avec compte actif
- ✅ **Inactifs** : Personnels avec compte inactif
- ✅ **Taux d'activation** : Pourcentage de personnels actifs
- ✅ **Nouveaux ce mois** : Personnels créés ce mois-ci

### Utilisation UI
**Cartes KPI** avec badges et indicateurs de tendance

---

## 2️⃣ Répartitions (repartitions)

### 2.1 Répartition par Rôle

```json
{
    "repartitions": {
        "par_role": {
            "commercial": {
                "count": 12,
                "pourcentage": 26.67,
                "actifs": 10,
                "inactifs": 2
            },
            "technicien": {
                "count": 15,
                "pourcentage": 33.33,
                "actifs": 13,
                "inactifs": 2
            },
            "medecin_controleur": {
                "count": 10,
                "pourcentage": 22.22,
                "actifs": 9,
                "inactifs": 1
            },
            "comptable": {
                "count": 8,
                "pourcentage": 17.78,
                "actifs": 6,
                "inactifs": 2
            }
        }
    }
}
```

**Graphique recommandé** : Graphique en secteurs ou barres empilées

### 2.2 Répartition par Sexe

```json
{
    "par_sexe": {
        "M": {
            "count": 25,
            "pourcentage": 55.56
        },
        "F": {
            "count": 20,
            "pourcentage": 44.44
        }
    }
}
```

**Graphique recommandé** : Graphique en secteurs

---

## 3️⃣ Évolution Mensuelle (evolution_mensuelle)

### Évolution sur les 12 derniers mois

```json
{
    "evolution_mensuelle": [
        {
            "mois": "2024-11",
            "mois_nom": "Nov 2024",
            "mois_complet": "November 2024",
            "total": 3,
            "actifs": 3,
            "inactifs": 0,
            "par_role": {
                "commercial": 1,
                "technicien": 2
            }
        }
        // ... 11 autres mois
    ]
}
```

### Données par mois
- ✅ **Total** : Personnels créés ce mois
- ✅ **Actifs/Inactifs** : Répartition par statut
- ✅ **Par rôle** : Détail par type de personnel
- ✅ **Formats multiples** : Pour affichage et graphiques

**Graphique recommandé** : Graphique en barres empilées ou lignes multiples

---

## 4️⃣ Derniers Personnels (derniers_personnels)

### 10 derniers personnels créés

```json
{
    "derniers_personnels": [
        {
            "id": 45,
            "nom_complet": "Koné Ibrahim",
            "email": "ibrahim.kone@example.com",
            "role": "commercial",
            "role_label": "Commercial",
            "sexe": "M",
            "est_actif": true,
            "date_creation": "2025-10-06 15:30:00",
            "date_creation_formatee": "06/10/2025 à 15:30",
            "anciennete_jours": 1
        }
        // ... 9 autres
    ]
}
```

### Informations par personnel
- ✅ ID et nom complet
- ✅ Email
- ✅ Rôle (code et libellé)
- ✅ Sexe
- ✅ Statut actif/inactif
- ✅ Date de création (2 formats)
- ✅ Ancienneté en jours

**Utilisation UI** : Liste déroulante ou timeline avec badges

---

## 5️⃣ Top 5 par Rôle (top_par_role)

### Top 5 des personnels les plus anciens par rôle

```json
{
    "top_par_role": {
        "commercial": {
            "role_label": "Commercial",
            "personnels": [
                {
                    "position": 1,
                    "id": 12,
                    "nom_complet": "Traoré Seydou",
                    "email": "seydou.traore@example.com",
                    "sexe": "M",
                    "date_creation": "2024-03-15 10:00:00",
                    "date_creation_formatee": "15/03/2024 à 10:00",
                    "anciennete_jours": 205,
                    "anciennete_formatee": "il y a 6 mois"
                }
                // ... 4 autres
            ]
        },
        "technicien": {
            "role_label": "Technicien",
            "personnels": [ /* ... */ ]
        },
        "medecin_controleur": {
            "role_label": "Médecin Contrôleur",
            "personnels": [ /* ... */ ]
        },
        "comptable": {
            "role_label": "Comptable",
            "personnels": [ /* ... */ ]
        }
    }
}
```

### Critères
- **Classement** : Par ancienneté (les plus anciens en premier)
- **Limite** : Maximum 5 par rôle
- **Filtre** : Uniquement les personnels actifs

**Utilisation UI** : Tableaux séparés par rôle avec badges d'ancienneté

---

## 🎨 Suggestions d'Interface

### Dashboard Layout

```
┌─────────────────────────────────────────────────────────┐
│            STATISTIQUES DES PERSONNELS                   │
├─────────────────────────────────────────────────────────┤
│  [KPI]      [KPI]      [KPI]      [KPI]      [KPI]     │
│  Total      Actifs    Inactifs    Taux     Nouveaux     │
├──────────────────────────┬──────────────────────────────┤
│                          │                              │
│  Évolution Mensuelle     │  Répartition par Rôle        │
│  [Graphique Barres]      │  [Graphique Secteurs]        │
│                          │                              │
├──────────────────────────┼──────────────────────────────┤
│                          │                              │
│  Répartition par Sexe    │  Derniers Personnels         │
│  [Graphique Secteurs]    │  [Liste avec badges]         │
│                          │                              │
├──────────────────────────┴──────────────────────────────┤
│                                                          │
│  Top 5 par Rôle                                          │
│  [Tableaux séparés par rôle]                             │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Cartes KPI

```html
<div class="kpi-cards">
    <div class="kpi-card">
        <h3>Total Personnels</h3>
        <div class="value">45</div>
        <div class="badge">+5 ce mois</div>
    </div>
    
    <div class="kpi-card">
        <h3>Actifs</h3>
        <div class="value">38</div>
        <div class="percentage">84.44%</div>
    </div>
    
    <div class="kpi-card">
        <h3>Inactifs</h3>
        <div class="value">7</div>
        <div class="percentage">15.56%</div>
    </div>
</div>
```

---

## 📊 Exemples de Graphiques

### 1. Évolution Mensuelle (Chart.js)

```javascript
const evolutionData = {
    labels: data.evolution_mensuelle.map(m => m.mois_nom),
    datasets: [
        {
            label: 'Actifs',
            data: data.evolution_mensuelle.map(m => m.actifs),
            backgroundColor: 'rgba(75, 192, 192, 0.6)'
        },
        {
            label: 'Inactifs',
            data: data.evolution_mensuelle.map(m => m.inactifs),
            backgroundColor: 'rgba(255, 99, 132, 0.6)'
        }
    ]
};
```

### 2. Répartition par Rôle

```javascript
const roleData = {
    labels: Object.keys(data.repartitions.par_role).map(role => 
        data.repartitions.par_role[role].role_label || role
    ),
    datasets: [{
        data: Object.values(data.repartitions.par_role).map(r => r.count),
        backgroundColor: [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0'
        ]
    }]
};
```

### 3. Répartition par Sexe

```javascript
const sexeData = {
    labels: ['Masculin', 'Féminin'],
    datasets: [{
        data: [
            data.repartitions.par_sexe.M?.count || 0,
            data.repartitions.par_sexe.F?.count || 0
        ],
        backgroundColor: ['#36A2EB', '#FF6384']
    }]
};
```

---

## 🎯 Statistiques Importantes Incluses

### ✅ Vue d'Ensemble
1. **Total personnels** - Nombre global
2. **Actifs/Inactifs** - Répartition par statut
3. **Taux d'activation** - Pourcentage d'actifs
4. **Nouveaux ce mois** - Tendance d'embauche

### ✅ Répartitions
1. **Par rôle** - Distribution des types de personnel
2. **Par sexe** - Équilibre homme/femme
3. **Actifs/Inactifs par rôle** - Détail par type

### ✅ Évolution
1. **12 derniers mois** - Tendance d'embauche
2. **Total par mois** - Volume mensuel
3. **Par rôle par mois** - Détail des recrutements

### ✅ Activité Récente
1. **10 derniers créés** - Nouveaux arrivants
2. **Informations complètes** - Tous les détails
3. **Ancienneté** - Temps depuis création

### ✅ Top Performers
1. **Top 5 par rôle** - Les plus anciens
2. **Ancienneté détaillée** - Fidélité
3. **Uniquement actifs** - Personnel en poste

---

## 🔔 Alertes et Indicateurs

### Alertes à implémenter

1. **🔴 Taux d'activation faible** (< 70%)
   - Badge rouge sur KPI
   - Notification gestionnaire

2. **🟠 Beaucoup d'inactifs** (> 30%)
   - Alerte tendance
   - Suggestion de réactivation

3. **🟢 Bonne performance** (> 90% actifs)
   - Badge vert
   - Message de félicitation

4. **🔵 Nouveaux ce mois** (> 5)
   - Badge bleu
   - Indicateur de croissance

---

## 📱 Responsive Design

### Mobile
- Cartes KPI empilées verticalement
- Graphiques en pleine largeur
- Tableaux scrollables
- Liste des derniers en accordéon

### Tablet
- Cartes KPI en grille 2x2
- Graphiques côte à côte
- Tableaux adaptés

### Desktop
- Layout complet
- Tous les graphiques visibles
- Tableaux en pleine largeur

---

## 🔄 Rafraîchissement

### Recommandations
- **Automatique** : Toutes les 5 minutes
- **Manuel** : Bouton de rafraîchissement
- **Indicateur** : Dernière mise à jour
- **Loading** : Skeleton screens

---

## 🎯 Différences avec Admin

### Admin Global
- Vue sur TOUS les utilisateurs
- Gestionnaires, commerciaux, clients
- Statistiques globales de la plateforme

### Gestionnaire
- Vue sur SES personnels uniquement
- Commerciaux, techniciens, médecins, comptables
- Statistiques de son équipe

---

## ✅ Résumé

**Statistiques complètes pour le gestionnaire** :
1. ✅ Vue d'ensemble avec KPI
2. ✅ Répartitions par rôle et sexe
3. ✅ Évolution mensuelle (12 mois)
4. ✅ 10 derniers personnels créés
5. ✅ Top 5 par rôle (les plus anciens)

**Format optimisé** :
- Données prêtes pour graphiques
- Pourcentages calculés
- Formats multiples pour dates
- Structure claire et organisée

Le système est prêt pour l'intégration frontend ! 🚀
