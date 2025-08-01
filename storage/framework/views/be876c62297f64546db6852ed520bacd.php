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
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
        
        <h1>🔑 Vos identifiants de connexion</h1>
        <p>Bienvenue <?php echo e($user->personnel->prenoms); ?> <?php echo e($user->personnel->nom); ?> ! Votre compte a été créé avec succès.</p>
        
        <div class="credentials">
            <p><strong>Email :</strong> <?php echo e($user->email); ?></p>
            <p><strong>Mot de passe :</strong> <?php echo e($password); ?></p>
        </div>

        <p><strong>Important :</strong> Changez votre mot de passe lors de votre première connexion.</p>

        <div style="text-align: center; margin-block: 30px;">
            <a href="<?php echo e(env('FRONTEND_URL')); ?>/auth/login" class="button">🚀 Se connecter maintenant</a>
        </div>

        <p>Conseils pour votre première connexion :</p>
        <ul>
            <li>Copiez vos identifiants</li>
            <li>Changez votre mot de passe</li>
            <li>Explorez votre tableau de bord</li>
        </ul>

        
        <div class="footer">
            <p><strong>SUNU Santé</strong> - Votre partenaire santé de confiance</p>
            <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; <?php echo e(date('Y')); ?> SUNU Santé. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\projects\amp\amp_backend\resources\views/emails/credentials.blade.php ENDPATH**/ ?>