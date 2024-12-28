<?php
session_start();
require_once "connexion.php";
$selected_stagiaires = array();
$matricul = "";

if (isset($_SESSION["matricul"]) ) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["envoyer_matricul"])) {
        $matricul = $_POST["matricul"];
        if (!empty($matricul)) {
            try {
                // Récupérer les absences non justifiées pour le stagiaire
                $sql1 = "SELECT ab.id, st.nom, st.prenom, ab.jour_absence, se.date_seance AS seance_absence 
                         FROM stagiaires AS st 
                         JOIN absences AS ab ON ab.stagiaire_id = st.id 
                         JOIN seances AS se ON se.id = ab.seance_id 
                         WHERE st.matricul = :matricul AND ab.justifie = 0;";
                $stmt1 = $pdo->prepare($sql1);
                $stmt1->bindParam(':matricul', $matricul);
                $stmt1->execute();
                $result1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Erreur de requête : " . $e->getMessage());
            }

            // Récupérer les motifs
            try {
                $sql2 = "SELECT id, motif FROM motifs";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->execute();
                $motifs = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Erreur de requête : " . $e->getMessage());
            }

            // Récupérer l'ID du stagiaire
            try {
                $sql3 = "SELECT id FROM stagiaires WHERE matricul = :matricul";
                $stmt3 = $pdo->prepare($sql3);
                $stmt3->bindParam(':matricul', $matricul);
                $stmt3->execute();
                $_SESSION["stagiaire_id"] = $stmt3->fetchColumn();
            } catch (PDOException $e) {
                die("Erreur de requête : " . $e->getMessage());
            }
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["justifier_absence"])) {
        $selected_stagiaires = isset($_POST['selected_stagiaires']) ? $_POST['selected_stagiaires'] : array();
        $fichier = null;
    
        // Gestion de l'upload du fichier en dehors de la boucle
        if ($_FILES["fichier"]["error"] !== 4) {
            $fileName = $_FILES["fichier"]["name"];
            $fileSize = $_FILES["fichier"]["size"];
            $tmpName = $_FILES["fichier"]["tmp_name"];
            $error = $_FILES["fichier"]["error"];
        
            if ($error !== 0) {
                echo "<script>alert('Erreur lors du téléchargement du fichier : $error');</script>";
                return;
            }
        
            $valideFileExtensions = ['jpg', 'png', 'jpeg', 'txt', 'docx'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
            if (!in_array($fileExtension, $valideFileExtensions)) {
                echo "<script>alert('Extension de fichier non prise en charge');</script>";
            } elseif ($fileSize > 5000000) {
                echo "<script>alert('La taille du fichier est trop grande');</script>";
            } else {
                // Utiliser l'ID du stagiaire et un horodatage pour créer un nom de fichier unique
                $stagiaire_id = $_SESSION["stagiaire_id"]; // Assurez-vous que cette variable est bien définie
                $timestamp = time();
                $newFileName = $stagiaire_id . '_' . $timestamp . '_' . $fileName;
        
                if (move_uploaded_file($tmpName, 'uploads/' . $newFileName)) {
                    $fichier = $newFileName;
                } else {
                    echo "<script>alert('Échec du téléchargement du fichier.');</script>";
                }
            }
        }
        
    
        // Boucle pour enregistrer chaque justification avec le même fichier si téléchargé
        foreach ($selected_stagiaires as $absence_id) {
            try {
                $sql4 = "UPDATE absences SET justifie = 1 WHERE id = :id";
                $stmt4 = $pdo->prepare($sql4);
                $stmt4->bindParam(':id', $absence_id);
                $stmt4->execute();
                
                $sql5 = "INSERT INTO justif(stagiaire_id, absence_id, motif_id, description_motif, fichier) 
                         VALUES (:stagiaire_id, :absence_id, :motif_id, :description_motif, :fichier)";
                $stmt5 = $pdo->prepare($sql5);
                $stmt5->bindParam(':stagiaire_id', $_SESSION["stagiaire_id"]);
                $stmt5->bindParam(':absence_id', $absence_id);
                $stmt5->bindParam(':motif_id', $_POST["motif"]);
                $stmt5->bindParam(':description_motif', $_POST["description_motif"]);
                $stmt5->bindParam(':fichier', $fichier);
                $stmt5->execute();
    
                echo "<script>alert('Enregistrement validé pour l'absence $absence_id !');</script>";
            } catch (PDOException $e) {
                die("Erreur de requête : " . $e->getMessage());
            }
    
            // Mettre à jour la note de discipline
            try {
                $sql_get_discipline = "SELECT note_disipline FROM stagiaires WHERE id = :stagiaire_id";
                $stmt_get_discipline = $pdo->prepare($sql_get_discipline);
                $stmt_get_discipline->bindParam(':stagiaire_id', $_SESSION["stagiaire_id"]);
                $stmt_get_discipline->execute();
                $current_discipline = $stmt_get_discipline->fetchColumn();
    
                if ($current_discipline < 20) {
                    $new_discipline = min($current_discipline + 0.50, 20);
                    $sql_update_discipline = "UPDATE stagiaires SET note_disipline = :new_discipline WHERE id = :stagiaire_id";
                    $stmt_update_discipline = $pdo->prepare($sql_update_discipline);
                    $stmt_update_discipline->bindParam(':stagiaire_id', $_SESSION["stagiaire_id"]);
                    $stmt_update_discipline->bindParam(':new_discipline', $new_discipline);
                    $stmt_update_discipline->execute();
                }
            } catch (PDOException $e) {
                die("Erreur de requête : " . $e->getMessage());
            }
        }
    }
    
} else {
    header("location:login.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Justifier les Absences</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  
</head>

<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar with Navy Blue Background -->
        <aside id="sidebar" class="w-64 bg-blue-900 shadow-md hidden md:block">
            <div class="p-6">
            <img src="https://th.bing.com/th/id/R.b6fae5d9e78a1e2dfe1059c9db1a68aa?rik=zvz3oE9xuEBxVQ&riu=http%3a%2f%2ftous-logos.com%2fwp-content%2fuploads%2f2019%2f03%2fLogo-OFPPT.png&ehk=n4bjmSAkWEmjJcCrgAFqAjxagn2Sypl4RqRrYYgr9gw%3d&risl=&pid=ImgRaw&r=0" alt="Logo" class="w-24 h-auto mx-auto mb-4">
            <nav class="mt-6">
                    <ul>
                        <li class="my-2"><a href="opp.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded"><i class="fas fa-home mr-3"></i>Operation</a></li>
                        <li class="my-2"><a href="justification.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded"><i class="fas fa-check-circle mr-3"></i>Justifier les absences</a></li>
                        <li class="my-2">
                            <a href="formateures_login.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-gavel mr-3"></i>Enregister les absences
                            </a>
                        </li>
                        <li class="my-2"><a href="statistique_Groupe.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded"><i class="fas fa-chart-pie mr-3"></i>Les Statistique par group</a></li>
                        <li class="my-2"><a href="discipline.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded"><i class="fas fa-gavel mr-3"></i>Discipline</a></li>
                        <li class="my-2"><a href="logout.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded"><i class="fas fa-sign-out-alt mr-3"></i>Deconnecter</a></li>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="flex-grow p-6">
            <header class="flex items-center justify-between mb-6">
                <button onclick="toggleSidebar()" class="md:hidden p-2 text-gray-600 hover:bg-gray-200 rounded">
                    <i class="fas fa-bars"></i>
                </button>
            </header>


        <!-- ========================= Main ==================== -->
        <div class="main">
           

            <!-- ======================= Cards ================== -->
            <div class="container-fluid">
            <div class="container mx-auto px-4">
    <div class="form-container-fluid py-6">
        <h1 class="text-center text-2xl font-bold mb-4">Recherche des Absences</h1>
        <form method="POST">
            <div class="mb-4">
                <label for="matricul" class="block text-sm font-medium text-gray-700">Matricule</label>
                <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="matricul" name="matricul" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" name="envoyer_matricul">Envoyer Matricule</button>
        </form>
    </div>

    <?php if (!empty($result1)): ?>
    <div class="form-container mt-5">
        <h2 class="text-center text-2xl font-bold mb-4">Liste des absences</h2>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off" enctype="multipart/form-data" class='form'>
            <table class="min-w-full bg-white shadow rounded-lg">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-2 px-4 border-b text-left">Nom</th>
                        <th class="py-2 px-4 border-b text-left">Prénom</th>
                        <th class="py-2 px-4 border-b text-left">Jour d'absence</th>
                        <th class="py-2 px-4 border-b text-left">Séance d'absence</th>
                        <th class="py-2 px-4 border-b text-left">Justifier cette absence</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result1 as $row): ?>
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['nom']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['prenom']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['jour_absence']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['seance_absence']); ?></td>
                        <td class="py-2 px-4 border-b"><input type="checkbox" name="selected_stagiaires[]" value="<?php echo $row['id']; ?>"></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="mb-4 mt-6">
                <label for="motif" class="block text-sm font-medium text-gray-700">Motif</label>
                <select class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="motif" name="motif" required>
                    <?php foreach ($motifs as $motif): ?>
                    <option value="<?php echo $motif['id']; ?>"><?php echo htmlspecialchars($motif['motif']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="description_motif" class="block text-sm font-medium text-gray-700">Description du motif</label>
                <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="description_motif" name="description_motif" placeholder="Description du motif" required>
            </div>
            <div class="mb-4">
                <label for="fichier" class="block text-sm font-medium text-gray-700">Ajouter un fichier</label>
                <input type="file" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="fichier" name="fichier">
                <p class="mt-1 text-sm text-gray-500">Extensions de fichiers acceptées : jpg, png, jpeg, txt, docx</p>
            </div>
            <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" name="justifier_absence">Enregistrer les absences</button>
        </form>
    </div>
    <?php else: ?>
    <div class="alert alert-warning mt-5 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
        Aucun stagiaire trouvé.
    </div>
    <?php endif; ?>
</div>

     
           
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

  
        <script>
        document.addEventListener('DOMContentLoaded', function() {
        const formElement = document.querySelector('.form');
        if (formElement) { // Vérifiez si l'élément existe
            formElement.addEventListener('submit', function (e) {
                const selected = document.querySelectorAll('input[type="checkbox"]:checked');
                if (selected.length === 0) {
                    e.preventDefault();
                    alert('Veuillez sélectionner au moins une absence à justifier.');
                }
            });
        }
    });

    function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("hidden");
        }
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>