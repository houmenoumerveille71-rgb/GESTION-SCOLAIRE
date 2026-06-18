<?php
session_start();
header("Content-Type: application/json");

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    echo json_encode([
        'authenticated' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'nom' => $_SESSION['user_nom'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'utilisateur'
        ]
    ]);
} else {
    echo json_encode([
        'authenticated' => false,
        'user' => null
    ]);
}
?>