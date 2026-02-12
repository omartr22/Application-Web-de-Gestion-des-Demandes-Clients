<?php
require_once '../includes/check_auth.php';

$error = '';
$success = '';

// Récupérer l'ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Récupérer la demande
$stmt = $pdo->prepare('
    SELECT d.*, c.nom as client_nom 
    FROM demandes d 
    JOIN clients c ON d.client_id = c.id 
    WHERE d.id = ?
');
$stmt->execute([$id]);
$demande = $stmt->fetch();

if (!$demande) {
    header('Location: list.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description']);
    $priorite = $_POST['priorite'];
    $statut = $_POST['statut'];
    
    if (empty($description)) {
        $error = 'La description est obligatoire';
    } else {
        $stmt = $pdo->prepare('
            UPDATE demandes 
            SET description = ?, priorite = ?, statut = ?
            WHERE id = ?
        ');
        
        if ($stmt->execute([$description, $priorite, $statut, $id])) {
            $success = 'Demande modifiée avec succès !';
            // Recharger
            $stmt = $pdo->prepare('
                SELECT d.*, c.nom as client_nom 
                FROM demandes d 
                JOIN clients c ON d.client_id = c.id 
                WHERE d.id = ?
            ');
            $stmt->execute([$id]);
            $demande = $stmt->fetch();
        } else {
            $error = 'Erreur lors de la modification';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Demande - Gestion Demandes</title>
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
                <li><a href="../auth/logout.php" class="btn-logout">Déconnexion</a></li>
            </ul>
            <div class="user-info">
                <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Modifier Demande #<?= htmlspecialchars($demande['numero_ticket']) ?></h2>
            <a href="list.php" class="btn btn-secondary">← Retour à la liste</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <div class="demande-info-box">
            <h3>Informations</h3>
            <p><strong>N° Ticket :</strong> <?= htmlspecialchars($demande['numero_ticket']) ?></p>
            <p><strong>Client :</strong> <?= htmlspecialchars($demande['client_nom']) ?></p>
            <p><strong>Créée le :</strong> <?= date('d/m/Y à H:i', strtotime($demande['date_creation'])) ?></p>
            <p><strong>Dernière modification :</strong> <?= date('d/m/Y à H:i', strtotime($demande['date_modification'])) ?></p>
        </div>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="6" required><?= htmlspecialchars($demande['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="priorite">Priorité *</label>
                    <select id="priorite" name="priorite" required>
                        <option value="basse" <?= $demande['priorite'] === 'basse' ? 'selected' : '' ?>>Basse</option>
                        <option value="normale" <?= $demande['priorite'] === 'normale' ? 'selected' : '' ?>>Normale</option>
                        <option value="haute" <?= $demande['priorite'] === 'haute' ? 'selected' : '' ?>>Haute</option>
                        <option value="urgente" <?= $demande['priorite'] === 'urgente' ? 'selected' : '' ?>>Urgente</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="statut">Statut *</label>
                    <select id="statut" name="statut" required>
                        <option value="en_attente" <?= $demande['statut'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="en_cours" <?= $demande['statut'] === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                        <option value="terminee" <?= $demande['statut'] === 'terminee' ? 'selected' : '' ?>>Terminée</option>
                        <option value="annulee" <?= $demande['statut'] === 'annulee' ? 'selected' : '' ?>>Annulée</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
            </form>
        </div>
    </div>
</body>
</html>
