<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Réussie</title>
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 24px;
            color: #FF0000;
        }
        p {
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background-color: #FF0000;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .button:hover {
            background-color: #FF0000;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h1>Connexion Réussie !</h1>
        <p>Bonjour {{ $user->name ?? 'Utilisateur' }},</p>
        <p>Vous êtes maintenant <strong>connecté avec succès</strong> à votre compte.</p>
        <p><strong>Date :</strong> {{ date('d/m/Y à H:i') }}</p>
        <p><strong>Adresse IP :</strong> {{ $ip_address ?? 'Non disponible' }}</p>
        <p><strong>Navigateur :</strong> {{ $user_agent ?? 'Non disponible' }}</p>
        <p class="warning">Si vous n'êtes pas à l'origine de cette connexion, veuillez nous contacter immédiatement.</p>
        <a href="{{ $dashboard_url ?? '#' }}" class="button">Accéder au Tableau de Bord</a>
    </div>
    <div class="footer">
        <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        <p>&copy; {{ date('Y') }} {{ 'Sunu Santé' }}. Tous droits réservés.</p>
    </div>
</body>
</html>