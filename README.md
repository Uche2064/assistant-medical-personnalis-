<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Assistant Médical Personnalisé - SUNU Santé

## Objectif du Projet

Dans le cadre de l'obtention du diplôme de licence professionnelle en génie logiciel et système d'informations de l'IAI TOGO, nous avons réalisé un stage à SUNU Santé où, il a été porté à notre égard le thème suivant « Mise en place d'une solution digitale multi-plateforme d'assistance médical personnalisé ». L'objectif de ce projet est de concevoir et réaliser une solution permettant de simplifier et d'accélérer le traitement des demandes de prise en charge ou entente préalable et la procédure de remboursement sinistre, tout en limitant les erreurs liées à la saisie des informations, de garantir une traçabilité efficace des réclamations des clients. Ce projet sera particulièrement bénéfique pour le département médical, département comptable et département technique en servant d'outil de gestion, de contrôle médical et de suivi des activités. Pour mener à bien ce projet, nous avons réalisé la modélisation avec le langage de modélisation UML, la méthode agile SCRUM pour la bonne gestion du projet, ce qui nous a permis de maitriser la complexité du système. En terme technique pour la réalisation du projet, nous avons utilisé le langage de programmation PHP avec le Framework LARAVEL pour le backend, associé au Framework Angular basé sur le langage TypeScript afin de concevoir l'interface web. Ainsi que le Framework Flutter pour les applications mobiles avec le système de gestion de bases de données PostgreSQL.

## Technologies Utilisées

- Backend: Laravel (PHP)
- Frontend Web: Angular (TypeScript)
- Application Mobile: Flutter
- Base de Données: PostgreSQL
- Gestion de Projet: Méthodologie Agile SCRUM
- Modélisation: UML

# Documentation du Processus de Création de Compte

Ce document détaille le fonctionnement du processus d'inscription des utilisateurs dans l'application.

## Endpoint

- **URL** : `/api/v1/auth/register`
- **Méthode** : `POST`
- **Description** : Permet à un nouvel utilisateur de créer un compte. Le processus gère différents types de personnes (physiques et morales) et inclut la création de l'utilisateur, de son profil détaillé, l'assignation de rôle, et l'envoi d'email de confirmation.

## Flux de Travail (Workflow)

Le processus de création de compte suit les étapes suivantes :

1.  **Validation des Données**
    -   Le système valide d'abord les données reçues en utilisant la `RegisterRequest`. Toutes les règles de validation (champs obligatoires, format de l'email, etc.) sont appliquées à ce stade.

2.  **Gestion de la Photo de Profil (Optionnel)**
    -   Si une `photo_url` est fournie, l'image est téléchargée sur le serveur via le `ImageUploadHelper` et son chemin d'accès est stocké.

3.  **Début de la Transaction**
    -   Pour garantir l'intégrité des données, toutes les opérations de base de données sont encapsulées dans une transaction (`DB::beginTransaction()`). En cas d'erreur à n'importe quelle étape, toutes les modifications sont annulées (`DB::rollBack()`).

4.  **Création de l'Entité `User`**
    -   Un nouvel enregistrement est créé dans la table `users` avec les informations de base :
        -   `email`
        -   `password` (qui est haché)
        -   `contact`
        -   `adresse`
        -   `photo_url` (le cas échéant)
        -   `profession`
        -   `email_verified_at` est défini à la date actuelle, marquant l'email comme vérifié.

5.  **Création de l'Entité `Personnes`**
    -   Un enregistrement associé est créé dans la table `personnes` pour stocker les détails du profil :
        -   `user_id` (lien vers l'utilisateur)
        -   `type_personne` (ex: `MORALE`, `PHYSIQUE`, `AUTRE`)
        -   `raison_sociale` (pour les personnes morales)
        -   `nom` et `prenoms` (pour les personnes physiques)
        -   `date_naissance`
        -   `sexe`

6.  **Assignation de Rôle**
    -   L'utilisateur se voit attribuer le rôle par défaut `user` (`RoleEnum::USER->value`).

7.  **Cas Spécifique : Création d'un Lien d'Invitation pour les Entreprises**
    -   Si le `type_personne` est `AUTRE` (correspondant à une entreprise), le système génère un lien d'invitation unique pour que l'entreprise puisse inviter ses employés.
    -   Un token `UUID` est généré et stocké dans la table `invitation_employes` avec une date d'expiration (7 jours).
    -   Le lien d'invitation est formaté (`/v1/Api/employes/formulaire/{token}`).

8.  **Envoi d'un Email de Confirmation**
    -   Une tâche (`SendEmailJob`) est ajoutée à la file d'attente pour envoyer un email de bienvenue et de confirmation à l'utilisateur.

9.  **Fin de la Transaction**
    -   Si toutes les étapes précédentes réussissent, la transaction est validée (`DB::commit()`).

10. **Réponse**
    -   Le système retourne une réponse JSON avec un statut de succès et les informations de l'utilisateur créé.

## Gestion des Erreurs

-   En cas d'échec d'une des opérations en base de données, la transaction est annulée, garantissant qu'aucune donnée partielle n'est sauvegardée.
-   Une réponse d'erreur standardisée est retournée, indiquant la nature du problème.
