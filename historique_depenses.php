<?php
require_once "securite.php";
$connexion = new mysqli(
    "localhost",
    "root",
    "",
    "gestion_stock_medicaments"
);

if ($connexion->connect_error) {
    die("Erreur de connexion à la base de données : " . $connexion->connect_error);
}


/* RÉCUPÉRER L'HISTORIQUE DES DÉPENSES */

$requete = "
    SELECT
        id,
        description,
        montant,
        date_depense
    FROM depenses
    ORDER BY date_depense DESC
";

$resultat = $connexion->query($requete);

?>

<!DOCTYPE html>

<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Historique des dépenses</title>

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


/* DÉPENSE */

.depense {
    background-color: white;
    padding: 18px;
    margin-bottom: 15px;
    border-radius: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.depense h2 {
    margin: 0 0 12px;
    color: #16845b;
    font-size: 19px;
}

.depense p {
    margin: 7px 0;
    font-size: 15px;
}

.montant {
    margin-top: 12px !important;
    font-size: 19px !important;
    font-weight: bold;
    color: #16845b;
}


/* AUCUNE DÉPENSE */

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


<h1>💸 Historique des dépenses</h1>


<?php if ($resultat->num_rows > 0) { ?>


<?php while ($depense = $resultat->fetch_assoc()) { ?>


<div class="depense">


<h2>
💸 <?php echo htmlspecialchars($depense['description']); ?>
</h2>


<p>

📅 Date :

<strong>

<?php

if (
    empty($depense['date_depense']) ||
    $depense['date_depense'] === '0000-00-00 00:00:00' ||
    strtotime($depense['date_depense']) === false
) {

    echo "Date non disponible";

} else {

    echo date(
        'd/m/Y à H:i',
        strtotime($depense['date_depense'])
    );

}

?>

</strong>

</p>


<p class="montant">

💰 Montant :

<?php

echo number_format(
    $depense['montant'],
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

Aucune dépense enregistrée pour le moment.

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