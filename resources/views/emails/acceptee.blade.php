<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demande d'adhésion approuvée - SUNU Santé</title>
    <style>
        body {
            font-family: Arial, sans-serif;
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
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
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
            top: 10px;
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
            font-size: 28px;
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
        .demande-details {
            background-color: #f8f9fa;
            border-left: 6px solid #28a745;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .demande-details h3 {
            color: #28a745;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .demande-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .demande-details td {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .demande-details td:first-child {
            font-weight: bold;
            width: 40%;
            color: #495057;
        }
        .demande-details td:last-child {
            color: #28a745;
            font-weight: 500;
        }
        .status-approved {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .next-steps {
            background: linear-gradient(135deg, #fff8e1, #ffecb3);
            border-left: 6px solid #ffc107;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .next-steps h3 {
            color: #856404;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .next-steps ul {
            color: #856404;
            padding-left: 20px;
            margin: 0;
        }
        .next-steps li {
            margin-bottom: 8px;
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
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            border-radius: 25px;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
        }
        .button:hover {
            background: linear-gradient(135deg, #218838, #1c7d6d);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }
        .welcome-message {
            background-color: #e7f3ff;
            border-left: 6px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .welcome-message p {
            margin: 0;
            color: #084298;
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
                <h1>🎊 FÉLICITATIONS ! 🎊</h1>
                <p class="message">Votre demande d'adhésion a été approuvée avec succès</p>
            </div>
            
            <p>Cher(e) <strong>{{ $demande->type_demande === 'prestataire' ? $demande->raison_sociale : $demande->nom_demandeur . ' ' . $demande->prenoms_demandeur }}</strong>,</p>
            
            <p>Nous avons l'immense plaisir de vous informer que votre demande d'adhésion à la plateforme SUNU Santé a été <strong style="color: #28a745;">APPROUVÉE</strong> ! 🎉</p>
            
            <div class="demande-details">
                <h3>📋 Détails de votre demande</h3>
                <table>
                    <tr>
                        <td>Référence :</td>
                        <td><strong>{{ $demande->id }}</strong></td>
                    </tr>
                    <tr>
                        <td>Type de demande :</td>
                        <td><strong>{{ ucfirst($demande->type_demande->value) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Date d'approbation :</td>
                        <td><strong>{{ now()->format('d/m/Y à H:i') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Statut :</td>
                        <td><span class="status-approved">✓ Approuvé</span></td>
                    </tr>
                </table>
            </div>

            <div class="divider"></div>

            <div class="next-steps">
                <h3>🚀 Prochaines étapes</h3>
                <ul>
                    <li><strong>Connectez-vous</strong> à votre espace personnel via le bouton ci-dessous</li>
                    <li><strong>Complétez</strong> votre profil si nécessaire</li>
                    <li><strong>Explorez</strong> toutes les fonctionnalités de la plateforme</li>
                    <li><strong>Contactez-nous</strong> si vous avez des questions</li>
                </ul>
            </div>

            <div class="button-container">
                <a href="https://app.sunusante.sn/login" class="button">
                    🚀 Accéder à ma plateforme
                </a>
            </div>

            <div class="welcome-message">
                <p><strong>Bienvenue dans la famille SUNU Santé !</strong> Toute notre équipe vous souhaite une excellente expérience sur notre plateforme et reste à votre disposition pour vous accompagner.</p>
            </div>

            <div class="contact-info">
                <h4>📞 Besoin d'aide ? Nous sommes là !</h4>
                <p><strong>Email :</strong> support@sunusante.sn</p>
                <p><strong>Téléphone :</strong> +221 33 XXX XX XX</p>
                <p><strong>Horaires :</strong> Lundi - Vendredi, 8h00 - 18h00</p>
                <p style="margin-top: 15px; font-style: italic;">Notre équipe support vous répondra dans les plus brefs délais.</p>
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