<?php
require "../backends/config/connexion.php";




// Détection du mode de chargement initial
if (isset($_GET['action']) && $_GET['action'] === 'get_classes') {
    header("Content-Type: application/json");
    try {
        // Sélection avec alias (as) pour correspondre au JavaScript
        $stmt = $bd->query("SELECT code_classe as id, nom_classe as nom, montant_scolarite as frais_scolaire_defaut FROM classes ORDER BY code_classe ASC");
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'classes' => $classes]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Détection de la requête de mise à jour du tarif (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_tarif') {
    header("Content-Type: application/json");
    
    $code_classe = isset($_POST['id_classe']) ? trim($_POST['id_classe']) : ''; // Ici id_classe contient le code (ex: CM1)
    $montant     = isset($_POST['montant']) ? floatval($_POST['montant']) : -1;

    if (empty($code_classe) || $montant < 0) {
        echo json_encode(['success' => false, 'message' => 'Données reçues incorrectes.']);
        exit;
    }

    try {
        // Mise à jour en utilisant 'montant_scolarite' et 'code_classe'
        $sql = "UPDATE classes SET montant_scolarite = ? WHERE code_classe = ?";
        $stmt = $bd->prepare($sql);
        $stmt->execute([$montant, $code_classe]);

        echo json_encode([
            'success' => true,
            'message' => "Le tarif de la classe " . $code_classe . " a été modifié à " . number_format($montant, 0, ',', ' ') . " FCFA."
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Scolarité par Classe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/responsive.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #eef2f5; display: flex; min-height: 100vh; color: #3e5569; }
        .main-content { flex-grow: 1; padding: 30px 15px; }
        .page-main-title { padding-bottom: 25px; font-size: 22px; font-weight: 400; color: #4f5f6f; }
        .table-container { background-color: #ffffff; border-radius: 8px; padding: 20px 15px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #cbd5e0; }
        .table-title { font-size: 18px; font-weight: 600; color: #2d3748; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { font-size: 14px; font-weight: 600; color: #718096; padding: 10px; border-bottom: 2px solid #edf2f7; background-color: #f7fafc; }
        td { font-size: 14px; color: #4a5568; padding: 12px 10px; border-bottom: 1px solid #edf2f7; }
        .input-tarif { padding: 6px 10px; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 14px; color: #2d3748; width: 120px; outline: none; }
        .input-tarif:focus { border-color: #3182ce; }
        .btn-save { background-color: #3182ce; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; transition: background 0.2s; white-space: nowrap; }
        .btn-save:hover { background-color: #2b6cb0; }
        .toast { position: fixed; bottom: 10px; right: 10px; left: 10px; padding: 12px 20px; background-color: #2f855a; color: white; border-radius: 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); display: none; font-weight: 500; z-index: 999; text-align: center; }
        @media (min-width: 768px) {
            .main-content { padding: 30px; }
            .table-container { padding: 30px; }
            .page-main-title { font-size: 22px; }
            th { padding: 12px 16px; font-size: 14px; }
            td { font-size: 15px; padding: 16px; }
            .input-tarif { padding: 8px 12px; width: 150px; }
            .btn-save { padding: 8px 15px; font-size: 13px; }
            .toast { left: auto; }
        }
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
                        <th>Code Classe</th>
                        <th>Nom de la Classe</th>
                        <th>Frais Scolaire (FCFA)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="classes-body">
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 20px; color: #718096;">
                            <i class="fa-solid fa-spinner fa-spin"></i> Chargement des classes...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="toastNotification" class="toast">Tarif mis à jour avec succès !</div>

    <script>
        document.addEventListener("DOMContentLoaded", chargerClasses);

        function chargerClasses() {
            fetch('?action=get_classes')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('classes-body');
                    tbody.innerHTML = ""; 

                    if (data.success) {
                        if(data.classes.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Aucune classe trouvée.</td></tr>`;
                            return;
                        }
                        data.classes.forEach(classe => {
                            // Sécurité au cas où le code classe contient des espaces ou caractères spéciaux pour l'ID HTML
                            const idSecurise = classe.id.replace(/\s+/g, '_');
                            
                            const row = `
                                <tr>
                                    <td><strong>${classe.id}</strong></td>
                                    <td>${classe.nom || 'Non défini'}</td>
                                    <td>
                                        <input type="number" class="input-tarif" id="tarif_${idSecurise}" value="${classe.frais_scolaire_defaut || 0}">
                                    </td>
                                    <td>
                                        <button class="btn-save" onclick="mettreAJourTarif('${classe.id}')">
                                            <i class="fa-solid fa-floppy-disk"></i> Enregistrer
                                        </button>
                                    </td>
                                </tr>
                            `;
                            tbody.innerHTML += row;
                        });
                    } else {
                        tbody.innerHTML = `<tr><td colspan="4" style="color:red; text-align:center;">Erreur : ${data.message}</td></tr>`;
                    }
                })
                .catch(error => {
                    document.getElementById('classes-body').innerHTML = `<tr><td colspan="4" style="text-align:center; color: red;">Erreur de connexion lors du chargement.</td></tr>`;
                });
        }

        function mettreAJourTarif(codeClasse) {
            const idSecurise = codeClasse.replace(/\s+/g, '_');
            const montantSaisi = document.getElementById('tarif_' + idSecurise).value;

            if(montantSaisi === '' || montantSaisi < 0) {
                alert("Veuillez entrer un montant valide.");
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update_tarif');
            formData.append('id_classe', codeClasse); // On envoie le code de la classe au serveur (ex: CI, CP...)
            formData.append('montant', montantSaisi);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const toast = document.getElementById('toastNotification');
                if (data.success) {
                    toast.innerText = data.message;
                    toast.style.backgroundColor = "#2f855a";
                    toast.style.display = "block";
                    setTimeout(() => { toast.style.display = "none"; }, 3000);
                } else {
                    alert("Erreur : " + data.message);
                }
            })
            .catch(error => {
                alert("Erreur réseau ou connexion serveur impossible.");
            });
        }
    </script>
    
</body>
</html>