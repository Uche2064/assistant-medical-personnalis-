// Service pour les statistiques des prestataires
class PrestataireStatsService {
    constructor(baseURL, token) {
        this.baseURL = baseURL;
        this.token = token;
    }

    /**
     * Récupérer les statistiques du prestataire connecté
     * @param {Object} filters - Filtres optionnels
     * @returns {Promise<Object>} Statistiques du prestataire
     */
    async getStats(filters = {}) {
        try {
            const params = new URLSearchParams();
            
            if (filters.date_debut) {
                params.append('date_debut', filters.date_debut);
            }
            if (filters.date_fin) {
                params.append('date_fin', filters.date_fin);
            }

            const url = `${this.baseURL}/api/v1/factures/stats?${params.toString()}`;
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data.data;
        } catch (error) {
            console.error('Erreur lors de la récupération des statistiques:', error);
            throw error;
        }
    }

    /**
     * Formater les montants en FCFA
     * @param {number} amount - Montant en centimes
     * @returns {string} Montant formaté
     */
    formatAmount(amount) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'XOF',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    /**
     * Préparer les données pour un graphique en camembert
     * @param {Object} repartitionData - Données de répartition
     * @returns {Array} Données formatées pour le graphique
     */
    preparePieChartData(repartitionData) {
        return Object.entries(repartitionData).map(([label, value]) => ({
            label: this.formatLabel(label),
            value: value,
            color: this.getColorForLabel(label)
        }));
    }

    /**
     * Préparer les données pour un graphique linéaire
     * @param {Array} evolutionData - Données d'évolution
     * @returns {Array} Données formatées pour le graphique
     */
    prepareLineChartData(evolutionData) {
        return evolutionData.map(item => ({
            x: item.mois,
            y: item.nombre_factures,
            montant: item.montant_total
        }));
    }

    /**
     * Formater les labels pour l'affichage
     * @param {string} label - Label à formater
     * @returns {string} Label formaté
     */
    formatLabel(label) {
        const labels = {
            'en_attente': 'En attente',
            'validee': 'Validée',
            'rejetee': 'Rejetée',
            'payee': 'Payée',
            'M': 'Masculin',
            'F': 'Féminin',
            'Non renseigné': 'Non renseigné'
        };
        return labels[label] || label;
    }

    /**
     * Obtenir une couleur pour un label
     * @param {string} label - Label
     * @returns {string} Code couleur hex
     */
    getColorForLabel(label) {
        const colors = {
            'en_attente': '#FFA500',
            'validee': '#28a745',
            'rejetee': '#dc3545',
            'payee': '#007bff',
            'M': '#007bff',
            'F': '#e83e8c',
            'Non renseigné': '#6c757d'
        };
        return colors[label] || '#6c757d';
    }
}

// Dashboard pour les prestataires
class PrestataireDashboard {
    constructor(containerId, service) {
        this.container = document.getElementById(containerId);
        this.service = service;
        this.charts = {};
    }

    /**
     * Initialiser le dashboard
     */
    async init() {
        try {
            const stats = await this.service.getStats();
            this.renderHeader(stats.prestataire);
            this.renderKPIs(stats);
            this.renderCharts(stats);
            this.renderTables(stats);
        } catch (error) {
            this.showError('Erreur lors du chargement des statistiques');
        }
    }

    /**
     * Afficher l'en-tête avec les infos du prestataire
     * @param {Object} prestataire - Informations du prestataire
     */
    renderHeader(prestataire) {
        const header = document.createElement('div');
        header.className = 'prestataire-header';
        header.innerHTML = `
            <div class="prestataire-info">
                <h1>Dashboard - ${prestataire.raison_sociale}</h1>
                <div class="prestataire-details">
                    <span>📞 ${prestataire.contact || 'Non renseigné'}</span>
                    <span>📧 ${prestataire.email || 'Non renseigné'}</span>
                </div>
            </div>
        `;
        this.container.appendChild(header);
    }

    /**
     * Afficher les KPIs principaux
     * @param {Object} stats - Statistiques
     */
    renderKPIs(stats) {
        const kpiContainer = document.createElement('div');
        kpiContainer.className = 'kpi-container';
        kpiContainer.innerHTML = `
            <div class="kpi-card">
                <h3>Total Factures</h3>
                <div class="kpi-value">${stats.total_factures}</div>
            </div>
            <div class="kpi-card">
                <h3>Patients Uniques</h3>
                <div class="kpi-value">${stats.nombre_patients}</div>
            </div>
            <div class="kpi-card">
                <h3>Total Sinistres</h3>
                <div class="kpi-value">${stats.sinistres_stats.total_sinistres}</div>
            </div>
            <div class="kpi-card">
                <h3>Montant Total</h3>
                <div class="kpi-value">${this.service.formatAmount(stats.montants.total)}</div>
            </div>
            <div class="kpi-card">
                <h3>Montant Remboursé</h3>
                <div class="kpi-value">${this.service.formatAmount(stats.montants.rembourse)}</div>
            </div>
            <div class="kpi-card">
                <h3>Montant Moyen/Facture</h3>
                <div class="kpi-value">${this.service.formatAmount(stats.sinistres_stats.montant_moyen_facture)}</div>
            </div>
        `;
        this.container.appendChild(kpiContainer);
    }

