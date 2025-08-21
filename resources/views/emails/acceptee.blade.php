<!DOCTYPE html>
<html lang="fr">
<head>
    
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>SUNU SANTÉ</h2>
        </div>
        
        <div class="content">
            <div class="success-banner">
                <h1>🎊 FÉLICITATIONS ! 🎊</h1>
                <p>Votre demande d'adhésion a été approuvée avec succès</p>
            </div>
            
            <p>Cher(e) <strong>{{ $demande->type_demandeur !== 'physique' ? $demande->raison_sociale : $demande->personne->nom . ' ' . $demande->personne->prenoms }}</strong>,</p>
            <p>Nous avons le plaisir de vous informer que votre demande d'adhésion à SUNU Santé a été <strong style="color: #28a745;">APPROUVÉE</strong> ! 🎉</p>
            
            <div class="demande-details">
                <h3>📋 Détails de votre demande</h3>
                <table>
                    <tr>
                        <td>Date d'approbation :</td>
                        <td><strong>{{ now()->format('d/m/Y à H:i') }}</strong></td>
                    </tr>
                </table>
            </div>

            @if(isset($contrat) && $contrat)
            <div class="contrat-details">
                <h3>📋 Détails du contrat</h3>
                <table>
                    <tr>
                        <td>ID du contrat :</td>
                        <td><strong>{{ $contrat->id }}</strong></td>
                    </tr>
                    <tr>
                        <td>Type de contrat :</td>
                        <td><strong>{{ $contrat->libelle ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td>Prime standard :</td>
                        <td><strong>{{ number_format($contrat->prime_standard, 2, ',', ' ') }} FCFA</strong></td>
                    </tr>
                </table>
            </div>
            @endif

            <div class="button-container">
                <a href="https://app.sunusante.sn/login" class="button">🚀 Accéder à ma plateforme</a>
            </div>

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
