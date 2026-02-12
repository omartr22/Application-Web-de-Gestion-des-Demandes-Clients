<?php
require_once '../includes/check_auth.php';

$error = '';
$success = '';

// Récupérer l'ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Récupérer le client
$stmt = $pdo->prepare('SELECT * FROM clients WHERE id = ?');
$stmt->execute([$id]);
$client = $stmt->fetch();

if (!$client) {
    header('Location: list.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $entreprise = trim($_POST['entreprise']);

    // Validation
    if (empty($nom) || empty($email)) {
        $error = 'Le nom et l\'email sont obligatoires';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide';
    } else {
        // Vérifier si l'email existe déjà (sauf pour ce client)
        $check = $pdo->prepare('SELECT id FROM clients WHERE email = ? AND id != ?');
        $check->execute([$email, $id]);
        
        if ($check->fetch()) {
            $error = 'Un autre client utilise déjà cet email';
        } else {
            // Mettre à jour
            $stmt = $pdo->prepare('
                UPDATE clients 
                SET nom = ?, email = ?, telephone = ?, entreprise = ?
                WHERE id = ?
            ');
            
            if ($stmt->execute([$nom, $email, $telephone, $entreprise, $id])) {
                $success = 'Client modifié avec succès !';
                // Recharger les données
                $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = ?');
                $stmt->execute([$id]);
                $client = $stmt->fetch();
            } else {
                $error = 'Erreur lors de la modification';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Client - Gestion Demandes</title>
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
                <li><a href="../auth/logout.php" class="btn-logout">Déconnexion</a></li>
            </ul>
            <div class="user-info">
                <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Modifier Client</h2>
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
                    <label for="nom">Nom complet *</label>
                    <input type="text" id="nom" name="nom" required 
                           value="<?= htmlspecialchars($client['nom']) ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= htmlspecialchars($client['email']) ?>">
                </div>

                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" 
                           value="<?= htmlspecialchars($client['telephone']) ?>">
                </div>

                <div class="form-group">
                    <label for="entreprise">Entreprise</label>
                    <input type="text" id="entreprise" name="entreprise" 
                           value="<?= htmlspecialchars($client['entreprise']) ?>">
                </div>

                <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
            </form>
        </div>
    </div>
</body>
</html>