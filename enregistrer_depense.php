<?php

$connexion = new mysqli(
    "localhost",
    "root",
    "",
    "gestion_stock_medicaments"
);

if ($connexion->connect_error) {
    die("Erreur de connexion à la base de données : " . $connexion->connect_error);
}


/* Récupération des données */

$description = $_POST['description'] ?? '';
$montant = $_POST['montant'] ?? '';


/* Vérification */

if (empty($description) || $montant === '' || $montant <= 0) {
    die("Veuillez renseigner une description et un montant valide.");
}


/* Enregistrement de la dépense */

$requete = $connexion->prepare(
    "INSERT INTO depenses
    (description, montant)
    VALUES (?, ?)"
);

$requete->bind_param(
    "sd",
    $description,
    $montant
);


if ($requete->execute()) {

    echo "<h2>✅ Dépense enregistrée avec succès !</h2>";

    echo "<p>Description : " .
         htmlspecialchars($description) .
         "</p>";

    echo "<p>Montant : " .
         number_format($montant, 0, ',', ' ') .
         " FCFA</p>";

    echo "<br>";

    echo '<a href="ajouter_depense.php">💸 Ajouter une autre dépense</a>';

    echo "<br><br>";

    echo '<a href="resultats.php">📊 Voir les résultats</a>';

    echo "<br><br>";

    echo '<a href="index.php">🏠 Retour à l’accueil</a>';

} else {

    echo "Erreur lors de l'enregistrement de la dépense : "
         . $requete->error;
}


/* Fermeture */

$requete->close();

$connexion->close();

?>