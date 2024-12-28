<?php
session_start();
require_once "connexion.php"; 

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Récupérer les informations actuelles du stagiaire
        $query = "SELECT st.id, st.matricul, st.nom, st.prenom, st.note_disipline, st.group_id, gr.nom_group, gr.filiere_id, fi.nom_filier, ut.email 
                  FROM stagiaires st 
                  JOIN groupes gr ON st.group_id = gr.id 
                  JOIN filieres fi ON gr.filiere_id = fi.id 
                  JOIN utilisateur ut ON st.matricul = ut.matricul 
                  WHERE st.id = :id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $stagiaire = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stagiaire) {
            header("Location: operation.php");
            exit();
        }

        // Récupérer toutes les filières
        $filieresQuery = "SELECT distinct id, nom_filier FROM filieres";
        $filieresStmt = $pdo->query($filieresQuery);
        $filieres = $filieresStmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer tous les groupes
        $groupesQuery = "SELECT distinct id, nom_group, filiere_id FROM groupes";
        $groupesStmt = $pdo->query($groupesQuery);
        $groupes = $groupesStmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        header("Location: operation.php");
        exit();
    }
} else {
    header("Location: operation.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricul = $_POST['matricul'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $group_id = $_POST['group_id'];

    try {
        $query = "UPDATE stagiaires st 
                  JOIN utilisateur ut ON st.matricul = ut.matricul 
                  SET st.nom = :nom, st.prenom = :prenom, ut.email = :email, st.group_id = :group_id
                  WHERE st.id = :id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);

        $stmt->execute();

        header("Location: opp.php");
        exit();

    } catch (PDOException $e) {
        // header("Location: modifier_stagiaire.php?id=" . $id);
        exit();
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
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("hidden");
        }
    </script>
</head>

<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-blue-900 shadow-md hidden md:block">
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
                            <a href="#" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-sign-out-alt mr-3"></i>Deconnecter
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-grow p-6">
            <div class="flex justify-between items-center">
                <button onclick="toggleSidebar()" class="md:hidden p-2 text-gray-700">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="text-xl font-semibold text-center text-green-600">Modifier Stagiaire</h2>
            </div>

            <!-- Form -->
            <div class="mt-6">
                <form method="post" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <div class="mb-4">
                        <label for="matricul" class="block text-gray-700 text-sm font-bold mb-2">Matricule</label>
                        <input type="text" id="matricul" name="matricul" value="<?php echo htmlspecialchars($stagiaire['matricul']); ?>" readonly class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label for="nom" class="block text-gray-700 text-sm font-bold mb-2">Nom</label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($stagiaire['nom']); ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label for="prenom" class="block text-gray-700 text-sm font-bold mb-2">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($stagiaire['prenom']); ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label for="note_disipline" class="block text-gray-700 text-sm font-bold mb-2">Note Discipline</label>
                        <input type="text" id="note_disipline" name="note_disipline" value="<?php echo htmlspecialchars($stagiaire['note_disipline']); ?>" readonly class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($stagiaire['email']); ?>"  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div>
                        <label for="filiere_id"class="block text-gray-700 text-sm font-bold mb-2">Filière</label>
                        <input type="text" id="filiere_id" name="filiere_id" value="<?php echo htmlspecialchars($stagiaire['nom_filier']); ?>"class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"readonly >                      
                    </div>
                    <div class="mb-4">
                        <label for="group_id" class="block text-gray-700 text-sm font-bold mb-2">Groupe</label>
                        <select id="group_id" name="group_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <?php
                                foreach ($groupes as $groupe) {
                                    if ($groupe['filiere_id'] == $stagiaire['filiere_id']) {
                                        echo "<option value='" . htmlspecialchars($groupe['id']) . "' " . ($groupe['id'] == $stagiaire['group_id'] ? "selected" : "") . ">" . htmlspecialchars($groupe['nom_group']) . "</option>";
                                    }
                                }
                        ?>
                        </select>
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>