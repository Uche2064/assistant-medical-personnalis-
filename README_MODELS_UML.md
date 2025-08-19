# Diagramme de Classe UML - AMP Backend

Ce document présente le diagramme de classe UML de tous les modèles de l'application AMP Backend, un système de gestion d'assurance médicale.

## Vue d'ensemble du système

Le système AMP Backend gère les demandes d'adhésion, les contrats d'assurance, les sinistres, les factures et les prestataires de soins. Il supporte différents types d'utilisateurs : personnel, entreprises, assurés, et prestataires.

## Diagramme de Classe UML

### Classes principales

#### 1. User (Utilisateur)
```
+------------------+
|      User        |
+------------------+
| - id: bigint     |
| - email: string  |
| - contact: string|
| - password: string|
| - adresse: string|
| - photo: string  |
| - est_actif: bool|
| - email_verified_at: datetime|
| - mot_de_passe_a_changer: bool|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + getJWTIdentifier()|
| + getJWTCustomClaims()|
| + getFullNameAttribute()|
| + getUserTypeAttribute()|
| + genererMotDePasse()|
+------------------+
```

#### 2. Personnel
```
+------------------+
|    Personnel     |
+------------------+
| - id: bigint     |
| - nom: string    |
| - prenoms: string|
| - sexe: enum     |
| - date_naissance: date|
| - code_parainage: string|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + getFullNameAttribute()|
| + isGestionnaire()|
| + isCommercial() |
| + isTechnicien() |
| + isMedecinControleur()|
| + isComptable()  |
| + genererCodeParainage()|
+------------------+
```

#### 3. Entreprise
```
+------------------+
|    Entreprise    |
+------------------+
| - id: bigint     |
| - raison_sociale: string|
| - statut: enum   |
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isActive()     |
| + isInactive()   |
| + generateAdhesionLink()|
| + getNameAttribute()|
| + getActiveEmployeesCountAttribute()|
+------------------+
```

#### 4. Assure
```
+------------------+
|      Assure      |
+------------------+
| - id: bigint     |
| - email: string  |
| - nom: string    |
| - prenoms: string|
| - date_naissance: date|
| - sexe: enum     |
| - lien_parente: enum|
| - est_principal: bool|
| - profession: string|
| - contact: string|
| - photo: string  |
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isPrincipal()  |
| + isBeneficiaire()|
| + isActive()     |
| + isInactive()   |
| + isSuspended()  |
| + getFullNameAttribute()|
| + getTypeAttribute()|
| + getSourceAttribute()|
| + hasContratActif()|
| + getContratAssocie()|
+------------------+
```

#### 5. Contrat
```
+------------------+
|     Contrat      |
+------------------+
| - id: bigint     |
| - type_contrat: string|
| - prime_standard: decimal|
| - frais_gestion: decimal|
| - couverture_moyenne: decimal|
| - couverture: decimal|
| - categories_garanties_standard: array|
| - est_actif: bool|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + generateNumeroPolice()|
| + isProposed()   |
| + isAccepted()   |
| + isRefused()    |
| + isActive()     |
| + isExpired()    |
| + isCancelled()  |
| + accept()       |
| + refuse()       |
| + activate()     |
| + getPrimeTotaleAttribute()|
| + getCommissionAmountAttribute()|
| + isValid()      |
+------------------+
```

#### 6. DemandeAdhesion
```
+------------------+
| DemandeAdhesion  |
+------------------+
| - id: bigint     |
| - type_demandeur: enum|
| - statut: enum   |
| - motif_rejet: text|
| - code_parainage: string|
| - valider_a: datetime|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isPending()    |
| + isValidated()  |
| + isRejected()   |
| + validate()     |
| + reject()       |
| + getTypeDemandeurFrancaisAttribute()|
+------------------+
```

