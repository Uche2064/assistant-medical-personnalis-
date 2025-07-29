<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Réussie - SUNU Santé</title>
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
            background: linear-gradient(135deg, #28a745, #20c997);
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
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        .success-banner h1 {
            color: #155724;
            margin: 15px 0;
            font-size: 24px;
        }
        .connection-details {
            background-color: #f8f9fa;
            border-left: 6px solid #28a745;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .connection-details h3 {
            color: #28a745;
            margin: 0 0 15px;
        }
        .connection-details td {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .connection-details td:first-child {
            font-weight: bold;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #28a745, #20c997);
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
                <h1>🎊 Connexion Réussie ! 🎊</h1>
                <p>Bienvenue sur votre espace personnel</p>
            </div>
            
            <p>Bonjour <strong>{{ $user->name ?? 'Utilisateur' }}</strong>,</p>
            <p>Vous êtes maintenant <strong style="color: #28a745;">connecté avec succès</strong> à votre compte SUNU Santé ! 🚀</p>
            
            <div class="connection-details">
                <h3>📊 Détails de votre connexion</h3>
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
                        <td>🔒 Statut :</td>
                        <td><span style="color: #28a745; font-weight: bold;">✅ Authentifié</span></td>
                    </tr>
                </table>
            </div>

            <div class="button-container">
                <a href="{{ $dashboard_url ?? '#' }}" class="button">🏠 Accéder au Tableau de Bord</a>
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
