# SUNU Santé – Flow de Création de Compte

## Vue d'ensemble du processus

Le processus de création de compte dans SUNU Santé suit un flow sécurisé en 3 étapes :
1. **Inscription** → Génération automatique d'OTP
2. **Vérification OTP** → Activation du compte + connexion automatique
3. **Première connexion** → Accès à l'application

**Types de demandeurs supportés :**
- **Client Physique** (`physique`)
- **Centre de Soins** (`centre_de_soins`)
- **Laboratoire/Centre de Diagnostic** (`laboratoire_centre_diagnostic`)
- **Pharmacie** (`pharmacie`)
- **Optique** (`optique`)
- **Autre** (`autre`)

---

## 1. Inscription (Création du compte)

### Route
```
POST /api/v1/auth/register
```

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Payload selon le type de demandeur

#### A. Client Physique
```json
{
  "type_demandeur": "physique",
  "email": "john.doe@email.com",
  "password": "MotDePasse123!",
  "contact": "+22501234567",
  "adresse": "Abidjan, Côte d'Ivoire",
  "nom": "Doe",
  "prenoms": "John",
  "date_naissance": "1990-01-15",
  "sexe": "M",
  "profession": "Ingénieur",
  "code_parrainage": "ABC123",
  "photo_url": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ..."
}
```

#### B. Entreprise (Autre)
```json
{
  "type_demandeur": "autre",
  "email": "contact@entreprise.com",
  "password": "MotDePasse123!",
  "contact": "+22501234567",
  "adresse": "Abidjan, Côte d'Ivoire",
  "raison_sociale": "Entreprise ABC SARL"
}
```

#### C. Prestataire de Soins
```json
{
  "type_demandeur": "centre_de_soins",
  "email": "contact@clinique.com",
  "password": "MotDePasse123!",
  "contact": "+22501234567",
  "adresse": "Abidjan, Côte d'Ivoire",
  "raison_sociale": "Clinique du Bonheur"
}
```

### Validation des champs

#### Champs communs (tous les types)
- **type_demandeur** : `required|in:physique,centre_de_soins,laboratoire_centre_diagnostic,pharmacie,optique,autre`
- **email** : `required|email|unique:users,email`
- **password** : `required|string|min:8`
- **contact** : `required|string|unique:users,contact`
- **adresse** : `required|string|max:500`
- **code_parrainage** : `nullable|string|exists:personnels,code_parainage`

#### Champs spécifiques Client Physique
- **nom** : `required_if:type_demandeur,physique|string|max:255`
- **prenoms** : `required_if:type_demandeur,physique|string|max:255`
- **date_naissance** : `required_if:type_demandeur,physique|date|before:today`
- **sexe** : `required_if:type_demandeur,physique|in:M,F`
- **profession** : `nullable|string|max:255`
- **photo_url** : `required_if:type_demandeur,physique|image|mimes:jpeg,png,jpg,gif,svg|max:2048`

#### Champs spécifiques Entreprise/Prestataire
- **raison_sociale** : `required_if:type_demandeur,autre,pharmacie,centre_de_soins,laboratoire_de_biologie_medicale,optique|string|max:255|unique:entreprises,raison_sociale`

### Réponse succès (200)
```json
{
  "success": true,
  "message": "Inscription réussie. Vérifiez votre email pour valider votre compte.",
  "data": {
    "user": {
      "id": 1,
      "email": "john.doe@email.com",
      "contact": "+22501234567",
      "adresse": "Abidjan, Côte d'Ivoire",
      "est_actif": false,
      "email_verified_at": null,
      "client": {
        "id": 1,
        "nom": "Doe",
        "prenoms": "John",
        "date_naissance": "1990-01-15",
        "sexe": "M",
        "profession": "Ingénieur",
        "type_client": "physique",
        "statut": "prospect"
      }
    },
    "email": "john.doe@email.com",
    "type_demandeur": "physique"
  }
}
```

### Réponse erreur (422)
```json
{
  "success": false,
  "message": "Erreur de validation",
  "errors": {
    "email": ["Cet email est déjà utilisé."],
    "contact": ["Ce contact existe déjà."],
    "photo_url": ["La photo est obligatoire pour un demandeur physique."],
    "raison_sociale": ["Cette raison sociale existe déjà."]
  }
}
```