#### 7. Prestataire
```
+------------------+
|   Prestataire    |
+------------------+
| - id: bigint     |
| - type_prestataire: enum|
| - raison_sociale: string|
| - documents_requis: array|
| - code_parrainage: string|
| - statut: enum   |
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isPending()    |
| + isValidated()  |
| + isRejected()   |
| + isSuspended()  |
| + validate()     |
| + reject()       |
| + suspend()      |
| + getNameAttribute()|
| + getTypeFrancaisAttribute()|
+------------------+
```

#### 8. Facture
```
+------------------+
|     Facture      |
+------------------+
| - id: bigint     |
| - numero_facture: string|
| - montant_reclame: decimal|
| - montant_a_rembourser: decimal|
| - diagnostic: text|
| - ticket_moderateur: decimal|
| - statut: enum   |
| - motif_rejet: text|
| - est_valide_par_technicien: bool|
| - valide_par_technicien_a: datetime|
| - est_valide_par_medecin: bool|
| - valide_par_medecin_a: datetime|
| - est_autorise_par_comptable: bool|
| - autorise_par_comptable_a: datetime|
| - motif_rejet_technicien: text|
| - rejet_par_technicien_a: datetime|
| - motif_rejet_medecin: text|
| - rejet_par_medecin_a: datetime|
| - motif_rejet_comptable: text|
| - rejet_par_comptable_a: datetime|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isPending()    |
| + isValidatedByTechnicien()|
| + isValidatedByMedecin()|
| + isAuthorizedByComptable()|
| + isReimbursed() |
| + isRejectedByTechnicien()|
| + isRejectedByMedecin()|
| + isRejectedByComptable()|
| + isRejected()   |
| + canBeModified()|
| + resetToPending()|
| + validateByTechnicien()|
| + validateByMedecin()|
| + authorizeByComptable()|
| + rejectByTechnicien()|
| + rejectByMedecin()|
| + rejectByComptable()|
| + reject()       |
| + markAsReimbursed()|
| + getStatutFrancaisAttribute()|
| + getDifferenceAttribute()|
+------------------+
```

#### 9. CategorieGarantie
```
+------------------+
| CategorieGarantie|
+------------------+
| - id: bigint     |
| - libelle: string|
| - description: text|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isActive()     |
| + getTotalCoverageAttribute()|
+------------------+
```

#### 10. Garantie
```
+------------------+
|     Garantie     |
+------------------+
| - id: bigint     |
| - libelle: string|
| - plafond: decimal|
| - prix_standard: decimal|
| - taux_couverture: decimal|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + getCoverageAmountAttribute()|
| + isWithinLimit()|
+------------------+
```

#### 11. Sinistre
```
+------------------+
|     Sinistre     |
+------------------+
| - id: bigint     |
| - description: text|
| - date_sinistre: date|
| - statut: enum   |
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isInProgress() |
| + isClosed()     |
| + updateStatus() |
| + getTotalAmountClaimedAttribute()|
| + getTotalAmountToReimburseAttribute()|
+------------------+
```

#### 12. Question
```
+------------------+
|     Question     |
+------------------+
| - id: bigint     |
| - libelle: string|
| - type_donnee: enum|
| - options: array |
| - destinataire: enum|
| - obligatoire: bool|
| - est_actif: bool|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + scopeActive()  |
| + scopeByDestinataire()|
| + scopeRequired()|
| + isActive()     |
| + isRequired()   |
| + scopeForDestinataire()|
| + getTypeDonneeFrancaisAttribute()|
| + getDestinataireFrancaisAttribute()|
+------------------+
```

#### 13. ReponseQuestionnaire
```
+------------------+
|ReponseQuestionnaire|
+------------------+
| - id: bigint     |
| - personne_type: string|
| - personne_id: bigint|
| - reponse_text: text|
| - reponse_bool: bool|
| - reponse_decimal: decimal|
| - reponse_date: date|
| - reponse_fichier: string|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + getReponseValueAttribute()|
| + setReponseValueAttribute()|
+------------------+
```

