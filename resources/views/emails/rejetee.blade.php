<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'adhésion non approuvée - SUNU Santé</title>
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
            padding: 30px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
        }
        .footer p {
            margin: 5px 0;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>SUNU SANTÉ</h2>
        </div>
        
        <div class="content">
            <div class="rejection-banner">
                <h1>📋 Suivi de votre demande</h1>
                <p>Décision concernant votre candidature</p>
            </div>
            
            <p>Cher(e) <strong>@if ($demande->type_demandeur === 'medecin_liberal' || $demande->type_demandeur ==='entreprise') {{ $demande->nom. ' ' . $demande->prenoms }} @else {{ $demande->raison_sociale }} @endif</strong>,</p>
            
            <p>Merci pour votre intérêt envers <strong>SUNU Santé</strong>.</p>
            
            <div class="request-details">
                <h3>Informations de votre demande</h3>
                <table>
                    <tr><td>Date de soumission :</td><td><strong>{{ $demande->created_at->format('d/m/Y H:i') }}</strong></td></tr>
                    <tr><td>Statut :</td><td><span style="color: #dc3545; font-weight: bold;">❌ Non approuvée</span></td></tr>
                </table>
            </div>

            <div class="rejection-info">
                <h4>Décision de votre demande</h4>
                <p><strong>Votre demande d'adhésion n'a pas été approuvée.</strong></p>
                @if($demande->motif_rejet)
                    <p>
                        <strong>Motif :</strong> {{ $demande->motif_rejet }}</p>
                @endif
            </div>

            <div class="contact-box">
                <h4>Besoin de plus d'informations ?</h4>
                <p>Contactez-nous :</p>
                <ul>
                    <li><strong>Email :</strong> adhesions@sunusante.sn</li>
                    <li><strong>Téléphone :</strong> +221 33 XXX XX XX</li>
                </ul>
            </div>

            <p style="text-align: center; color: #dc3545; font-weight: bold;">
                Nous restons à votre disposition,<br>
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
