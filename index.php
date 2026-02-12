<?php
require_once 'includes/check_auth.php';


// Récupérer les statistiques
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
        SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as en_cours,
        SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as terminee
    FROM demandes
";
$stats = $pdo->query($stats_query)->fetch();

// Récupérer les demandes récentes
$recent_query = "
    SELECT d.*, c.nom as client_nom 
    FROM demandes d
    JOIN clients c ON d.client_id = c.id
    ORDER BY d.date_creation DESC
    LIMIT 5
";
$recent_demandes = $pdo->query($recent_query)->fetchAll();

// Nombre total de clients
$total_clients = $pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion Demandes</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Gestion Demandes Clients</h1>
            <ul class="nav-menu">
                <li><a href="index.php" class="active">Tableau de Bord</a></li>
                <li><a href="clients/list.php">Clients</a></li>
                <li><a href="demandes/list.php">Demandes</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="admin/logs.php">Logs</a></li>
                <?php endif; ?>
                <li><a href="admin/backup.php" class="active">Sauvegarde</a></li>
                <li><a href="auth/logout.php" class="btn-logout">Déconnexion</a></li>
            </ul>
            <div class="user-info">
                <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
                
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Tableau de Bord</h2>
            <p>Bienvenue, <?= htmlspecialchars($_SESSION['username']) ?> !</p>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3><?= $stats['total'] ?></h3>
                    <p>Demandes Totales</p>
                </div>
            </div>

            <div class="stat-card orange">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3><?= $stats['en_attente'] ?></h3>
                    <p>En Attente</p>
                </div>
            </div>

            <div class="stat-card purple">
                <div class="stat-icon">⚙️</div>
                <div class="stat-info">
                    <h3><?= $stats['en_cours'] ?></h3>
                    <p>En Cours</p>
                </div>
            </div>

            <div class="stat-card green">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3><?= $stats['terminee'] ?></h3>
                    <p>Terminées</p>
                </div>
            </div>

            <div class="stat-card teal">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?= $total_clients ?></h3>
                    <p>Clients</p>
                </div>
            </div>
        </div>

        <!-- Actions Rapides -->
        <div class="quick-actions">
            <h3>Actions Rapides</h3>
            <div class="action-buttons">
                <a href="demandes/add.php" class="btn btn-primary">
                    ➕ Nouvelle Demande
                </a>
                <a href="clients/add.php" class="btn btn-secondary">
                    👤 Nouveau Client
                </a>
                <a href="demandes/list.php" class="btn btn-secondary">
                    📋 Toutes les Demandes
                </a>
            </div>
        </div>

        <!-- Demandes Récentes -->
        <div class="recent-section">
            <h3>Demandes Récentes</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>N° Ticket</th>
                        <th>Client</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th>Priorité</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_demandes as $demande): ?>
                    <tr>
                        <td><strong><?= $demande['numero_ticket'] ?></strong></td>
                        <td><?= htmlspecialchars($demande['client_nom']) ?></td>
                        <td><?= substr(htmlspecialchars($demande['description']), 0, 50) ?>...</td>
                        <td><span class="badge badge-<?= $demande['statut'] ?>">
                            <?= ucfirst(str_replace('_', ' ', $demande['statut'])) ?>
                        </span></td>
                        <td><span class="priority-<?= $demande['priorite'] ?>">
                            <?= ucfirst($demande['priorite']) ?>
                        </span></td>
                        <td><?= date('d/m/Y H:i', strtotime($demande['date_creation'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
