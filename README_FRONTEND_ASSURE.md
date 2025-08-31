# 📋 Documentation Frontend - Gestion des Assurés

## 🎯 Vue d'ensemble

Ce document décrit les routes et fonctionnalités disponibles pour afficher et gérer les assurés dans l'application frontend.

---

## 🔐 Authentification

Toutes les routes nécessitent une authentification via le token Bearer dans le header :

```javascript
headers: {
  'Authorization': 'Bearer ' + token,
  'Content-Type': 'application/json'
}
```

---

## 📊 Routes pour récupérer les assurés

### 1. **Récupérer tous les assurés (Personnel)**

**Route :** `GET /api/v1/personnel/assures`

**Permissions :** 
- ✅ Technicien
- ✅ Médecin contrôleur  
- ✅ Comptable
- ✅ Admin global
- ✅ Gestionnaire (limité à son entreprise)

**Paramètres de requête :**
```javascript
{
  search?: string,        // Recherche par nom, prénoms, email
  sexe?: 'M' | 'F',       // Filtre par sexe
  est_principal?: boolean, // Filtre assurés principaux vs bénéficiaires
  entreprise_id?: number, // Filtre par entreprise spécifique
  per_page?: number       // Nombre d'éléments par page (défaut: 10)
}
```

**Exemple d'utilisation :**
```javascript
// Récupérer tous les assurés
const response = await fetch('/api/v1/personnel/assures', {
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  }
});

// Avec filtres
const response = await fetch('/api/v1/personnel/assures?search=DOE&sexe=M&per_page=20', {
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  }
});
```

**Réponse :**
```json
{
  "status": true,
  "message": "Liste des assurés récupérée avec succès",
  "data": {
    "data": [
      {
        "id": 1,
        "nom": "DOE",
        "prenoms": "John",
        "date_naissance": "1990-01-01",
        "email": "john@example.com",
        "contact": "+1234567890",
        "sexe": "M",
        "profession": "Ingénieur",
        "est_principal": true,
        "entreprise": null,
        "assure_principal": null,
        "photo": "path/to/photo.jpg",
        "lien_parente": null,
        "contrat": {
          "id": 1,
          "numero_police": "POL001",
          "date_debut": "2024-01-01",
          "date_fin": "2024-12-31",
          "statut": "actif",
          "garanties": [
            {
              "id": 1,
              "libelle": "Hospitalisation",
              "prix_standard": 50000,
              "taux_couverture": 80
            }
          ]
        }
      }
    ],
    "current_page": 1,
    "per_page": 10,
    "total": 25,
    "last_page": 3
  }
}
```

---

### 2. **Récupérer les assurés d'un prestataire**

**Route :** `GET /api/v1/prestataire/assures`

**Permissions :** Prestataire uniquement

**Paramètres de requête :**
```javascript
{
  search?: string,    // Recherche par nom, prénoms
  sexe?: 'M' | 'F',   // Filtre par sexe
  per_page?: number   // Nombre d'éléments par page (défaut: 10)
}
```

**Exemple d'utilisation :**
```javascript
const response = await fetch('/api/v1/prestataire/assures?search=DOE', {
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  }
});
```

---

## 🎨 Interface utilisateur recommandée

### **Tableau des assurés**

```html
<div class="assures-table">
  <!-- Filtres -->
  <div class="filters">
    <input type="text" placeholder="Rechercher..." v-model="search" />
    <select v-model="sexe">
      <option value="">Tous les sexes</option>
      <option value="M">Masculin</option>
      <option value="F">Féminin</option>
    </select>
    <select v-model="est_principal">
      <option value="">Tous</option>
      <option value="true">Assurés principaux</option>
      <option value="false">Bénéficiaires</option>
    </select>
  </div>

  <!-- Tableau -->
  <table>
    <thead>
      <tr>
        <th>Photo</th>
        <th>Nom complet</th>
        <th>Type</th>
        <th>Contact</th>
        <th>Contrat</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="assure in assures" :key="assure.id">
        <td>
          <img :src="assure.photo" :alt="assure.nom" class="avatar" />
        </td>
        <td>{{ assure.nom }} {{ assure.prenoms }}</td>
        <td>
          <span :class="assure.est_principal ? 'badge-primary' : 'badge-secondary'">
            {{ assure.est_principal ? 'Principal' : 'Bénéficiaire' }}
          </span>
        </td>
        <td>{{ assure.contact }}</td>
        <td>
          <span v-if="assure.contrat" class="badge-success">
            {{ assure.contrat.numero_police }}
          </span>
          <span v-else class="badge-warning">Aucun contrat</span>
        </td>
        <td>
          <button @click="viewDetails(assure.id)">Voir détails</button>
        </td>
      </tr>
    </tbody>
  </table>

  <!-- Pagination -->
  <div class="pagination">
    <button @click="previousPage" :disabled="currentPage === 1">Précédent</button>
    <span>Page {{ currentPage }} sur {{ lastPage }}</span>
    <button @click="nextPage" :disabled="currentPage === lastPage">Suivant</button>
  </div>
</div>
```

### **Vue détaillée d'un assuré**

