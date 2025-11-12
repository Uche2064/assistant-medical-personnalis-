# Guide des Graphiques - Médecin Contrôleur 📊

## 🎨 Vue d'ensemble

Les statistiques du médecin contrôleur incluent maintenant des données optimisées pour l'affichage de graphiques dynamiques et interactifs.

---

## 📈 1. Évolution Mensuelle des Demandes Prestataires (12 derniers mois)

### Données disponibles

```json
{
    "evolutions_mensuelles": [
        {
            "mois": "2024-11",
            "mois_nom": "Nov 2024",
            "mois_complet": "November 2024",
            "demandes_recues": 5,
            "demandes_en_attente": 1,
            "demandes_validees": 4,
            "demandes_rejetees": 1,
            "factures_validees": 12,
            "taux_validation": 80.00,
            "taux_rejet": 20.00
        }
        // ... 11 autres mois
    ]
}
```

### Graphique 1 : Évolution des Demandes Prestataires par Statut

**Type** : Graphique en barres empilées

```javascript
const demandesEvolutionData = {
    labels: data.evolutions_mensuelles.map(m => m.mois_nom),
    datasets: [
        {
            label: 'Validées',
            data: data.evolutions_mensuelles.map(m => m.demandes_validees),
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        },
        {
            label: 'En attente',
            data: data.evolutions_mensuelles.map(m => m.demandes_en_attente),
            backgroundColor: 'rgba(255, 206, 86, 0.6)',
            borderColor: 'rgba(255, 206, 86, 1)',
            borderWidth: 1
        },
        {
            label: 'Rejetées',
            data: data.evolutions_mensuelles.map(m => m.demandes_rejetees),
            backgroundColor: 'rgba(255, 99, 132, 0.6)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }
    ]
};

const config = {
    type: 'bar',
    data: demandesEvolutionData,
    options: {
        responsive: true,
        scales: {
            x: { stacked: true },
            y: { stacked: true, beginAtZero: true }
        },
        plugins: {
            title: {
                display: true,
                text: 'Évolution des Demandes Prestataires (12 mois)'
            },
            legend: { display: true }
        }
    }
};
```

### Graphique 2 : Taux de Validation Mensuel

**Type** : Graphique en lignes avec aires

```javascript
const tauxValidationData = {
    labels: data.evolutions_mensuelles.map(m => m.mois_nom),
    datasets: [
        {
            label: 'Taux de Validation (%)',
            data: data.evolutions_mensuelles.map(m => m.taux_validation),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true,
            tension: 0.4
        },
        {
            label: 'Taux de Rejet (%)',
            data: data.evolutions_mensuelles.map(m => m.taux_rejet),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            fill: true,
            tension: 0.4
        }
    ]
};

const config = {
    type: 'line',
    data: tauxValidationData,
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Évolution des Taux de Validation/Rejet'
            },
            legend: { display: true }
        },
        scales: {
            y: { 
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        }
    }
};
```

### Graphique 3 : Volume des Demandes Mensuelles

**Type** : Graphique en lignes

```javascript
const volumeDemandesData = {
    labels: data.evolutions_mensuelles.map(m => m.mois_nom),
    datasets: [{
        label: 'Demandes Reçues',
        data: data.evolutions_mensuelles.map(m => m.demandes_recues),
        borderColor: 'rgb(54, 162, 235)',
        backgroundColor: 'rgba(54, 162, 235, 0.1)',
        fill: true,
        tension: 0.4
    }]
};

const config = {
    type: 'line',
    data: volumeDemandesData,
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Volume Mensuel des Demandes Prestataires'
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
};
```

---

## 📊 2. Top 10 Garanties par Plafond

### Données disponibles

```json
{
    "top_garanties": [
        {
            "position": 1,
            "id": 5,
            "libelle": "Hospitalisation",
            "plafond": 500000,
            "prix_standard": 400000,
            "taux_couverture": 80.00,
            "montant_couverture": 320000,
            "est_active": true
        }
        // ... 9 autres
    ]
}
```

### Graphique : Top Garanties par Plafond

**Type** : Graphique en barres horizontales

```javascript
const topGarantiesData = {
    labels: data.top_garanties.map(g => g.libelle),
    datasets: [{
        label: 'Plafond (FCFA)',
        data: data.top_garanties.map(g => g.plafond),
        backgroundColor: [
            'rgba(255, 99, 132, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(199, 199, 199, 0.8)',
            'rgba(83, 102, 255, 0.8)',
            'rgba(255, 99, 255, 0.8)',
            'rgba(99, 255, 132, 0.8)'
        ],
        borderColor: 'rgba(0, 0, 0, 0.1)',
        borderWidth: 1
    }]
};

const config = {
    type: 'bar',
    data: topGarantiesData,
    options: {
        indexAxis: 'y', // Barres horizontales
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Top 10 Garanties par Montant'
            },
            legend: { display: false }
        },
        scales: {
            x: { 
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString() + ' FCFA';
                    }
                }
            }
        }
    }
};
```

