# Résumé des Modèles Eloquent Créés - SUNU Santé

## Vue d'ensemble

Tous les modèles Eloquent ont été créés avec leurs relations, méthodes utilitaires et accesseurs appropriés pour la nouvelle architecture de base de données.

## Modèles Principaux

### 1. **User** - Utilisateur central
- **Relations** : `personnel`, `client`, `entreprise`, `assure`, `prestataire`, `notifications`, `conversations`, `messages`
- **Traits** : `HasApiTokens`, `HasFactory`, `Notifiable`, `HasRoles`, `SoftDeletes`
- **Méthodes** : `hasRole()`, `getFullNameAttribute()`, `getUserTypeAttribute()`

### 2. **Personnel** - Employés SUNU Santé
- **Relations** : `user`, `gestionnaire`, `personnels`, `clients`, `contrats`, `categoriesGaranties`, `garanties`, `prestataires`
- **Méthodes** : `genererCodeParainage()`, `getFullNameAttribute()`, `isGestionnaire()`, `isCommercial()`, `isTechnicien()`, `isMedecinControleur()`, `isComptable()`

### 3. **Client** - Prospects
- **Relations** : `user`, `commercial`, `assures`, `demandesAdhesions`
- **Méthodes** : `isProspect()`, `isClient()`, `isAssure()`, `isPhysique()`, `isMoral()`, `promote()`, `getFullNameAttribute()`

### 4. **Entreprise** - Clients moraux
- **Relations** : `user`, `assures`, `invitationEmployes`, `demandesAdhesions`
- **Méthodes** : `isActive()`, `isInactive()`, `generateAdhesionLink()`, `getNameAttribute()`, `getActiveEmployeesCountAttribute()`

### 5. **Assure** - Assurés principaux et bénéficiaires
- **Relations** : `user`, `client`, `entreprise`, `assurePrincipal`, `beneficiaires`, `contrat`, `sinistres`
- **Méthodes** : `isPrincipal()`, `isBeneficiaire()`, `isActive()`, `isInactive()`, `isSuspended()`, `getFullNameAttribute()`, `getTypeAttribute()`, `getSourceAttribute()`

### 6. **Contrat** - Contrats d'assurance
- **Relations** : `technicien`, `assures`, `categoriesGaranties`
- **Méthodes** : `generateNumeroPolice()`, `isProposed()`, `isAccepted()`, `isRefused()`, `isActive()`, `isExpired()`, `isCancelled()`, `accept()`, `refuse()`, `activate()`, `getPrimeTotaleAttribute()`, `getCommissionAmountAttribute()`, `getDureeAttribute()`, `isValid()`

### 7. **CategorieGarantie** - Catégories de garanties
- **Relations** : `medecinControleur`, `garanties`, `contrats`
- **Méthodes** : `isActive()`, `getTotalCoverageAttribute()`

### 8. **Garantie** - Garanties spécifiques
- **Relations** : `categorieGarantie`, `medecinControleur`
- **Méthodes** : `getCoverageAmountAttribute()`, `isWithinLimit()`

### 9. **Prestataire** - Prestataires de soins
- **Relations** : `user`, `medecinControleur`, `sinistres`, `factures`, `demandesAdhesions`
- **Méthodes** : `isPending()`, `isValidated()`, `isRejected()`, `isSuspended()`, `validate()`, `reject()`, `suspend()`, `getNameAttribute()`, `getTypeFrancaisAttribute()`

### 10. **DemandeAdhesion** - Demandes d'adhésion
- **Relations** : `user`, `validePar`, `reponsesQuestionnaire`
- **Méthodes** : `isPending()`, `isValidated()`, `isRejected()`, `validate()`, `reject()`, `getTypeDemandeurFrancaisAttribute()`

### 11. **Question** - Questions dynamiques
- **Relations** : `creePar`, `reponses`
- **Scopes** : `active()`, `byDestinataire()`, `required()`
- **Méthodes** : `isActive()`, `isRequired()`, `getTypeDonneeFrancaisAttribute()`, `getDestinataireFrancaisAttribute()`

