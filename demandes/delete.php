<?php
require_once '../includes/check_auth.php';

// Vérifier l'ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Supprimer la demande
    $stmt = $pdo->prepare('DELETE FROM demandes WHERE id = ?');
    $stmt->execute([$id]);
}

// Rediriger vers la liste
header('Location: list.php');
exit;