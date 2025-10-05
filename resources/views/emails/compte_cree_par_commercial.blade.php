<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre compte SUNU Santé a été créé</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2c5aa0;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .credentials {
            background-color: #e8f4fd;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #2c5aa0;
        }
        .button {
            display: inline-block;
            background-color: #2c5aa0;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏥 SUNU Santé</h1>
        <h2>Votre compte a été créé</h2>
    </div>
    
    <div class="content">
        <p>Bonjour {{ $user->personne->prenoms ?? $user->personne->nom }},</p>
        
        <p>Votre compte SUNU Santé a été créé avec succès par notre commercial <strong>{{ $commercial->personne->prenoms ?? $commercial->personne->nom }}</strong>.</p>
        
        <div class="credentials">
            <h3>🔑 Vos informations de connexion :</h3>
            <p><strong>Email :</strong> {{ $user->email }}</p>
            <p><strong>Mot de passe temporaire :</strong> <code>{{ $mot_de_passe }}</code></p>
            <p><strong>Type de compte :</strong> {{ ucfirst($type_client) }}</p>
        </div>
        
        <h3>📋 Prochaines étapes :</h3>
        <ol>
            <li>Connectez-vous à votre compte avec les informations ci-dessus</li>
            <li><strong>Changez votre mot de passe</strong> lors de votre première connexion</li>
            <li>Complétez votre profil si nécessaire</li>
            <li>Explorez les fonctionnalités de votre assurance santé</li>
        </ol>
        
        <p><strong>⚠️ Important :</strong> Pour des raisons de sécurité, vous devrez changer votre mot de passe lors de votre première connexion.</p>
        
        <div style="text-align: center;">
            <a href="{{ config('app.frontend_url') }}/login" class="button">Se connecter maintenant</a>
        </div>
        
        <h3>📞 Besoin d'aide ?</h3>
        <p>Si vous avez des questions ou besoin d'assistance, n'hésitez pas à contacter notre équipe :</p>
        <ul>
            <li>📧 Email : support@sunusante.com</li>
            <li>📱 Téléphone : +225 XX XX XX XX</li>
            <li>💬 Chat en ligne disponible sur notre site</li>
        </ul>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} SUNU Santé. Tous droits réservés.</p>
        <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
    </div>
</body>
</html>
