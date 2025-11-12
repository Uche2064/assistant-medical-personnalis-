# Documentation - Dashboard Admin Global

## 📊 Vue d'ensemble

Le dashboard admin global fournit une vue complète et centralisée de toutes les statistiques clés de la plateforme, incluant les gestionnaires, commerciaux et clients.

## 🔗 Endpoint

```
GET /v1/admin/dashboard-global
```

**Authentification** : Requise (Token JWT)  
**Rôle requis** : `admin_global`

---

## 📋 Structure de la Réponse

```json
{
    "success": true,
    "message": "Dashboard global récupéré avec succès",
    "data": {
        "vue_ensemble": { /* ... */ },
        "graphiques": { /* ... */ },
        "activites_recentes": { /* ... */ },
        "top_commerciaux": [ /* ... */ ]
    }
}
```

---

## 1️⃣ Vue d'Ensemble (vue_ensemble)

### Statistiques clés des différents types d'utilisateurs

```json
{
    "vue_ensemble": {
        "gestionnaires": {
            "total": 15,
            "actifs": 12,
            "inactifs": 3,
            "taux_activation": 80.00
        },
        "commerciaux": {
            "total": 25,
            "actifs": 22,
            "inactifs": 3,
            "taux_activation": 88.00,
            "codes_parrainage_actifs": 20
        },
        "clients": {
            "total": 450,
            "actifs": 380,
            "inactifs": 70,
            "taux_activation": 84.44
        },
        "total_utilisateurs": 490,
        "total_utilisateurs_actifs": 414
    }
}
```

### Utilisation pour l'UI

**Cartes KPI (Key Performance Indicators)** :
- Total gestionnaires avec badge actifs/inactifs
- Total commerciaux avec badge actifs/inactifs
- Total clients avec badge actifs/inactifs
- Total utilisateurs global

**Indicateurs visuels** :
- Jauges de taux d'activation par type
- Codes de parrainage actifs pour les commerciaux

---

## 2️⃣ Graphiques et Analyses (graphiques)

### 2.1 Évolution Mensuelle

Évolution des inscriptions sur les 12 derniers mois par type d'utilisateur.

```json
{
    "graphiques": {
        "evolution_mensuelle": [
            {
                "mois": "2024-11",
                "mois_nom": "Nov 2024",
                "mois_complet": "November 2024",
                "gestionnaires": 2,
                "commerciaux": 3,
                "clients": 35,
                "total": 40
            }
            // ... 11 autres mois
        ]
    }
}
```

**Graphique recommandé** : Graphique en barres empilées ou lignes multiples

```javascript
// Exemple Chart.js
const chartData = {
    labels: data.evolution_mensuelle.map(m => m.mois_nom),
    datasets: [
        {
            label: 'Gestionnaires',
            data: data.evolution_mensuelle.map(m => m.gestionnaires),
            backgroundColor: 'rgba(255, 99, 132, 0.6)'
        },
        {
            label: 'Commerciaux',
            data: data.evolution_mensuelle.map(m => m.commerciaux),
            backgroundColor: 'rgba(54, 162, 235, 0.6)'
        },
        {
            label: 'Clients',
            data: data.evolution_mensuelle.map(m => m.clients),
            backgroundColor: 'rgba(75, 192, 192, 0.6)'
        }
    ]
};
```

### 2.2 Répartition par Sexe des Gestionnaires

```json
{
    "repartition_sexe_gestionnaires": {
        "data": {
            "M": 8,
            "F": 7
        },
        "pourcentages": {
            "M": 53.33,
            "F": 46.67
        }
    }
}
```

**Graphique recommandé** : Graphique en secteurs (Pie Chart)

### 2.3 Répartition des Clients par Type

```json
{
    "repartition_clients_par_type": {
        "physiques": 320,
        "moraux": 130,
        "total": 450,
        "pourcentage_physiques": 71.11,
        "pourcentage_moraux": 28.89
    }
}
```

**Graphique recommandé** : Graphique en secteurs ou en barres

### 2.4 Taux d'Activation par Rôle

