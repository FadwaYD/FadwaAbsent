<?php
require_once "connexion.php";

$utilisateurs = array("stagiaires", "surveillants", "formateures","utilisateur");

foreach ($utilisateurs as $utilisateur) {
    try {
        $sql = "SELECT * FROM $utilisateur";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            if (array_key_exists('mot_de_passe', $row)) {
                // Vérifiez si le mot de passe n'est pas déjà haché
                if (!password_needs_rehash($row['mot_de_passe'], PASSWORD_DEFAULT)) {
                    continue; // Le mot de passe est déjà haché, passez au prochain utilisateur
                }
        
                // Hachez le mot de passe
                $mot_de_passe = password_hash($row['mot_de_passe'], PASSWORD_DEFAULT);
                $sql2 = "UPDATE $utilisateur SET mot_de_passe = :mot_de_passe WHERE matricul = :matricul"; 
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->bindParam(":mot_de_passe", $mot_de_passe);
                $stmt2->bindParam(":matricul", $row['matricul']); 
                $stmt2->execute();
                header('location:login.php');
            }
        }
        
    } catch (PDOException $e) {
        echo "Une erreur PDO s'est produite : " . $e->getMessage();
    } catch (Exception $e) {
        echo "Une erreur s'est produite : " . $e->getMessage();
    }
}
?>   