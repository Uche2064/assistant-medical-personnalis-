<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos identifiants de connexion - SUNU Santé</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .header {
            background: #FF0000;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        h1 {
            color: #FF0000;
            font-size: 20px;
            text-align: center;
            margin-bottom: 15px;
        }
        p {
            line-height: 1.5;
            color: #333;
            margin-bottom: 15px;
        }
        .credentials {
            background-color: #f8f9fa;
            border: 1px solid #FF0000;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background: #FF0000;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: background 0.3s;
            text-align: center;
        }
        .button:hover {
            background: #CC0000;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>SUNU SANTÉ</h2>
        </div>
        
        <h1>🔑 Vos identifiants de connexion</h1>
        <p>Bienvenue {{ $user->personnel->prenoms }} {{ $user->personnel->nom }} ! Votre compte a été créé avec succès.</p>
        
        <div class="credentials">
            <p><strong>📧 Email :</strong> {{ $user->email }}</p>
            <p><strong>🔒 Mot de passe :</strong> {{ $password }}</p>
        </div>

        <p><strong>⚠️ Important :</strong> Changez votre mot de passe lors de votre première connexion.</p>

        <div style="text-align: center;">
            <a href="{{ env('FRONTEND_URL') }}/auth/login" class="button">🚀 Se connecter maintenant</a>
        </div>

        <p>💡 Conseils pour votre première connexion :</p>
        <ul>
            <li>Copiez vos identifiants</li>
            <li>Changez votre mot de passe</li>
            <li>Explorez votre tableau de bord</li>
        </ul>

        <p>📞 Besoin d'aide ? Contactez-nous à <strong>support@sunusante.sn</strong> ou au <strong>+221 33 XXX XX XX</strong>.</p>
        
        <div class="footer">
            <p><strong>SUNU Santé</strong> - Votre partenaire santé de confiance</p>
            <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; {{ date('Y') }} SUNU Santé. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