```json
{
    "taux_activation_par_role": {
        "gestionnaires": {
            "total": 15,
            "actifs": 12,
            "inactifs": 3,
            "taux": 80.00
        },
        "commerciaux": {
            "total": 25,
            "actifs": 22,
            "inactifs": 3,
            "taux": 88.00
        },
        "clients": {
            "total": 450,
            "actifs": 380,
            "inactifs": 70,
            "taux": 84.44
        }
    }
}
```

**Graphique recommandé** : Graphique en barres horizontales ou jauges

---

## 3️⃣ Activités Récentes (activites_recentes)

### 3.1 Derniers Gestionnaires Créés

```json
{
    "activites_recentes": {
        "derniers_gestionnaires": [
            {
                "id": 15,
                "nom_complet": "Kouassi Marie",
                "email": "marie.kouassi@example.com",
                "est_actif": true,
                "date_creation": "2025-10-06 15:30:00",
                "date_creation_formatee": "06/10/2025 à 15:30"
            }
            // ... 4 autres
        ]
    }
}
```

### 3.2 Derniers Commerciaux Créés

```json
{
    "derniers_commerciaux": [
        {
            "id": 25,
            "nom_complet": "Traoré Amadou",
            "email": "amadou.traore@example.com",
            "est_actif": true,
            "date_creation": "2025-10-05 14:20:00",
            "date_creation_formatee": "05/10/2025 à 14:20"
        }
        // ... 4 autres
    ]
}
```

### 3.3 Derniers Clients Créés

```json
{
    "derniers_clients": [
        {
            "id": 450,
            "nom_complet": "Diallo Fatoumata",
            "email": "fatoumata.diallo@example.com",
            "est_actif": true,
            "type_client": "physique",
            "date_creation": "2025-10-06 16:45:00",
            "date_creation_formatee": "06/10/2025 à 16:45"
        }
        // ... 4 autres
    ]
}
```

**Utilisation pour l'UI** : Liste déroulante ou timeline avec badges de statut

---

## 4️⃣ Top 5 Commerciaux (top_commerciaux)

Classement des 5 meilleurs commerciaux par nombre de clients parrainés.

## 5️⃣ Top 5 Gestionnaires (top_gestionnaires)

Les 5 gestionnaires les plus anciens et actifs.

## 6️⃣ Top 5 Clients (top_clients)

Les 5 meilleurs clients par nombre de contrats.

```json
{
    "top_commerciaux": [
        {
            "position": 1,
            "id": 12,
            "nom_complet": "Koné Ibrahim",
            "email": "ibrahim.kone@example.com",
            "total_clients": 45,
            "clients_actifs": 38,
            "clients_inactifs": 7,
            "taux_activation": 84.44,
            "code_parrainage_actuel": "COMABC123",
            "date_expiration_code": "2026-10-06"
        },
        {
            "position": 2,
            "id": 8,
            "nom_complet": "Yao Adjoua",
            "email": "adjoua.yao@example.com",
            "total_clients": 38,
            "clients_actifs": 35,
            "clients_inactifs": 3,
            "taux_activation": 92.11,
            "code_parrainage_actuel": "COMXYZ789",
            "date_expiration_code": "2026-09-15"
        }
        // ... 3 autres
    ]
}
```

**Utilisation pour l'UI** : 
- Tableau avec classement
- Cartes de performance
- Badges pour les positions (🥇🥈🥉)

### Top 5 Gestionnaires

```json
{
    "top_gestionnaires": [
        {
            "position": 1,
            "id": 1,
            "nom_complet": "Admin Principal",
            "email": "admin@example.com",
            "sexe": "M",
            "est_actif": true,
            "date_creation": "2024-01-15 10:00:00",
            "date_creation_formatee": "15/01/2024 à 10:00",
            "anciennete_jours": 265,
            "anciennete_formatee": "il y a 8 mois"
        }
        // ... 4 autres
    ]
}
```

**Critère de classement** : Ancienneté (les plus anciens en premier)  
**Utilisation pour l'UI** : Tableau avec badge d'ancienneté

### Top 5 Clients

