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


/* RÉCUPÉRER L'HISTORIQUE DES VENTES */

$requete = "
    SELECT
        ventes.id,
        produits.nom,
        ventes.quantite,
        ventes.prix_unitaire,
        ventes.total,
        ventes.date_vente
    FROM ventes
    INNER JOIN produits
        ON ventes.produit_id = produits.id
    ORDER BY ventes.date_vente DESC
";

$resultat = $connexion->query($requete);

?>

<!DOCTYPE html>

<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Historique des ventes</title>

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #f5fdf9;
    color: #222;
}

.container {
    max-width: 430px;
    margin: auto;
    padding: 25px 20px;
}

h1 {
    text-align: center;
    color: #16845b;
    margin-bottom: 25px;
}


/* VENTE */

.vente {
    background-color: white;
    padding: 18px;
    margin-bottom: 15px;
    border-radius: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.vente h2 {
    margin: 0 0 12px;
    color: #16845b;
    font-size: 19px;
}

.vente p {
    margin: 7px 0;
    font-size: 15px;
}

.total {
    margin-top: 12px !important;
    font-size: 19px !important;
    font-weight: bold;
    color: #16845b;
}


/* MESSAGE SI AUCUNE VENTE */

.aucune {
    background-color: white;
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    color: #666;
}


/* RETOUR */

.retour {
    display: block;
    text-align: center;
    margin-top: 25px;
    color: #16845b;
    text-decoration: none;
    font-weight: bold;
}

</style>

</head>


<body>

<div class="container">


<h1>📋 Historique des ventes</h1>


<?php if ($resultat->num_rows > 0) { ?>


<?php while ($vente = $resultat->fetch_assoc()) { ?>


<div class="vente">


<h2>
🌿 <?php echo htmlspecialchars($vente['nom']); ?>
</h2>


<p>
📅 Date :

<strong>

<?php

if (
    empty($vente['date_vente']) ||
    $vente['date_vente'] === '0000-00-00 00:00:00' ||
    strtotime($vente['date_vente']) === false
) {

    echo "Date non disponible";

} else {

    echo date(
        'd/m/Y à H:i',
        strtotime($vente['date_vente'])
    );

}

?>

</strong>

</p>


<p>
🔢 Quantité vendue :

<strong>
<?php echo $vente['quantite']; ?>
</strong>

</p>


<p>
💰 Prix unitaire :

<strong>

<?php

echo number_format(
    $vente['prix_unitaire'],
    0,
    ',',
    ' '
);

?>

FCFA

</strong>

</p>


<p class="total">

💵 Total :

<?php

echo number_format(
    $vente['total'],
    0,
    ',',
    ' '
);

?>

FCFA

</p>


</div>


<?php } ?>


<?php } else { ?>


<div class="aucune">

Aucune vente enregistrée pour le moment.

</div>


<?php } ?>


<a
    href="index.php"
    class="retour"
>
← Retour à l'accueil
</a>


</div>

</body>

</html>


<?php

$connexion->close();

?>