# Modèles Supprimés - Nettoyage Architecture

## Modèles supprimés lors de la refactorisation

### Modèles en double (anciens vs nouveaux)
1. **DemandesAdhesions.php** → Remplacé par **DemandeAdhesion.php**
2. **ReponsesQuestionnaire.php** → Remplacé par **ReponseQuestionnaire.php**
3. **InvitationEmployes.php** → Remplacé par **InvitationEmploye.php**
4. **CategoriesGaranties.php** → Remplacé par **CategorieGarantie.php**

### Modèles obsolètes
5. **Personnes.php** → Fonctionnalité intégrée dans User, Client, Assure
6. **ContratCategorieGarantie.php** → Relation gérée via table pivot
7. **EmployesTemp.php** → Fonctionnalité intégrée dans Assure
8. **BeneficiaireTemp.php** → Fonctionnalité intégrée dans Assure
9. **ClientContrat.php** → Relation directe Contrat ↔ Assure
10. **ReseauPrestatairee.php** → Fonctionnalité intégrée dans Prestataire

## Raisons de suppression

### 1. **Redondance**
- Les modèles en double créaient de la confusion
- Les nouveaux modèles ont une architecture plus claire

### 2. **Architecture simplifiée**
- `Personnes` était redondant avec `User`, `Client`, `Assure`
- Les tables temporaires (`*Temp`) ne sont plus nécessaires

### 3. **Relations optimisées**
- `ContratCategorieGarantie` → Relation many-to-many directe
- `ClientContrat` → Relation directe via `Assure`

### 4. **Cohérence**
- Tous les modèles suivent maintenant la même convention de nommage
- Relations claires et sans ambiguïté

## Modèles conservés (19 au total)

### Modèles principaux (14)
1. **User.php** - Utilisateur central
2. **Personnel.php** - Employés SUNU Santé
3. **Client.php** - Prospects
4. **Entreprise.php** - Clients moraux
5. **Assure.php** - Assurés principaux et bénéficiaires
6. **Contrat.php** - Contrats d'assurance
7. **CategorieGarantie.php** - Catégories de garanties
8. **Garantie.php** - Garanties spécifiques
9. **Prestataire.php** - Prestataires de soins
10. **DemandeAdhesion.php** - Demandes d'adhésion
11. **Question.php** - Questions dynamiques
12. **ReponseQuestionnaire.php** - Réponses aux questionnaires
13. **Sinistre.php** - Sinistres
14. **Facture.php** - Factures des prestataires

### Modèles de support (5)
15. **Notification.php** - Notifications système
16. **Conversation.php** - Conversations entre utilisateurs
17. **Message.php** - Messages dans les conversations
18. **InvitationEmploye.php** - Liens d'invitation pour entreprises
19. **Otp.php** - Codes OTP pour authentification

## Avantages du nettoyage

1. **Clarté** : Architecture plus simple et compréhensible
2. **Performance** : Moins de modèles = moins de complexité
3. **Maintenance** : Code plus facile à maintenir
4. **Cohérence** : Conventions de nommage uniformes
5. **Évolutivité** : Structure prête pour les futures fonctionnalités 