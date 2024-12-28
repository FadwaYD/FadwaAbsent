<?php
require_once "connexion.php"; // Assurez-vous que connexion.php contient la connexion PDO

// Initialisation des variables
$nom_filiere = "";
$nom_group = "";
$annee = "";
$mois_debut = 1; // Mois de début par défaut
$mois_fin = 12; // Mois de fin par défaut
$donnees_absences = array();

// Récupérer les filières
$filiere_query = "SELECT nom_filier FROM filieres";
$filiere_stmt = $pdo->prepare($filiere_query);
$filiere_stmt->execute();
$filiere_options = $filiere_stmt->fetchAll(PDO::FETCH_COLUMN);

// Récupérer les groupes
$group_query = "SELECT nom_group FROM groupes";
$group_stmt = $pdo->prepare($group_query);
$group_stmt->execute();
$group_options = $group_stmt->fetchAll(PDO::FETCH_COLUMN);

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nom_filiere"]) && isset($_POST["annee"])) {
    // Récupération des données du formulaire
    $nom_filiere = $_POST["nom_filiere"];
    $nom_group = $_POST["nom_group"] ?? null; // Récupérer le groupe s'il est fourni
    $annee = $_POST["annee"];
    
    // Vérification de la saisie des mois de début et de fin
    if (isset($_POST["mois_debut"]) && isset($_POST["mois_fin"])) {
        $mois_debut = max(1, min(12, $_POST["mois_debut"])); // Assure que le mois de début est entre 1 et 12
        $mois_fin = max($mois_debut, min(12, $_POST["mois_fin"])); // Assure que le mois de fin est entre le mois de début et 12
    }

    // Requête pour récupérer les données d'absence pour le groupe spécifié, l'année donnée, et le mois intervalle spécifié
    $query = "SELECT MONTH(absences.jour_absence) AS mois, COUNT(absences.id) AS nombre_absences
              FROM absences
              INNER JOIN stagiaires ON absences.stagiaire_id = stagiaires.id
              INNER JOIN groupes ON stagiaires.group_id = groupes.id
              INNER JOIN filieres ON groupes.filiere_id = filieres.id
              WHERE filieres.nom_filier = :nom_filiere 
              AND (:nom_group IS NULL OR groupes.nom_group = :nom_group) 
              AND YEAR(absences.jour_absence) = :annee
              AND MONTH(absences.jour_absence) BETWEEN :mois_debut AND :mois_fin
              GROUP BY mois
              ORDER BY mois";

    $stmt = $pdo->prepare($query);

    // Liaison des paramètres
    $stmt->bindParam(':nom_filiere', $nom_filiere);
    if ($nom_group) {
        $stmt->bindParam(':nom_group', $nom_group);
    } else {
        $null = null;
        $stmt->bindParam(':nom_group', $null, PDO::PARAM_NULL); // Si nom_group est vide, on lie une valeur NULL
    }
    $stmt->bindParam(':annee', $annee);
    $stmt->bindParam(':mois_debut', $mois_debut);
    $stmt->bindParam(':mois_fin', $mois_fin);

    // Exécution de la requête
    $stmt->execute();

    // Récupération des résultats
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si la requête s'est bien exécutée
    if ($result) {
        // Initialiser le tableau des absences par mois
        $donnees_absences = array_fill($mois_debut, $mois_fin - $mois_debut + 1, 0); // Mois initialisés à 0

        // Parcourir les résultats de la requête
        foreach ($result as $row) {
            // Stocker le nombre d'absences pour le mois spécifié
            $mois = $row['mois'];
            $nombre_absences = $row['nombre_absences'];
            $donnees_absences[$mois] = $nombre_absences;
        }
    } 
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("hidden");
        }
    </script>
</head>

