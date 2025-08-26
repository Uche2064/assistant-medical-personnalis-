# 🔴 Configuration Temps Réel avec Reverb et Angular

## 📋 **BILAN DE LA CONFIGURATION ACTUELLE**

### ✅ **CE QUI EST DÉJÀ CONFIGURÉ :**

1. **Backend Laravel** :

    - Package `laravel/reverb` installé
    - Configuration Reverb dans `config/broadcasting.php`
    - Broadcasting activé dans `bootstrap/app.php`
    - Channels définis dans `routes/channels.php`
    - Événements créés : `NouveauCompteCree`, `NouvelleDemandeAdhesion`
    - Service `NotificationService` modifié pour dispatcher les événements

2. **Frontend** :
    - Configuration Echo dans `resources/js/echo.js`
    - Variables d'environnement VITE configurées

### ❌ **CE QUI MANQUE POUR FONCTIONNER :**

1. **Variables d'environnement** dans le fichier `.env`
2. **Démarrage du serveur Reverb**
3. **Configuration côté Angular**

---

## 🚀 **ÉTAPES POUR ACTIVER LE TEMPS RÉEL**

### 1. **Configuration des Variables d'Environnement**

Ajoutez ces variables dans votre fichier `.env` :

```bash
# Configuration Broadcasting
BROADCAST_CONNECTION=reverb

# Configuration Reverb
REVERB_APP_KEY=votre_clé_reverb
REVERB_APP_SECRET=votre_secret_reverb
REVERB_APP_ID=votre_app_id_reverb
REVERB_HOST=votre_host_reverb
REVERB_PORT=443
REVERB_SCHEME=https

# Variables pour le frontend (Angular)
VITE_REVERB_APP_KEY=votre_clé_reverb
VITE_REVERB_HOST=votre_host_reverb
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

### 2. **Démarrer le Serveur Reverb**

```bash
# Installer Reverb globalement si pas déjà fait
composer global require laravel/reverb

# Démarrer le serveur Reverb
php artisan reverb:start
```

### 3. **Configuration Côté Angular**

#### A. Installer les dépendances

```bash
npm install laravel-echo pusher-js
```

#### B. Créer le service Echo

Créez un fichier `src/app/services/echo.service.ts` :

```typescript
import Echo from "laravel-echo";
import Pusher from "pusher-js";

export class EchoService {
    private echo: Echo;

    constructor() {
        // Configuration Pusher pour Reverb
        window.Pusher = Pusher;

        this.echo = new Echo({
            broadcaster: "reverb",
            key: environment.REVERB_APP_KEY,
            wsHost: environment.REVERB_HOST,
            wsPort: environment.REVERB_PORT || 80,
            wssPort: environment.REVERB_PORT || 443,
            forceTLS: environment.REVERB_SCHEME === "https",
            enabledTransports: ["ws", "wss"],
            auth: {
                headers: {
                    Authorization: `Bearer ${this.getAuthToken()}`,
                    Accept: "application/json",
                },
            },
        });
    }

    private getAuthToken(): string {
        // Récupérer le token JWT depuis le localStorage ou un service d'auth
        return localStorage.getItem("auth_token") || "";
    }

    // Écouter les nouveaux comptes créés
    listenToNewAccounts(callback: (data: any) => void) {
        this.echo.private("techniciens").listen("nouveau.compte", callback);
    }

    // Écouter les nouvelles demandes d'adhésion
    listenToNewDemandes(callback: (data: any) => void) {
        this.echo
            .private("techniciens")
            .listen("nouvelle.demande.adhésion", callback);
    }

    // Écouter les nouvelles demandes prestataires (médecins contrôleurs)
    listenToNewPrestataires(callback: (data: any) => void) {
        this.echo
            .private("medecins_controleurs")
            .listen("nouvelle.demande.adhésion", callback);
    }

    // Se désabonner
    disconnect() {
        this.echo.disconnect();
    }
}
```

#### C. Créer le fichier d'environnement

Dans `src/environments/environment.ts` :

```typescript
export const environment = {
    production: false,
    apiUrl: "http://localhost:8000/api",
    REVERB_APP_KEY: "votre_clé_reverb",
    REVERB_HOST: "votre_host_reverb",
    REVERB_PORT: 443,
    REVERB_SCHEME: "https",
};
```

#### D. Utiliser le service dans les composants

```typescript
import { Component, OnInit, OnDestroy } from "@angular/core";
import { EchoService } from "../services/echo.service";

@Component({
    selector: "app-dashboard-technicien",
    template: `
        <div class="notifications">
            <div
                *ngFor="let notification of notifications"
                class="notification"
            >
                {{ notification.message }}
            </div>
        </div>
    `,
})
export class DashboardTechnicienComponent implements OnInit, OnDestroy {
    notifications: any[] = [];