    /**
     * Afficher les graphiques
     * @param {Object} stats - Statistiques
     */
    renderCharts(stats) {
        // Graphique de répartition par statut
        this.createPieChart('statut-chart', 'Répartition des Factures par Statut', 
            this.service.preparePieChartData(stats.repartition_statut));

        // Graphique de répartition par sexe
        this.createPieChart('sexe-chart', 'Répartition des Patients par Sexe', 
            this.service.preparePieChartData(stats.repartition_sexe));

        // Graphique d'évolution mensuelle des factures
        this.createLineChart('evolution-factures-chart', 'Évolution Mensuelle des Factures', 
            this.service.prepareLineChartData(stats.evolution_mensuelle));

        // Graphique d'évolution mensuelle des sinistres
        this.createLineChart('evolution-sinistres-chart', 'Évolution Mensuelle des Sinistres', 
            stats.sinistres_par_mois.map(item => ({
                x: item.nom_mois,
                y: item.count
            })));
    }

    /**
     * Créer un graphique en camembert
     * @param {string} canvasId - ID du canvas
     * @param {string} title - Titre du graphique
     * @param {Array} data - Données
     */
    createPieChart(canvasId, title, data) {
        const canvas = document.createElement('canvas');
        canvas.id = canvasId;
        canvas.width = 400;
        canvas.height = 300;

        const container = document.createElement('div');
        container.className = 'chart-container';
        container.innerHTML = `<h3>${title}</h3>`;
        container.appendChild(canvas);
        this.container.appendChild(container);

        const ctx = canvas.getContext('2d');
        this.charts[canvasId] = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.map(item => item.label),
                datasets: [{
                    data: data.map(item => item.value),
                    backgroundColor: data.map(item => item.color)
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    /**
     * Créer un graphique linéaire
     * @param {string} canvasId - ID du canvas
     * @param {string} title - Titre du graphique
     * @param {Array} data - Données
     */
    createLineChart(canvasId, title, data) {
        const canvas = document.createElement('canvas');
        canvas.id = canvasId;
        canvas.width = 600;
        canvas.height = 300;

        const container = document.createElement('div');
        container.className = 'chart-container';
        container.innerHTML = `<h3>${title}</h3>`;
        container.appendChild(canvas);
        this.container.appendChild(container);

        const ctx = canvas.getContext('2d');
        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.x),
                datasets: [{
                    label: 'Nombre',
                    data: data.map(item => item.y),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    /**
     * Afficher les tableaux
     * @param {Object} stats - Statistiques
     */
    renderTables(stats) {
        // Tableau des sinistres par mois
        this.createTable('sinistres-table', 'Sinistres par Mois',
            stats.sinistres_par_mois.map(item => ({
                mois: item.nom_mois,
                nombreSinistres: item.count
            })));

        // Tableau de l'évolution mensuelle
        this.createTable('evolution-table', 'Évolution Mensuelle',
            stats.evolution_mensuelle.map(item => ({
                mois: item.mois,
                nombreFactures: item.nombre_factures,
                montantTotal: this.service.formatAmount(item.montant_total)
            })));
    }

    /**
     * Créer un tableau
     * @param {string} tableId - ID du tableau
     * @param {string} title - Titre du tableau
     * @param {Array} data - Données
     */
    createTable(tableId, title, data) {
        const table = document.createElement('table');
        table.id = tableId;
        table.className = 'stats-table';

        const headers = Object.keys(data[0] || {});
        const headerRow = table.insertRow();
        headers.forEach(header => {
            const th = document.createElement('th');
            th.textContent = this.service.formatLabel(header);
            headerRow.appendChild(th);
        });

        data.forEach(row => {
            const tr = table.insertRow();
            headers.forEach(header => {
                const td = document.createElement('td');
                td.textContent = row[header];
                tr.appendChild(td);
            });
        });

        const container = document.createElement('div');
        container.className = 'table-container';
        container.innerHTML = `<h3>${title}</h3>`;
        container.appendChild(table);
        this.container.appendChild(container);
    }

    /**
     * Afficher une erreur
     * @param {string} message - Message d'erreur
     */
    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        this.container.appendChild(errorDiv);
    }
}

// Exemple d'utilisation
document.addEventListener('DOMContentLoaded', function() {
    const service = new PrestataireStatsService('http://localhost:8000', 'your-token-here');
    const dashboard = new PrestataireDashboard('prestataire-dashboard', service);
    dashboard.init();
});

// CSS pour le dashboard prestataire
const styles = `
.prestataire-header {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
    text-align: center;
}

.prestataire-header h1 {
    margin: 0 0 15px 0;
    font-size: 28px;
}

.prestataire-details {
    display: flex;
    justify-content: center;
    gap: 30px;
    font-size: 16px;
}

.kpi-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.kpi-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    text-align: center;
    border-left: 4px solid #007bff;
}

.kpi-card h3 {
    margin: 0 0 15px 0;
    color: #666;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.kpi-value {
    font-size: 28px;
    font-weight: bold;
    color: #007bff;
}

.chart-container {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 25px;
}

.chart-container h3 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 18px;
    text-align: center;
}

.table-container {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 25px;
}

.table-container h3 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 18px;
    text-align: center;
}

.stats-table {
    width: 100%;
    border-collapse: collapse;
}

.stats-table th,
.stats-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.stats-table th {
    background-color: #f8f9fa;
    font-weight: bold;
    color: #333;
}

.stats-table tr:hover {
    background-color: #f8f9fa;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    text-align: center;
    font-weight: bold;
}

@media (max-width: 768px) {
    .prestataire-details {
        flex-direction: column;
        gap: 10px;
    }
    
    .kpi-container {
        grid-template-columns: 1fr;
    }
    
    .chart-container,
    .table-container {
        padding: 15px;
    }
}
`;

// Injecter les styles
const styleSheet = document.createElement('style');
styleSheet.textContent = styles;
document.head.appendChild(styleSheet);

