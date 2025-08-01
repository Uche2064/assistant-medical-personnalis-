<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Réussie - SUNU Santé</title>
    <style>
        body {
            font-family: 'Poppins', Verdana, sans-serif;
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
            padding: 20px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
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
            
            <p>Bonjour <strong><?php echo e($user->name ?? 'Utilisateur'); ?></strong>,</p>
            <p>Vous êtes maintenant <strong>connecté avec succès</strong> à votre compte SUNU Santé !</p>
            
            <div class="connection-details">
                <h3>Détails de votre connexion</h3>
                <table>
                    <tr>
                        <td>Date et heure :</td>
                        <td><strong><?php echo e(date('d/m/Y à H:i')); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Adresse IP :</td>
                        <td><strong><?php echo e($ip_address ?? 'Non disponible'); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Navigateur :</td>
                        <td><strong><?php echo e($user_agent ?? 'Non disponible'); ?></strong></td>
                    </tr>
                   
                </table>
            </div>

            <div class="button-container">
                <a href="<?php echo e($dashboard_url ?? '#'); ?>" class="button">Accéder au Tableau de Bord</a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>SUNU Santé</strong> - Votre partenaire santé de confiance</p>
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.</p>
            <p>&copy; <?php echo e(date('Y')); ?> SUNU Santé. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\projects\amp\amp_backend\resources\views/emails/login_successful.blade.php ENDPATH**/ ?>