    constructor(private echoService: EchoService) {}

    ngOnInit() {
        // Écouter les nouveaux comptes
        this.echoService.listenToNewAccounts((data) => {
            this.notifications.push({
                type: "nouveau_compte",
                message: `Nouveau compte ${data.user.type} créé : ${data.user.email}`,
                data: data,
            });

            // Afficher une notification toast
            this.showToast(`Nouveau compte ${data.user.type} créé !`);
        });

        // Écouter les nouvelles demandes
        this.echoService.listenToNewDemandes((data) => {
            this.notifications.push({
                type: "nouvelle_demande",
                message: `Nouvelle demande d'adhésion ${data.demande.type_demandeur}`,
                data: data,
            });

            this.showToast("Nouvelle demande d'adhésion reçue !");
        });
    }

    ngOnDestroy() {
        this.echoService.disconnect();
    }

    private showToast(message: string) {
        // Implémenter l'affichage de toast
        console.log("Toast:", message);
    }
}
```

#### E. Service de notification toast

```typescript
import { Injectable } from "@angular/core";
import { Subject } from "rxjs";

@Injectable({
    providedIn: "root",
})
export class ToastService {
    private toastSubject = new Subject<{ message: string; type: string }>();
    toast$ = this.toastSubject.asObservable();

    show(message: string, type: "success" | "error" | "info" = "info") {
        this.toastSubject.next({ message, type });
    }
}
```

### 4. **Test de la Configuration**

#### A. Tester côté backend

```bash
# Démarrer le serveur Laravel
php artisan serve

# Démarrer Reverb
php artisan reverb:start

# Dans un autre terminal, tester un événement
php artisan tinker
>>> event(new App\Events\NouveauCompteCree(App\Models\User::first(), 'test', []));
```

#### B. Tester côté Angular

1. Démarrer Angular : `ng serve`
2. Ouvrir la console du navigateur
3. Créer un nouveau compte via l'API
4. Vérifier que les événements arrivent en temps réel

---

## 🔧 **ÉVÉNEMENTS DISPONIBLES**

### 1. **NouveauCompteCree**

-   **Channel** : `techniciens` (privé)
-   **Événement** : `nouveau.compte`
-   **Déclenché** : Quand un compte physique ou entreprise est créé
-   **Données** :
    ```json
    {
        "user": {
            "id": 1,
            "email": "user@example.com",
            "type": "entreprise",
            "created_at": "01/01/2024 à 10:30"
        },
        "notification": {
            "type_notification": "nouveau_compte"
        }
    }
    ```

### 2. **NouvelleDemandeAdhesion**

-   **Channel** : `techniciens` ou `medecins_controleurs` (privé)
-   **Événement** : `nouvelle.demande.adhésion`
-   **Déclenché** : Quand une demande d'adhésion est soumise
-   **Données** :
    ```json
    {
        "demande": {
            "id": 1,
            "type_demandeur": "entreprise",
            "statut": "en_attente",
            "created_at": "01/01/2024 à 10:30",
            "user_email": "user@example.com"
        },
        "notification": {
            "type_notification": "nouvelle_demande_adhésion"
        }
    }
    ```

---

## 🚨 **DÉPANNAGE**

### Problèmes courants :

1. **Erreur de connexion WebSocket** :

    - Vérifier que Reverb est démarré
    - Vérifier les variables d'environnement
    - Vérifier les ports et le schéma HTTPS

2. **Événements non reçus** :

    - Vérifier l'authentification JWT
    - Vérifier les permissions des channels
    - Vérifier que l'utilisateur a le bon rôle

3. **Erreur CORS** :
    - Configurer CORS dans Laravel
    - Vérifier les headers d'autorisation

### Commandes utiles :

```bash
# Vérifier la configuration broadcasting
php artisan config:show broadcasting

# Tester un événement
php artisan tinker
>>> event(new App\Events\NouveauCompteCree(App\Models\User::first(), 'test', []));

# Voir les logs Reverb
tail -f storage/logs/laravel.log
```

---

## 📝 **NOTES IMPORTANTES**

1. **Sécurité** : Les channels privés nécessitent une authentification JWT valide
2. **Performance** : Reverb utilise WebSockets, plus performant que le polling
3. **Scalabilité** : Reverb peut être déployé sur plusieurs serveurs
4. **Fallback** : En cas de problème WebSocket, prévoir un fallback avec polling

---

## 🎯 **PROCHAINES ÉTAPES**

1. Configurer les variables d'environnement
2. Démarrer le serveur Reverb
3. Tester avec Angular
4. Ajouter d'autres événements selon les besoins
5. Implémenter la gestion d'erreurs et les retry
