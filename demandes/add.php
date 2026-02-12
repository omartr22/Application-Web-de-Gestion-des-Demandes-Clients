<?php
require_once '../includes/check_auth.php';

$error = '';
$success = '';

// Récupérer la liste des clients
$clients = $pdo->query('SELECT id, nom, email FROM clients ORDER BY nom')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = (int)$_POST['client_id'];
    $description = trim($_POST['description']);
    $priorite = $_POST['priorite'];
    
    // Validation
    if ($client_id <= 0 || empty($description)) {
        $error = 'Tous les champs obligatoires doivent être remplis';
    } else {
        // Générer le numéro de ticket
        $year = date('Y');
        $count = $pdo->query("SELECT COUNT(*) FROM demandes WHERE YEAR(date_creation) = $year")->fetchColumn();
        $numero_ticket = "DMD-$year-" . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        // Insérer la demande
        $stmt = $pdo->prepare('
            INSERT INTO demandes (numero_ticket, client_id, description, priorite, created_by) 
            VALUES (?, ?, ?, ?, ?)
        ');
        
        if ($stmt->execute([$numero_ticket, $client_id, $description, $priorite, $_SESSION['user_id']])) {
            $success = "Demande créée avec succès ! N° de ticket : $numero_ticket";
            $_POST = []; // Réinitialiser
        } else {
            $error = 'Erreur lors de la création de la demande';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Demande - Gestion Demandes</title>
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
            <h2>Nouvelle Demande</h2>
            <a href="list.php" class="btn btn-secondary">← Retour à la liste</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="client_id">Client *</label>
                    <select id="client_id" name="client_id" required>
                        <option value="">-- Sélectionner un client --</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id'] ?>" 
                                    <?= (isset($_POST['client_id']) && $_POST['client_id'] == $client['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['nom']) ?> (<?= htmlspecialchars($client['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>
                        <a href="../clients/add.php" target="_blank">+ Créer un nouveau client</a>
                    </small>
                </div>

                <div class="form-group">
                    <label for="description">Description de la demande *</label>
                    <textarea id="description" name="description" rows="6" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="priorite">Priorité *</label>
                    <select id="priorite" name="priorite" required>
                        <option value="basse" <?= (isset($_POST['priorite']) && $_POST['priorite'] === 'basse') ? 'selected' : '' ?>>
                            Basse
                        </option>
                        <option value="normale" selected <?= (isset($_POST['priorite']) && $_POST['priorite'] === 'normale') ? 'selected' : '' ?>>
                            Normale
                        </option>
                        <option value="haute" <?= (isset($_POST['priorite']) && $_POST['priorite'] === 'haute') ? 'selected' : '' ?>>
                            Haute
                        </option>
                        <option value="urgente" <?= (isset($_POST['priorite']) && $_POST['priorite'] === 'urgente') ? 'selected' : '' ?>>
                            Urgente
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">💾 Créer la demande</button>
            </form>
        </div>
    </div>
</body>
</html>
