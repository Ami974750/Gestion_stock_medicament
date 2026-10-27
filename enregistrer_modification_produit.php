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
$id = $_POST['id'] ?? '';
$nom = $_POST['nom'] ?? '';
$categorie = $_POST['categorie'] ?? '';
$prix_achat = $_POST['prix_achat'] ?? '';
$frais_transport = $_POST['frais_transport'] ?? '';
$prix_vente = $_POST['prix_vente'] ?? '';
$quantite_stock = $_POST['quantite_stock'] ?? '';
$seuil_alerte = $_POST['seuil_alerte'] ?? '';

/* Vérification */
if (
    empty($id) ||
    empty($nom) ||
    empty($categorie) ||
    $prix_achat === '' ||
    $frais_transport === '' ||
    $prix_vente === '' ||
    $quantite_stock === '' ||
    $seuil_alerte === ''
) {
    die("Veuillez remplir tous les champs.");
}

/* Vérifier les valeurs numériques */
if (
    $prix_achat < 0 ||
    $frais_transport < 0 ||
    $prix_vente < 0 ||
    $quantite_stock < 0 ||
    $seuil_alerte < 0
) {
    die("Les valeurs ne peuvent pas être négatives.");
}

/* Modification du produit */
$requete = $connexion->prepare(
    "UPDATE produits
     SET
        nom = ?,
        categorie = ?,
        prix_achat = ?,
        frais_transport = ?,
        prix_vente = ?,
        quantite_stock = ?,
        seuil_alerte = ?
     WHERE id = ?"
);

$requete->bind_param(
    "ssddiiii",
    $nom,
    $categorie,
    $prix_achat,
    $frais_transport,
    $prix_vente,
    $quantite_stock,
    $seuil_alerte,
    $id
);

/* Exécution */
if ($requete->execute()) {

    echo "<h2>✅ Produit modifié avec succès !</h2>";

    echo "<p>Produit : "
        . htmlspecialchars($nom)
        . "</p>";

    echo "<p>Prix d'achat en gros : "
        . number_format($prix_achat, 0, ',', ' ')
        . " FCFA</p>";

    echo "<p>Frais de transport : "
        . number_format($frais_transport, 0, ',', ' ')
        . " FCFA</p>";

    echo "<p>Prix de vente unitaire : "
        . number_format($prix_vente, 0, ',', ' ')
        . " FCFA</p>";

    echo "<p>Stock : "
        . $quantite_stock
        . "</p>";

    echo "<br>";

    echo '<a href="stock.php">📦 Retour au stock</a>';

    echo "<br><br>";

    echo '<a href="index.php">🏠 Retour à l’accueil</a>';

} else {

    echo "Erreur lors de la modification : "
        . $requete->error;
}

$requete->close();
$connexion->close();

?>