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
$quantite = $_POST['quantite'] ?? '';


if (
    empty($produit_id) ||
    empty($quantite) ||
    $quantite < 1
) {
    die("Informations invalides.");
}


/*
    Récupérer le produit
*/
$requete = $connexion->prepare(
    "SELECT
        nom,
        prix_vente,
        stock_unitaire,
        cout_unitaire
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


/*
    Vérifier le stock
*/
if ($quantite > $produit['stock_unitaire']) {

    die(
        "Stock unitaire insuffisant.<br><br>" .
        "Stock disponible : " .
        $produit['stock_unitaire'] .
        " petits sacs."
    );
}


/*
    Vérifier qu'une transformation a déjà été faite
*/
if ($produit['cout_unitaire'] <= 0) {

    die(
        "Ce produit n'a pas encore de coût unitaire.<br><br>" .
        "Transforme d'abord un gros sac en petits sacs."
    );
}


/*
    Calcul de la vente
*/
$prix_unitaire = (float) $produit['prix_vente'];

$prix_achat_unitaire = (float) $produit['cout_unitaire'];

$total = $prix_unitaire * $quantite;

$date_vente = date('Y-m-d H:i:s');


/*
    Enregistrer la vente
*/
$vente = $connexion->prepare(
    "INSERT INTO ventes
    (
        produit_id,
        quantite,
        prix_unitaire,
        prix_achat_unitaire,
        total,
        date_vente
    )
    VALUES (?, ?, ?, ?, ?, ?)"
);


$vente->bind_param(
    "iiddds",
    $produit_id,
    $quantite,
    $prix_unitaire,
    $prix_achat_unitaire,
    $total,
    $date_vente
);


if (!$vente->execute()) {

    die(
        "Erreur lors de l'enregistrement de la vente : " .
        $vente->error
    );
}


/*
    Diminuer le stock unitaire
*/
$nouveau_stock = $produit['stock_unitaire'] - $quantite;


$mise_a_jour = $connexion->prepare(
    "UPDATE produits
     SET stock_unitaire = ?,
         quantite_stock = ?
     WHERE id = ?"
);


$mise_a_jour->bind_param(
    "iii",
    $nouveau_stock,
    $nouveau_stock,
    $produit_id
);


if (!$mise_a_jour->execute()) {

    die(
        "La vente a été enregistrée, " .
        "mais le stock n'a pas pu être mis à jour."
    );
}


$nom = htmlspecialchars($produit['nom']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Vente enregistrée</title>

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
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
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

        <h1>Vente enregistrée</h1>

        <p>
            <strong><?php echo $nom; ?></strong>
        </p>

        <div class="resultat">

            🛍️ Quantité vendue :
            <strong><?php echo $quantite; ?></strong>

            <br>

            💵 Prix unitaire :
            <strong>
                <?php echo number_format($prix_unitaire, 0, ',', ' '); ?>
                FCFA
            </strong>

            <br>

            💰 Total de la vente :
            <strong>
                <?php echo number_format($total, 0, ',', ' '); ?>
                FCFA
            </strong>

            <br>

            📦 Stock unitaire restant :
            <strong><?php echo $nouveau_stock; ?></strong>

        </div>

        <a href="vente.php" class="retour">
            🛍️ Faire une autre vente
        </a>

        <a href="index.php" class="retour">
            🏠 Retour à l'accueil
        </a>

    </div>

</div>

</body>

</html>

<?php

$vente->close();
$requete->close();
$mise_a_jour->close();
$connexion->close();

?>