### Actions côté serveur
1. ✅ Validation des données selon le type de demandeur
2. ✅ Upload de la photo (si client physique)
3. ✅ Hashage du mot de passe
4. ✅ Création de l'utilisateur (inactif)
5. ✅ Création de l'entité spécifique (Client/Entreprise/Prestataire)
6. ✅ Attribution du rôle approprié
7. ✅ Génération automatique d'OTP
8. ✅ Envoi email avec OTP
9. ✅ Transaction DB pour garantir l'intégrité

---

## 2. Envoi d'OTP (Si nécessaire)

### Route
```
POST /api/v1/auth/send-otp
```

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Payload
```json
{
  "email": "john.doe@email.com"
}
```

### Validation des champs
- **email** : `required|email|exists:users,email`

### Réponse succès (200)
```json
{
  "success": true,
  "message": "Code de validation envoyé à votre email.",
  "data": {
    "email": "john.doe@email.com",
    "message": "OTP envoyé avec succès"
  }
}
```

### Réponse erreur (404)
```json
{
  "success": false,
  "message": "Aucun compte en attente de validation trouvé avec cet email."
}
```

---

## 3. Vérification de l'OTP (Activation du compte)

### Route
```
POST /api/v1/auth/verify-otp
```

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Payload
```json
{
  "email": "john.doe@email.com",
  "otp": "123456"
}
```

### Validation des champs
- **email** : `required|email|exists:users,email`
- **otp** : `required|string|size:6`

### Réponse succès (200)
```json
{
  "success": true,
  "message": "Votre compte a été validé avec succès. Vous pouvez maintenant vous connecter.",
  "data": {
    "user": {
      "id": 1,
      "email": "john.doe@email.com",
      "contact": "+22501234567",
      "adresse": "Abidjan, Côte d'Ivoire",
      "est_actif": true,
      "email_verified_at": "2024-01-15T10:30:00.000000Z",
      "client": {
        "id": 1,
        "nom": "Doe",
        "prenoms": "John",
        "date_naissance": "1990-01-15",
        "sexe": "M",
        "profession": "Ingénieur",
        "type_client": "physique",
        "statut": "prospect"
      }
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "message": "Compte validé avec succès"
  }
}
```

### Réponse erreur (403)
```json
{
  "success": false,
  "message": "Code de validation invalide ou expiré."
}
```

### Actions côté serveur
1. ✅ Validation email et OTP
2. ✅ Vérification OTP en base (non expiré, non utilisé)
3. ✅ Activation du compte (`est_actif = true`)
4. ✅ Mise à jour `email_verified_at`
5. ✅ Marquer OTP comme vérifié
6. ✅ Envoi email de bienvenue
7. ✅ Génération JWT token
8. ✅ Connexion automatique

---

## 4. Connexion (Après activation)

### Route
```
POST /api/v1/auth/login
```

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Payload
```json
{
  "email": "john.doe@email.com",
  "password": "MotDePasse123!"
}
```

### Validation des champs
- **email** : `required|email`
- **password** : `required|string`