### 12. **ReponseQuestionnaire** - Réponses aux questionnaires
- **Relations** : `question`, `demandeAdhesion`, `personne` (polymorphique)
- **Méthodes** : `getReponseValueAttribute()`, `setReponseValueAttribute()`

### 13. **Sinistre** - Sinistres
- **Relations** : `assure`, `prestataire`, `factures`
- **Méthodes** : `isDeclared()`, `isInProgress()`, `isTreated()`, `isClosed()`, `updateStatus()`, `getTotalAmountClaimedAttribute()`, `getTotalAmountToReimburseAttribute()`

### 14. **Facture** - Factures des prestataires
- **Relations** : `sinistre`, `prestataire`, `technicien`, `medecin`, `comptable`
- **Méthodes** : `isPending()`, `isValidatedByTechnicien()`, `isValidatedByMedecin()`, `isAuthorizedByComptable()`, `isReimbursed()`, `isRejected()`, `validateByTechnicien()`, `validateByMedecin()`, `authorizeByComptable()`, `reject()`, `markAsReimbursed()`, `getStatutFrancaisAttribute()`, `getDifferenceAttribute()`

## Modèles de Support

### 15. **Notification** - Notifications système
- **Relations** : `user`
- **Scopes** : `unread()`, `read()`, `byType()`
- **Méthodes** : `markAsRead()`, `markAsUnread()`, `isRead()`, `isUnread()`

### 16. **Conversation** - Conversations entre utilisateurs
- **Relations** : `user1`, `user2`, `messages`, `latestMessage`
- **Méthodes** : `getOtherUser()`, `hasUser()`, `getUnreadCount()`, `markAsRead()`

### 17. **Message** - Messages dans les conversations
- **Relations** : `conversation`, `expediteur`
- **Scopes** : `unread()`, `read()`
- **Méthodes** : `markAsRead()`, `markAsUnread()`, `isRead()`, `isUnread()`, `isSentBy()`

### 18. **InvitationEmploye** - Liens d'invitation pour entreprises
- **Relations** : `entreprise`
- **Méthodes** : `isExpired()`, `isValid()`, `generateToken()`, `createInvitation()`, `getInvitationUrlAttribute()`

### 19. **Otp** - Codes OTP pour authentification
- **Méthodes** : `isExpired()`, `isValid()`, `generateOtp()`, `verifyOtp()`, `cleanExpired()`

## Fonctionnalités Clés

### Relations Polymorphiques
- `ReponseQuestionnaire` → `personne` (peut pointer vers différents modèles)

### Relations Auto-référentielles
- `Personnel` → `gestionnaire` (un personnel peut gérer d'autres personnels)
- `Assure` → `assure_principal` (un assuré peut avoir des bénéficiaires)

### Relations Many-to-Many
- `Contrat` ↔ `CategorieGarantie` (via table pivot avec `couverture`)

### Méthodes Utilitaires
- Génération de codes uniques (`generateNumeroPolice`, `genererCodeParainage`, `generateToken`)
- Vérifications de statut (`isActive`, `isPending`, `isValidated`, etc.)
- Calculs automatiques (`getPrimeTotaleAttribute`, `getCommissionAmountAttribute`)
- Accesseurs pour noms complets et traductions françaises

### Scopes Eloquent
- Filtrage par statut (`active`, `unread`, `read`)
- Filtrage par type (`byDestinataire`, `byType`)
- Filtrage par obligation (`required`)

## Prochaines Étapes

1. **Tester les migrations** : `php artisan migrate:fresh`
2. **Créer les seeders** pour peupler la base de données
3. **Mettre à jour les contrôleurs** existants
4. **Implémenter les nouveaux workflows** métier
5. **Créer les tests unitaires** pour les modèles
6. **Documenter les API** avec les nouvelles structures 