---

## 📦 3. Garanties par Catégorie

### Données disponibles

```json
{
    "garanties_par_categorie": [
        {
            "id": 1,
            "nom": "Hospitalisation",
            "nombre_garanties": 8,
            "garanties_actives": 7,
            "garanties_inactives": 1,
            "montant_total_max": 2500000,
            "montant_moyen_max": 312500
        }
        // ... autres catégories
    ]
}
```

### Graphique 1 : Nombre de Garanties par Catégorie

**Type** : Graphique en barres

```javascript
const categoriesData = {
    labels: data.garanties_par_categorie.map(c => c.nom),
    datasets: [{
        label: 'Nombre de Garanties',
        data: data.garanties_par_categorie.map(c => c.nombre_garanties),
        backgroundColor: 'rgba(54, 162, 235, 0.6)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
    }]
};

const config = {
    type: 'bar',
    data: categoriesData,
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Nombre de Garanties par Catégorie'
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
};
```

### Graphique 2 : Montant Total par Catégorie

**Type** : Graphique en secteurs

```javascript
const montantsParCategorieData = {
    labels: data.garanties_par_categorie.map(c => c.nom),
    datasets: [{
        data: data.garanties_par_categorie.map(c => c.montant_total_max),
        backgroundColor: [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40',
            '#FF6384',
            '#C9CBCF'
        ]
    }]
};

const config = {
    type: 'pie',
    data: montantsParCategorieData,
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Répartition des Montants par Catégorie'
            },
            legend: { 
                position: 'right',
                labels: {
                    generateLabels: function(chart) {
                        const data = chart.data;
                        return data.labels.map((label, i) => ({
                            text: label + ' (' + 
                                  data.datasets[0].data[i].toLocaleString() + ' FCFA)',
                            fillStyle: data.datasets[0].backgroundColor[i]
                        }));
                    }
                }
            }
        }
    }
};
```

---

## 🎯 4. Questions par Type de Données

### Graphique : Répartition des Questions

**Type** : Graphique en secteurs

```javascript
const questionsTypesData = {
    labels: Object.keys(data.questions.repartition_par_type_donnee).map(type => {
        const labels = {
            'text': 'Texte',
            'number': 'Nombre',
            'select': 'Sélection',
            'checkbox': 'Cases à cocher',
            'radio': 'Boutons radio',
            'date': 'Date',
            'file': 'Fichier'
        };
        return labels[type] || type;
    }),
    datasets: [{
        data: Object.values(data.questions.repartition_par_type_donnee).map(t => t.count),
        backgroundColor: [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40',
            '#C9CBCF'
        ]
    }]
};

const config = {
    type: 'doughnut',
    data: questionsTypesData,
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Répartition des Questions par Type'
            },
            legend: { position: 'bottom' }
        }
    }
};
```

---

## 📊 5. Demandes Prestataires par Statut

### Graphique : Statuts des Demandes

**Type** : Graphique en secteurs avec légende

```javascript
const demandesStatutsData = {
    labels: ['En attente', 'Validées', 'Rejetées'],
    datasets: [{
        data: [
            data.demandes_prestataires.en_attente,
            data.demandes_prestataires.validees,
            data.demandes_prestataires.rejetees
        ],
        backgroundColor: [
            'rgba(255, 165, 0, 0.8)',  // Orange pour en attente
            'rgba(76, 175, 80, 0.8)',  // Vert pour validées
            'rgba(244, 67, 54, 0.8)'   // Rouge pour rejetées
        ]
    }]
};

const config = {
    type: 'pie',
    data: demandesStatutsData,
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Répartition des Demandes Prestataires'
            },
            legend: { 
                position: 'bottom',
                labels: {
                    generateLabels: function(chart) {
                        const data = chart.data;
                        const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                        return data.labels.map((label, i) => {
                            const value = data.datasets[0].data[i];
                            const percentage = ((value / total) * 100).toFixed(2);
                            return {
                                text: label + ' (' + value + ' - ' + percentage + '%)',
                                fillStyle: data.datasets[0].backgroundColor[i]
                            };
                        });
                    }
                }
            }
        }
    }
};
```

---

