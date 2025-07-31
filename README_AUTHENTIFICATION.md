# SUNU Santé – Guide Authentification

## Flow complet du processus d'authentification et changement de mot de passe

---

## 1. Création de compte (Inscription)

**Route :**
```
POST /api/v1/auth/register
```

**Payload :**
```json
{
  "nom": "Doe",
  "prenoms": "John", 
  "email": "john.doe@email.com",
  "password": "motdepasse",
  "code_parrainage": "ABC123" // optionnel
}
```

**Réponse succès :**
```json
{
  "success": true,
  "message": "Compte créé avec succès. Vérifiez votre email pour l'OTP.",
  "data": {
    "user": {
      "id": 1,
      "nom": "Doe",
      "prenoms": "John",
      "email": "john.doe@email.com"
    }
  }
}
```

**Réponse erreur :**
```json
{
  "success": false,
  "message": "Erreur de validation",
  "errors": {
    "email": ["L'email est déjà utilisé."],
    "password": ["Le mot de passe doit contenir au moins 8 caractères."]
  }
}
```

---

## 2. Envoi de l'OTP (si non automatique)

**Route :**
```
POST /api/v1/auth/send-otp
```

**Payload :**
```json
{
  "email": "john.doe@email.com"
}
```

**Réponse succès :**
```json
{
  "success": true,
  "message": "OTP envoyé à votre email.",
  "data": {
    "email": "john.doe@email.com"
  }
}
```

---

## 3. Vérification de l'OTP

**Route :**
```
POST /api/v1/auth/verify-otp
```

**Payload :**
```json
{
  "email": "john.doe@email.com",
  "otp": "123456"
}
```

