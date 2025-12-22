<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe - SUNU Santé</title>
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
        .otp-container {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            margin: 30px 0;
        }
         .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            letter-spacing: 8px;
            background-color: white;
            padding: 15px 25px;
            border-radius: 6px;
            border: 2px solid #dee2e6;
            display: inline-block;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div>
            <div>SUNU Santé</div>
            <h1>Réinitialisation de mot de passe</h1>
            <p>Votre code de sécurité</p>
        </div>
        
        <!-- Content -->
        <div>
            <div>
                Bonjour <?php echo e($user->prenoms); ?> <?php echo e($user->nom); ?>,
            </div>
            
            <div>
                Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte SUNU Santé. 
                Pour continuer, veuillez utiliser le code de sécurité ci-dessous.
            </div>
            
            <!-- OTP Code -->
            <div class="otp-container">
                <div class="otp-label">Code de sécurité</div>
                <div class="otp-code"><?php echo e($otp); ?></div>
                <div class="expiry-info">
                    Ce code expire le <?php echo e($expire_at->format('d/m/Y à H:i')); ?>

                </div>
            </div>
            
        </div>
        
        <!-- Footer -->
        <div>
            <p>© <?php echo e(date('Y')); ?> SUNU Santé. Tous droits réservés.</p>
            <div>
                Besoin d'aide ? Contactez-nous à <a href="mailto:support@sunusante.com">support@sunusante.com</a>
            </div>
        </div>
    </div>
</body>
</html> <?php /**PATH G:\projects\amp\amp_backend\resources\views\emails\password_reset_otp.blade.php ENDPATH**/ ?>