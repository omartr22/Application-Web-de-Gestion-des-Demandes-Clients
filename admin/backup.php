<?php
require_once '../includes/check_auth.php';

// Vérifier que l'utilisateur est admin
if ($_SESSION['role'] !== 'admin') {
    die('Accès refusé. Réservé aux administrateurs.');
}

$message = '';
$error = '';

// Configuration Google Drive (OPTIONNEL - pour automatisation)
// Vous pouvez aussi faire une sauvegarde manuelle et l'uploader manuellement

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Créer le dossier de sauvegarde s'il n'existe pas
    $backup_dir = '../backups/';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    // Nom du fichier de sauvegarde
    $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = $backup_dir . $filename;
    
    // Paramètres de connexion
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'gestion_demandes';
    
    // Commande mysqldump (Windows)
    $mysqldump_path = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
    $command = "\"$mysqldump_path\" -h $host -u $user $dbname > \"$filepath\"";
    
    // Exécuter la commande
    exec($command, $output, $return_var);
    
    if ($return_var === 0 && file_exists($filepath)) {
        $message = "Sauvegarde créée avec succès : $filename";
        
        // Instructions pour upload manuel
        $message .= "<br><br><strong>Prochaines étapes :</strong><br>";
        $message .= "1. Le fichier se trouve dans : " . realpath($filepath) . "<br>";
        $message .= "2. Uploadez-le sur Google Drive, Dropbox ou votre service cloud préféré<br>";
        $message .= "3. Conservez une copie locale également";
    } else {
        $error = "Erreur lors de la création de la sauvegarde";
    }
}

// Lister les sauvegardes existantes
$backup_dir = '../backups/';
$backups = [];
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = [
                'name' => $file,
                'size' => filesize($backup_dir . $file),
                'date' => filemtime($backup_dir . $file)
            ];
        }
    }
    // Trier par date (plus récent en premier)
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sauvegarde - Admin</title>
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
                <li><a href="backup.php" class="active">Sauvegarde</a></li>
                <li><a href="../auth/logout.php" class="btn-logout">Déconnexion</a></li>
            </ul>
            <div class="user-info">
                <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>🔒 Sauvegarde de la Base de Données</h2>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="backup-box">
            <h3>Créer une nouvelle sauvegarde</h3>
            <p>Cette action va créer un fichier .sql contenant toutes les données de la base de données.</p>
            <form method="POST" action="">
                <button type="submit" class="btn btn-primary">💾 Créer la sauvegarde maintenant</button>
            </form>
        </div>

        <div class="backups-list">
            <h3>Sauvegardes existantes</h3>
            <?php if (empty($backups)): ?>
                <p style="text-align: center; padding: 30px; color: #718096;">
                    Aucune sauvegarde disponible
                </p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nom du fichier</th>
                            <th>Taille</th>
                            <th>Date de création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($backup['name']) ?></strong></td>
                            <td><?= number_format($backup['size'] / 1024, 2) ?> KB</td>
                            <td><?= date('d/m/Y H:i:s', $backup['date']) ?></td>
                            <td>
                                <a href="../backups/<?= htmlspecialchars($backup['name']) ?>" 
                                   class="btn-action btn-edit" download>
                                    ⬇️ Télécharger
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="cloud-info">
            <h3>📤 Upload vers le Cloud</h3>
            <p><strong>Méthode 1 : Manuel (Recommandé pour débuter)</strong></p>
            <ol>
                <li>Télécharger la sauvegarde créée ci-dessus</li>
                <li>Se connecter à Google Drive ou Dropbox</li>
                <li>Créer un dossier "Sauvegardes Gestion Demandes"</li>
                <li>Uploader le fichier .sql dans ce dossier</li>
            </ol>

            <p style="margin-top: 20px;"><strong>Méthode 2 : Automatique (Avancé)</strong></p>
            <p>Pour automatiser l'upload vers Google Drive :</p>
            <ol>
                <li>Créer un projet sur Google Cloud Console</li>
                <li>Activer l'API Google Drive</li>
                <li>Installer la librairie PHP Google Drive</li>
                <li>Configurer les credentials OAuth2</li>
            </ol>
            <p><em>Cette fonctionnalité peut être développée plus tard si nécessaire.</em></p>
        </div>
    </div>
</body>
</html>
