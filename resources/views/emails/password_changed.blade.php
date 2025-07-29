<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe modifié - SUNU Santé</title>
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
            padding: 25px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .success-banner {
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            border: 2px solid #17a2b8;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .success-banner h1 {
            color: #0c5460;
            margin: 15px 0;
            font-size: 24px;
        }
        .change-details {
            background-color: #f8f9fa;
            border-left: 6px solid #17a2b8;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .change-details h3 {
            color: #17a2b8;
            margin: 0 0 15px;
        }
        .change-details td {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .change-details td:first-child {
            font-weight: bold;
            color: #495057;
        }
        .security-alert {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .security-alert h4 {
            color: #856404;
            margin: 0 0 10px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>SUNU SANTÉ</h2>
        </div>
        
        <div class="content">
            <div class="success-banner">
                <h1>🔐 Mot de passe modifié avec succès !</h1>
                <p>Votre compte est maintenant sécurisé</p>
            </div>
            
            <p>Bonjour <strong>{{ $user->name ?? 'Utilisateur' }}</strong>,</p>
            <p>Votre mot de passe a été <strong style="color: #17a2b8;">modifié avec succès</strong> sur votre compte SUNU Santé. 🛡️</p>
            
            <div class="change-details">
                <h3>📋 Détails de la modification</h3>
                <table>
                    <tr>
                        <td>📅 Date et heure :</td>
                        <td><strong>{{ date('d/m/Y à H:i') }}</strong></td>
                    </tr>
                    <tr>
                        <td>🌐 Adresse IP :</td>
                        <td><strong>{{ $ip_address ?? 'Non disponible' }}</strong></td>
                    </tr>
                    <tr>
                        <td>💻 Navigateur :</td>
                        <td><strong>{{ $user_agent ?? 'Non disponible' }}</strong></td>
                    </tr>
                    <tr>
                        <td>🔒 Type de modification :</td>
                        <td><span style="color: #17a2b8; font-weight: bold;">✅ Mot de passe</span></td>
                    </tr>
                </table>
            </div>

            <div class="security-alert">
                <h4>⚠️ Important - Sécurité</h4>
                <p><strong>Si vous n'êtes pas à l'origine de cette modification</strong>, veuillez nous contacter immédiatement.</p>
            </div>

            <div class="button-container">
                <a href="{{ $dashboard_url ?? '#' }}" class="button">🏠 Accéder à Mon Compte</a>
            </div>

            <p style="text-align: center; color: #dc3545; font-weight: bold;">
                Nous restons à votre disposition,<br>
                L'équipe SUNU Santé 🏥
            </p>
        </div>
        
        <div class="footer">
            <p><strong>SUNU Santé</strong> - Votre partenaire santé de confiance</p>
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.</p>
            <p>&copy; {{ date('Y') }} SUNU Santé. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
