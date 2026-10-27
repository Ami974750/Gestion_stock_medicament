<?php

$connexion = new mysqli(
    "localhost",
    "root",
    "",
    "gestion_stock_medicaments"
);

if ($connexion->connect_error) {
    die("Erreur de connexion : " . $connexion->connect_error);
}

$produit_id = $_POST['produit_id'] ?? '';
$quantite_gros = $_POST['quantite_gros'] ?? '';
$quantite_unitaire = $_POST['quantite_unitaire'] ?? '';

if (
    empty($produit_id) ||
    empty($quantite_gros) ||
    empty($quantite_unitaire) ||
    $quantite_gros < 1 ||
    $quantite_unitaire < 1
) {
    die("Informations invalides.");
}

/* Récupérer le produit */
$requete = $connexion->prepare(
    "SELECT nom, stock_gros, stock_unitaire, prix_achat, frais_transport
     FROM produits
     WHERE id = ?"
);

$requete->bind_param("i", $produit_id);
$requete->execute();

$resultat = $requete->get_result();

if ($resultat->num_rows === 0) {
    die("Produit introuvable.");
}

$produit = $resultat->fetch_assoc();

/* Vérifier le stock en gros */
if ($quantite_gros > $produit['stock_gros']) {
    die(
        "Stock en gros insuffisant.<br><br>" .
        "Stock disponible : " . $produit['stock_gros']
    );
}

/* Calcul du coût */
$prix_achat = (float) $produit['prix_achat'];
$frais_transport = (float) $produit['frais_transport'];

$cout_total = ($prix_achat + $frais_transport) * $quantite_gros;

$cout_unitaire = $cout_total / $quantite_unitaire;

/* Enregistrer la transformation */
$transformation = $connexion->prepare(
    "INSERT INTO transformations
    (
        produit_id,
        quantite_gros_utilisee,
        quantite_unitaire_produite,
        prix_achat_gros,
        frais_transport,
        cout_total,
        cout_unitaire
    )
    VALUES (?, ?, ?, ?, ?, ?, ?)"
);

$transformation->bind_param(
    "iiidddd",
    $produit_id,
    $quantite_gros,
    $quantite_unitaire,
    $prix_achat,
    $frais_transport,
    $cout_total,
    $cout_unitaire
);

if (!$transformation->execute()) {
    die("Erreur lors de l'enregistrement.");
}

/* Mettre à jour les stocks */
$nouveau_stock_gros =
    $produit['stock_gros'] - $quantite_gros;

$nouveau_stock_unitaire =
    $produit['stock_unitaire'] + $quantite_unitaire;

/* Mettre à jour le produit */
$mise_a_jour = $connexion->prepare(
    "UPDATE produits
     SET stock_gros = ?,
         stock_unitaire = ?,
         cout_unitaire = ?
     WHERE id = ?"
);

$mise_a_jour->bind_param(
    "iidi",
    $nouveau_stock_gros,
    $nouveau_stock_unitaire,
    $cout_unitaire,
    $produit_id
);

if (!$mise_a_jour->execute()) {
    die("Erreur lors de la mise à jour du stock.");
}

$nom = htmlspecialchars($produit['nom']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Transformation réussie</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f2f8f5;
            color: #173b2b;
        }

        .container {
            max-width: 430px;
            margin: auto;
            padding: 30px 20px;
        }

        .carte {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
        }

        .succes {
            font-size: 50px;
        }

        h1 {
            color: #16845b;
        }

        .resultat {
            background: #eef8f3;
            padding: 18px;
            border-radius: 15px;
            margin-top: 20px;
            text-align: left;
            line-height: 1.8;
        }

        .retour {
            display: block;
            margin-top: 20px;
            padding: 14px;
            background: #16845b;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: bold;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="carte">

        <div class="succes">✅</div>

        <h1>Transformation réussie</h1>

        <p>
            <strong><?php echo $nom; ?></strong>
        </p>

        <div class="resultat">

            📦 Gros sacs utilisés :
            <strong><?php echo $quantite_gros; ?></strong>

            <br>

            🛍️ Petits sacs produits :
            <strong><?php echo $quantite_unitaire; ?></strong>

            <br>

            💰 Coût total :
            <strong>
                <?php echo number_format($cout_total, 0, ',', ' '); ?> FCFA
            </strong>

            <br>

            💵 Coût d'un petit sac :
            <strong>
                <?php echo number_format($cout_unitaire, 0, ',', ' '); ?> FCFA
            </strong>

            <br><br>

            📦 Nouveau stock en gros :
            <strong><?php echo $nouveau_stock_gros; ?></strong>

            <br>

            🛍️ Nouveau stock unitaire :
            <strong><?php echo $nouveau_stock_unitaire; ?></strong>

        </div>

        <a href="transformation.php" class="retour">
            🔄 Faire une autre transformation
        </a>

        <a href="index.php" class="retour">
            🏠 Retour à l'accueil
        </a>

    </div>

</div>

</body>

</html>

<?php
$connexion->close();
?>