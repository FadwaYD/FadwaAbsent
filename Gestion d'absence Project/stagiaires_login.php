<?php
require_once "connexion.php";
session_start();
$nb_absences="";
if (!isset($_SESSION["matricul"]) ) {
    header("location:login.php");
    exit();
}
// Requête SQL pour récupérer les données de l'utilisateur
$matricul = $_SESSION["matricul"];
$query = "SELECT st.matricul as matricul, st.nom as nom, st.prenom as prenom, st.note_disipline as note_disipline, gr.nom_group as nom_group, fi.nom_filier as nom_filier, ut.email as email
          FROM utilisateur ut 
          JOIN stagiaires st ON st.matricul = ut.matricul 
          JOIN groupes gr ON gr.id = st.group_id
          JOIN filieres fi ON fi.id = gr.filiere_id 
          WHERE st.matricul=:matricul";

try {
    $statement = $pdo->prepare($query);
    $statement->execute(['matricul' => $matricul]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Erreur lors de l\'exécution de la requête : ' . $e->getMessage();
}


$sql_absences = "SELECT COUNT(a.id) AS nb_absences FROM stagiaires s LEFT JOIN absences a ON s.id = a.stagiaire_id WHERE s.matricul = ?";
$stmt = $pdo->prepare($sql_absences);
$stmt->execute([$matricul]);
$result_absences = $stmt->fetch(PDO::FETCH_ASSOC);
if($result_absences){
$nb_absences= $result_absences["nb_absences"];   
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Utilisateur</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-100 via-indigo-50 to-purple-100 min-h-screen flex items-center justify-center relative">

    <!-- Logo en haut à gauche de la page -->
    <div class="absolute top-4 left-4">
    <img src="logo.png" alt="Logo" class="w-20 h-20 sm:w-24 sm:h-24 md:w-32 md:h-32 lg:w-40 lg:h-40">
</div>

    <div class="w-full max-w-5xl mx-auto p-6 bg-white shadow-lg rounded-lg grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Section Image de Profil et Nom -->
        <div class="flex flex-col items-center text-center md:items-start md:text-left space-y-4">
            <img class="w-36 h-36 rounded-full shadow-lg border-4 border-indigo-100" 
                 src="https://media.istockphoto.com/id/1352526755/it/vettoriale/icona-grigia-del-profilo-utente-avatar-web-simbolo-del-dipendente.jpg?s=170667a&w=0&k=20&c=G_W5JGdKb2yqWqUQ9aaDJivjFVpJs7XxeZMW4bn0HzA=" alt="Photo de Profil">
            <h2 class="text-2xl font-semibold text-indigo-700"><?php echo $user['nom'] . " " . $user['prenom']; ?></h2>
            <p class="text-gray-600 text-lg font-medium"><?php echo $user['nom_filier']; ?> - <?php echo $user['nom_group']; ?></p>
        </div>

        <!-- Informations Principales -->
        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-indigo-50 rounded-lg">
            <div class="p-4 bg-white rounded-lg shadow-md flex flex-col items-center md:items-start">
                <h3 class="font-semibold text-gray-600">Matricule</h3>
                <p class="text-gray-800 font-medium"><?php echo $user['matricul']; ?></p>
            </div>
            <div class="p-4 bg-white rounded-lg shadow-md flex flex-col items-center md:items-start">
                <h3 class="font-semibold text-gray-600">Email</h3>
                <p class="text-gray-800 font-medium"><?php echo $user['email']; ?></p>
            </div>
            <div class="p-4 bg-white rounded-lg shadow-md flex flex-col items-center md:items-start">
                <h3 class="font-semibold text-gray-600">Note de Discipline</h3>
                <p class="text-gray-800 font-medium"><?php echo $user['note_disipline']; ?></p>
            </div>
            <div class="p-4 bg-white rounded-lg shadow-md flex flex-col items-center md:items-start">
                <h3 class="font-semibold text-gray-600">Nombre d'Absences</h3>
                <p class="text-gray-800 font-medium"><?php echo $nb_absences; ?></p>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex justify-center md:col-span-3 space-x-4 mt-8">
            <a href="logout.php" class="bg-red-500 text-white py-2 px-5 rounded-lg font-semibold hover:bg-red-600 transition">
                <i class="fas fa-sign-out-alt"></i> Déconnecter
            </a>
            <button onclick="window.print();" class="bg-indigo-500 text-white py-2 px-5 rounded-lg font-semibold hover:bg-indigo-600 transition">
                Imprimer la page
            </button>
        </div>
    </div>

    <!-- Import des Icônes FontAwesome pour les Boutons -->
</body>
</html>

