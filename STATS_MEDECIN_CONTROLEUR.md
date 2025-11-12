# Documentation - Statistiques Médecin Contrôleur 🩺

## 📊 Vue d'ensemble

Le médecin contrôleur dispose d'un endpoint unique qui regroupe toutes les statistiques importantes : questions, garanties, catégories de garanties, demandes prestataires et factures.

## 🔗 Endpoint

```
GET /v1/medecin-controleur/stats
```

**Authentification** : Requise (Token JWT)  
**Rôle requis** : `medecin_controleur`

---

## 📋 Structure de la Réponse Complète

```json
{
    "success": true,
    "message": "Statistiques du médecin contrôleur récupérées avec succès",
    "data": {
        "questions": { /* ... */ },
        "garanties": { /* ... */ },
        "categories_garanties": { /* ... */ },
        "demandes_prestataires": { /* ... */ },
        "factures": { /* ... */ }
    }
}
```

---

## 1️⃣ Statistiques des Questions

### Données retournées

```json
{
    "questions": {
        "total": 14,
        "actives": 14,
        "inactives": 0,
        "obligatoires": 9,
        "optionnelles": 5,
        "taux_activation": 100.00,
        
        "repartition_par_destinataire": {
            "prestataire": {
                "count": 14,
                "pourcentage": 100.00,
                "actives": 14,
                "inactives": 0
            },
            "client": {
                "count": 0,
                "pourcentage": 0,
                "actives": 0,
                "inactives": 0
            }
        },
        
        "repartition_par_type_donnee": {
            "text": {
                "count": 3,
                "pourcentage": 21.43
            },
            "number": {
                "count": 2,
                "pourcentage": 14.29
            },
            "select": {
                "count": 1,
                "pourcentage": 7.14
            },
            "radio": {
                "count": 4,
                "pourcentage": 28.57
            },
            "checkbox": {
                "count": 4,
                "pourcentage": 28.57
            }
        }
    }
}
```

### Métriques clés
- ✅ Total questions
- ✅ Actives / Inactives
- ✅ Obligatoires / Optionnelles
- ✅ Taux d'activation
- ✅ **Répartition par destinataire** (prestataire, client, autre)
- ✅ **Répartition par type de données** (text, select, checkbox, etc.)

### Graphiques suggérés
- **Secteurs** : Répartition par destinataire
- **Barres** : Répartition par type de données
- **Gauge** : Taux d'activation

---

## 2️⃣ Statistiques des Garanties

### Données retournées

```json
{
    "garanties": {
        "total": 25,
        "actives": 22,
        "inactives": 3,
        "taux_activation": 88.00,
        "montant_total_max": 5250000,
        "montant_moyen_max": 210000,
        
        "garantie_max": {
            "libelle": "Hospitalisation",
            "montant": 500000
        },
        
        "garantie_min": {
            "libelle": "Consultation générale",
            "montant": 50000
        }
    }
}
```

### Métriques clés
- ✅ Total garanties
- ✅ Actives / Inactives
- ✅ Taux d'activation
- ✅ **Plafond total** (somme de tous les plafonds)
- ✅ **Plafond moyen**
- ✅ **Prix standard total** (somme des prix standards)
- ✅ **Prix standard moyen**
- ✅ **Taux de couverture moyen**
- ✅ **Garantie avec plafond max**
- ✅ **Garantie avec plafond min**

### Graphiques suggérés
- **Gauge** : Taux d'activation
- **Barres** : Top garanties par montant
- **Compteurs** : Total, actives, inactives

---

## 3️⃣ Statistiques des Catégories de Garanties

### Données retournées

```json
{
    "categories_garanties": {
        "total": 8,
        "avec_garanties": 7,
        "sans_garanties": 1,
        "nombre_moyen_garanties": 3.12,
        
        "categorie_plus_fournie": {
            "nom": "Hospitalisation",
            "nombre_garanties": 8
        }
    }
}
```

