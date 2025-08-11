# Guide Complet du Système de Réseautage de Prestataires - Frontend

## Vue d'Ensemble

Le système de réseautage permet d'assigner des prestataires de santé (centres de soins, pharmacies, laboratoires, optiques) aux clients qui ont accepté leurs contrats d'assurance. Ce guide détaille toutes les routes API et interfaces nécessaires pour implémenter cette fonctionnalité côté frontend.

## Acteurs du Système

### 1. **Technicien**
- **Rôle principal** : Gère l'assignation des prestataires aux clients
- **Permissions** : Peut voir tous les clients avec contrats acceptés et tous les prestataires validés
- **Actions** : Assigne/désassigne les prestataires, consulte les assignations existantes

### 2. **Client (Assuré Physique ou Entreprise)**
- **Rôle** : Consulte ses prestataires assignés
- **Permissions** : Peut voir uniquement ses propres prestataires
- **Actions** : Consulte la liste, filtre par type, voit les détails

### 3. **Prestataire**
- **Rôle** : Consulte ses clients assignés
- **Permissions** : Peut voir uniquement les clients qui lui sont assignés
- **Actions** : Consulte la liste, recherche, voit les statistiques

## Workflow du Réseautage

```mermaid
graph TB
    A[Client accepte contrat] --> B[Technicien reçoit notification]
    B --> C[Technicien accède à l'interface de réseautage]
    C --> D[Recherche client avec contrat accepté]
    C --> E[Recherche prestataires disponibles]
    D --> F[Sélectionne client]
    E --> G[Sélectionne prestataire(s)]
    F --> H[Confirme l'assignation]
    G --> H
    H --> I[Enregistrement en base]
    I --> J[Notification au prestataire]
    I --> K[Notification au client]
    J --> L[Prestataire voit nouveau client]
    K --> M[Client voit nouveau prestataire]
```

## Routes API Détaillées

### 🔧 Routes Technicien

#### 1. Récupérer les clients avec contrats acceptés
```http
GET /api/v1/technicien/clients-avec-contrats-acceptes
Authorization: Bearer {token}
Content-Type: application/json
```

**Paramètres de requête :**
```javascript
{
  "search": "nom ou email ou raison sociale", // optionnel
  "per_page": 20, // optionnel, défaut: 20
  "page": 1 // optionnel, défaut: 1
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Clients avec contrats acceptés récupérés avec succès",
  "data": {
    "data": [
      {
        "id": 15,
        "nom": "Dupont",
        "prenoms": "Jean",
        "email": "jean.dupont@email.com",
        "contact": "+221 77 123 45 67",
        "type_client": "physique", // ou "entreprise"
        "raison_sociale": null, // pour entreprise seulement
        "contrat": {
          "id": 3,
          "type_contrat": "INDIVIDUEL",
          "date_acceptation": "2024-01-15T10:30:00Z",
          "prime_standard": 50000.00,
          "couverture": 80.00
        },
        "prestataires_assignes": false, // true si déjà des prestataires
        "nombre_employes": null, // pour entreprise seulement
        "created_at": "2024-01-10T08:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 45,
      "last_page": 3
    }
  }
}
```

#### 2. Récupérer les prestataires pour assignation
```http
GET /api/v1/technicien/prestataires-pour-assignation
Authorization: Bearer {token}
```

**Paramètres :**
```javascript
{
  "search": "nom ou adresse", // optionnel
  "type_prestataire": "CENTRE_SOINS", // optionnel
  "per_page": 50 // optionnel
}
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 8,
        "raison_sociale": "Clinique du Sahel",
        "type_prestataire": "CENTRE_SOINS",
        "adresse": "VDN, Dakar",
        "contact": "+221 33 123 45 67",
        "email": "contact@cliniquesahel.sn",
        "statut": "VALIDE",
        "nombre_clients_assignes": 12,
        "created_at": "2024-01-05T14:20:00Z"
      }
    ],
    "pagination": { /* ... */ }
  }
}
```

