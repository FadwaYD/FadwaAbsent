<?php
session_start();
require_once "connexion.php";
$message_erreur = "";
$note_disipline = array();

if (isset($_SESSION["matricul"]) ) {
    try {
        $sql2 = "SELECT id, nom_filier FROM filieres";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute();
        $filieres = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $groupesQuery = "SELECT DISTINCT id, nom_group, filiere_id FROM groupes";
        $groupesStmt = $pdo->query($groupesQuery);
        $groupes = $groupesStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur de requête : " . $e->getMessage());
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["Entrer"])) {
        if (!empty($_POST["filieres"]) && !empty($_POST["nom_group"])) {
            try {
                $sql = "SELECT st.nom, st.prenom, st.id, st.note_disipline 
                        FROM stagiaires st 
                        JOIN groupes gr ON st.group_id = gr.id 
                        JOIN filieres fil ON gr.filiere_id = fil.id 
                        WHERE gr.nom_group = :nom_group AND fil.nom_filier = :nom_filier;";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nom_group', $_POST["nom_group"]);
                $stmt->bindParam(':nom_filier', $_POST["filieres"]);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Erreur de requête : " . $e->getMessage());
            }

            if ($result) {
                foreach ($result as $row) {
                    $sanction = "";
                    $decision_authority = "";
                    if ($row["note_disipline"] > 19) {
                        $sanction = "Aucune";
                        $decision_authority = "Aucun";
                    } elseif ($row["note_disipline"] > 18) {
                        $sanction = "1ère Mise en garde";
                        $decision_authority = "SG";
                    } elseif ($row["note_disipline"] > 17) {
                        $sanction = "2ème Mise en garde";
                        $decision_authority = "SG";
                    } elseif ($row["note_disipline"] > 16) {
                        $sanction = "1ère avertissement";
                        $decision_authority = "D";
                    } elseif ($row["note_disipline"] > 15) {
                        $sanction = "2ème avertissement";
                        $decision_authority = "D";
                    } elseif ($row["note_disipline"] > 14) {
                        $sanction = "Blâme";
                        $decision_authority = "CD";
                    } elseif ($row["note_disipline"] >= 10) {
                        $sanction = "Exclusion temporaire ou définitive";
                        $decision_authority = "CD";
                    } else {
                        $sanction = "Exclusion définitive";
                        $decision_authority = "CD";
                    }

                    $note_disipline[$row['id']] = array(
                        "Nom" => $row['nom'],
                        "Prenom" => $row['prenom'],
                        "Sanctions" => $sanction,
                        "Autorité de décision" => $decision_authority
                    );
                }
            } else {
                $message_erreur = "Aucun stagiaire trouvé";
            }
        } else {
            $message_erreur = "Tous les champs sont obligatoires";
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

            <!-- Form Section -->
            <main class="flex-1">
                <form method="POST" class="grid grid-cols-1 gap-4 sm:grid-cols-2 max-w-lg mx-auto bg-white p-6 rounded-lg shadow-lg">
                <div>
    <label for="filieres" class="block text-gray-700 font-medium">Filière</label>
    <select id="filieres" name="filieres" 
            class="w-full border-gray-300 rounded-lg shadow-sm p-2 sm:p-3 focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50 transition duration-200" 
            required>
        <?php foreach ($filieres as $filiere): ?>
            <option value="<?php echo htmlspecialchars($filiere['nom_filier']); ?>">
                <?php echo htmlspecialchars($filiere['nom_filier']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div>
    <label for="group_id" class="block text-gray-700 font-medium">Groupe</label>
    <select id="group_id" name="nom_group" 
            class="w-full border-gray-300 rounded-lg shadow-sm p-2 sm:p-3 focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50 transition duration-200" 
            required>
        <?php foreach ($groupes as $groupe): ?>
            <option value="<?php echo htmlspecialchars($groupe['nom_group']); ?>">
                <?php echo htmlspecialchars($groupe['nom_group']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

                    <div class="col-span-2">
                        <input type="submit" name="Entrer" value="Entrer" class="w-full py-3 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition duration-200 cursor-pointer">
                    </div>
                </form>

                <!-- Error or Success Message -->
                <?php if (!empty($message_erreur)): ?>
                    <div class="mt-4 p-4 bg-red-200 text-red-800 rounded-lg shadow-md max-w-lg mx-auto">
                        <?php echo $message_erreur; ?>
                    </div>
                <?php endif; ?>

                <!-- Table Section -->
                <?php if (!empty($note_disipline)): ?>
                    <div class="mt-6 overflow-auto">
                        <table class="min-w-full bg-white rounded-lg shadow-md overflow-hidden">
                            <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="py-3 px-6 text-left text-gray-600 font-semibold">Nom</th>
                                    <th class="py-3 px-6 text-left text-gray-600 font-semibold">Prénom</th>
                                    <th class="py-3 px-6 text-left text-gray-600 font-semibold">Sanctions</th>
                                    <th class="py-3 px-6 text-left text-gray-600 font-semibold">Autorité de décision</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($note_disipline as $row): ?>
                                    <tr class="hover:bg-gray-50 transition duration-200">
                                        <td class="py-3 px-6"><?php echo htmlspecialchars($row['Nom']); ?></td>
                                        <td class="py-3 px-6"><?php echo htmlspecialchars($row['Prenom']); ?></td>
                                        <td class="py-3 px-6"><?php echo htmlspecialchars($row['Sanctions']); ?></td>
                                        <td class="py-3 px-6"><?php echo htmlspecialchars($row['Autorité de décision']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>


