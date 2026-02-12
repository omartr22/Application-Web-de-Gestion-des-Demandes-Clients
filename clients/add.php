<?php
require_once '../includes/check_auth.php';

$error = '';
$success = '';

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
        // Vérifier si l'email existe déjà
        $check = $pdo->prepare('SELECT id FROM clients WHERE email = ?');
        $check->execute([$email]);
        
        if ($check->fetch()) {
            $error = 'Un client avec cet email existe déjà';
        } else {
            // Insérer le nouveau client
            $stmt = $pdo->prepare('
                INSERT INTO clients (nom, email, telephone, entreprise) 
                VALUES (?, ?, ?, ?)
            ');
            
            if ($stmt->execute([$nom, $email, $telephone, $entreprise])) {
                $success = 'Client ajouté avec succès !';
                // Réinitialiser le formulaire
                $_POST = [];
            } else {
                $error = 'Erreur lors de l\'ajout du client';
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
    <title>Nouveau Client - Gestion Demandes</title>
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
            <h2>Nouveau Client</h2>
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
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" 
                           value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="entreprise">Entreprise</label>
                    <input type="text" id="entreprise" name="entreprise" 
                           value="<?= htmlspecialchars($_POST['entreprise'] ?? '') ?>">
                </div>

                <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
            </form>
        </div>
    </div>
</body>
</html>