#### 14. ClientContrat
```
+------------------+
|  ClientContrat   |
+------------------+
| - id: bigint     |
| - type_client: string|
| - date_debut: date|
| - date_fin: date |
| - statut: enum   |
| - numero_police: string|
| - created_at: datetime|
| - updated_at: datetime|
+------------------+
| + genererNumeroPolice()|
| + isActif()      |
| + isExpire()     |
+------------------+
```

#### 15. PropositionContrat
```
+------------------+
| PropositionContrat|
+------------------+
| - id: bigint     |
| - commentaires_technicien: text|
| - statut: enum   |
| - date_acceptation: datetime|
| - date_refus: datetime|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isProposee()   |
| + getPrimeAttribute()|
| + getPrimeFormattedAttribute()|
| + getTauxCouvertureAttribute()|
| + getFraisGestionAttribute()|
| + getPrimeTotaleAttribute()|
| + getPrimeTotaleFormattedAttribute()|
| + isAcceptee()   |
| + isRefusee()    |
| + isExpiree()    |
| + accepter()     |
| + refuser()      |
| + expirer()      |
+------------------+
```

#### 16. Conversation
```
+------------------+
|   Conversation   |
+------------------+
| - id: bigint     |
| - dernier_message: text|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + getOtherUser() |
| + hasUser()      |
| + getUnreadCount()|
| + markAsRead()   |
+------------------+
```

#### 17. Message
```
+------------------+
|     Message      |
+------------------+
| - id: bigint     |
| - contenu: text  |
| - lu: bool       |
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + scopeUnread()  |
| + scopeRead()    |
| + markAsRead()   |
| + markAsUnread() |
| + isRead()       |
| + isUnread()     |
| + isSentBy()     |
+------------------+
```

#### 18. Notification
```
+------------------+
|   Notification   |
+------------------+
| - id: bigint     |
| - type: string   |
| - titre: string  |
| - message: text  |
| - data: array    |
| - lu: bool       |
| - created_at: datetime|
| - updated_at: datetime|
+------------------+
| + scopeUnread()  |
| + scopeRead()    |
| + scopeByType()  |
| + markAsRead()   |
| + markAsUnread() |
| + isRead()       |
| + isUnread()     |
+------------------+
```

#### 19. Otp
```
+------------------+
|       Otp        |
+------------------+
| - id: bigint     |
| - email: string  |
| - otp: string    |
| - expire_at: datetime|
| - verifier_a: datetime|
| - type: enum     |
| - created_at: datetime|
| - updated_at: datetime|
+------------------+
| + isExpired()    |
| + isValid()      |
| + generateOtp()  |
| + verifyOtp()    |
| + cleanExpired() |
+------------------+
```

#### 20. InvitationEmploye
```
+------------------+
| InvitationEmploye|
+------------------+
| - id: bigint     |
| - token: string  |
| - expire_at: datetime|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isExpired()    |
| + isValid()      |
| + generateToken()|
| + createInvitation()|
| + getInvitationUrlAttribute()|
+------------------+
```

#### 21. LigneFacture
```
+------------------+
|  LigneFacture    |
+------------------+
| - id: bigint     |
| - libelle_acte: string|
| - prix_unitaire: decimal|
| - quantite: integer|
| - prix_total: decimal|
| - taux_couverture: decimal|
| - montant_couvert: decimal|
| - ticket_moderateur: decimal|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + calculateCoverage()|
+------------------+
```

#### 22. ClientPrestataire
```
+------------------+
| ClientPrestataire|
+------------------+
| - id: bigint     |
| - type_prestataire: string|
| - statut: string |
| - created_at: datetime|
| - updated_at: datetime|
+------------------+
| + isActif()      |
| + getTypePrestataireLabel()|
+------------------+
```

## Relations entre les classes

### Relations principales