### Métriques clés
- ✅ Total catégories
- ✅ Catégories avec/sans garanties
- ✅ **Nombre moyen de garanties par catégorie**
- ✅ **Catégorie la plus fournie**

### Graphiques suggérés
- **Barres** : Nombre de garanties par catégorie
- **Compteurs** : Total catégories

---

## 4️⃣ Statistiques des Demandes Prestataires

### Données retournées

```json
{
    "demandes_prestataires": {
        "total": 45,
        "en_attente": 12,
        "validees": 28,
        "rejetees": 5,
        "nouvelles_ce_mois": 8,
        
        "repartition_par_statut": {
            "en_attente": {
                "count": 12,
                "pourcentage": 26.67
            },
            "validee": {
                "count": 28,
                "pourcentage": 62.22
            },
            "rejetee": {
                "count": 5,
                "pourcentage": 11.11
            }
        }
    }
}
```

### Métriques clés
- ✅ Total demandes prestataires
- ✅ En attente / Validées / Rejetées
- ✅ **Nouvelles ce mois**
- ✅ **Répartition par statut avec pourcentages**

### Graphiques suggérés
- **Secteurs** : Répartition par statut
- **Compteurs** : En attente (alerte si > 10)
- **Badge** : Nouvelles ce mois

---

## 5️⃣ Statistiques des Factures

### Données retournées

```json
{
    "factures": {
        "total": 150,
        "a_valider_par_medecin": 15,
        "validees_par_medecin": 120,
        "en_attente_technicien": 15
    }
}
```

### Métriques clés
- ✅ Total factures
- ✅ **À valider par médecin** (priorité)
- ✅ Validées par médecin
- ✅ En attente technicien

### Graphiques suggérés
- **Compteurs** avec alertes
- **Badge rouge** si factures en attente > 10

---

## 🎨 Dashboard Suggéré

### Layout Recommandé

```
┌─────────────────────────────────────────────────────────┐
│         DASHBOARD MÉDECIN CONTRÔLEUR                     │
├─────────────────────────────────────────────────────────┤
│  [KPI]      [KPI]       [KPI]       [KPI]      [KPI]   │
│ Questions  Garanties  Catégories  Demandes  Factures    │
│    14         25          8          12        15       │
│  Actives   Actives    Total     En attente  À valider   │
├──────────────────────────┬──────────────────────────────┤
│                          │                              │
│  Questions par Type      │  Garanties par Montant       │
│  [Graphique Barres]      │  [Graphique Barres]          │
│                          │                              │
├──────────────────────────┼──────────────────────────────┤
│                          │                              │
│  Demandes par Statut     │  Questions par Destinataire  │
│  [Graphique Secteurs]    │  [Graphique Secteurs]        │
│                          │                              │
└──────────────────────────────────────────────────────────┘
```

### Cartes KPI

```html
<div class="kpi-cards">
    <!-- Questions -->
    <div class="kpi-card">
        <h3>Questions</h3>
        <div class="value">14</div>
        <div class="badge success">14 actives</div>
        <div class="subtitle">100% activation</div>
    </div>
    
    <!-- Garanties -->
    <div class="kpi-card">
        <h3>Garanties</h3>
        <div class="value">25</div>
        <div class="badge success">22 actives</div>
        <div class="subtitle">88% activation</div>
    </div>
    
    <!-- Catégories -->
    <div class="kpi-card">
        <h3>Catégories</h3>
        <div class="value">8</div>
        <div class="badge info">3.12 garanties/cat</div>
    </div>
    
    <!-- Demandes -->
    <div class="kpi-card alert">
        <h3>Demandes</h3>
        <div class="value">12</div>
        <div class="badge warning">En attente</div>
        <div class="subtitle">8 nouvelles ce mois</div>
    </div>
    
    <!-- Factures -->
    <div class="kpi-card alert">
        <h3>Factures</h3>
        <div class="value">15</div>
        <div class="badge danger">À valider</div>
        <div class="action">Voir →</div>
    </div>
</div>
```

