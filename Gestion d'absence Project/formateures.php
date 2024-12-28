<?php
session_start();
require_once "connexion.php";
$date = date("Y-m-d");

function enregistrerAbsenceOuRetard($seances, $note, $DATE, $PDO, $type) {
    foreach ($seances as $stagiaire_id => $seance) {
        foreach ($seance as $numero_seance => $checked) {
            if ($checked) {
                $table = ($type === 'absence') ? 'absences' : 'retard';
                $jour = ($type === 'absence') ? 'jour_absence' : 'jour_retard';

                // Insérer dans la base de données
                $sql = "INSERT INTO $table(stagiaire_id, seance_id, $jour) VALUES(?, ?, ?);";
                $stmt = $PDO->prepare($sql);
                $stmt->execute([$stagiaire_id, $numero_seance, $DATE]);

                // Mettre à jour la note de discipline
                mettreAJourDiscipline($stagiaire_id, $note, $PDO);
            }
        }
    }
}

function mettreAJourDiscipline($stagiaire_id, $note, $PDO) {
    // Récupérer la note de discipline actuelle
    $sql_get_discipline = "SELECT note_disipline FROM stagiaires WHERE id = :stagiaire_id;";
    $stmt_get_discipline = $PDO->prepare($sql_get_discipline);
    $stmt_get_discipline->bindParam(':stagiaire_id', $stagiaire_id);
    $stmt_get_discipline->execute();
    $current_discipline = $stmt_get_discipline->fetchColumn();

    // Calculer la nouvelle note de discipline
    $new_discipline = max(0, $current_discipline - $note); // Ne pas permettre de note négative
    $sql_update_discipline = "UPDATE stagiaires SET note_disipline = :new_discipline WHERE id = :stagiaire_id;";
    $stmt_update_discipline = $PDO->prepare($sql_update_discipline);
    $stmt_update_discipline->bindParam(':stagiaire_id', $stagiaire_id);
    $stmt_update_discipline->bindParam(':new_discipline', $new_discipline);
    $stmt_update_discipline->execute();
}

// Récupérer le nom_group et les filières
if (isset($_SESSION["nom_group"]) && isset($_SESSION["filieres"])) {
    try {
        $sql = "SELECT st.nom, st.prenom, st.id FROM stagiaires st JOIN groupes gr
        ON st.group_id = gr.id JOIN filieres fil
        ON gr.filiere_id = fil.id WHERE gr.nom_group = :nom_group 
        AND fil.nom_filier = :nom_filier;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nom_group', $_SESSION["nom_group"]);
        $stmt->bindParam(':nom_filier', $_SESSION["filieres"]);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        die("Query Failed: " . $e->getMessage()); 
    }
} else {
    header("location:formateures_login.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seances = isset($_POST['seance']) ? $_POST['seance'] : [];
    enregistrerAbsenceOuRetard($seances, 0.50, $date, $pdo, 'absence');

    $retards = isset($_POST['retard']) ? $_POST['retard'] : [];
    enregistrerAbsenceOuRetard($retards, 0.25, $date, $pdo, 'retard');
    
    header("location:formateures.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<header class="bg-blue-900 text-white py-4">
    <div class="container mx-auto flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <img src="https://th.bing.com/th/id/R.b6fae5d9e78a1e2dfe1059c9db1a68aa?rik=zvz3oE9xuEBxVQ&riu=http%3a%2f%2ftous-logos.com%2fwp-content%2fuploads%2f2019%2f03%2fLogo-OFPPT.png&ehk=n4bjmSAkWEmjJcCrgAFqAjxagn2Sypl4RqRrYYgr9gw%3d&risl=&pid=ImgRaw&r=0" width="100" alt="OFPPT Logo" class="w-20">
            <div>
                <p class="text-2xl font-bold"><?php echo $_SESSION["filieres"] . ' - ' . $_SESSION["nom_group"]; ?></p>
                <div class="flex space-x-4">
                    <a href="formateures_login.php" class="text-gray-200 hover:text-white">Retourner</a>
                    <a href="logout.php" class="text-gray-200 hover:text-white">Déconnecter</a>
                    <a href="opp.php" class="text-gray-200 hover:text-white">Retourner a la page des operations</a>

                </div>
            </div>
        </div>
    </div>
</header>

<div class="container mx-auto mt-8">
    <h1 class="text-3xl font-semibold text-blue-900 mb-4">Liste des stagiaires</h1>
    <p class="mb-4"><?php echo $date; ?></p>

    <form method="POST" action="">
        <div class="overflow-auto">
            <table class="min-w-full bg-white border rounded-lg">
                <thead class="bg-blue-800 text-white">
                    <tr>
                        <th colspan="2" class="px-4 py-2 border">NOM/PRENOM du STAGIAIRE</th>
                        <th colspan="4" class="px-4 py-2 border">SEANCES</th>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 border">Nom</th>
                        <th class="px-4 py-2 border">Prénom</th>
                        <th class="px-4 py-2 border">8:30/11</th>
                        <th class="px-4 py-2 border">11/13:30</th>
                        <th class="px-4 py-2 border">13:30/16</th>
                        <th class="px-4 py-2 border">16/18:30</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($result)): ?>
                        <?php foreach ($result as $row): ?>
                            <tr class="text-center">
                                <td class="border px-4 py-2"><?php echo $row['nom']; ?></td>
                                <td class="border px-4 py-2"><?php echo $row['prenom']; ?></td>
                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                    <td class="border px-4 py-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="seance[<?php echo $row['id']; ?>][<?php echo $i; ?>]" class="mr-2">
                                            <span>Absence</span>
                                        </label>
                                        <br>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="retard[<?php echo $row['id']; ?>][<?php echo $i; ?>]" class="mr-2">
                                            <span>Retard</span>
                                        </label>
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-gray-600 py-4">Aucun stagiaire trouvé.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <button type="submit" class="mt-4 bg-blue-800 text-white px-4 py-2 rounded hover:bg-blue-900">Enregistrer les absences</button>
    </form>
</div>

</body>
</html>