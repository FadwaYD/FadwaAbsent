<?php
session_start();
require_once "connexion.php";
$message_erreur = "";

if (!isset($_SESSION["matricul"])) {
    header("location:login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["filieres"]) && !empty($_POST["nom_group"])) {
        $_SESSION["filieres"] = $_POST["filieres"];
        $_SESSION["nom_group"] = $_POST["nom_group"];
        header("location:formateures.php");
        exit();
    } else {
        $message_erreur = "Tous les champs sont obligatoires";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="images/favicon.png">
    <title>Finexo</title>
</head>
<body class="flex flex-col items-center justify-center min-h-screen bg-gray-100 space-y-4 m-0">
    <!-- Logo dans le coin supérieur gauche -->
    <div class="absolute top-4 left-4">
        <img src="logo.png" alt="Logo" class="h-20">
    </div>

    <!-- Conteneur Principal -->
    <div class="flex flex-col md:flex-row w-3/4 bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Section Formulaire -->
        <div class="w-full md:w-1/2 p-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Connecter</h2>
            <form method="post">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="filieres">Filières</label>
                    <div class="relative">
                        <select name="filieres" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-200">
                            <option value="" disabled selected>Choisir une filière</option>
                            <?php
                            $stmt = $pdo->query("SELECT nom_filier FROM filieres");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='" . htmlspecialchars($row['nom_filier']) . "'>" . htmlspecialchars($row['nom_filier']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nom_group">Nom du Groupe</label>
                    <div class="relative">
                        <select name="nom_group" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-200">
                            <option value="" disabled selected>Choisir un groupe</option>
                            <?php
                            $stmt = $pdo->query("SELECT nom_group FROM groupes");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='" . htmlspecialchars($row['nom_group']) . "'>" . htmlspecialchars($row['nom_group']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <p class="text-red-500 mb-4"><?php echo $message_erreur; ?></p>
                <button class="w-full bg-indigo-500 text-white py-2 rounded-lg hover:bg-indigo-600 focus:outline-none focus:ring focus:ring-indigo-200 font-bold" type="submit" name="submit">Entrer</button>
            </form>
        </div>

        <!-- Section Image -->
        <div class="hidden md:block md:w-1/2">
            <img class="object-cover w-full h-full" src="login-img.jpg" alt="Image de connexion">
        </div>
    </div>
</body>
</html>
