<?php

$connexion = new mysqli(
    "localhost",
    "root",
    "",
    "gestion_stock_medicaments"
);

if ($connexion->connect_error) {
    die("Erreur de connexion à la base de données.");
}

$connexion->set_charset("utf8mb4");


$nom = trim($_POST['nom'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$adresse = trim($_POST['adresse'] ?? '');
$notes = trim($_POST['notes'] ?? '');


if ($nom === '') {
    die("Le nom du fournisseur est obligatoire.");
}


$requete = $connexion->prepare("
    INSERT INTO fournisseurs
    (nom, telephone, adresse, notes)
    VALUES (?, ?, ?, ?)
");


$requete->bind_param(
    "ssss",
    $nom,
    $telephone,
    $adresse,
    $notes
);


if ($requete->execute()) {

    header("Location: fournisseurs.php");
    exit;

} else {

    echo "Erreur lors de l'enregistrement du fournisseur : "
        . htmlspecialchars($requete->error);

}


$requete->close();
$connexion->close();

?>