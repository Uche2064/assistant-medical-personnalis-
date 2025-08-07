<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre contrat SUNU Santé est prêt</title>
    <style>
        body {
            font-family: Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            color: #FF0000;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        h2 {
            font-size: 18px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #FF0000;
            padding-bottom: 5px;
        }
        p {
            line-height: 1.6;
            color: #333;
            margin-bottom: 15px;
        }
        .success-message, .contract-details, .credentials, .warning {
            background-color: #f8f9fa;
            border-left: 6px solid #28a745;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .button {
            display: inline-block;
            background-color: #FF0000;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #CC0000;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SUNU SANTÉ</h1>
        </div>

        <h1>Félicitations {{ $user->prenoms }} {{ $user->nom }} !</h1>
        
        <div class="success-message">
            <p><strong>🎉 Votre contrat d'assurance SUNU Santé est maintenant actif !</strong></p>
        </div>

        <p>Bienvenue dans la famille SUNU Santé. Vous pouvez dès à présent accéder à notre plateforme pour gérer vos prestations.</p>
        
        <h2>📋 Détails de votre contrat</h2>
        <div class="contract-details">
            <p><strong>Numéro de police :</strong> {{ $contrat->numero_police ?? 'N/A' }}</p>
            <p><strong>Prime annuelle :</strong> {{ $contrat->prime_standard ? number_format($contrat->prime_standard, 0, ',', ' ') : '0' }} FCFA</p>
            <p><strong>Statut :</strong> <span style="color: #28a745;">✅ Actif</span></p>
        </div>

        <h2>🔐 Vos identifiants de connexion</h2>
        <div class="credentials">
            <p><strong>Email :</strong> {{ $user->email }}</p>
            <p><strong>Mot de passe :</strong> {{ $password }}</p>
        </div>

        <div class="warning">
            <strong>⚠️ Important :</strong> Changez votre mot de passe lors de votre première connexion.
        </div>
        
        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/login" class="button">🚀 Se connecter maintenant</a>
        </div>

        <h2>📞 Besoin d'aide ?</h2>
        <p><strong>Contactez notre service client :</strong></p>
        <p>📧 Email : support@sunusante.com<br>📱 Téléphone : +221 33 XXX XX XX</p>

        <div class="footer">
            <p><strong>Merci de votre confiance,</strong><br>L'équipe SUNU Santé</p>
            <p style="font-size: 12px; color: #999;">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
