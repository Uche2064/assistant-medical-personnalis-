<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de votre compte SUNU Santé</title>
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
        <div>
            <h1>SUNU SANTÉ</h1>
            <p>Votre partenaire santé de confiance</p>
        </div>
        <div>
            <p>Bonjour,</p>
            <p>Merci de vous être inscrit sur <strong>SUNU Santé</strong> !</p>
            <p>Pour finaliser la création de votre compte, veuillez saisir le code de vérification ci-dessous :</p>
            <!-- OTP Code -->
            <div class="otp-container">
                <div class="otp-label">Code de sécurité</div>
                <div class="otp-code"><?php echo e($otp); ?></div>
                <div class="expiry-info">
                    Ce code expire le <?php echo e($expire_at->format('d/m/Y à H:i')); ?>

                </div>
            </div>
        </div>
        <div class="footer">
            <p>Merci pour votre confiance,</p>
            <p>L'équipe SUNU Santé</p>
            <p>© <?php echo e(date('Y')); ?> SUNU Santé. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\projects\amp\amp_backend\resources\views/emails/otp_verification.blade.php ENDPATH**/ ?>