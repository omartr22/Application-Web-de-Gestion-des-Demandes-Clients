<?php
require_once '../includes/check_auth.php';

// Récupérer l'ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // Vérifier si le client a des demandes associées
        $check = $pdo->prepare('SELECT COUNT(*) FROM demandes WHERE client_id = ?');
        $check->execute([$id]);
        $count = $check->fetchColumn();
        
        if ($count > 0) {
            // Le client a des demandes, on ne peut pas le supprimer directement
            $_SESSION['error'] = "Impossible de supprimer ce client car il a $count demande(s) associée(s).";
        } else {
            // Supprimer le client
            $stmt = $pdo->prepare('DELETE FROM clients WHERE id = ?');
            $stmt->execute([$id]);
            $_SESSION['success'] = 'Client supprimé avec succès !';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erreur lors de la suppression du client.';
    }
}

// Rediriger vers la liste
header('Location: list.php');
exit;