---

## 📊 Exemples de Graphiques

### 1. Questions par Type de Données

```javascript
const typesDonneesData = {
    labels: Object.keys(data.questions.repartition_par_type_donnee),
    datasets: [{
        label: 'Questions par Type',
        data: Object.values(data.questions.repartition_par_type_donnee).map(t => t.count),
        backgroundColor: [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF'
        ]
    }]
};
```

### 2. Demandes par Statut

```javascript
const demandesData = {
    labels: ['En attente', 'Validées', 'Rejetées'],
    datasets: [{
        data: [
            data.demandes_prestataires.en_attente,
            data.demandes_prestataires.validees,
            data.demandes_prestataires.rejetees
        ],
        backgroundColor: ['#FFA500', '#4CAF50', '#F44336']
    }]
};
```

### 3. Garanties par Montant (Top 10)

```javascript
// Nécessite un appel séparé pour récupérer les détails
const garantiesData = {
    labels: topGaranties.map(g => g.libelle),
    datasets: [{
        label: 'Montant Maximum',
        data: topGaranties.map(g => g.montant_max),
        backgroundColor: 'rgba(54, 162, 235, 0.6)'
    }]
};
```

---

## 🔔 Alertes et Notifications

### Alertes à Implémenter

1. **🔴 Demandes en attente > 10**
   - Badge rouge sur KPI
   - Notification urgente
   - Action rapide "Valider"

2. **🟠 Factures à valider > 10**
   - Badge orange sur KPI
   - Notification importante
   - Lien direct vers factures

3. **🟡 Questions inactives > 20%**
   - Alerte qualité
   - Suggestion de révision

4. **🟢 Taux d'activation > 90%**
   - Badge vert
   - Message de félicitation

---

## 🎯 Actions Rapides

Boutons d'action à placer sur le dashboard :

```html
<div class="actions-rapides">
    <button onclick="navigateTo('/questions/create')">
        ➕ Créer Questions
    </button>
    <button onclick="navigateTo('/garanties/create')">
        ➕ Créer Garantie
    </button>
    <button onclick="navigateTo('/demandes-prestataires')">
        ✅ Valider Demandes (12)
    </button>
    <button onclick="navigateTo('/factures-a-valider')">
        💰 Valider Factures (15)
    </button>
</div>
```

---

## 📱 Responsive Design

### Mobile
- Cartes KPI empilées verticalement
- Graphiques en pleine largeur
- Actions en menu déroulant

### Tablet
- Cartes KPI en grille 2x3
- Graphiques côte à côte

### Desktop
- Layout complet avec 5 KPI en ligne
- Graphiques en grille 2x2

---

## 🔄 Rafraîchissement

### Recommandations
- **Automatique** : Toutes les 5 minutes
- **Manuel** : Bouton de rafraîchissement
- **En temps réel** : WebSocket pour demandes urgentes
- **Badge** : Compteur de nouvelles demandes

---

## 📊 Exemple de Réponse Complète

