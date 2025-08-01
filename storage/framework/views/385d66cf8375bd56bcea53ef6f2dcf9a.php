<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de votre compte SUNU Santé</title>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>SUNU SANTÉ</h1>
            <p>Votre partenaire santé de confiance</p>
        </div>
        <div class="content">
            <p>Bonjour,</p>
            <p>Merci de vous être inscrit sur <strong>SUNU Santé</strong> !</p>
            <p>Pour finaliser la création de votre compte, veuillez saisir le code de vérification ci-dessous :</p>
            <div class="otp-section">
                <h2>Code de vérification</h2>
                <p><?php echo e($otp); ?></p>
            </div>
            <p>Ce code expire le <?php echo e(now()->addMinutes(10)->format('d/m/Y à H:i')); ?></p>
            <a href="<?php echo e(config('app.url')); ?>" class="cta-button">Accéder à SUNU Santé</a>
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