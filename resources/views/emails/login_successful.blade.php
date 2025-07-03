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
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        .header::before {
            content: "✓";
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header .subtitle {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .success-banner {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            position: relative;
        }
        .success-banner::before {
            content: "🎉";
            font-size: 32px;
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #28a745;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-banner h1 {
            color: #155724;
            margin: 15px 0 10px 0;
            font-size: 24px;
        }
        .success-banner .message {
            color: #155724;
            font-size: 16px;
            font-weight: 500;
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
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .connection-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .connection-details td {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .connection-details td:first-child {
            font-weight: bold;
            width: 35%;
            color: #495057;
        }
        .connection-details td:last-child {
            color: #28a745;
            font-weight: 500;
        }
        .security-info {
            background-color: #fff3cd;
            border-left: 6px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .security-info p {
            margin: 0;
            color: #856404;
            font-weight: 500;
        }
        .security-info strong {
            color: #664107;
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
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
        }
        .button:hover {
            background: linear-gradient(135deg, #218838, #1c7d6d);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }
        .quick-actions {
            background-color: #e7f3ff;
            border-left: 6px solid #007bff;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .quick-actions h4 {
            color: #084298;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .quick-actions ul {
            color: #084298;
            margin: 0;
            padding-left: 20px;
        }
        .quick-actions li {
            margin-bottom: 8px;
        }
        .contact-info {
            background-color: #f8f9fa;
            padding: 20px;
            margin: 25px 0;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .contact-info h4 {
            color: #495057;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .contact-info p {
            margin: 8px 0;
            color: #6c757d;
        }
        .contact-info strong {
            color: #28a745;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        .footer p {
            margin: 5px 0;
            font-size: 12px;
            color: #6c757d;
        }
        .divider {
            height: 2px;
            background: linear-gradient(90deg, #28a745, #20c997, #28a745);
            margin: 25px 0;
            border-radius: 1px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>SUNU SANTÉ</h2>
            <p class="subtitle">Votre partenaire santé de confiance</p>
        </div>
        
        <div class="content">
            <div class="success-banner">
                <h1>🎊 Connexion Réussie ! 🎊</h1>
                <p class="message">Bienvenue sur votre espace personnel</p>
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

            <div class="security-info">
                <p><strong>🔐 Sécurité :</strong> Si vous n'êtes pas à l'origine de cette connexion, veuillez nous contacter immédiatement pour sécuriser votre compte.</p>
            </div>

            <div class="button-container">
                <a href="{{ $dashboard_url ?? '#' }}" class="button">
                    🏠 Accéder au Tableau de Bord
                </a>
            </div>

            <div class="divider"></div>

            <div class="contact-info">
                <h4>📞 Besoin d'assistance ?</h4>
                <p><strong>Email :</strong> support@sunusante.sn</p>
                <p><strong>Téléphone :</strong> +221 33 XXX XX XX</p>
                <p><strong>Horaires :</strong> Lundi - Vendredi, 8h00 - 18h00</p>
                <p style="margin-top: 15px; font-style: italic;">Notre équipe est disponible pour vous aider.</p>
            </div>

            <div class="divider"></div>
            
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