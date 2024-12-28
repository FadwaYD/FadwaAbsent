<?php
session_start();
require_once "connexion.php";
$message_erreur = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["matricul"]) && !empty($_POST["mot_de_passe"]) && !empty($_POST["email"])) {
        try {
            $query = "SELECT * FROM utilisateur WHERE matricul = :matricul AND email = :email";
            $stmt1 = $pdo->prepare($query);
            $stmt1->bindParam(':matricul', $_POST["matricul"]);
            $stmt1->bindParam(':email', $_POST["email"]);

            $stmt1->execute();
            $resultat = $stmt1->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error : " . $e->getMessage());
        }

        if ($resultat) {
            try {
                $sql = "SELECT type_utilisateur, mot_de_passe FROM utilisateur WHERE matricul=:matricul";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':matricul', $_POST["matricul"]);
                $stmt->execute();
        
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
                if ($row) {
                    // Vérifier le mot de passe
                    if (password_verify($_POST["mot_de_passe"], $row['mot_de_passe'])) {
                        $_SESSION["matricul"] = $_POST["matricul"];
                        $_SESSION["email"] = $_POST["email"];
        
                        if ($row["type_utilisateur"] == 'formateure') {
                            header("location:formateures_login.php");
                        } elseif ($row["type_utilisateur"] == 'surveillants') {
                            header("location:opp.php");
                        } else {
                            header("location:stagiaires_login.php");
                        }
                        exit;
                    } else {
                        $message_erreur = "Mot de passe incorrect. Veuillez réessayer.";
                    }
                } else {
                    $message_erreur = "Utilisateur non trouvé.";
                }
            } catch (PDOException $e) {
                die("Query Failed: " . $e->getMessage());
            }
        } else {
            $message_erreur = "Utilisateur non trouvé.";
        }
    } else {
        $message_erreur = "Tous les champs sont obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Connexion</h2>
            <?php if ($message_erreur) : ?>
                <div class="text-red-500 mb-4"><?php echo $message_erreur; ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="matricule">Matricule</label>
                    <input class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-200" type="text" id="matricule" name="matricul" placeholder="Entrer votre matricule">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                    <input class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-200" type="email" id="email" name="email" placeholder="Entrer votre email">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Mot de passe</label>
                    <input class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-200" type="password" id="password" name="mot_de_passe" placeholder="Entrer votre mot de passe">
                </div>
                <button class="w-full bg-indigo-500 text-white py-2 rounded-lg hover:bg-indigo-600 focus:outline-none focus:ring focus:ring-indigo-200 font-bold" type="submit" name="submit">Se connecter</button>
            </form>
        </div>

        <!-- Section Image -->
        <div class="hidden md:block md:w-1/2">
            <img class="object-cover w-full h-full" src="login-img.jpg" alt="Image de connexion">
        </div>
    </div>
</body>
</html>