```html
<div class="assure-details" v-if="selectedAssure">
  <div class="header">
    <img :src="selectedAssure.photo" :alt="selectedAssure.nom" />
    <div>
      <h2>{{ selectedAssure.nom }} {{ selectedAssure.prenoms }}</h2>
      <p>{{ selectedAssure.profession }}</p>
    </div>
  </div>

  <div class="info-grid">
    <div class="info-card">
      <h3>Informations personnelles</h3>
      <p><strong>Date de naissance :</strong> {{ selectedAssure.date_naissance }}</p>
      <p><strong>Sexe :</strong> {{ selectedAssure.sexe }}</p>
      <p><strong>Contact :</strong> {{ selectedAssure.contact }}</p>
      <p><strong>Email :</strong> {{ selectedAssure.email }}</p>
    </div>

    <div class="info-card" v-if="selectedAssure.contrat">
      <h3>Contrat</h3>
      <p><strong>Numéro :</strong> {{ selectedAssure.contrat.numero_police }}</p>
      <p><strong>Statut :</strong> {{ selectedAssure.contrat.statut }}</p>
      <p><strong>Date début :</strong> {{ selectedAssure.contrat.date_debut }}</p>
      <p><strong>Date fin :</strong> {{ selectedAssure.contrat.date_fin }}</p>
    </div>

    <div class="info-card" v-if="selectedAssure.entreprise">
      <h3>Entreprise</h3>
      <p><strong>Raison sociale :</strong> {{ selectedAssure.entreprise.raison_sociale }}</p>
      <p><strong>Contact :</strong> {{ selectedAssure.entreprise.contact }}</p>
    </div>
  </div>
</div>
```

---

## 🔧 Gestion des états

### **Variables réactives (Vue.js)**

```javascript
export default {
  data() {
    return {
      assures: [],
      selectedAssure: null,
      loading: false,
      error: null,
      
      // Filtres
      search: '',
      sexe: '',
      est_principal: '',
      
      // Pagination
      currentPage: 1,
      lastPage: 1,
      perPage: 10,
      total: 0
    }
  },
  
  methods: {
    async fetchAssures() {
      this.loading = true;
      try {
        const params = new URLSearchParams({
          page: this.currentPage,
          per_page: this.perPage
        });
        
        if (this.search) params.append('search', this.search);
        if (this.sexe) params.append('sexe', this.sexe);
        if (this.est_principal) params.append('est_principal', this.est_principal);
        
        const response = await fetch(`/api/v1/personnel/assures?${params}`, {
          headers: {
            'Authorization': 'Bearer ' + this.token,
            'Content-Type': 'application/json'
          }
        });
        
        const data = await response.json();
        
        if (data.status) {
          this.assures = data.data.data;
          this.currentPage = data.data.current_page;
          this.lastPage = data.data.last_page;
          this.total = data.data.total;
        } else {
          this.error = data.message;
        }
      } catch (error) {
        this.error = 'Erreur lors de la récupération des assurés';
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    
    async viewDetails(assureId) {
      // Logique pour afficher les détails d'un assuré
      this.selectedAssure = this.assures.find(a => a.id === assureId);
    },
    
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
        this.fetchAssures();
      }
    },
    
    nextPage() {
      if (this.currentPage < this.lastPage) {
        this.currentPage++;
        this.fetchAssures();
      }
    }
  },
  
  mounted() {
    this.fetchAssures();
  },
  
  watch: {
    search() {
      this.currentPage = 1;
      this.fetchAssures();
    },
    sexe() {
      this.currentPage = 1;
      this.fetchAssures();
    },
    est_principal() {
      this.currentPage = 1;
      this.fetchAssures();
    }
  }
}
```

---

## 🎨 Styles CSS recommandés

```css
.assures-table {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  padding: 20px;
}

.filters {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.filters input,
.filters select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  background: #f8f9fa;
  font-weight: 600;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.badge-primary {
  background: #007bff;
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
}

.badge-secondary {
  background: #6c757d;
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
}

.badge-success {
  background: #28a745;
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
}

.badge-warning {
  background: #ffc107;
  color: #212529;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 15px;
  margin-top: 20px;
}

.pagination button {
  padding: 8px 16px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 4px;
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.assure-details {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  padding: 20px;
  margin-top: 20px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.info-card {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 6px;
  border-left: 4px solid #007bff;
}

.info-card h3 {
  margin: 0 0 15px 0;
  color: #007bff;
}
```

---

## 🚨 Gestion des erreurs

```javascript
// Intercepteur pour gérer les erreurs d'authentification
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Rediriger vers la page de connexion
      router.push('/login');
    } else if (error.response?.status === 403) {
      // Afficher un message d'erreur d'autorisation
      showNotification('Vous n\'avez pas les permissions nécessaires', 'error');
    }
    return Promise.reject(error);
  }
);
```

---

## 📱 Responsive Design

```css
@media (max-width: 768px) {
  .filters {
    flex-direction: column;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  table {
    font-size: 14px;
  }
  
  th, td {
    padding: 8px;
  }
}
```

---

## ✅ Checklist d'implémentation

- [ ] Authentification avec token Bearer
- [ ] Gestion des permissions selon le rôle
- [ ] Filtres de recherche et de tri
- [ ] Pagination
- [ ] Affichage des détails d'un assuré
- [ ] Gestion des erreurs
- [ ] Design responsive
- [ ] Loading states
- [ ] Messages d'erreur utilisateur

---

## 🔗 Liens utiles

- **Documentation API complète :** `/api/documentation`
- **Test des routes :** Postman Collection disponible
- **Support :** Contactez l'équipe backend pour toute question

---

*Dernière mise à jour : Août 2025*

