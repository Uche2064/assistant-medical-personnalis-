<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Créé - SUNU Santé</title>
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
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        .header::before {
            content: "🎉";
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
            background: linear-gradient(135deg, #d1ecf1, #b3d7ff);
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            position: relative;
        }
        .success-banner::before {
            content: "🎊";
            font-size: 32px;
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #007bff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-banner h1 {
            color: #004085;
            margin: 15px 0 10px 0;
            font-size: 24px;
        }
        .success-banner .message {
            color: #004085;
            font-size: 16px;
            font-weight: 500;
        }
        .account-details {
            background-color: #f8f9fa;
            border-left: 6px solid #007bff;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .account-details h3 {
            color: #007bff;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .account-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .account-details td {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .account-details td:first-child {
            font-weight: bold;
            width: 35%;
            color: #495057;
        }
        .account-details td:last-child {
            color: #007bff;
            font-weight: 500;
        }
        .credentials-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .credentials-box h4 {
            color: #856404;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .credentials-box p {
            margin: 10px 0;
            color: #856404;
            font-weight: 500;
        }
        .credentials-box .credential-item {
            background-color: #fff;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ffc107;
        }
        .security-info {
            background-color: #f8d7da;
            border-left: 6px solid #dc3545;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .security-info p {
            margin: 0;
            color: #721c24;
            font-weight: 500;
        }
        .security-info strong {
            color: #491217;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            transition: all 0.3s ease;
            margin: 5px;
        }
        .button:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }
        .button.secondary {
            background: linear-gradient(135deg, #28a745, #20c997);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        .button.secondary:hover {
            background: linear-gradient(135deg, #218838, #1c7d6d);
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
            color: #007bff;
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
            background: linear-gradient(90deg, #007bff, #0056b3, #007bff);
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
                <h1>🎊 Compte Créé avec Succès ! 🎊</h1>
                <p class="message">Bienvenue dans la famille SUNU Santé</p>
            </div>
            
            <p>Bonjour <strong>{{ $user->nom ?? 'Utilisateur' }}</strong>,</p>
            
            <p>Félicitations ! Votre compte SUNU Santé a été <strong style="color: #007bff;">créé avec succès</strong> ! 🎉</p>
            
            <p>Nous sommes ravis de vous accueillir dans notre communauté dédiée à votre bien-être et à votre santé.</p>
            
            <div class="account-details">
                <h3>👤 Informations de votre compte</h3>
                <table>
                    <tr>
                        <td>📧 Email :</td>
                        <td><strong>{{ $user->email ?? 'email@exemple.com' }}</strong></td>
                    </tr>
                    <tr>
                        <td>📅 Date de création :</td>
                        <td><strong>{{ $user->created_at->format('d/m/Y à H:i') }}</strong></td>
                    </tr>
                    <tr>
                        <td>🆔 ID du compte :</td>
                        <td><strong>{{ $user->id ?? 'XXXX-XXXX-XXXX' }}</strong></td>
                    </tr>
                    <tr>
                        <td>🔒 Statut :</td>
                        <td><span style="color: #28a745; font-weight: bold;">✅ Activé</span></td>
                    </tr>
                </table>
            </div>

            <div class="quick-actions">
                <h4>🚀 Prochaines étapes recommandées</h4>
                <ul>
                    <li>Complétez votre profil personnel</li>
                    <li>Ajoutez vos informations médicales</li>
                    <li>Configurez vos préférences de notification</li>
                    <li>Explorez nos services de santé</li>
                    <li>Téléchargez notre application mobile</li>
                </ul>
            </div>

            <p style="text-align: center; color: #007bff; font-weight: bold;">
                Bienvenue dans votre nouveau parcours santé !<br>
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