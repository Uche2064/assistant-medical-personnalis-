    # API Notifications - Documentation Frontend

    ## 🎯 **Vue d'ensemble**

    Documentation des routes API pour implémenter le widget de notifications dans le frontend.

    ## 🔐 **Authentification**

    Toutes les routes nécessitent un token JWT dans le header :
    ```
    Authorization: Bearer {token}
    ```

    ## 📋 **Routes API**

    ### **1. Récupérer les notifications**

    #### **GET** `/api/notifications`

    **Description :** Récupère la liste paginée des notifications de l'utilisateur connecté.

    **Query Parameters :**
    - `lu` (boolean, optionnel) : Filtrer par statut lu/non lu
    - `type` (string, optionnel) : Filtrer par type (info, warning, error, success)
    - `per_page` (integer, optionnel) : Nombre d'éléments par page (défaut: 10)

    **Exemples de requêtes :**
    ```bash
    # Toutes les notifications
    GET /api/notifications

    # Seulement les non lues
    GET /api/notifications?lu=false

    # Seulement les notifications de type info
    GET /api/notifications?type=info

    # Avec pagination
    GET /api/notifications?per_page=20&lu=false
    ```

    **Réponse :**
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

    ### **2. Statistiques des notifications**

    #### **GET** `/api/notifications/stats`

    **Description :** Récupère les statistiques des notifications de l'utilisateur connecté.

    **Réponse :**
    ```json
    {
    "success": true,
    "message": "Statistiques des notifications récupérées.",
    "data": {
        "total": 15,
        "unread": 8,
        "read": 7,
        "par_type": {
        "info": 10,
        "warning": 3,
        "error": 2
        },
        "recentes": [
        {
            "id": 1,
            "titre": "Nouvelle fiche employé soumise",
            "message": "L'employé John Doe de l'entreprise ABC a soumis sa fiche.",
            "type": "info",
            "created_at": "2025-08-06T16:30:00.000000Z"
        }
        ]
    }
    }
    ```

    ### **3. Marquer une notification comme lue**

    #### **PATCH** `/api/notifications/{id}/mark-as-read`

    **Description :** Marque une notification spécifique comme lue.

    **Paramètres :**
    - `id` (integer) : ID de la notification

    **Réponse :**
    ```json
    {
    "success": true,
    "message": "Notification marquée comme lue.",
    "data": {
        "id": 1,
        "user_id": 123,
        "titre": "Nouveau compte créé",
        "message": "Un nouveau compte personne physique a été créé : john@example.com",
        "type": "info",
        "lu": true,
        "donnees": {
        "user_id": 456,
        "user_email": "john@example.com",
        "user_type": "personne physique",
        "date_creation": "06/08/2025 à 16:30",
        "type_notification": "nouveau_compte"
        },
        "created_at": "2025-08-06T16:30:00.000000Z",
        "updated_at": "2025-08-06T17:00:00.000000Z"
    }
    }
    ```

    ### **4. Marquer une notification comme non lue**

    #### **PATCH** `/api/notifications/{id}/mark-as-unread`

    **Description :** Marque une notification spécifique comme non lue.

    **Paramètres :**
    - `id` (integer) : ID de la notification

    **Réponse :**
    ```json
    {
    "success": true,
    "message": "Notification marquée comme non lue.",
    "data": {
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
        "updated_at": "2025-08-06T17:00:00.000000Z"
    }
    }
    ```

    ### **5. Marquer toutes les notifications comme lues**

    #### **PATCH** `/api/notifications/mark-all-as-read`

    **Description :** Marque toutes les notifications non lues comme lues.

    **Réponse :**
    ```json
    {
    "success": true,
    "message": "5 notification(s) marquée(s) comme lue(s).",
    "data": {
        "notifications_marquees": 5
    }
    }
    ```

    ### **6. Supprimer une notification**

    #### **DELETE** `/api/notifications/{id}`

    **Description :** Supprime une notification spécifique.

    **Paramètres :**
    - `id` (integer) : ID de la notification

    **Réponse :**
    ```json
    {
    "success": true,
    "message": "Notification supprimée avec succès.",
    "data": null
    }
    ```

    ### **7. Supprimer toutes les notifications lues**

    #### **DELETE** `/api/notifications/destroy-read`

    **Description :** Supprime toutes les notifications marquées comme lues.

    **Réponse :**
    ```json
    {
    "success": true,
    "message": "3 notification(s) supprimée(s).",
    "data": {
        "notifications_supprimees": 3
    }
    }
    ```

    ## 📊 **Types de notifications**

    ### **Types disponibles :**
    - `info` : Informations générales
    - `warning` : Avertissements
    - `error` : Erreurs
    - `success` : Succès

    ### **Données contextuelles :**
    Chaque notification contient des données spécifiques dans le champ `donnees` selon le type :

    - **Nouveau compte** : `user_id`, `user_email`, `user_type`, `date_creation`
    - **Nouvelle fiche employé** : `employe_id`, `employe_nom`, `employe_prenoms`, `employe_email`, `date_soumission`
    - **Demande d'adhésion** : `demande_id`, `user_email`, `type_demandeur`, `date_soumission`

    ## 🔧 **Configuration**

    ### **Headers requis :**
    ```javascript
    headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
    }
    ```

    ## 🎯 **Exemples d'utilisation**

    ### **Récupérer les notifications non lues :**
    ```javascript
    fetch('/api/notifications?lu=false', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
    })
    .then(response => response.json())
    .then(data => {
    console.log('Notifications non lues:', data.data.notifications.data);
    });
    ```

    ### **Marquer une notification comme lue :**
    ```javascript
    fetch(`/api/notifications/${notificationId}/mark-as-read`, {
    method: 'PATCH',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
    })
    .then(response => response.json())
    .then(data => {
    console.log('Notification marquée comme lue');
    });
    ```

    ### **Récupérer les statistiques :**
    ```javascript
    fetch('/api/notifications/stats', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
    })
    .then(response => response.json())
    .then(data => {
    console.log('Nombre de notifications non lues:', data.data.unread);
    });
    ```

    ## 🚀 **Bonnes pratiques**

    1. **Polling** : Mettre à jour les notifications toutes les 30 secondes
    2. **Gestion d'erreurs** : Gérer les erreurs 401 (token expiré) et 500
    3. **Cache** : Mettre en cache les notifications pour éviter les requêtes inutiles
    4. **UX** : Afficher un indicateur visuel pour les notifications non lues
    5. **Performance** : Limiter le nombre de requêtes simultanées

    ## ⚡ **Notifications en temps réel (WebSockets)**

    ### **Configuration WebSocket :**

    Pour une expérience optimale, implémentez les WebSockets pour recevoir les notifications en temps réel :

    ```javascript
    // Connexion WebSocket
    const ws = new WebSocket('ws://localhost:6001');

    // Écouter les nouvelles notifications
    ws.onmessage = function(event) {
    const data = JSON.parse(event.data);
    
    if (data.type === 'notification') {
        // Ajouter la nouvelle notification à la liste
        addNewNotification(data.notification);
        
        // Afficher une notification toast
        showToast(data.notification.titre, data.notification.message);
        
        // Mettre à jour le compteur
        updateNotificationCount();
    }
    };

    // Gestion de la reconnexion
    ws.onclose = function() {
    console.log('WebSocket fermé, tentative de reconnexion...');
    setTimeout(() => {
        // Reconnecter après 5 secondes
        connectWebSocket();
    }, 5000);
    };
    ```

    ### **Événements WebSocket :**

    ```json
    {
    "type": "notification",
    "notification": {
        "id": 123,
        "titre": "Nouveau compte créé",
        "message": "Un nouveau compte personne physique a été créé",
        "type": "info",
        "created_at": "2025-08-06T16:30:00.000000Z"
    }
    }
    ```

    ### **Backend WebSocket (Laravel Echo + Pusher) :**

    ```php
    // Dans NotificationService.php
    public function createNotification($userId, $titre, $message, $type, $donnees = [])
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'titre' => $titre,
            'message' => $message,
            'type' => $type,
            'lu' => false,
            'donnees' => $donnees
        ]);

        // Diffuser en temps réel
        broadcast(new NotificationCreated($notification))->toUser($userId);

        return $notification;
    }
    ```

    ### **Alternative : Server-Sent Events (SSE)**

    Si WebSockets ne sont pas disponibles :

    ```javascript
    // Connexion SSE
    const eventSource = new EventSource('/api/notifications/stream');

    eventSource.onmessage = function(event) {
    const notification = JSON.parse(event.data);
    addNewNotification(notification);
    };

    eventSource.onerror = function() {
    console.log('SSE fermé, tentative de reconnexion...');
    // Reconnecter automatiquement
    };
    ```

    ### **Hybride : WebSocket + Polling de fallback**

    ```javascript
    class NotificationManager {
    constructor(token) {
        this.token = token;
        this.ws = null;
        this.pollingInterval = null;
        this.isWebSocketConnected = false;
    }

    connect() {
        // Essayer WebSocket d'abord
        this.connectWebSocket();
        
        // Fallback vers polling si WebSocket échoue
        setTimeout(() => {
        if (!this.isWebSocketConnected) {
            this.startPolling();
        }
        }, 3000);
    }

    connectWebSocket() {
        this.ws = new WebSocket('ws://localhost:6001');
        
        this.ws.onopen = () => {
        this.isWebSocketConnected = true;
        console.log('WebSocket connecté');
        };

        this.ws.onmessage = (event) => {
        const data = JSON.parse(event.data);
        if (data.type === 'notification') {
            this.handleNewNotification(data.notification);
        }
        };

        this.ws.onclose = () => {
        this.isWebSocketConnected = false;
        this.startPolling();
        };
    }

    startPolling() {
        this.pollingInterval = setInterval(() => {
        this.fetchNewNotifications();
        }, 30000);
    }

    handleNewNotification(notification) {
        // Ajouter à la liste
        this.addNotificationToList(notification);
        
        // Afficher toast
        this.showToast(notification);
        
        // Mettre à jour compteur
        this.updateCounter();
    }
    }
    ```

    ### **Avantages du temps réel :**

    1. **Réactivité** : Notifications instantanées
    2. **UX améliorée** : Pas de délai d'attente
    3. **Efficacité** : Moins de requêtes HTTP
    4. **Engagement** : Meilleure interaction utilisateur

    ### **Recommandation :**

    Pour une implémentation optimale, utilisez **WebSockets** avec **polling de fallback** :
    - WebSockets pour la réactivité
    - Polling comme backup si WebSocket échoue
    - Gestion automatique de la reconnexion 