1. **User** ↔ **Personnel** (1:1)
2. **User** ↔ **Entreprise** (1:1)
3. **User** ↔ **Assure** (1:1)
4. **User** ↔ **Prestataire** (1:1)
5. **User** ↔ **DemandeAdhesion** (1:N)
6. **User** ↔ **ClientContrat** (1:N)
7. **User** ↔ **Notification** (1:N)
8. **User** ↔ **Conversation** (N:N)
9. **User** ↔ **Message** (1:N)

### Relations métier

1. **Personnel** ↔ **Personnel** (1:N)
2. **Personnel** ↔ **Contrat** (1:N)
3. **Personnel** ↔ **CategorieGarantie** (1:N)
4. **Personnel** ↔ **Garantie** (1:N)
5. **Personnel** ↔ **Prestataire** (1:N)
6. **Personnel** ↔ **DemandeAdhesion** (1:N)
7. **Personnel** ↔ **Facture** (1:N)
8. **Personnel** ↔ **Question** (1:N)
9. **Personnel** ↔ **PropositionContrat** (1:N)

### Relations d'assurance

1. **Entreprise** ↔ **Assure** (1:N)
2. **Entreprise** ↔ **InvitationEmploye** (1:N)
3. **Assure** ↔ **Assure** (1:N)
4. **Assure** ↔ **Contrat** (N:1)
5. **Assure** ↔ **Sinistre** (1:N)
6. **Assure** ↔ **ReponseQuestionnaire** (1:N)

### Relations de contrats et garanties

1. **Contrat** ↔ **CategorieGarantie** (N:N)
2. **CategorieGarantie** ↔ **Garantie** (1:N)
3. **ClientContrat** ↔ **Contrat** (N:1)
4. **ClientContrat** ↔ **ClientPrestataire** (1:N)
5. **PropositionContrat** ↔ **DemandeAdhesion** (N:1)
6. **PropositionContrat** ↔ **Contrat** (N:1)
7. **PropositionContrat** ↔ **Garantie** (N:N)

### Relations de facturation

1. **Sinistre** ↔ **Facture** (1:N)
2. **Prestataire** ↔ **Facture** (1:N)
3. **Prestataire** ↔ **Sinistre** (1:N)
4. **Facture** ↔ **LigneFacture** (1:N)
5. **LigneFacture** ↔ **Garantie** (N:1)

### Relations de communication

1. **Conversation** ↔ **Message** (1:N)
2. **Question** ↔ **ReponseQuestionnaire** (1:N)

## Enums utilisés

- **StatutClientEnum**: active, inactive
- **LienParenteEnum**: conjoint, enfant, parent, autre
- **SexeEnum**: masculin, feminin
- **StatutContratEnum**: propose, accepte, refuse, actif, expire, resilie
- **StatutDemandeAdhesionEnum**: en_attente, validee, rejetee
- **TypeDemandeurEnum**: client, entreprise, prestataire
- **StatutPrestataireEnum**: en_attente, valide, rejete, suspendu
- **TypePrestataireEnum**: pharmacie, centre_soins, optique, laboratoire
- **StatutFactureEnum**: en_attente, validee_technicien, validee_medecin, autorisee_comptable, remboursee, rejetee
- **StatutSinistreEnum**: en_cours, cloture
- **TypeDonneeEnum**: text, number, boolean, date, file
- **OtpTypeEnum**: verification, reset_password
- **StatutPropositionContratEnum**: proposee, acceptee, refusee, expiree

## Notes importantes

1. **Soft Deletes**: La plupart des modèles utilisent SoftDeletes pour la suppression logique
2. **Polymorphic Relations**: ReponseQuestionnaire utilise des relations polymorphiques
3. **Pivot Tables**: Plusieurs relations many-to-many utilisent des tables pivot avec des attributs supplémentaires
4. **Enums**: Le système utilise des enums pour les statuts et types
5. **Timestamps**: Tous les modèles incluent created_at et updated_at
6. **JWT**: Le modèle User implémente JWTSubject pour l'authentification
7. **Roles**: Le modèle User utilise Spatie Permission pour la gestion des rôles
