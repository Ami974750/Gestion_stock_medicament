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


$dette_id = (int)($_POST['dette_id'] ?? 0);
$montant = (float)($_POST['montant'] ?? 0);
$notes = trim($_POST['notes'] ?? '');


if ($dette_id <= 0) {
    die("Veuillez choisir une dette.");
}

if ($montant <= 0) {
    die("Le montant doit être supérieur à 0.");
}


/* Vérifier la dette */

$requete = $connexion->prepare("
    SELECT
        d.montant_total,
        COALESCE(SUM(p.montant), 0) AS deja_paye
    FROM dettes_fournisseurs d
    LEFT JOIN paiements_dettes p
        ON p.dette_id = d.id
    WHERE d.id = ?
    GROUP BY d.id, d.montant_total
");

$requete->bind_param("i", $dette_id);
$requete->execute();

$resultat = $requete->get_result();

if ($resultat->num_rows === 0) {
    die("Cette dette n'existe pas.");
}

$dette = $resultat->fetch_assoc();

$montant_total = (float)$dette['montant_total'];
$deja_paye = (float)$dette['deja_paye'];

$reste = $montant_total - $deja_paye;


/* Empêcher de payer plus que la dette */

if ($montant > $reste) {
    die(
        "Le paiement de "
        . number_format($montant, 0, ',', ' ')
        . " FCFA dépasse le reste à payer de "
        . number_format($reste, 0, ',', ' ')
        . " FCFA."
    );
}

$requete->close();


/* Enregistrer le paiement */

$requete = $connexion->prepare("
    INSERT INTO paiements_dettes
    (
        dette_id,
        montant,
        note
    )
    VALUES (?, ?, ?)
");

$requete->bind_param(
    "ids",
    $dette_id,
    $montant,
    $notes
);


if ($requete->execute()) {

    header("Location: fournisseurs.php");
    exit;

} else {

    die(
        "Erreur lors de l'enregistrement du paiement : "
        . htmlspecialchars($requete->error)
    );

}


$requete->close();
$connexion->close();

?>