#### 3. Voir les assignations d'un client
```http
GET /api/v1/technicien/clients/{clientId}/assignations
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "client": {
      "id": 15,
      "nom": "Dupont Jean",
      "email": "jean.dupont@email.com"
    },
    "assignations": [
      {
        "contrat": {
          "id": 3,
          "type_contrat": "INDIVIDUEL",
          "date_debut": "2024-01-15",
          "date_fin": "2025-01-15"
        },
        "prestataires": [
          {
            "id": 8,
            "raison_sociale": "Clinique du Sahel",
            "type_prestataire": "CENTRE_SOINS",
            "adresse": "VDN, Dakar",
            "date_assignation": "2024-01-16T09:15:00Z",
            "statut": "ACTIF"
          }
        ]
      }
    ]
  }
}
```

### 👤 Routes Client

#### 1. Mes prestataires assignés
```http
GET /api/v1/client/reseau/mes-prestataires
Authorization: Bearer {token}
```

**Paramètres :**
```javascript
{
  "search": "nom ou adresse", // optionnel
  "type_prestataire": "PHARMACIE", // optionnel
  "per_page": 20 // optionnel
}
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 12,
        "raison_sociale": "Pharmacie Moderne",
        "type_prestataire": "PHARMACIE",
        "adresse": "Plateau, Dakar",
        "contact": "+221 33 987 65 43",
        "email": "contact@pharmaciemoderne.sn",
        "date_assignation": "2024-01-20T14:30:00Z",
        "statut_assignation": "ACTIF",
        "contrat": {
          "id": 3,
          "type_contrat": "INDIVIDUEL",
          "date_debut": "2024-01-15",
          "date_fin": "2025-01-15"
        }
      }
    ],
    "pagination": { /* ... */ }
  }
}
```

#### 2. Statistiques de mon réseau
```http
GET /api/v1/client/reseau/statistiques
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "total_prestataires": 4,
    "par_type": {
      "CENTRE_SOINS": 1,
      "PHARMACIE": 2,
      "LABORATOIRE": 1,
      "OPTIQUE": 0
    },
    "types_disponibles": {
      "CENTRE_SOINS": "Centre de soins",
      "PHARMACIE": "Pharmacie",
      "LABORATOIRE": "Laboratoire",
      "OPTIQUE": "Optique"
    }
  }
}
```

#### 3. Détails d'un prestataire
```http
GET /api/v1/client/reseau/prestataires/{prestataireId}
Authorization: Bearer {token}
```

### 🏥 Routes Prestataire

#### 1. Mes clients assignés
```http
GET /api/v1/prestataire/reseau/mes-clients
Authorization: Bearer {token}
```

**Paramètres :**
```javascript
{
  "search": "nom ou email ou raison sociale", // optionnel
  "type_client": "physique", // optionnel: "physique" ou "entreprise"
  "per_page": 20 // optionnel
}
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 15,
        "nom": "Dupont",
        "prenoms": "Jean",
        "email": "jean.dupont@email.com",
        "contact": "+221 77 123 45 67",
        "type_client": "physique",
        "raison_sociale": null,
        "contrat": {
          "id": 3,
          "type_contrat": "INDIVIDUEL",
          "date_debut": "2024-01-15",
          "date_fin": "2025-01-15"
        },
        "assignation": {
          "date_assignation": "2024-01-20T14:30:00Z",
          "statut": "ACTIF"
        }
      }
    ],
    "pagination": { /* ... */ }
  }
}
```

#### 2. Statistiques de mes clients
```http
GET /api/v1/prestataire/reseau/statistiques
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "total_clients": 25,
    "date_derniere_assignation": "2024-01-20T14:30:00Z"
  }
}
```

## Interfaces Frontend Recommandées

### 1. **Interface Technicien - Page de Réseautage**

```javascript
// Composant principal
const ReseautagePrestataires = () => {
  const [clients, setClients] = useState([]);
  const [prestataires, setPrestataires] = useState([]);
  const [selectedClient, setSelectedClient] = useState(null);
  const [selectedPrestataires, setSelectedPrestataires] = useState([]);
  
  // Interface en 3 colonnes
  return (
    <div className="grid grid-cols-3 gap-6">
      {/* Colonne 1: Clients avec contrats acceptés */}
      <ClientsAvecContrats 
        clients={clients}
        onSelectClient={setSelectedClient}
        selectedClient={selectedClient}
      />
      
      {/* Colonne 2: Prestataires disponibles */}
      <PrestatairesDisponibles 
        prestataires={prestataires}
        selectedPrestataires={selectedPrestataires}
        onTogglePrestataire={handleTogglePrestataire}
      />
      
      {/* Colonne 3: Assignations en cours */}
      <AssignationsEnCours 
        client={selectedClient}
        prestataires={selectedPrestataires}
        onConfirmAssignation={handleAssignation}
      />
    </div>
  );
};
```

