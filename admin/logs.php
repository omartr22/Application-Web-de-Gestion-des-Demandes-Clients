<?php
require_once '../includes/check_auth.php';

// Vérifier que l'utilisateur est admin
if ($_SESSION['role'] !== 'admin') {
    die('Accès refusé. Réservé aux administrateurs.');
}

// Filtres
$user_filter = isset($_GET['user']) ? (int)$_GET['user'] : 0;
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Requête avec filtres
$query = "SELECT l.*, u.username, u.role 
          FROM logs_connexion l 
          JOIN users u ON l.user_id = u.id";

$conditions = [];
$params = [];

if ($user_filter > 0) {
    $conditions[] = "l.user_id = :user_id";
    $params['user_id'] = $user_filter;
}

if ($date_filter) {
    $conditions[] = "DATE(l.date_connexion) = :date";
    $params['date'] = $date_filter;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY l.date_connexion DESC LIMIT 100";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Récupérer la liste des utilisateurs pour le filtre
$users = $pdo->query('SELECT id, username FROM users ORDER BY username')->fetchAll();

// Statistiques
$stats_query = "SELECT 
    COUNT(*) as total,
    COUNT(DISTINCT user_id) as unique_users,
    COUNT(DISTINCT ip_address) as unique_ips,
    MAX(date_connexion) as last_login
    FROM logs_connexion";
$stats = $pdo->query($stats_query)->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de Connexion - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Gestion Demandes - Admin</h1>
            <ul class="nav-menu">
                <li><a href="../index.php">Tableau de Bord</a></li>
                <li><a href="../clients/list.php">Clients</a></li>
                <li><a href="../demandes/list.php">Demandes</a></li>
                <li><a href="logs.php" class="active">Logs</a></li>
                <li><a href="backup.php">Sauvegarde</a></li>
                <li><a href="../auth/logout.php" class="btn-logout">Déconnexion</a></li>
            </ul>
            <div class="user-info">
                <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <div>
                <h2>🔒 Logs de Connexion</h2>
                <p>Historique de toutes les connexions au système</p>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3><?= $stats['total'] ?></h3>
                    <p>Connexions Totales</p>
                </div>
            </div>

            <div class="stat-card purple">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?= $stats['unique_users'] ?></h3>
                    <p>Utilisateurs Uniques</p>
                </div>
            </div>

            <div class="stat-card orange">
                <div class="stat-icon">🌐</div>
                <div class="stat-info">
                    <h3><?= $stats['unique_ips'] ?></h3>
                    <p>Adresses IP Uniques</p>
                </div>
            </div>

            <div class="stat-card green">
                <div class="stat-icon">🕐</div>
                <div class="stat-info">
                    <h3><?= $stats['last_login'] ? date('H:i', strtotime($stats['last_login'])) : '-' ?></h3>
                    <p>Dernière Connexion</p>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filters-box">
            <form method="GET" action="">
                <div class="filter-row">
                    <select name="user">
                        <option value="">Tous les utilisateurs</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" 
                                    <?= $user_filter == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="date" name="date" value="<?= htmlspecialchars($date_filter) ?>">
                    
                    <button type="submit" class="btn btn-primary">🔍 Filtrer</button>
                    <a href="logs.php" class="btn btn-secondary">✖ Réinitialiser</a>
                </div>
            </form>
        </div>

        <!-- Table des logs -->
        <div class="logs-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Adresse IP</th>
                        <th>Date & Heure</th>
                        <th>Navigateur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                Aucun log trouvé
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= $log['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($log['username']) ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-<?= $log['role'] ?>">
                                    <?= $log['role'] ?>
                                </span>
                            </td>
                            <td>
                                <code class="ip-address"><?= htmlspecialchars($log['ip_address']) ?></code>
                            </td>
                            <td>
                                <?= date('d/m/Y à H:i:s', strtotime($log['date_connexion'])) ?>
                            </td>
                            <td>
                                <small class="user-agent" title="<?= htmlspecialchars($log['user_agent']) ?>">
                                    <?php
                                    // Extraire le nom du navigateur
                                    $ua = $log['user_agent'];
                                    if (strpos($ua, 'Chrome') !== false) echo '🌐 Chrome';
                                    elseif (strpos($ua, 'Firefox') !== false) echo '🦊 Firefox';
                                    elseif (strpos($ua, 'Safari') !== false) echo '🧭 Safari';
                                    elseif (strpos($ua, 'Edge') !== false) echo '🌊 Edge';
                                    else echo '🖥️ Autre';
                                    ?>
                                </small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Info -->
        <div class="info-box">
            <h3>ℹ️ Information</h3>
            <p><strong>Pourquoi surveiller les logs de connexion ?</strong></p>
            <ul>
                <li>✅ Détecter les tentatives d'accès non autorisées</li>
                <li>✅ Suivre l'activité des utilisateurs</li>
                <li>✅ Identifier les connexions inhabituelles (nouvelles IP)</li>
                <li>✅ Conformité et audit de sécurité</li>
            </ul>
            <p style="margin-top: 15px;">
                <strong>💡 Conseil :</strong> Vérifiez régulièrement les logs pour détecter 
                toute activité suspecte.
            </p>
        </div>
    </div>
</body>
</html>