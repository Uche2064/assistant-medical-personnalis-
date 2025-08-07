# API Proposition de Contrat

## 🎯 **Vue d'ensemble**

Cette documentation décrit l'étape de proposition de contrat par un technicien à un client (physique ou entreprise) après validation de sa demande d'adhésion.

## 📋 **Route API**

### **PUT** `/api/demandes-adhesions/{demande_id}/proposer-contrat`

**Description :** Propose un contrat à un client après validation de sa demande d'adhésion.

**Permissions :** 
- Technicien

**Méthode :** PUT

**Paramètres URL :**
- `demande_id` (integer, requis) : ID de la demande d'adhésion

## 🔐 **Authentification**

Toutes les routes nécessitent un token JWT dans le header :
```
Authorization: Bearer {token}
```

## 📤 **Payload Request**

### **Headers requis :**
```http
Content-Type: application/json
Authorization: Bearer {token}
```

### **Body JSON :**
```json
{
  "contrat_id": 123,
  "prime_proposee": 50000,
  "commentaires": "Contrat adapté à vos besoins et profil de risque",
  "taux_couverture": 80,
  "frais_gestion": 20,
  "garanties_incluses": [1, 2, 3]
}
```

### **Paramètres du body :**
- `contrat_id` (integer, requis) : ID du contrat à proposer
- `prime_proposee` (numeric, requis) : Prime proposée en FCFA
- `commentaires` (string, optionnel) : Commentaires du technicien
- `taux_couverture` (numeric, optionnel) : Taux de couverture en % (défaut: 80)
- `frais_gestion` (numeric, optionnel) : Frais de gestion en % (défaut: 20)
- `garanties_incluses` (array, optionnel) : IDs des garanties à inclure

## 📥 **Payload Response**

### **Succès (200) :**
```json
{
  "success": true,
  "message": "Proposition de contrat envoyée avec succès. Le client doit maintenant accepter ou refuser.",
  "data": {
    "proposition_id": 456,
    "contrat_id": 123,
    "type_contrat": "standard",
    "prime_proposee": 50000,
    "token_acceptation": "abc123def456ghi789...",
    "expiration_token": "2025-08-13T16:30:00.000000Z",
    "statut": "proposee",
    "propose_par": "Jean Dupont"
  }
}
```

### **Erreur - Demande non trouvée (404) :**
```json
{
  "success": false,
  "message": "Demande d'adhésion non trouvée",
  "data": null
}
```

### **Erreur - Demande déjà traitée (400) :**
```json
{
  "success": false,
  "message": "Cette demande a déjà été traitée",
  "data": null
}
```

### **Erreur - Contrat invalide (400) :**
```json
{
  "success": false,
  "message": "Contrat non valide ou introuvable",
  "data": null
}
```

### **Erreur - Validation (422) :**
```json
{
  "success": false,
  "message": "Erreur de validation",
  "data": {
    "contrat_id": [
      "Le contrat sélectionné est requis"
    ],
    "prime_proposee": [
      "La prime proposée doit être un nombre"
    ]
  }
}
```

### **Erreur - Permission (403) :**
```json
{
  "success": false,
  "message": "Accès non autorisé",
  "data": null
}
```

### **Erreur - Serveur (500) :**
```json
{
  "success": false,
  "message": "Erreur lors de la proposition de contrat: {message}",
  "data": null
}
```

## 📊 **Exemples d'utilisation**

### **Exemple 1 : Proposition contrat standard**

#### **Request :**
```bash
curl -X PUT "http://localhost:8000/api/demandes-adhesions/123/proposer-contrat" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "contrat_id": 1,
    "prime_proposee": 75000,
    "commentaires": "Contrat standard adapté à votre profil. Couverture complète avec garanties essentielles.",
    "taux_couverture": 85,
    "frais_gestion": 15,
    "garanties_incluses": [1, 2, 3, 4]
  }'
```

#### **Response :**
```json
{
  "success": true,
  "message": "Proposition de contrat envoyée avec succès. Le client doit maintenant accepter ou refuser.",
  "data": {
    "proposition_id": 456,
    "contrat_id": 1,
    "type_contrat": "standard",
    "prime_proposee": 75000,
    "token_acceptation": "abc123def456ghi789jkl012mno345pqr678stu901vwx234yz",
    "expiration_token": "2025-08-13T16:30:00.000000Z",
    "statut": "proposee",
    "propose_par": "Jean Dupont"
  }
}
```

### **Exemple 2 : Proposition contrat premium**

