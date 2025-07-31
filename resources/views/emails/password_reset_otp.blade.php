<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe - SUNU Santé</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .message {
            font-size: 16px;
            margin-bottom: 30px;
            color: #555;
            line-height: 1.8;
        }
        
        .otp-container {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            margin: 30px 0;
        }
        
        .otp-label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            background-color: white;
            padding: 15px 25px;
            border-radius: 6px;
            border: 2px solid #dee2e6;
            display: inline-block;
            margin: 10px 0;
        }
        
        .expiry-info {
            font-size: 14px;
            color: #dc3545;
            margin-top: 15px;
            font-weight: 500;
        }
        
        .security-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .security-note h3 {
            color: #856404;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .security-note p {
            color: #856404;
            font-size: 14px;
            margin: 0;
        }
        
        .footer {
            background-color: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .footer p {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            margin-bottom: 10px;
        }
        
        .contact-info {
            margin-top: 20px;
            font-size: 14px;
            color: #6c757d;
        }
        
        .contact-info a {
            color: #667eea;
            text-decoration: none;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 8px;
            }
            
            .header, .content, .footer {
                padding: 20px;
            }
            
            .otp-code {
                font-size: 24px;
                letter-spacing: 4px;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">SUNU Santé</div>
            <h1>Réinitialisation de mot de passe</h1>
            <p>Votre code de sécurité</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Bonjour {{ $user->prenoms }} {{ $user->nom }},
            </div>
            
            <div class="message">
                Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte SUNU Santé. 
                Pour continuer, veuillez utiliser le code de sécurité ci-dessous.
            </div>
            
            <!-- OTP Code -->
            <div class="otp-container">
                <div class="otp-label">Code de sécurité</div>
                <div class="otp-code">{{ $otp->otp }}</div>
                <div class="expiry-info">
                    Ce code expire le {{ $expire_at->format('d/m/Y à H:i') }}
                </div>
            </div>
            
            <!-- Security Note -->
            <div class="security-note">
                <h3> Sécurité</h3>
                <p>
                    • Ne partagez jamais ce code avec qui que ce soit<br>
                    • SUNU Santé ne vous demandera jamais ce code par téléphone ou email<br>
                    • Si vous n'avez pas demandé cette réinitialisation, ignorez cet email
                </p>
            </div>
            
            <div class="message">
                <strong>Prochaines étapes :</strong><br>
                1. Saisissez ce code dans l'application<br>
                2. Définissez votre nouveau mot de passe<br>
                3. Connectez-vous avec vos nouveaux identifiants
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>© {{ date('Y') }} SUNU Santé. Tous droits réservés.</p>
            <div class="contact-info">
                Besoin d'aide ? Contactez-nous à <a href="mailto:support@sunusante.com">support@sunusante.com</a>
            </div>
        </div>
    </div>
</body>
</html> 