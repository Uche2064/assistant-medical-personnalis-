<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['api']]);

// Channel pour les techniciens
Broadcast::channel('techniciens', function ($user) {
    return $user->hasRole('technicien');
}, ['guards' => ['api']]);

// Channel pour les médecins contrôleurs
Broadcast::channel('medecins_controleurs', function ($user) {
    return $user->hasRole('medecin_controleur');
}, ['guards' => ['api']]);

// Channel pour les gestionnaires
Broadcast::channel('gestionnaires', function ($user) {
    return $user->hasRole('gestionnaire');
}, ['guards' => ['api']]);