#### **Request :**
```bash
curl -X PUT "http://localhost:8000/api/demandes-adhesions/456/proposer-contrat" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "contrat_id": 3,
    "prime_proposee": 120000,
    "commentaires": "Contrat premium avec couverture étendue. Idéal pour une protection maximale.",
    "taux_couverture": 90,
    "frais_gestion": 10
  }'
```

#### **Response :**
```json
{
  "success": true,
  "message": "Proposition de contrat envoyée avec succès. Le client doit maintenant accepter ou refuser.",
  "data": {
    "proposition_id": 789,
    "contrat_id": 3,
    "type_contrat": "premium",
    "prime_proposee": 120000,
    "token_acceptation": "def456ghi789jkl012mno345pqr678stu901vwx234yz",
    "expiration_token": "2025-08-13T16:30:00.000000Z",
    "statut": "proposee",
    "propose_par": "Marie Martin"
  }
}
```

## 🔄 **Actions automatiques**

Lors de la proposition d'un contrat, le système effectue automatiquement :

1. **Création de la proposition** : Enregistrement dans `proposition_contrats`
2. **Génération du token** : Token unique valable 7 jours
3. **Association des garanties** : Liens vers les garanties sélectionnées
4. **Notification in-app** : Notification immédiate au client
5. **Email automatique** : Email avec lien d'acceptation
6. **Cache du token** : Stockage temporaire pour validation

## 📧 **Email envoyé**

Un email automatique est envoyé au client avec :
- Détails du contrat proposé
- Prime et conditions
- Lien d'acceptation sécurisé
- Informations du technicien
- Date d'expiration (7 jours)

## 🛡️ **Sécurité et validation**

### **Vérifications effectuées :**
- ✅ Authentification de l'utilisateur
- ✅ Vérification des permissions (technicien uniquement)
- ✅ Existence de la demande d'adhésion
- ✅ Statut de la demande (doit être "en_attente")
- ✅ Validité du contrat (doit être actif)
- ✅ Validation des garanties (si fournies)

### **Contraintes :**
- Seuls les techniciens peuvent proposer des contrats
- Seules les demandes en attente peuvent recevoir une proposition
- Le contrat doit être actif
- La prime doit être positive
- Le token expire après 7 jours

## 📱 **Utilisation Frontend**

### **JavaScript/TypeScript :**
```javascript
async function proposerContrat(demandeId, contratData) {
  try {
    const response = await fetch(`/api/demandes-adhesions/${demandeId}/proposer-contrat`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(contratData)
    });

    const data = await response.json();
    
    if (data.success) {
      console.log('Contrat proposé avec succès');
      // Afficher le token d'acceptation
      console.log('Token:', data.data.token_acceptation);
      // Mettre à jour l'interface
      updateDemandeStatus(demandeId, 'contrat_propose');
    } else {
      console.error('Erreur:', data.message);
    }
  } catch (error) {
    console.error('Erreur de connexion:', error);
  }
}
```

### **Angular :**
```typescript
proposerContrat(demandeId: number, contratData: any): Observable<any> {
  return this.http.put(
    `${this.apiUrl}/demandes-adhesions/${demandeId}/proposer-contrat`,
    contratData,
    { headers: this.getHeaders() }
  );
}
```

## 🎯 **Cas d'usage**

### **1. Client physique :**
- Demande individuelle validée
- Contrat personnel
- Bénéficiaires optionnels

### **2. Entreprise :**
- Demande groupée validée
- Contrat collectif
- Employés + bénéficiaires

### **3. Types de contrats :**
- **Basic** : Couverture minimale
- **Standard** : Couverture complète
- **Premium** : Couverture étendue

## 📊 **Statuts possibles**

### **Proposition de contrat :**
- `proposee` : Proposée au client
- `acceptee` : Acceptée par le client
- `refusee` : Refusée par le client
- `expiree` : Token expiré

### **Demande d'adhésion :**
- `en_attente` : En cours de traitement
- `validee` : Acceptée
- `rejetee` : Refusée

## 🔍 **Logs et traçabilité**

Toutes les propositions de contrat sont loggées avec :
- ID de la demande
- ID du technicien
- Détails du contrat
- Prime proposée
- Token généré
- Timestamp
- IP de l'utilisateur

## 📋 **Étapes suivantes**

Après la proposition de contrat :

1. **Client reçoit** notification in-app + email
2. **Client clique** sur le lien d'acceptation
3. **Client accepte/refuse** le contrat
4. **Système traite** la réponse
5. **Contrat final** créé si accepté

## 🔗 **Routes liées**

- `POST /api/contrat/accepter/{token}` : Accepter le contrat
- `POST /api/contrat/refuser/{token}` : Refuser le contrat
- `GET /api/demandes-adhesions/{id}` : Détails de la demande
- `GET /api/contrats` : Liste des contrats disponibles 