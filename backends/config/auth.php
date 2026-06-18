<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function estConnecte() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check if user is admin
function estAdmin() {
    return estConnecte() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Function to redirect to login if not connected
function redirigerSiNonConnecte() {
    if (!estConnecte()) {
        header('Location: ../frontends/connexion.html');
        exit;
    }
}

// Function to redirect to home if not admin (or show error for API)
function redirigerSiNonAdmin() {
    if (!estAdmin()) {
        // For API requests, we return JSON error
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Accès non autorisé : privilèges administrateur requis']);
            exit;
        } else {
            // For regular page requests, redirect to an error page or home
            header('Location: ../frontends/acces_interdit.html');
            exit;
        }
    }
}
?>