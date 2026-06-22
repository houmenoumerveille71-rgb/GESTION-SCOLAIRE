
<?php
// Charger la connexion
require "../config/connexion.php";

header("Content-Type: application/json");
// ACTION 1 : Lecture des données (Équivalent de read.php)
if (isset($_GET['action']) && $_GET['action'] === 'read') {
    header('Content-Type: application/json');
    try {
        // Sélection de l'id, du nom et des frais par défaut (ajustez les noms des colonnes si nécessaire)
        $stmt = $pdo->query("SELECT id, nom_classe, frais_scolaire_defaut FROM classes ORDER BY id ASC");
        $classes = $stmt->fetchAll();
        echo json_encode($classes);
    } catch (Exception $e) {
        echo json_encode([]);
    }
    exit; // Stop l'exécution ici pour ne pas charger le HTML
}

// ACTION 2 : Mise à jour des tarifs (Équivalent de update.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['frais_scolaire_defaut'])) {
    header('Content-Type: application/json');
    
    $id = intval($_POST['id']);
    $frais = floatval($_POST['frais_scolaire_defaut']);

    if ($id <= 0 || $frais < 0) {
        echo json_encode(['success' => false, 'message' => "Données saisies invalides."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE classes SET frais_scolaire_defaut = :frais WHERE id = :id");
        $result = $stmt->execute([
            ':frais' => $frais,
            ':id' => $id
        ]);

        if ($stmt->rowCount() >= 0) { // >= 0 car si la valeur ne change pas, rowCount renvoie 0 mais c'est un succès
            echo json_encode(['success' => true, 'message' => "Tarif mis à jour avec succès !"]);
        } else {
            echo json_encode(['success' => false, 'message' => "Aucune modification apportée."]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "Erreur lors de la mise à jour : " . $e->getMessage()]);
    }
    exit; // Stop l'exécution ici pour ne pas charger le HTML
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Scolarité par Classe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #eef2f5; display: flex; min-height: 100vh; color: #3e5569; }
        .main-content { flex-grow: 1; padding: 30px; }
        .page-main-title { padding-bottom: 25px; font-size: 22px; font-weight: 400; color: #4f5f6f; }
        .table-container { background-color: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #cbd5e0; }
        .table-title { font-size: 18px; font-weight: 600; color: #2d3748; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { font-size: 14px; font-weight: 600; color: #718096; padding: 12px 16px; border-bottom: 2px solid #edf2f7; background-color: #f7fafc; }
        td { font-size: 15px; color: #4a5568; padding: 16px; border-bottom: 1px solid #edf2f7; }
        .input-tarif { padding: 8px 12px; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 14px; color: #2d3748; width: 150px; outline: none; }
        .input-tarif:focus { border-color: #3182ce; }
        .btn-save { background-color: #3182ce; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; transition: background 0.2s; }
        .btn-save:hover { background-color: #2b6cb0; }
        .toast { position: fixed; bottom: 20px; right: 20px; padding: 15px 25px; color: white; border-radius: 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); display: none; font-weight: 500; z-index: 999; }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="page-main-title">Configuration Générale</div>
        <div class="table-container">
            <div class="table-title">Définition des frais de scolarité par classe</div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom de la Classe</th>
                        <th>Frais Scolaire (FCFA)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="classes-body">
                    </tbody>
            </table>
        </div>
    </div>

    <div id="toastNotification" class="toast"></div>

    <script>
        // URL du script actuel (puisque tout est centralisé ici)
        const currentScriptUrl = window.location.href.split('?')[0];

        // Chargement des classes au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            fetch(`${currentScriptUrl}?action=read`)
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('classes-body');
                    tbody.innerHTML = ""; // Vide le tableau par sécurité
                    
                    if(data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Aucune classe trouvée.</td></tr>`;
                        return;
                    }

                    data.forEach(classe => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${classe.id}</td>
                            <td>${classe.nom_classe}</td>
                            <td><input type="number" class="input-tarif" id="tarif_${classe.id}" value="${classe.frais_scolaire_defaut || 0}" min="0"></td>
                            <td><button class="btn-save" onclick="mettreAJourTarif(${classe.id})"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button></td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(error => {
                    console.error("Erreur de chargement:", error);
                });
        });

        // Fonction de mise à jour du tarif
        function mettreAJourTarif(idClasse) {
            const montantSaisi = document.getElementById('tarif_' + idClasse).value;

            if(montantSaisi === '' || montantSaisi < 0) {
                alert("Veuillez entrer un montant valide.");
                return;
            }

            const formData = new FormData();
            formData.append('id', idClasse);
            formData.append('frais_scolaire_defaut', montantSaisi);

            fetch(currentScriptUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const toast = document.getElementById('toastNotification');
                toast.innerText = data.message;
                
                if (data.success) {
                    toast.style.backgroundColor = "#2f855a"; // Vert
                } else {
                    toast.style.backgroundColor = "#e53e3e"; // Rouge
                }
                
                toast.style.display = "block";
                setTimeout(() => { toast.style.display = "none"; }, 4000);
            })
            .catch(error => {
                alert("Erreur réseau ou connexion impossible au serveur.");
            });
        }
    </script>
</body>
</html>