### Réponse succès (200)
```json
{
  "success": true,
  "message": "Connexion réussie.",
  "data": {
    "user": {
      "id": 1,
      "email": "john.doe@email.com",
      "contact": "+22501234567",
      "adresse": "Abidjan, Côte d'Ivoire",
      "est_actif": true,
      "email_verified_at": "2024-01-15T10:30:00.000000Z",
      "roles": ["user"],
      "client": {
        "id": 1,
        "nom": "Doe",
        "prenoms": "John",
        "date_naissance": "1990-01-15",
        "sexe": "M",
        "profession": "Ingénieur",
        "type_client": "physique",
        "statut": "prospect"
      }
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

### Réponse erreur (401)
```json
{
  "success": false,
  "message": "Identifiants incorrects."
}
```

---

## 5. Vérification d'unicité

### Route Email
```
POST /api/v1/auth/check-unique
```

### Payload
```json
{
  "field": "email",
  "value": "john.doe@email.com"
}
```

### Route Contact
```
POST /api/v1/auth/check-contact-unique
```

### Payload
```json
{
  "contact": "+22501234567"
}
```

---

## Flow complet côté frontend

### Étape 1 : Sélection du type de demandeur
```javascript
// Types disponibles
const typesDemandeur = [
  { value: 'physique', label: 'Client Physique' },
  { value: 'centre_de_soins', label: 'Centre de Soins' },
  { value: 'laboratoire_centre_diagnostic', label: 'Laboratoire/Centre de Diagnostic' },
  { value: 'pharmacie', label: 'Pharmacie' },
  { value: 'optique', label: 'Optique' },
  { value: 'autre', label: 'Autre' }
];
```

### Étape 2 : Inscription selon le type
```javascript
// Exemple pour Client Physique
const registerData = {
  type_demandeur: 'physique',
  email: 'john.doe@email.com',
  password: 'MotDePasse123!',
  contact: '+22501234567',
  adresse: 'Abidjan, Côte d\'Ivoire',
  nom: 'Doe',
  prenoms: 'John',
  date_naissance: '1990-01-15',
  sexe: 'M',
  profession: 'Ingénieur',
  photo_url: photoFile // File object ou base64
};

// Appel API
const response = await fetch('/api/v1/auth/register', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify(registerData)
});

if (response.success) {
  // Redirection vers page de vérification OTP
  navigate('/verify-otp', { 
    state: { email: registerData.email } 
  });
} else {
  displayErrors(response.errors);
}
```

### Étape 3 : Vérification OTP
```javascript
const otpData = {
  email: 'john.doe@email.com',
  otp: '123456'
};

const response = await fetch('/api/v1/auth/verify-otp', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify(otpData)
});

if (response.success) {
  // Stockage du token
  localStorage.setItem('token', response.data.access_token);
  
  // Redirection vers dashboard
  navigate('/dashboard');
} else {
  displayError(response.message);
}
```

---

## Gestion des erreurs

### Codes HTTP
- **200** : Succès
- **400** : Erreur de validation
- **401** : Non authentifié
- **403** : OTP invalide/expiré
- **404** : Ressource non trouvée
- **422** : Erreur de validation des données
- **500** : Erreur serveur

### Messages d'erreur courants
- `"Cet email est déjà utilisé."`
- `"Ce contact existe déjà."`
- `"La photo est obligatoire pour un demandeur physique."`
- `"Cette raison sociale existe déjà."`
- `"Code de validation invalide ou expiré."`
- `"Aucun compte en attente de validation trouvé."`

---

## Sécurité

### OTP
- **Format** : 6 chiffres
- **Expiration** : 10 minutes
- **Usage unique** : Supprimé après utilisation
- **Type** : `email_verification` pour inscription

### JWT Token
- **Expiration** : 1 heure
- **Refresh** : Possible via `/api/v1/auth/refresh-token`
- **Stockage** : `localStorage` côté frontend
- **Header** : `Authorization: Bearer <token>`

### Validation
- **Email** : Format valide + unicité
- **Contact** : Format valide + unicité
- **Mot de passe** : Minimum 8 caractères
- **Photo** : Formats jpeg, png, jpg, gif, svg, max 2MB
- **OTP** : Exactement 6 chiffres

---

## Notes importantes

### Pour le frontend
1. **Gestion des types** : Afficher les champs selon le type de demandeur
2. **Upload photo** : Convertir en base64 ou FormData
3. **Validation** : Vérifier l'unicité email/contact en temps réel
4. **UX** : Indicateurs de chargement pendant les requêtes
5. **Sécurité** : Ne jamais stocker le mot de passe en clair

### Pour le backend
1. **Rate limiting** : Limiter les tentatives d'inscription/connexion
2. **Validation** : Validation stricte côté serveur
3. **Logs** : Logger les tentatives de connexion échouées
4. **Sécurité** : Hashage des mots de passe avec bcrypt
5. **Transactions** : Garantir l'intégrité des données

---

**Ce guide sert de référence complète pour l'implémentation du flow de création de compte avec tous les types de demandeurs supportés.** 