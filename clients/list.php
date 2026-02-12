<?php
require_once '../includes/check_auth.php';

// Recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "SELECT * FROM clients";
if ($search) {
    $query .= " WHERE nom LIKE :search OR email LIKE :search OR entreprise LIKE :search";
}
$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
if ($search) {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}
$clients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Clients - Gestion Demandes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Gestion Demandes Clients</h1>
            <ul class="nav-menu">
                <li><a href="../index.php">Tableau de Bord</a></li>
                <li><a href="list.php" class="active">Clients</a></li>
                <li><a href="../demandes/list.php">Demandes</a></li>
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
                <h2>Gestion des Clients</h2>
                <p>Liste de tous les clients</p>
            </div>
            <a href="add.php" class="btn btn-primary">➕ Nouveau Client</a>
        </div>

        <!-- Recherche -->
        <div class="search-box">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Rechercher un client..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
                <?php if ($search): ?>
                    <a href="list.php" class="btn btn-secondary">✖ Effacer</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Entreprise</th>
                    <th>Date d'ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clients)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            Aucun client trouvé
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?= $client['id'] ?></td>
                        <td><strong><?= htmlspecialchars($client['nom']) ?></strong></td>
                        <td><?= htmlspecialchars($client['email']) ?></td>
                        <td><?= htmlspecialchars($client['telephone']) ?></td>
                        <td><?= htmlspecialchars($client['entreprise'] ?: '-') ?></td>
                        <td><?= date('d/m/Y', strtotime($client['created_at'])) ?></td>
                        <td>
                            <a href="edit.php?id=<?= $client['id'] ?>" class="btn-action btn-edit">✏️ Modifier</a>
                            <a href="delete.php?id=<?= $client['id'] ?>"  class="btn-action btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')"> 🗑️ Supprimer</a>
                        </td>
                        
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>