## 🎨 6. Dashboard Complet avec Chart.js

### Exemple de Composant Angular

```typescript
import { Component, OnInit } from '@angular/core';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

@Component({
  selector: 'app-medecin-dashboard',
  templateUrl: './medecin-dashboard.component.html'
})
export class MedecinDashboardComponent implements OnInit {
  stats: any;
  charts: any = {};

  ngOnInit() {
    this.loadStats();
  }

  loadStats() {
    this.medecinService.getStats().subscribe({
      next: (response) => {
        this.stats = response.data;
        this.createCharts();
      }
    });
  }

  createCharts() {
    // 1. Évolution des demandes prestataires
    this.charts.demandesEvolution = new Chart('demandesChart', {
      type: 'bar',
      data: {
        labels: this.stats.evolutions_mensuelles.map(m => m.mois_nom),
        datasets: [
          {
            label: 'Validées',
            data: this.stats.evolutions_mensuelles.map(m => m.demandes_validees),
            backgroundColor: 'rgba(75, 192, 192, 0.6)'
          },
          {
            label: 'En attente',
            data: this.stats.evolutions_mensuelles.map(m => m.demandes_en_attente),
            backgroundColor: 'rgba(255, 206, 86, 0.6)'
          },
          {
            label: 'Rejetées',
            data: this.stats.evolutions_mensuelles.map(m => m.demandes_rejetees),
            backgroundColor: 'rgba(255, 99, 132, 0.6)'
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          x: { stacked: true },
          y: { stacked: true, beginAtZero: true }
        },
        plugins: {
          title: {
            display: true,
            text: 'Évolution Mensuelle des Demandes Prestataires'
          }
        }
      }
    });

    // 2. Top garanties par plafond
    this.charts.topGaranties = new Chart('garantiesChart', {
      type: 'bar',
      data: {
        labels: this.stats.top_garanties.map(g => g.libelle),
        datasets: [{
          label: 'Plafond (FCFA)',
          data: this.stats.top_garanties.map(g => g.plafond),
          backgroundColor: 'rgba(54, 162, 235, 0.6)'
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: 'Top 10 Garanties par Plafond'
          }
        }
      }
    });

    // 3. Questions par type
    this.charts.questionsTypes = new Chart('questionsTypesChart', {
      type: 'doughnut',
      data: {
        labels: Object.keys(this.stats.questions.repartition_par_type_donnee),
        datasets: [{
          data: Object.values(this.stats.questions.repartition_par_type_donnee).map(t => t.count),
          backgroundColor: [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
          ]
        }]
      }
    });

    // 4. Garanties par catégorie
    this.charts.garantiesCategories = new Chart('categoriesChart', {
      type: 'bar',
      data: {
        labels: this.stats.garanties_par_categorie.map(c => c.nom),
        datasets: [{
          label: 'Nombre de Garanties',
          data: this.stats.garanties_par_categorie.map(c => c.nombre_garanties),
          backgroundColor: 'rgba(153, 102, 255, 0.6)'
        }]
      }
    });

    // 5. Demandes par statut
    this.charts.demandesStatuts = new Chart('demandesStatutsChart', {
      type: 'pie',
      data: {
        labels: ['En attente', 'Validées', 'Rejetées'],
        datasets: [{
          data: [
            this.stats.demandes_prestataires.en_attente,
            this.stats.demandes_prestataires.validees,
            this.stats.demandes_prestataires.rejetees
          ],
          backgroundColor: ['#FFA500', '#4CAF50', '#F44336']
        }]
      }
    });
  }
}
```

### Template HTML

```html
<div class="dashboard-medecin">
  <!-- KPI Cards -->
  <div class="kpi-row">
    <div class="kpi-card">
      <h3>Questions</h3>
      <div class="value">{{ stats?.questions?.total }}</div>
      <div class="badge success">{{ stats?.questions?.actives }} actives</div>
    </div>
    
    <div class="kpi-card">
      <h3>Garanties</h3>
      <div class="value">{{ stats?.garanties?.total }}</div>
      <div class="badge success">{{ stats?.garanties?.actives }} actives</div>
    </div>
    
    <div class="kpi-card alert" *ngIf="stats?.demandes_prestataires?.en_attente > 0">
      <h3>Demandes</h3>
      <div class="value">{{ stats?.demandes_prestataires?.en_attente }}</div>
      <div class="badge warning">En attente</div>
      <button (click)="goToValidation()">Valider →</button>
    </div>
    
    <div class="kpi-card alert" *ngIf="stats?.factures?.a_valider_par_medecin > 0">
      <h3>Factures</h3>
      <div class="value">{{ stats?.factures?.a_valider_par_medecin }}</div>
      <div class="badge danger">À valider</div>
      <button (click)="goToFactures()">Voir →</button>
    </div>
  </div>

  <!-- Graphiques -->
  <div class="charts-grid">
    <div class="chart-container">
      <canvas id="demandesChart"></canvas>
    </div>
    
    <div class="chart-container">
      <canvas id="garantiesChart"></canvas>
    </div>
    
    <div class="chart-container">
      <canvas id="questionsTypesChart"></canvas>
    </div>
    
    <div class="chart-container">
      <canvas id="categoriesChart"></canvas>
    </div>
    
    <div class="chart-container">
      <canvas id="demandesStatutsChart"></canvas>
    </div>
    
    <div class="chart-container full-width">
      <canvas id="activiteGlobaleChart"></canvas>
    </div>
  </div>
</div>
```

