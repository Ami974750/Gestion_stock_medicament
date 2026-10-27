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


/* =========================
   CHOIX DE LA PÉRIODE
========================= */

$periode = $_GET['periode'] ?? 'tout';

switch ($periode) {

    case 'aujourd_hui':
        $condition_date = "DATE(date_vente) = CURDATE()";
        $condition_depense = "DATE(date_depense) = CURDATE()";
        $titre_periode = "Aujourd'hui";
        break;

    case 'semaine':
        $condition_date = "YEARWEEK(date_vente, 1) = YEARWEEK(CURDATE(), 1)";
        $condition_depense = "YEARWEEK(date_depense, 1) = YEARWEEK(CURDATE(), 1)";
        $titre_periode = "Cette semaine";
        break;

    case 'mois':
        $condition_date = "YEAR(date_vente) = YEAR(CURDATE())
                           AND MONTH(date_vente) = MONTH(CURDATE())";
        $condition_depense = "YEAR(date_depense) = YEAR(CURDATE())
                              AND MONTH(date_depense) = MONTH(CURDATE())";
        $titre_periode = "Ce mois";
        break;

    default:
        $condition_date = "1=1";
        $condition_depense = "1=1";
        $titre_periode = "Tout";
        $periode = 'tout';
}


/* =========================
   CHIFFRE D'AFFAIRES
========================= */

$requete_ventes = $connexion->query(
    "SELECT COALESCE(SUM(total), 0) AS chiffre_affaires
     FROM ventes
     WHERE $condition_date"
);

$ventes = $requete_ventes->fetch_assoc();

$chiffre_affaires = $ventes['chiffre_affaires'];


/* =========================
   COÛT DES PRODUITS VENDUS
========================= */

$requete_cout = $connexion->query(
    "SELECT COALESCE(
        SUM(quantite * prix_achat_unitaire),
        0
     ) AS cout_produits
     FROM ventes
     WHERE $condition_date"
);

$cout = $requete_cout->fetch_assoc();

$cout_produits = $cout['cout_produits'];


/* =========================
   DÉPENSES
========================= */

$requete_depenses = $connexion->query(
    "SELECT COALESCE(SUM(montant), 0) AS depenses
     FROM depenses
     WHERE $condition_depense"
);

$depenses_resultat = $requete_depenses->fetch_assoc();

$depenses = $depenses_resultat['depenses'];


/* =========================
   BÉNÉFICE RÉEL
========================= */

$benefice_reel =
    $chiffre_affaires
    - $cout_produits
    - $depenses;

?>

<!DOCTYPE html>

<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Résultats</title>

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
    margin-bottom: 20px;
}


/* SÉLECTEUR DE PÉRIODE */

.periode {
    background-color: white;
    padding: 18px;
    border-radius: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.periode label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
}

.periode select {
    width: 100%;
    box-sizing: border-box;
    padding: 13px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 16px;
    background-color: white;
}


/* PÉRIODE AFFICHÉE */

.periode-actuelle {
    text-align: center;
    color: #16845b;
    font-weight: bold;
    margin-bottom: 20px;
}


/* RÉSULTATS */

.resultat {
    background-color: white;
    padding: 20px;
    margin-bottom: 15px;
    border-radius: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.resultat p {
    margin: 0;
    font-size: 16px;
}

.montant {
    display: block;
    margin-top: 8px;
    color: #16845b;
    font-size: 22px;
    font-weight: bold;
}


/* BÉNÉFICE */

.benefice {
    border: 2px solid #16845b;
}

.benefice .montant {
    font-size: 26px;
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


<h1>📊 Résultats</h1>


<!-- CHOIX DE LA PÉRIODE -->

<form class="periode" method="GET">

<label for="periode">
📅 Voir les résultats pour :
</label>

<select
    id="periode"
    name="periode"
    onchange="this.form.submit()"
>

<option
    value="aujourd_hui"
    <?php if ($periode === 'aujourd_hui') echo 'selected'; ?>
>
Aujourd'hui
</option>

<option
    value="semaine"
    <?php if ($periode === 'semaine') echo 'selected'; ?>
>
Cette semaine
</option>

<option
    value="mois"
    <?php if ($periode === 'mois') echo 'selected'; ?>
>
Ce mois
</option>

<option
    value="tout"
    <?php if ($periode === 'tout') echo 'selected'; ?>
>
Tout
</option>

</select>

</form>


<div class="periode-actuelle">

Résultats : <?php echo $titre_periode; ?>

</div>


<!-- CHIFFRE D'AFFAIRES -->

<div class="resultat">

<p>
💰 Chiffre d'affaires
</p>

<span class="montant">

<?php

echo number_format(
    $chiffre_affaires,
    0,
    ',',
    ' '
);

?>

FCFA

</span>

</div>


<!-- COÛT DES PRODUITS -->

<div class="resultat">

<p>
📦 Coût des produits vendus
</p>

<span class="montant">

<?php

echo number_format(
    $cout_produits,
    0,
    ',',
    ' '
);

?>

FCFA

</span>

</div>


<!-- DÉPENSES -->

<div class="resultat">

<p>
💸 Dépenses
</p>

<span class="montant">

<?php

echo number_format(
    $depenses,
    0,
    ',',
    ' '
);

?>

FCFA

</span>

</div>


<!-- BÉNÉFICE -->

<div class="resultat benefice">

<p>
📈 Bénéfice réel
</p>

<span class="montant">

<?php

echo number_format(
    $benefice_reel,
    0,
    ',',
    ' '
);

?>

FCFA

</span>

</div>


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