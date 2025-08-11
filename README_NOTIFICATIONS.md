# Système de Notifications - SUNU Santé

## 🎯 **Vue d'ensemble**

Le système de notifications permet d'informer automatiquement les utilisateurs appropriés des événements importants dans l'application.

## 🔐 **Sécurité et Permissions**

### **Isolation des données :**
- Chaque utilisateur ne voit que ses propres notifications
- Vérification automatique de propriété avant toute action
- Protection contre l'accès aux notifications d'autres utilisateurs

### **Authentification :**
- Toutes les routes sont protégées par `auth:api`
- Seuls les utilisateurs connectés peuvent accéder aux notifications

## 📋 **Types de Notifications**

### **1. Notifications pour les Techniciens :**

#### **Nouveaux comptes créés :**
- Quand une personne physique crée un compte
- Quand une entreprise crée un compte

#### **Nouvelles demandes d'adhésion :**
- Demandes de personnes physiques
- Demandes d'entreprises

### **2. Notifications pour les Médecins Contrôleurs :**

#### **Nouveaux prestataires :**
- Quand un prestataire crée un compte
- Quand un prestataire soumet sa demande d'adhésion

### **3. Notifications pour les Entreprises :**

#### **Nouvelles fiches employés :**
- Quand un employé soumet sa fiche d'adhésion (seule l'entreprise concernée est notifiée)

## 🚀 **Routes API**

### **Lecture des notifications :**
```bash
# Récupérer toutes les notifications
GET /api/notifications

# Récupérer seulement les non lues
GET /api/notifications?lu=false

# Filtrer par type
GET /api/notifications?type=info

# Pagination
GET /api/notifications?per_page=20

# Statistiques
GET /api/notifications/stats
```

### **Actions sur les notifications :**
```bash
# Marquer comme lue
PATCH /api/notifications/{id}/mark-as-read

# Marquer comme non lue
PATCH /api/notifications/{id}/mark-as-unread

# Marquer toutes comme lues
PATCH /api/notifications/mark-all-as-read

# Supprimer une notification
DELETE /api/notifications/{id}

# Supprimer toutes les lues
DELETE /api/notifications/destroy-read
```

## 📊 **Exemples de Réponses**

### **Liste des notifications :**
```json
{
  "success": true,
  "message": "Notifications récupérées avec succès.",
  "data": {
    "notifications": {
      "current_page": 1,
      "data": [
        {
          "id": 1,
          "user_id": 123,
          "titre": "Nouveau compte créé",
          "message": "Un nouveau compte personne physique a été créé : john@example.com",
          "type": "info",
          "lu": false,
          "donnees": {
            "user_id": 456,
            "user_email": "john@example.com",
            "user_type": "personne physique",
            "date_creation": "06/08/2025 à 16:30",
            "type_notification": "nouveau_compte"
          },
          "created_at": "2025-08-06T16:30:00.000000Z",
          "updated_at": "2025-08-06T16:30:00.000000Z"
        }
      ],
      "total": 1,
      "per_page": 10
    },
    "statistiques": {
      "total": 5,
      "unread": 3,
      "read": 2
    }
  }
}
```


## 🔄 **Déclencheurs Automatiques**

### **Création de compte :**
```php
// Dans AuthController::register()
switch ($validated['type_demandeur']) {
    case TypeDemandeurEnum::PHYSIQUE->value:
        $this->notificationService->notifyTechniciensNouveauCompte($user, 'personne physique');
        break;
    case TypeDemandeurEnum::ENTREPRISE->value:
        $this->notificationService->notifyTechniciensNouveauCompte($user, 'entreprise');
        break;
    default: // Prestataires
        $this->notificationService->notifyMedecinsControleursNouveauPrestataire($user);
        break;
}
```

### **Soumission de demande d'adhésion :**
```php
// Dans DemandeAdhesionController::store()
if ($typeDemandeur === TypeDemandeurEnum::PHYSIQUE->value || $typeDemandeur === TypeDemandeurEnum::ENTREPRISE->value) {
    $this->notificationService->notifyTechniciensNouvelleDemande($demande);
} else {
    $this->notificationService->notifyMedecinsControleursDemandePrestataire($demande);
}
```

### **Soumission de fiche employé :**
```php
// Dans DemandeAdhesionController::soumettreFicheEmploye()
// Seule l'entreprise concernée est notifiée (pas les techniciens)
$this->notificationService->createNotification(
    $entreprise->user->id,
    'Nouvelle fiche employé soumise',
    "L'employé {$assure->nom} {$assure->prenoms} a soumis sa fiche d'adhésion.",
    'info',
    [
        'employe_id' => $assure->id,
        'employe_nom' => $assure->nom,
        'employe_prenoms' => $assure->prenoms,
        'employe_email' => $assure->email,
        'date_soumission' => now()->format('d/m/Y à H:i'),
        'type' => 'nouvelle_fiche_employe'
    ]
);
```

## 🎨 **Types de Notifications**

### **Types disponibles :**
- `info` : Informations générales
- `warning` : Avertissements
- `error` : Erreurs
- `success` : Succès

### **Données incluses :**
- `user_id` : ID de l'utilisateur concerné
- `user_email` : Email de l'utilisateur
- `date_creation` : Date de création
- `type_notification` : Type spécifique de notification
- Données contextuelles selon le type

## 🔧 **Configuration**

### **Middleware :**
```php
Route::middleware(['auth:api'])->prefix('notifications')->group(function () {
    // Routes des notifications
});
```

### **Modèle Notification :**
```php
protected $fillable = [
    'user_id',
    'titre',
    'message',
    'type',
    'lu',
    'donnees'
];
```

## 📱 **Utilisation Frontend**

### **Récupérer les notifications :**
```javascript
// Récupérer toutes les notifications
const response = await fetch('/api/notifications', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});

// Récupérer seulement les non lues
const unreadResponse = await fetch('/api/notifications?lu=false', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});
```

### **Marquer comme lue :**
```javascript
await fetch(`/api/notifications/${notificationId}/mark-as-read`, {
    method: 'PATCH',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
});
```

## 🚀 **Avantages du Système**

1. **Sécurité** : Isolation complète des données par utilisateur
2. **Flexibilité** : Filtrage et pagination avancés
3. **Performance** : Requêtes optimisées avec eager loading
4. **Extensibilité** : Facile d'ajouter de nouveaux types de notifications
5. **Traçabilité** : Historique complet des notifications
6. **Automatisation** : Déclenchement automatique selon les événements

## 🔮 **Évolutions Futures**

- Notifications push en temps réel
- Templates de notifications personnalisables
- Notifications groupées
- Préférences de notification par utilisateur
- Notifications par email automatiques 