```json
{
    "top_clients": [
        {
            "position": 1,
            "id": 120,
            "nom_complet": "Entreprise SUNU SA",
            "email": "contact@sunu.com",
            "type_client": "moral",
            "est_actif": true,
            "nombre_contrats": 15,
            "commercial": {
                "id": 12,
                "nom_complet": "Koné Ibrahim",
                "email": "ibrahim.kone@example.com"
            },
            "code_parrainage": "COMABC123",
            "date_creation": "2024-03-10 14:30:00",
            "date_creation_formatee": "10/03/2024 à 14:30",
            "anciennete_jours": 210
        }
        // ... 4 autres
    ]
}
```

**Critère de classement** : Nombre de contrats (du plus grand au plus petit)  
**Utilisation pour l'UI** : Tableau avec badge du nombre de contrats et lien vers le commercial

---

## 🎨 Exemples d'Intégration UI

### Dashboard Layout Recommandé

```
┌─────────────────────────────────────────────────────────┐
│                   DASHBOARD ADMIN                        │
├─────────────────────────────────────────────────────────┤
│  [KPI Card]    [KPI Card]    [KPI Card]    [KPI Card]  │
│ Gestionnaires  Commerciaux    Clients    Total Users    │
├──────────────────────────┬──────────────────────────────┤
│                          │                              │
│  Évolution Mensuelle     │  Répartition Clients         │
│  [Graphique Barres]      │  [Graphique Secteurs]        │
│                          │                              │
├──────────────────────────┼──────────────────────────────┤
│                          │                              │
│  Taux d'Activation       │  Répartition Sexe            │
│  [Graphique Barres]      │  [Graphique Secteurs]        │
│                          │                              │
├──────────────────────────┴──────────────────────────────┤
│                                                          │
│  Top 5 Commerciaux                                       │
│  [Tableau avec classement]                               │
│                                                          │
├──────────────────────────────────────────────────────────┤
│  Activités Récentes                                      │
│  [Derniers Gestionnaires] [Derniers Commerciaux]         │
│  [Derniers Clients]                                      │
└──────────────────────────────────────────────────────────┘
```

### Actions Rapides

Boutons d'action à placer en haut du dashboard :

```html
<div class="actions-rapides">
    <button onclick="navigateTo('/admin/gestionnaires/create')">
        ➕ Créer un Gestionnaire
    </button>
    <button onclick="navigateTo('/admin/gestionnaires')">
        👥 Voir tous les Gestionnaires
    </button>
    <button onclick="navigateTo('/admin/stats')">
        📊 Statistiques Détaillées
    </button>
</div>
```

---

## 🔄 Rafraîchissement des Données

**Recommandations** :
- Rafraîchissement automatique toutes les 5 minutes
- Bouton de rafraîchissement manuel
- Indicateur de dernière mise à jour

```javascript
// Exemple de rafraîchissement automatique
setInterval(() => {
    fetchDashboardData();
}, 300000); // 5 minutes
```

---

## 📱 Responsive Design

### Mobile
- Cartes KPI empilées verticalement
- Graphiques adaptés à la largeur de l'écran
- Tableaux scrollables horizontalement

### Tablet
- Cartes KPI en grille 2x2
- Graphiques côte à côte

### Desktop
- Layout complet comme montré ci-dessus
- Graphiques en pleine largeur

---

## 🎯 Métriques Clés à Surveiller

### Alertes à implémenter :

1. **Taux d'activation faible** (< 70%) → Badge rouge
2. **Codes de parrainage expirant bientôt** (< 30 jours) → Badge orange
3. **Augmentation anormale d'inactifs** → Notification
4. **Baisse d'inscriptions** → Alerte tendance

---

## 🔐 Sécurité

- Endpoint protégé par authentification JWT
- Accès réservé au rôle `admin_global`
- Logs des accès au dashboard
- Pas de données sensibles exposées (mots de passe, etc.)

---

## ⚡ Performance

- Requêtes optimisées avec `clone()` pour éviter les conflits
- Utilisation de `withCount()` pour les agrégations
- Mise en cache recommandée (5 minutes)
- Pagination pour les listes longues

---

## 📝 Notes Techniques

- Toutes les dates sont formatées en `Y-m-d H:i:s` et `d/m/Y à H:i`
- Les pourcentages sont arrondis à 2 décimales
- Les données sont triées par pertinence (récents en premier, top en premier)
- Gestion des cas null avec `optional()`
