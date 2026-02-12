<?php
require_once '../includes/check_auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Statuts autorisés
$allowed_statuses = ['en_attente', 'en_cours', 'terminee', 'annulee'];

if ($id > 0 && in_array($status, $allowed_statuses)) {
    $stmt = $pdo->prepare('UPDATE demandes SET statut = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
}

// Rediriger vers la liste
header('Location: list.php');
exit;