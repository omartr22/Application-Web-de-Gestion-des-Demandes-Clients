<?php
require_once '../includes/check_auth.php';

// Filtres
$statut_filter = isset($_GET['statut']) ? $_GET['statut'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT d.*, c.nom as client_nom, c.email as client_email 
          FROM demandes d 
          JOIN clients c ON d.client_id = c.id";

$conditions = [];
$params = [];

if ($statut_filter) {
    $conditions[] = "d.statut = :statut";
    $params['statut'] = $statut_filter;
}

if ($search) {
    $conditions[] = "(d.numero_ticket LIKE :search OR c.nom LIKE :search OR d.description LIKE :search)";
    $params['search'] = "%$search%";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY d.date_creation DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$demandes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Demandes - Gestion Demandes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Gestion Demandes Clients</h1>
            <ul class="nav-menu">
                <li><a href="../index.php">Tableau de Bord</a></li>
                <li><a href="../clients/list.php">Clients</a></li>
                <li><a href="list.php" class="active">Demandes</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?><li><a href="../admin/logs.php">Logs</a></li><?php endif; ?>
                <li><a href="../admin/backup.php" class="active">Sauvegarde</a></li>
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
                <h2>Gestion des Demandes</h2>
                <p>Liste de toutes les demandes clients</p>
            </div>
            <a href="add.php" class="btn btn-primary">➕ Nouvelle Demande</a>
        </div>

        <!-- Filtres -->
        <div class="filters-box">
            <form method="GET" action="">
                <div class="filter-row">
                    <input type="text" name="search" placeholder="Rechercher..." 
                           value="<?= htmlspecialchars($search) ?>">
                    
                    <select name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" <?= $statut_filter === 'en_attente' ? 'selected' : '' ?>>
                            En attente
                        </option>
                        <option value="en_cours" <?= $statut_filter === 'en_cours' ? 'selected' : '' ?>>
                            En cours
                        </option>
                        <option value="terminee" <?= $statut_filter === 'terminee' ? 'selected' : '' ?>>
                            Terminée
                        </option>
                        <option value="annulee" <?= $statut_filter === 'annulee' ? 'selected' : '' ?>>
                            Annulée
                        </option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">🔍 Filtrer</button>
                    <a href="list.php" class="btn btn-secondary">✖ Réinitialiser</a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>N° Ticket</th>
                    <th>Client</th>
                    <th>Description</th>
                    <th>Statut</th>
                    <th>Priorité</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($demandes)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            Aucune demande trouvée
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($demandes as $demande): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($demande['numero_ticket']) ?></strong></td>
                        <td>
                            <?= htmlspecialchars($demande['client_nom']) ?><br>
                            <small style="color: #718096;"><?= htmlspecialchars($demande['client_email']) ?></small>
                        </td>
                        <td><?= substr(htmlspecialchars($demande['description']), 0, 80) ?>...</td>
                        <td>
                            <span class="badge badge-<?= $demande['statut'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $demande['statut'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="priority-<?= $demande['priorite'] ?>">
                                <?= ucfirst($demande['priorite']) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($demande['date_creation'])) ?></td>
                        <td> <a href="edit.php?id=<?= $demande['id'] ?>" class="btn-action btn-edit">✏️ Modifier</a>
                            <?php if ($demande['statut'] === 'en_attente'): ?><a href="quick_status.php?id=<?= $demande['id'] ?>&status=en_cours" class="btn-action btn-start">▶️ Démarrer</a>
                            <?php endif; ?>
                            <?php if ($demande['statut'] === 'en_cours'): ?><a href="quick_status.php?id=<?= $demande['id'] ?>&status=terminee" class="btn-action btn-complete">✅ Terminer</a>
                            <?php endif; ?>
                            <a href="delete.php?id=<?= $demande['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Supprimer cette demande ?')">🗑️ Supprimer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>