### 2. **Interface Client - Mes Prestataires**

```javascript
const MesPrestataires = () => {
  const [prestataires, setPrestataires] = useState([]);
  const [filtres, setFiltres] = useState({
    search: '',
    type_prestataire: ''
  });
  
  return (
    <div>
      {/* Barre de filtres */}
      <FiltresPrestataires 
        filtres={filtres}
        onChange={setFiltres}
      />
      
      {/* Statistiques rapides */}
      <StatistiquesReseau />
      
      {/* Liste des prestataires */}
      <ListePrestataires 
        prestataires={prestataires}
        onVoirDetails={handleVoirDetails}
      />
    </div>
  );
};
```

### 3. **Interface Prestataire - Mes Clients**

```javascript
const MesClients = () => {
  const [clients, setClients] = useState([]);
  const [stats, setStats] = useState({});
  
  return (
    <div>
      {/* Dashboard avec statistiques */}
      <StatistiquesClients stats={stats} />
      
      {/* Filtres de recherche */}
      <FiltresClients />
      
      {/* Liste des clients avec actions */}
      <ListeClients 
        clients={clients}
        onCreerSinistre={handleCreerSinistre}
        onVoirHistorique={handleVoirHistorique}
      />
    </div>
  );
};
```

## Gestion des États et Erreurs

### États de Chargement
```javascript
const [loading, setLoading] = useState({
  clients: false,
  prestataires: false,
  assignation: false
});
```

### Gestion des Erreurs
```javascript
const [errors, setErrors] = useState({});

// Gestion d'erreur type
const handleApiError = (error, context) => {
  if (error.response?.status === 403) {
    setErrors(prev => ({
      ...prev,
      [context]: 'Accès non autorisé'
    }));
  } else if (error.response?.status === 404) {
    setErrors(prev => ({
      ...prev,
      [context]: 'Ressource non trouvée'
    }));
  } else {
    setErrors(prev => ({
      ...prev,
      [context]: 'Une erreur est survenue'
    }));
  }
};
```

## Notifications et Feedback

### Notifications d'Assignation
- **Pour le client** : "Nouveau prestataire assigné : {nom_prestataire}"
- **Pour le prestataire** : "Nouveau client assigné : {nom_client}"
- **Pour le technicien** : "Assignation effectuée avec succès"

### Messages de Confirmation
```javascript
const confirmAssignation = (client, prestataires) => {
  const message = `Assigner ${prestataires.length} prestataire(s) à ${client.nom} ?`;
  
  if (window.confirm(message)) {
    executeAssignation();
  }
};
```

## Bonnes Pratiques d'Implémentation

### 1. **Recherche et Filtrage**
- Implémentez la recherche avec debouncing (300ms)
- Utilisez des filtres combinables
- Sauvegardez les filtres dans l'URL

### 2. **Pagination**
- Utilisez la pagination côté serveur
- Implémentez le scroll infini pour les listes longues
- Affichez le nombre total d'éléments

### 3. **Performance**
- Mettez en cache les listes de prestataires
- Utilisez React.memo pour les composants de liste
- Implémentez le lazy loading pour les détails

### 4. **UX/UI**
- Utilisez des indicateurs de chargement
- Implémentez la recherche en temps réel
- Affichez des messages d'état clairs
- Utilisez des couleurs pour différencier les statuts

### 5. **Sécurité**
- Validez toujours les permissions côté frontend
- Ne cachez pas les données sensibles avec CSS uniquement
- Gérez les timeouts de session

## Codes d'Erreur Spécifiques

| Code | Message | Action Recommandée |
|------|---------|-------------------|
| 403  | Accès non autorisé | Rediriger vers login |
| 404  | Client/Prestataire non trouvé | Rafraîchir la liste |
| 422  | Assignation déjà existante | Afficher message informatif |
| 500  | Erreur serveur | Réessayer + support |

Ce guide fournit toutes les informations nécessaires pour implémenter le système de réseautage côté frontend. Les interfaces suggérées peuvent être adaptées selon vos besoins UX/UI spécifiques.