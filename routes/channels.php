<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('gestionnaires', function ($user) {
    return true; // Tu peux vérifier ici selon le rôle/auth
});