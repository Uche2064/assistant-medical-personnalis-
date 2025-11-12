# Documentation - Statistiques Technicien 🔧

## 📊 Vue d'ensemble

Le technicien dispose de statistiques complètes sur les demandes d'adhésion, propositions de contrats, types de contrats, factures et clients.

## 🔗 Endpoints

```
GET /v1/technicien/dashboard    - Dashboard simplifié
GET /v1/technicien/stats        - Statistiques complètes avec graphiques
```

**Authentification** : Requise (Token JWT)  
**Rôle requis** : `technicien`

---

## 📋 Structure de la Réponse

### Endpoint : `/v1/technicien/stats`

```json
{
    "success": true,
    "message": "Statistiques du technicien récupérées avec succès",
    "data": {
        "demandes_adhesion": { /* ... */ },
        "propositions_contrats": { /* ... */ },
        "types_contrats": { /* ... */ },
        "factures": { /* ... */ },
        "clients": { /* ... */ },
        "evolutions_mensuelles": [ /* ... */ ]
    }
}
```

---

## 1️⃣ Demandes d'Adhésion

### Données retournées

```json
{
    "demandes_adhesion": {
        "total": 150,
        "en_attente": 25,
        "validees": 100,
        "rejetees": 25,
        "taux_validation": 66.67,
        "nouvelles_ce_mois": 12,
        
        "repartition_par_statut": {
            "en_attente": {
                "count": 25,
                "pourcentage": 16.67
            },
            "validee": {
                "count": 100,
                "pourcentage": 66.67
            },
            "rejetee": {
                "count": 25,
                "pourcentage": 16.67
            }
        },
        
        "repartition_par_type": {
            "client": {
                "count": 120,
                "pourcentage": 80.00
            },
            "prestataire": {
                "count": 20,
                "pourcentage": 13.33
            },
            "autre": {
                "count": 10,
                "pourcentage": 6.67
            }
        }
    }
}
```

### Métriques clés
- ✅ Total demandes
- ✅ En attente / Validées / Rejetées
- ✅ Taux de validation
- ✅ Nouvelles ce mois
- ✅ **Répartition par statut** (avec pourcentages)
- ✅ **Répartition par type de demandeur** (client, prestataire, autre)

---

## 2️⃣ Propositions de Contrats

### Données retournées

```json
{
    "propositions_contrats": {
        "total": 85,
        "proposees": 15,
        "acceptees": 60,
        "refusees": 8,
        "expirees": 2,
        "taux_acceptation": 70.59,
        
        "repartition_par_statut": {
            "proposee": {
                "count": 15,
                "pourcentage": 17.65
            },
            "acceptee": {
                "count": 60,
                "pourcentage": 70.59
            },
            "refusee": {
                "count": 8,
                "pourcentage": 9.41
            },
            "expiree": {
                "count": 2,
                "pourcentage": 2.35
            }
        }
    }
}
```

### Métriques clés
- ✅ Total propositions
- ✅ Proposées / Acceptées / Refusées / Expirées
- ✅ **Taux d'acceptation**
- ✅ Répartition par statut

---

## 3️⃣ Types de Contrats

### Données retournées

```json
{
    "types_contrats": {
        "total": 12,
        "actifs": 10,
        "inactifs": 2,
        "taux_activation": 83.33,
        "prime_moyenne": 125000,
        "prime_totale": 1500000
    }
}
```

### Métriques clés
- ✅ Total types de contrats créés par le technicien
- ✅ Actifs / Inactifs
- ✅ Taux d'activation
- ✅ **Prime moyenne**
- ✅ **Prime totale**

---

## 4️⃣ Factures

### Données retournées

```json
{
    "factures": {
        "total": 200,
        "validees_par_technicien": 150,
        "a_valider_par_technicien": 30,
        "en_attente_medecin": 20
    }
}
```

### Métriques clés
- ✅ Total factures
- ✅ Validées par le technicien
- ✅ **À valider par le technicien** (priorité)
- ✅ En attente de validation médecin

---

## 5️⃣ Clients

### Données retournées

```json
{
    "clients": {
        "total": 450,
        "actifs": 380,
        "inactifs": 70,
        "taux_activation": 84.44
    }
}
```

### Métriques clés
- ✅ Total clients
- ✅ Actifs / Inactifs
- ✅ Taux d'activation

---

## 6️⃣ Évolutions Mensuelles (12 mois)

### Données retournées

```json
{
    "evolutions_mensuelles": [
        {
            "mois": "2024-11",
            "mois_nom": "Nov 2024",
            "mois_complet": "November 2024",
            "demandes_recues": 15,
            "demandes_validees": 12,
            "demandes_rejetees": 2,
            "propositions_envoyees": 10,
            "propositions_acceptees": 8,
            "factures_validees": 25,
            "clients_crees": 12,
            "taux_validation": 80.00,
            "taux_rejet": 13.33
        }
        // ... 11 autres mois
    ]
}
```

### Métriques par mois
- ✅ Demandes reçues / validées / rejetées
- ✅ Propositions envoyées / acceptées
- ✅ Factures validées
- ✅ Clients créés
- ✅ **Taux de validation** (%)
- ✅ **Taux de rejet** (%)