<body class="bg-gray-100">
    <div class="flex min-h-screen flex-col md:flex-row">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-full md:w-64 bg-blue-900 shadow-md hidden md:block">
            <div class="p-6">
                <img src="https://th.bing.com/th/id/R.b6fae5d9e78a1e2dfe1059c9db1a68aa?rik=zvz3oE9xuEBxVQ&riu=http%3a%2f%2ftous-logos.com%2fwp-content%2fuploads%2f2019%2f03%2fLogo-OFPPT.png&ehk=n4bjmSAkWEmjJcCrgAFqAjxagn2Sypl4RqRrYYgr9gw%3d&risl=&pid=ImgRaw&r=0" alt="Logo" class="w-24 h-auto mx-auto mb-4">
                <nav class="mt-6">
                    <ul>
                        <li class="my-2">
                            <a href="opp.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-home mr-3"></i>Operation
                            </a>
                        </li>
                        <li class="my-2">
                            <a href="justification.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-check-circle mr-3"></i>Justifier les absences
                            </a>
                        </li>
                        <li class="my-2">
                            <a href="formateures_login.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-gavel mr-3"></i>Enregister les absences
                            </a>
                        </li>
                       
                        <li class="my-2">
                            <a href="statistique_Groupe.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-chart-pie mr-3"></i>Les Statistique par groupe
                            </a>
                        </li>
                        <li class="my-2">
                            <a href="discipline.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-gavel mr-3"></i>Discipline
                            </a>
                        </li>
                        <li class="my-2">
                            <a href="logout.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-sign-out-alt mr-3"></i>Deconnecter
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-grow p-6">
            <div class="flex justify-between items-center mb-4">
                <button onclick="toggleSidebar()" class="md:hidden bg-blue-500 text-white p-2 rounded mb-4">
                    Toggle Sidebar
                </button>
            </div>

            <h2 class="text-green-600 text-center text-2xl font-semibold mb-6">Graphe des absences par mois pour la filière <?php echo htmlspecialchars($nom_filiere); ?> en <?php echo htmlspecialchars($annee); ?></h2>

            <!-- Formulaire pour entrer le nom de la filière et le groupe -->
            <form method="POST" class="mb-4">
                <div class="flex flex-col md:flex-row mb-4">
                    <div class="md:w-1/3 md:mr-2 mb-2">
                        <label for="nom_filiere" class="block text-gray-700">Filière :</label>
                        <select name="nom_filiere" id="nom_filiere" required class="w-full p-2 border border-gray-300 rounded">
                            <option value="">Sélectionnez une filière</option>
                            <?php foreach ($filiere_options as $filiere) : ?>
                                <option value="<?php echo htmlspecialchars($filiere); ?>"><?php echo htmlspecialchars($filiere); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:w-1/3 md:mr-2 mb-2">
                        <label for="nom_group" class="block text-gray-700">Groupe :</label>
                        <select name="nom_group" id="nom_group" class="w-full p-2 border border-gray-300 rounded">
                            <option value="">Sélectionnez un groupe</option>
                            <?php foreach ($group_options as $group) : ?>
                                <option value="<?php echo htmlspecialchars($group); ?>"><?php echo htmlspecialchars($group); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:w-1/3 mb-2">
                        <label for="annee" class="block text-gray-700">Année :</label>
                        <input type="number" name="annee" id="annee" required value="<?php echo htmlspecialchars($annee); ?>" class="w-full p-2 border border-gray-300 rounded">
                    </div>
                </div>
                <div class="flex flex-col md:flex-row mb-4">
                    <div class="md:w-1/2 md:mr-2 mb-2">
                        <label for="mois_debut" class="block text-gray-700">Mois de début :</label>
                        <input type="number" name="mois_debut" id="mois_debut" value="<?php echo htmlspecialchars($mois_debut); ?>" min="1" max="12" class="w-full p-2 border border-gray-300 rounded">
                    </div>
                    <div class="md:w-1/2 mb-2">
                        <label for="mois_fin" class="block text-gray-700">Mois de fin :</label>
                        <input type="number" name="mois_fin" id="mois_fin" value="<?php echo htmlspecialchars($mois_fin); ?>" min="1" max="12" class="w-full p-2 border border-gray-300 rounded">
                    </div>
                </div>
                <button type="submit" class="bg-blue-600 text-white p-2 rounded">Générer le graphique</button>
            </form>

            <!-- Section pour afficher le graphique -->
            <div class="mb-4">
                <canvas id="myChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('myChart').getContext('2d');
        const labels = Array.from({ length: <?php echo $mois_fin - $mois_debut + 1; ?> }, (_, i) => <?php echo $mois_debut; ?> + i);
        const data = {
            labels: labels,
            datasets: [{
                label: 'Nombre d\'absences',
                data: <?php echo json_encode(array_values($donnees_absences)); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };
        const config = {
            type: 'bar',
            data: data,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };
        const myChart = new Chart(ctx, config);
    </script>
</body>
</html>
