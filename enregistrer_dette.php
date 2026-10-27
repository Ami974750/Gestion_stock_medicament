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


$fournisseur_id = (int)($_POST['fournisseur_id'] ?? 0);
$description = trim($_POST['description'] ?? '');
$montant_total = (float)($_POST['montant_total'] ?? 0);
$date_dette = $_POST['date_dette'] ?? '';
$notes = trim($_POST['notes'] ?? '');


if ($fournisseur_id <= 0) {
    die("Veuillez choisir un fournisseur.");
}

if ($description === '') {
    die("La description est obligatoire.");
}

if ($montant_total <= 0) {
    die("Le montant doit être supérieur à 0.");
}

if ($date_dette === '') {
    die("La date est obligatoire.");
}


/* Vérifier que le fournisseur existe */

$verification = $connexion->prepare("
    SELECT id
    FROM fournisseurs
    WHERE id = ?
");

$verification->bind_param(
    "i",
    $fournisseur_id
);

$verification->execute();

$resultat = $verification->get_result();

if ($resultat->num_rows === 0) {
    die("Le fournisseur sélectionné n'existe pas.");
}

$verification->close();


/* Enregistrer la dette */

$requete = $connexion->prepare("
    INSERT INTO dettes_fournisseurs
    (
        fournisseur_id,
        description,
        montant_total,
        date_dette,
        notes
    )
    VALUES (?, ?, ?, ?, ?)
");

$requete->bind_param(
    "isdss",
    $fournisseur_id,
    $description,
    $montant_total,
    $date_dette,
    $notes
);


if ($requete->execute()) {

    header("Location: fournisseurs.php");
    exit;

} else {

    die(
        "Erreur lors de l'enregistrement de la dette : "
        . htmlspecialchars($requete->error)
    );

}


$requete->close();
$connexion->close();

?>