---

## 🎨 Graphiques Suggérés

### 1. Évolution des Demandes (Barres empilées)
```javascript
const demandesData = {
    labels: data.evolutions_mensuelles.map(m => m.mois_nom),
    datasets: [
        {
            label: 'Validées',
            data: data.evolutions_mensuelles.map(m => m.demandes_validees),
            backgroundColor: 'rgba(75, 192, 192, 0.6)'
        },
        {
            label: 'Rejetées',
            data: data.evolutions_mensuelles.map(m => m.demandes_rejetees),
            backgroundColor: 'rgba(255, 99, 132, 0.6)'
        }
    ]
};
```

### 2. Taux de Validation Mensuel (Lignes)
```javascript
const tauxData = {
    labels: data.evolutions_mensuelles.map(m => m.mois_nom),
    datasets: [{
        label: 'Taux de Validation (%)',
        data: data.evolutions_mensuelles.map(m => m.taux_validation),
        borderColor: 'rgb(75, 192, 192)',
        fill: true,
        tension: 0.4
    }]
};
```

### 3. Propositions de Contrats (Secteurs)
```javascript
const propositionsData = {
    labels: ['Proposées', 'Acceptées', 'Refusées', 'Expirées'],
    datasets: [{
        data: [
            data.propositions_contrats.proposees,
            data.propositions_contrats.acceptees,
            data.propositions_contrats.refusees,
            data.propositions_contrats.expirees
        ],
        backgroundColor: ['#FFA500', '#4CAF50', '#F44336', '#9E9E9E']
    }]
};
```

### 4. Demandes par Type (Secteurs)
```javascript
const typesData = {
    labels: Object.keys(data.demandes_adhesion.repartition_par_type),
    datasets: [{
        data: Object.values(data.demandes_adhesion.repartition_par_type).map(t => t.count),
        backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56']
    }]
};
```

### 5. Activité Globale (Lignes multiples)
```javascript
const activiteData = {
    labels: data.evolutions_mensuelles.map(m => m.mois_nom),
    datasets: [
        {
            label: 'Demandes reçues',
            data: data.evolutions_mensuelles.map(m => m.demandes_recues),
            borderColor: 'rgb(54, 162, 235)',
            tension: 0.4
        },
        {
            label: 'Propositions envoyées',
            data: data.evolutions_mensuelles.map(m => m.propositions_envoyees),
            borderColor: 'rgb(255, 206, 86)',
            tension: 0.4
        },
        {
            label: 'Factures validées',
            data: data.evolutions_mensuelles.map(m => m.factures_validees),
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.4
        }
    ]
};
```

---

## 🎯 Dashboard Layout Suggéré

```
┌─────────────────────────────────────────────────────────┐
│              DASHBOARD TECHNICIEN                        │
├─────────────────────────────────────────────────────────┤
│  [KPI]      [KPI]       [KPI]       [KPI]      [KPI]   │
│ Demandes  Propositions  Contrats   Factures   Clients   │
│   25         15           12         30        450      │
│En attente  Proposées    Créés    À valider    Total     │
├──────────────────────────┬──────────────────────────────┤
│                          │                              │
│  Évolution Demandes      │  Propositions par Statut     │
│  [Barres empilées]       │  [Graphique Secteurs]        │
│                          │                              │
├──────────────────────────┼──────────────────────────────┤
│                          │                              │
│  Taux de Validation      │  Demandes par Type           │
│  [Ligne]                 │  [Graphique Secteurs]        │
│                          │                              │
├──────────────────────────┴──────────────────────────────┤
│                                                          │
│  Activité Mensuelle Globale                              │
│  [Lignes multiples]                                      │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

---

## 🔔 Alertes Suggérées

### Priorité Haute
1. **🔴 Demandes en attente > 20**
   - Badge rouge
   - Action rapide "Valider"

2. **🟠 Factures à valider > 25**
   - Badge orange
   - Lien direct

### Priorité Moyenne
3. **🟡 Propositions en attente > 10**
   - Alerte suivi
   - Relance clients

4. **🔵 Taux de rejet > 20%**
   - Analyse qualité
   - Amélioration processus

---

## 📊 Résumé des Graphiques

| # | Type | Données | Graphique |
|---|------|---------|-----------|
| 1 | Évolution demandes | 12 mois | Barres empilées |
| 2 | Taux validation | 12 mois | Ligne |
| 3 | Propositions statut | Tous | Secteurs |
| 4 | Demandes par type | Tous | Secteurs |
| 5 | Activité globale | 12 mois | Lignes multiples |
| 6 | Clients créés | 12 mois | Ligne |

---

## ✅ Résumé

**Endpoint unique** : `GET /v1/technicien/stats`

**6 catégories de statistiques** :
1. ✅ Demandes d'adhésion (total, répartitions, taux)
2. ✅ Propositions de contrats (statuts, taux acceptation)
3. ✅ Types de contrats (actifs, primes)
4. ✅ Factures (à valider, validées)
5. ✅ Clients (total, actifs)
6. ✅ Évolutions mensuelles (12 mois avec graphiques)

Le dashboard technicien est maintenant complet ! 🚀