```json
{
    "success": true,
    "message": "Statistiques du médecin contrôleur récupérées avec succès",
    "data": {
        "questions": {
            "total": 14,
            "actives": 14,
            "inactives": 0,
            "obligatoires": 9,
            "optionnelles": 5,
            "taux_activation": 100.00,
            "repartition_par_destinataire": {
                "prestataire": {
                    "count": 14,
                    "pourcentage": 100.00,
                    "actives": 14,
                    "inactives": 0
                }
            },
            "repartition_par_type_donnee": {
                "text": {"count": 3, "pourcentage": 21.43},
                "number": {"count": 2, "pourcentage": 14.29},
                "select": {"count": 1, "pourcentage": 7.14},
                "radio": {"count": 4, "pourcentage": 28.57},
                "checkbox": {"count": 4, "pourcentage": 28.57}
            }
        },
        "garanties": {
            "total": 25,
            "actives": 22,
            "inactives": 3,
            "taux_activation": 88.00,
            "plafond_total": 5250000,
            "plafond_moyen": 210000,
            "prix_standard_total": 3500000,
            "prix_standard_moyen": 140000,
            "taux_couverture_moyen": 75.50,
            "garantie_plafond_max": {
                "libelle": "Hospitalisation",
                "plafond": 500000
            },
            "garantie_plafond_min": {
                "libelle": "Consultation générale",
                "plafond": 50000
            }
        },
        "categories_garanties": {
            "total": 8,
            "avec_garanties": 7,
            "sans_garanties": 1,
            "nombre_moyen_garanties": 3.12,
            "categorie_plus_fournie": {
                "nom": "Hospitalisation",
                "nombre_garanties": 8
            }
        },
        "demandes_prestataires": {
            "total": 45,
            "en_attente": 12,
            "validees": 28,
            "rejetees": 5,
            "nouvelles_ce_mois": 8,
            "repartition_par_statut": {
                "en_attente": {"count": 12, "pourcentage": 26.67},
                "validee": {"count": 28, "pourcentage": 62.22},
                "rejetee": {"count": 5, "pourcentage": 11.11}
            }
        },
        "factures": {
            "total": 150,
            "a_valider_par_medecin": 15,
            "validees_par_medecin": 120,
            "en_attente_technicien": 15
        }
    }
}
```

---

## 🎯 Utilisation dans le Frontend

### Composant Dashboard

```typescript
export class MedecinControleurDashboardComponent implements OnInit {
  stats: any;
  loading = false;

  ngOnInit() {
    this.loadStats();
  }

  loadStats() {
    this.loading = true;
    this.medecinService.getStats().subscribe({
      next: (response) => {
        this.stats = response.data;
        this.loading = false;
        
        // Vérifier les alertes
        this.checkAlerts();
      },
      error: (error) => {
        console.error('Erreur:', error);
        this.loading = false;
      }
    });
  }

  checkAlerts() {
    // Alerte demandes en attente
    if (this.stats.demandes_prestataires.en_attente > 10) {
      this.showAlert('danger', 'Attention : ' + 
        this.stats.demandes_prestataires.en_attente + 
        ' demandes prestataires en attente de validation');
    }
    
    // Alerte factures à valider
    if (this.stats.factures.a_valider_par_medecin > 10) {
      this.showAlert('warning', 
        this.stats.factures.a_valider_par_medecin + 
        ' factures en attente de validation médicale');
    }
  }
}
```

---

## 🔍 Métriques Importantes à Surveiller

### 🔴 Priorité Haute
1. **Demandes en attente** : À traiter rapidement
2. **Factures à valider** : Ne pas bloquer les remboursements

### 🟠 Priorité Moyenne
3. **Questions inactives** : Réviser régulièrement
4. **Garanties inactives** : Vérifier pertinence
5. **Catégories sans garanties** : Compléter

### 🟢 Suivi
6. **Taux d'activation** : Maintenir > 80%
7. **Nouvelles demandes** : Tendance mensuelle
8. **Montants garanties** : Vérifier cohérence

---

## ✅ Résumé

**Une seule méthode pour tout** :
- ✅ `GET /v1/medecin-controleur/stats`

**5 catégories de statistiques** :
1. ✅ Questions (total, répartitions, taux)
2. ✅ Garanties (total, montants, min/max)
3. ✅ Catégories (total, moyenne, plus fournie)
4. ✅ Demandes prestataires (statuts, nouvelles)
5. ✅ Factures (à valider, validées)

**Format optimisé** :
- Données prêtes pour graphiques
- Pourcentages calculés
- Métriques pertinentes
- Alertes intégrées

Le dashboard médecin contrôleur est maintenant complet ! 🚀
