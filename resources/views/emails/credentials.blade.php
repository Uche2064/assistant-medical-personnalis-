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
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #FF0000, #CC0000);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        .header::before {
            content: "🔐";
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
        h1 {
            color: #FF0000;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        p {
            line-height: 1.6;
            color: #333;
            margin-bottom: 15px;
        }
        .welcome-message {
            background: linear-gradient(135deg, #fff5f5, #ffe6e6);
            border-left: 6px solid #FF0000;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .welcome-message h3 {
            color: #CC0000;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .credentials {
            background-color: #f8f9fa;
            border: 2px solid #FF0000;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .credentials h3 {
            color: #FF0000;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 18px;
        }
        .credentials table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .credentials td {
            padding: 12px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .credentials td:first-child {
            font-weight: bold;
            width: 30%;
            color: #495057;
            text-align: left;
        }
        .credentials td:last-child {
            color: #FF0000;
            font-weight: 600;
            text-align: left;
            word-break: break-all;
        }
        .security-warning {
            background-color: #fff3cd;
            border-left: 6px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .security-warning p {
            margin: 0;
            color: #856404;
            font-weight: 500;
        }
        .security-warning strong {
            color: #664107;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #FF0000, #CC0000);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        .button:hover {
            background: linear-gradient(135deg, #CC0000, #990000);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 0, 0, 0.4);
        }
        .tips {
            background-color: #e7f3ff;
            border-left: 6px solid #007bff;
            padding: 15px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .tips h4 {
            color: #084298;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .tips ul {
            color: #084298;
            margin: 0;
            padding-left: 20px;
        }
        .tips li {
            margin-bottom: 5px;
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
            color: #FF0000;
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
            background: linear-gradient(90deg, #FF0000, #CC0000, #FF0000);
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
            <h1>🔑 Vos identifiants de connexion</h1>
            
            <div class="welcome-message">
                <h3>Bienvenue {{ $user->prenoms }} {{ $user->nom }} !</h3>
                <p>Votre compte a été créé avec succès sur la plateforme SUNU Santé. Vous pouvez maintenant accéder à tous nos services.</p>
            </div>
            
            <div class="credentials">
                <h3>🔐 Vos identifiants de connexion</h3>
                <table>
                    <tr>
                        <td>📧 Email :</td>
                        <td><strong>{{ $user->email }}</strong></td>
                    </tr>
                    <tr>
                        <td>🔒 Mot de passe :</td>
                        <td><strong>{{ $password }}</strong></td>
                    </tr>
                </table>
            </div>

            <div class="security-warning">
                <p><strong>⚠️ Important pour votre sécurité :</strong></p>
                <p>Nous vous recommandons fortement de changer votre mot de passe lors de votre première connexion pour garantir la sécurité de votre compte.</p>
            </div>

            <div class="button-container">
                <a href="{{ config('app.url') }}/login" class="button">
                    🚀 Se connecter maintenant
                </a>
            </div>

            <div class="divider"></div>

            <div class="tips">
                <h4>💡 Conseils pour votre première connexion</h4>
                <ul>
                    <li><strong>Copiez</strong> soigneusement vos identifiants</li>
                    <li><strong>Changez</strong> votre mot de passe dès la première connexion</li>
                    <li><strong>Explorez</strong> votre tableau de bord</li>
                    <li><strong>Complétez</strong> votre profil si nécessaire</li>
                </ul>
            </div>

            <div class="contact-info">
                <h4>📞 Besoin d'aide ?</h4>
                <p><strong>Email :</strong> support@sunusante.sn</p>
                <p><strong>Téléphone :</strong> +221 33 XXX XX XX</p>
                <p><strong>Horaires :</strong> Lundi - Vendredi, 8h00 - 18h00</p>
                <p style="margin-top: 15px; font-style: italic;">Notre équipe est à votre disposition pour vous accompagner.</p>
            </div>

            <div class="divider"></div>
            
            <p style="text-align: center; color: #dc3545; font-weight: bold;">
                Nous restons à votre disposition,<br>
                L'équipe SUNU Santé 🏥
            </p>
        </div>
        
        <div class="footer">
            <p><strong>SUNU Santé</strong> - Votre partenaire santé de confiance</p>
            <p>Ce message est généré automatiquement, merci de ne pas y répondre directement.</p>
            <p>&copy; {{ date('Y') }} SUNU Santé. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>