---

## 🎨 7. Styles CSS Recommandés

```css
.dashboard-medecin {
  padding: 20px;
}

.kpi-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.kpi-card {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  text-align: center;
}

.kpi-card.alert {
  border-left: 4px solid #ff9800;
}

.kpi-card .value {
  font-size: 2.5rem;
  font-weight: bold;
  color: #333;
  margin: 10px 0;
}

.kpi-card .badge {
  display: inline-block;
  padding: 5px 10px;
  border-radius: 12px;
  font-size: 0.85rem;
  margin-top: 10px;
}

.badge.success {
  background: #4caf50;
  color: white;
}

.badge.warning {
  background: #ff9800;
  color: white;
}

.badge.danger {
  background: #f44336;
  color: white;
}

.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 20px;
}

.chart-container {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.chart-container.full-width {
  grid-column: 1 / -1;
}

/* Responsive */
@media (max-width: 768px) {
  .charts-grid {
    grid-template-columns: 1fr;
  }
  
  .kpi-row {
    grid-template-columns: 1fr;
  }
}
```

---

## 📊 8. Graphiques Supplémentaires Suggérés

### Graphique de Jauge : Taux d'Activation

```javascript
// Utiliser Chart.js avec plugin gauge ou ApexCharts
const gaugeOptions = {
  series: [stats.questions.taux_activation],
  chart: {
    type: 'radialBar',
    height: 250
  },
  plotOptions: {
    radialBar: {
      hollow: {
        size: '70%'
      },
      dataLabels: {
        name: {
          show: true,
          text: 'Taux d\'activation'
        },
        value: {
          formatter: function(val) {
            return val + '%';
          }
        }
      }
    }
  },
  colors: ['#4CAF50']
};
```

### Graphique de Timeline : Activité

```javascript
// Timeline des validations
const timelineData = {
  labels: stats.evolutions_mensuelles.map(m => m.mois_nom),
  datasets: [{
    label: 'Factures Validées',
    data: stats.evolutions_mensuelles.map(m => m.factures_validees),
    borderColor: 'rgb(75, 192, 192)',
    backgroundColor: 'rgba(75, 192, 192, 0.2)',
    fill: true,
    tension: 0.4
  }]
};
```

---

## ✅ Résumé des Graphiques Disponibles

| # | Type | Données | Graphique Recommandé |
|---|------|---------|---------------------|
| 1 | Évolution demandes prestataires | 12 mois | Barres empilées |
| 2 | Taux validation/rejet | 12 mois | Lignes avec aires |
| 3 | Volume demandes mensuelles | 12 mois | Ligne simple |
| 4 | Top garanties par plafond | Top 10 | Barres horizontales |
| 5 | Garanties/catégorie | Toutes | Barres verticales |
| 6 | Plafonds/catégorie | Toutes | Secteurs |
| 7 | Questions/type | Tous types | Secteurs/Doughnut |
| 8 | Demandes/statut | 3 statuts | Secteurs |
| 9 | Taux activation | Pourcentage | Jauge |
| 10 | Factures validées | 12 mois | Ligne |

---

## 🚀 Prêt pour l'Intégration

Toutes les données sont maintenant **optimisées pour les graphiques** :
- ✅ Formats multiples (mois, mois_nom, mois_complet)
- ✅ Pourcentages calculés
- ✅ Top 10 pour les classements
- ✅ Évolutions sur 12 mois
- ✅ Répartitions détaillées

**Bibliothèques compatibles** :
- Chart.js ✅
- ApexCharts ✅
- D3.js ✅
- Highcharts ✅
- ECharts ✅

Le dashboard est maintenant prêt pour des visualisations riches ! 📊🚀
