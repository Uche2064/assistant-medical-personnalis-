<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe modifié - SUNU Santé</title>
    <style>
      
        
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
            padding: 20px;
        }
          .details-container {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 20px;
            margin: 30px 0;
        }

        
        .success-title {
            font-size: 16px;
            font-weight: bold;
            color: #155724;
        }
        
        .success-message {
            font-size: 14px;
            color: #155724;
            margin-bottom: 10px;
        }

          .message {
            font-size: 14px;
            margin-bottom: 30px;
            color: #555;
            line-height: 1.8;
        }
        
       
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">SUNU Santé</div>
            <h1>Mot de passe modifié</h1>
            <p>Confirmation de sécurité</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Bonjour {{ $user->prenoms }} {{ $user->nom }},
            </div>
            
            <div class="message">
                Nous confirmons que votre mot de passe a été modifié avec succès. 
                Cette action a été effectuée pour votre compte SUNU Santé.
            </div>
            
            <!-- Details Container -->
            <div class="details-container">
                 <div class="success-title">Mot de passe mis à jour</div>
                <div class="success-message">
                    Votre nouveau mot de passe est maintenant actif
                </div>
                
                <div class="details-title">Détails de la modification</div>
                <div class="detail-item">
                    <span class="detail-label">Compte :</span>
                    <span class="detail-value">{{ $user->email }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Date de modification :</span>
                    <span class="detail-value">{{ $changed_at->format('d/m/Y à H:i') }}</span>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
                <a href="{{ config('app.frontend_url', 'https://app.sunusante.com') }}/login" class="action-button">
                    Se connecter maintenant
                </a>
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