**Réponse succès :**
```json
{
  "success": true,
  "message": "Compte vérifié avec succès.",
  "data": {
    "user": {
      "id": 1,
      "nom": "Doe",
      "prenoms": "John",
      "email": "john.doe@email.com",
      "email_verified_at": "2024-01-15T10:30:00Z"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

**Réponse erreur :**
```json
{
  "success": false,
  "message": "OTP invalide ou expiré.",
  "errors": {
    "otp": ["Le code OTP est incorrect."]
  }
}
```

---

## 4. Connexion (Login)

**Route :**
```
POST /api/v1/auth/login
```

**Payload :**
```json
{
  "email": "john.doe@email.com",
  "password": "motdepasse"
}
```

**Réponse succès :**
```json
{
  "success": true,
  "message": "Connexion réussie.",
  "data": {
    "user": {
      "id": 1,
      "nom": "Doe",
      "prenoms": "John",
      "email": "john.doe@email.com",
      "roles": ["user"],
      "email_verified_at": "2024-01-15T10:30:00Z"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

**Réponse erreur :**
```json
{
  "success": false,
  "message": "Identifiants incorrects.",
  "errors": {
    "email": ["Ces identifiants ne correspondent pas à nos enregistrements."]
  }
}
```

---

## 5. Récupération des infos utilisateur connecté

**Route :**
```
GET /api/v1/auth/me
```

**Headers :**
```
Authorization: Bearer <token>
```

**Réponse succès :**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nom": "Doe",
    "prenoms": "John",
    "email": "john.doe@email.com",
    "contact": "+22501234567",
    "adresse": "Abidjan, Côte d'Ivoire",
    "roles": ["user"],
    "email_verified_at": "2024-01-15T10:30:00Z",
    "created_at": "2024-01-15T10:00:00Z"
  }
}
```

---

## 6. Déconnexion

**Route :**
```
POST /api/v1/auth/logout
```

**Headers :**
```
Authorization: Bearer <token>
```

**Réponse succès :**
```json
{
  "success": true,
  "message": "Déconnexion réussie."
}
```

---

## 7. Changement de mot de passe (utilisateur connecté)

**Route :**
```
POST /api/v1/auth/change-password
```

**Headers :**
```
Authorization: Bearer <token>
```

**Payload :**
```json
{
  "old_password": "motdepasse",
  "new_password": "nouveaumotdepasse"
}
```

**Réponse succès :**
```json
{
  "success": true,
  "message": "Mot de passe modifié avec succès."
}
```

**Réponse erreur :**
```json
{
  "success": false,
  "message": "Ancien mot de passe incorrect.",
  "errors": {
    "old_password": ["L'ancien mot de passe est incorrect."]
  }
}
```

---

## 8. Mot de passe oublié (Reset)

### a) Demander un code OTP de réinitialisation

**Route :**
```
POST /api/v1/auth/forgot-password
```

**Payload :**
```json
{
  "email": "john.doe@email.com"
}
```

**Réponse succès :**
```json
{
  "success": true,
  "data": {
    "email": "john.doe@email.com",
    "message": "Un code OTP a été envoyé à votre email."
  },
  "message": "Code OTP envoyé avec succès."
}
```

**Réponse erreur :**
```json
{
  "success": false,
  "message": "Aucun compte trouvé avec cet email.",
  "errors": {
    "email": ["Aucun compte trouvé avec cet email."]
  }
}
```

### b) Vérifier le code OTP

**Route :**
```
POST /api/v1/auth/verify-reset-otp
```

**Payload :**
```json
{
  "email": "john.doe@email.com",
  "otp": "123456"
}
```

**Réponse succès :**
```json
{
  "success": true,
  "data": {
    "reset_token": "abc123...",
    "expires_at": "2024-01-15T11:00:00Z",
    "message": "Code OTP vérifié. Vous pouvez maintenant définir votre nouveau mot de passe."
  },
  "message": "Code OTP vérifié avec succès."
}
```

**Réponse erreur :**
```json
{
  "success": false,
  "message": "Code OTP invalide ou expiré.",
  "errors": {
    "otp": ["Le code OTP est incorrect."]
  }
}
```

### c) Réinitialiser le mot de passe

**Route :**
```
POST /api/v1/auth/reset-password
```

**Payload :**
```json
{
  "email": "john.doe@email.com",
  "token": "<reset_token reçu précédemment>",
  "password": "nouveaumotdepasse",
  "password_confirmation": "nouveaumotdepasse"
}
```

**Réponse succès :**
```json
{
  "success": true,
  "data": {
    "message": "Mot de passe réinitialisé avec succès."
  },
  "message": "Mot de passe modifié avec succès."
}
```

**Réponse erreur :**
```json
{
  "success": false,
  "message": "Token de réinitialisation invalide ou expiré.",
  "errors": {
    "token": ["Le token de réinitialisation est invalide."]
  }
}
```

---

## 9. Refresh Token (Renouvellement)

**Route :**
```
POST /api/v1/auth/refresh-token
```

**Headers :**
```
Authorization: Bearer <token>
```

**Réponse succès :**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

---

## Résumé du flow utilisateur

### Flow d'inscription :
1. **Inscription** (`POST /auth/register`) → OTP envoyé
2. **Vérification OTP** (`POST /auth/verify-otp`) → Compte activé + connexion auto

### Flow de connexion :
1. **Connexion** (`POST /auth/login`) → JWT token
2. **Utilisation** → Header `Authorization: Bearer <token>` sur toutes les routes protégées

### Flow de changement de mot de passe :
1. **Changement** (`POST /auth/change-password`) → Nouveau mot de passe
2. **Reconnexion** → L'utilisateur doit se reconnecter avec le nouveau mot de passe

### Flow de mot de passe oublié :
1. **Demande OTP** (`POST /auth/forgot-password`) → Vérification email + envoi OTP
2. **Vérification OTP** (`POST /auth/verify-reset-otp`) → Validation OTP + génération token temporaire
3. **Réinitialisation** (`POST /auth/reset-password`) → Nouveau mot de passe défini avec token

---

## Codes d'erreur HTTP

- **200** : Succès
- **400** : Erreur de validation (payload incorrect)
- **401** : Non authentifié (token manquant/invalide)
- **403** : Non autorisé (rôle insuffisant)
- **404** : Ressource non trouvée
- **422** : Erreur de validation des données
- **500** : Erreur serveur

---

## Notes importantes pour le frontend

1. **Gestion des tokens** : Stocker le JWT token et l'inclure dans le header `Authorization: Bearer <token>` pour toutes les requêtes protégées.

2. **Expiration du token** : Gérer le refresh automatique ou la redirection vers la page de login.

3. **Validation des emails** : L'OTP est obligatoire après inscription pour activer le compte.

4. **Messages d'erreur** : Toujours afficher les messages d'erreur retournés par l'API.

5. **Sécurité** : Ne jamais stocker le mot de passe en clair côté frontend.

6. **UX** : Afficher des indicateurs de chargement pendant les requêtes d'authentification.

---

**Ce guide sert de référence pour l'implémentation frontend de l'authentification.**
Pour toute question, se référer à la documentation API complète ou contacter l'équipe technique. 