<?php
require_once "securite.php";

$nom_utilisateur = $_SESSION["nom_utilisateur"] ?? "Utilisateur";

$serveur = "localhost";
$utilisateur = "root";
$mot_de_passe = "";
$base = "gestion_stock_medicaments";

$connexion = new mysqli(
    $serveur,
    $utilisateur,
    $mot_de_passe,
    $base
);

if ($connexion->connect_error) {
    die("Erreur de connexion : " . $connexion->connect_error);
}

$connexion->set_charset("utf8mb4");


/* =========================
   RÉSULTATS
   ========================= */

$requete = $connexion->query("
    SELECT COALESCE(SUM(total), 0) AS chiffre_affaires
    FROM ventes
");

$chiffre_affaires = $requete->fetch_assoc()['chiffre_affaires'];


$requete = $connexion->query("
    SELECT COALESCE(SUM(quantite * prix_achat_unitaire), 0) AS cout_produits
    FROM ventes
");

$cout_produits = $requete->fetch_assoc()['cout_produits'];


$requete = $connexion->query("
    SELECT COALESCE(SUM(montant), 0) AS depenses
    FROM depenses
");

$depenses = $requete->fetch_assoc()['depenses'];


$benefice_reel =
    $chiffre_affaires
    - $cout_produits
    - $depenses;


/* =========================
   STOCK
   ========================= */

$requete = $connexion->query("
    SELECT COALESCE(SUM(stock_unitaire), 0) AS stock_unitaire
    FROM produits
");

$stock_unitaire = $requete->fetch_assoc()['stock_unitaire'];


$requete = $connexion->query("
    SELECT COALESCE(SUM(stock_gros), 0) AS stock_gros
    FROM produits
");

$stock_gros = $requete->fetch_assoc()['stock_gros'];


$requete = $connexion->query("
    SELECT COUNT(*) AS nombre_produits
    FROM produits
");

$nombre_produits = $requete->fetch_assoc()['nombre_produits'];


$requete = $connexion->query("
    SELECT COUNT(*) AS alertes
    FROM produits
    WHERE stock_unitaire <= seuil_alerte
");

$alertes = $requete->fetch_assoc()['alertes'];


/* =========================
   DERNIÈRES VENTES
   ========================= */

$dernieres_ventes = $connexion->query("
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
    ORDER BY ventes.id DESC
    LIMIT 5
");


/* =========================
   DERNIÈRES DÉPENSES
   ========================= */

$dernieres_depenses = $connexion->query("
    SELECT
        id,
        description,
        montant,
        date_depense
    FROM depenses
    ORDER BY id DESC
    LIMIT 5
");


/* =========================
   FONCTIONS
   ========================= */

function argent($montant)
{
    return number_format(
        (float)$montant,
        0,
        ',',
        ' '
    ) . " FCFA";
}


function afficherDate($date)
{
    if (
        empty($date) ||
        $date === "0000-00-00 00:00:00" ||
        $date === "0000-00-00"
    ) {
        return "Date non disponible";
    }

    return date(
        "d/m/Y",
        strtotime($date)
    );
}

?>


<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Gestion Stock</title>


<style>

/* =========================
   BASE
   ========================= */

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;
    min-height: 100%;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    color: #174b3b;
    background-color: #e8f3ec;

    background-image:
        linear-gradient(
            rgba(238, 248, 241, 0.76),
            rgba(238, 248, 241, 0.76)
        ),
        url("fond.jpg");

    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}


/* =========================
   PAGE
   ========================= */

.page {
    width: min(1380px, calc(100% - 60px));
    margin: 0 auto;
    padding: 30px 0 45px;
}


/* =========================
   HEADER
   ========================= */

.header {
    min-height: 105px;
    padding: 22px 32px;
    border-radius: 22px;

    background:
        linear-gradient(
            110deg,
            #08794f,
            #14905f
        );

    color: white;

    display: flex;
    align-items: center;
    justify-content: space-between;

    box-shadow:
        0 8px 25px
        rgba(0, 80, 50, 0.18);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 18px;
}

.header-logo {
    font-size: 52px;
}

.header h1 {
    margin: 0;
    font-size: 32px;
}

.header p {
    margin: 7px 0 0;
    font-size: 17px;
}

.header-decoration {
    font-size: 58px;
}


/* =========================
   DATE
   ========================= */

.date-card {
    margin-top: 22px;
    padding: 15px 22px;
    border-radius: 16px;

    background: rgba(255,255,255,0.94);

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.06);

    font-size: 17px;
}

.date-card strong {
    color: #08794f;
}


/* =========================
   TITRES
   ========================= */

.section-title {
    display: flex;
    align-items: center;
    gap: 10px;

    margin: 38px 0 18px;

    color: #08794f;
    font-size: 24px;
}


/* =========================
   RÉSULTATS
   ========================= */

.resultats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 22px;
}

.resultat-card {
    min-height: 125px;
    padding: 22px;
    border-radius: 18px;

    background: rgba(255,255,255,0.95);

    display: grid;
    grid-template-columns: 58px 1fr;
    align-items: center;

    border: 1px solid rgba(0,0,0,0.06);

    box-shadow:
        0 6px 18px
        rgba(0,0,0,0.06);
}

.resultat-card .icone {
    font-size: 38px;
    grid-row: span 2;
}

.resultat-card .titre {
    font-size: 15px;
    color: #456255;
    line-height: 1.3;
}

.resultat-card .montant {
    color: #08794f;
    font-weight: bold;
    font-size: 23px;
    margin-top: 5px;
}

.benefice {
    border: 2px solid #08794f;
}

.benefice .titre {
    color: #08794f;
    font-weight: bold;
}

.voir-plus {
    display: block;
    margin: 16px 0 0;

    text-align: right;
    color: #08794f;
    font-weight: bold;
    text-decoration: none;
    font-size: 15px;
}


/* =========================
   STOCK
   ========================= */

.stock-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 22px;
}

.stock-card {
    min-height: 115px;
    padding: 22px;
    border-radius: 18px;

    background: rgba(255,255,255,0.95);

    display: flex;
    align-items: center;
    gap: 18px;

    border: 1px solid rgba(0,0,0,0.06);

    box-shadow:
        0 6px 18px
        rgba(0,0,0,0.06);
}

.stock-card .icone {
    font-size: 38px;
    flex-shrink: 0;
}

.stock-card .nombre {
    color: #08794f;
    font-size: 28px;
    font-weight: bold;
}

.stock-card .texte {
    color: #456255;
    margin-top: 4px;
}


/* =========================
   ACTIONS
   ========================= */

.actions {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 22px;
}

.action {
    min-height: 125px;
    padding: 20px;
    border-radius: 18px;

    background: rgba(255,255,255,0.96);

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;

    text-align: center;
    text-decoration: none;
    color: #244d40;
    font-weight: bold;

    border: 1px solid rgba(0,0,0,0.06);

    box-shadow:
        0 6px 18px
        rgba(0,0,0,0.06);

    transition:
        transform 0.2s,
        box-shadow 0.2s;
}

.action:hover {
    transform: translateY(-3px);

    box-shadow:
        0 10px 22px
        rgba(0,0,0,0.10);

    border-color: #08794f;
}

.action .icone {
    font-size: 34px;
    margin-bottom: 10px;
}

.action-principale {
    background: #08794f;
    color: white;
}


/* =========================
   HISTORIQUES
   ========================= */

.historique-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 28px;
    margin-top: 38px;
}

.historique-bloc {
    min-width: 0;
    padding: 22px;
    border-radius: 20px;

    background: rgba(255,255,255,0.95);

    box-shadow:
        0 6px 18px
        rgba(0,0,0,0.06);
}

.historique-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 8px;
}

.historique-header h2 {
    margin: 0;
    color: #08794f;
    font-size: 21px;
}

.historique-header a {
    color: #08794f;
    text-decoration: none;
    font-weight: bold;
    white-space: nowrap;
    font-size: 14px;
}

.table-wrapper {
    overflow-x: auto;
    margin-top: 18px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #e0eee7;
    color: #214d40;
    padding: 12px;
    text-align: left;
    font-size: 13px;
}

td {
    padding: 13px 12px;
    border-bottom: 1px solid #e5ece8;
    color: #36564b;
    font-size: 13px;
}

td.total {
    color: #08794f;
    font-weight: bold;
}

.empty {
    text-align: center;
    padding: 30px;
    color: #777;
}


/* =========================
   FOOTER
   ========================= */

.footer {
    text-align: center;
    padding: 40px 10px 0;
    color: #456255;
}

.footer strong {
    color: #08794f;
    font-size: 18px;
}


/* =========================
   ÉCRANS MOYENS
   ========================= */

@media (max-width: 1150px) {

    .page {
        width: min(calc(100% - 40px), 1000px);
    }

    .resultats,
    .stock-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .actions {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}


/* =========================
   TABLETTE
   ========================= */

@media (max-width: 800px) {

    .page {
        width: calc(100% - 30px);
        padding-top: 20px;
    }

    .header {
        padding: 20px;
    }

    .header h1 {
        font-size: 27px;
    }

    .header-decoration {
        display: none;
    }

    .historique-grid {
        grid-template-columns: 1fr;
    }
}


/* =========================
   TÉLÉPHONE
   ========================= */

@media (max-width: 600px) {

    body {
        background-attachment: scroll;
    }

    .page {
        width: calc(100% - 24px);
        padding: 12px 0 30px;
    }

    .header {
        min-height: 90px;
        padding: 17px;
        border-radius: 17px;
    }

    .header-left {
        gap: 11px;
    }

    .header-logo {
        font-size: 38px;
    }

    .header h1 {
        font-size: 23px;
    }

    .header p {
        font-size: 13px;
    }

    .date-card {
        margin-top: 14px;
        padding: 13px 15px;
        font-size: 14px;
    }

    .section-title {
        margin: 28px 0 14px;
        font-size: 20px;
    }

    .resultats {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .resultat-card {
        min-height: 105px;
        padding: 18px;
        grid-template-columns: 50px 1fr;
    }

    .resultat-card .icone {
        font-size: 31px;
    }

    .resultat-card .titre {
        font-size: 14px;
    }

    .resultat-card .montant {
        font-size: 20px;
    }

    .voir-plus {
        text-align: center;
        margin-top: 13px;
    }

    .stock-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .stock-card {
        min-height: 100px;
        padding: 18px;
    }

    .stock-card .icone {
        font-size: 32px;
    }

    .stock-card .nombre {
        font-size: 25px;
    }

    .actions {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .action {
        min-height: 90px;
        padding: 16px;
    }

    .action .icone {
        font-size: 30px;
        margin-bottom: 7px;
    }

    .historique-grid {
        grid-template-columns: 1fr;
        gap: 18px;
        margin-top: 30px;
    }

    .historique-bloc {
        padding: 16px;
        border-radius: 17px;
    }

    .historique-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .historique-header h2 {
        font-size: 19px;
    }

    .table-wrapper {
        margin-top: 12px;
    }

    table {
        min-width: 540px;
    }

    .footer {
        padding-top: 30px;
    }
}


/* =========================
   TRÈS PETIT TÉLÉPHONE
   ========================= */

@media (max-width: 380px) {

    .page {
        width: calc(100% - 18px);
    }

    .header h1 {
        font-size: 21px;
    }

    .header p {
        font-size: 12px;
    }

    .resultat-card .montant {
        font-size: 18px;
    }
}

.bouton-deconnexion {
    display: inline-block;
    padding: 10px 14px;
    border: 1px solid white;
    border-radius: 10px;
    color: white;
    text-decoration: none;
    font-weight: bold;
    white-space: nowrap;
}

.bouton-deconnexion:hover {
    background: rgba(255, 255, 255, 0.18);
}

</style>

</head>


<body>

<div class="page">


<!-- =========================
     HEADER
     ========================= -->

<header class="header">

    <div class="header-left">

        <div class="header-logo">
            🌿
        </div>

        <div>

            <h1>
                Gestion Stock
            </h1>

            <p>
                Bienvenue <?= htmlspecialchars(
                    $nom_utilisateur,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?> 👋
            </p>

        </div>

    </div>

    <a href="deconnexion.php" class="bouton-deconnexion">
    🚪 Déconnexion
</a>

</header>


<!-- =========================
     DATE
     ========================= -->

<div class="date-card">

    📅 Aujourd'hui :

    <strong>
        <?php echo date("d/m/Y"); ?>
    </strong>

</div>


<!-- =========================
     RÉSULTATS
     ========================= -->

<h2 class="section-title">
    📊 Résultats
</h2>

<div class="resultats">

    <div class="resultat-card">

        <div class="icone">💰</div>

        <div class="titre">
            Chiffre d'affaires
        </div>

        <div class="montant">
            <?php echo argent($chiffre_affaires); ?>
        </div>

    </div>


    <div class="resultat-card">

        <div class="icone">📦</div>

        <div class="titre">
            Coût des produits vendus
        </div>

        <div class="montant">
            <?php echo argent($cout_produits); ?>
        </div>

    </div>


    <div class="resultat-card">

        <div class="icone">🪙</div>

        <div class="titre">
            Dépenses
        </div>

        <div class="montant">
            <?php echo argent($depenses); ?>
        </div>

    </div>


    <div class="resultat-card benefice">

        <div class="icone">📊</div>

        <div class="titre">
            Bénéfice réel
        </div>

        <div class="montant">
            <?php echo argent($benefice_reel); ?>
        </div>

    </div>

</div>


<a href="resultats.php" class="voir-plus">
    Voir les résultats détaillés →
</a>


<!-- =========================
     STOCK
     ========================= -->

<h2 class="section-title">
    📦 État du stock
</h2>

<div class="stock-grid">

    <div class="stock-card">

        <div class="icone">🛍️</div>

        <div>

            <div class="nombre">
                <?php echo $stock_unitaire; ?>
            </div>

            <div class="texte">
                Petits sacs prêts
            </div>

        </div>

    </div>


    <div class="stock-card">

        <div class="icone">👜</div>

        <div>

            <div class="nombre">
                <?php echo $stock_gros; ?>
            </div>

            <div class="texte">
                Grands sacs
            </div>

        </div>

    </div>


    <div class="stock-card">

        <div class="icone">🌿</div>

        <div>

            <div class="nombre">
                <?php echo $nombre_produits; ?>
            </div>

            <div class="texte">
                Produits
            </div>

        </div>

    </div>


    <div class="stock-card">

        <div class="icone">⚠️</div>

        <div>

            <div class="nombre">
                <?php echo $alertes; ?>
            </div>

            <div class="texte">
                Alertes stock
            </div>

        </div>

    </div>

</div>


<!-- =========================
     ACTIONS
     ========================= -->

<h2 class="section-title">
    ⚡ Actions rapides
</h2>

<div class="actions">

    <a href="vente.php" class="action action-principale">

        <span class="icone">🛒</span>

        Nouvelle vente

    </a>


    <a href="ajouter_produit.html" class="action">

        <span class="icone">➕</span>

        Ajouter produit

    </a>


    <a href="transformation.php" class="action">

        <span class="icone">🔄</span>

        Transformation

    </a>


    <a href="ajouter_depense.php" class="action">

        <span class="icone">💳</span>

        Ajouter dépense

    </a>


    <a href="stock.php" class="action">

        <span class="icone">📦</span>

        Voir le stock

    </a>


    <a href="resultats.php" class="action">

        <span class="icone">📊</span>

        Résultats

    </a>


    <a href="fournisseurs.php" class="action">

        <span class="icone">🤝</span>

        Fournisseurs / Dettes

    </a>

</div>


<!-- =========================
     HISTORIQUES
     ========================= -->

<div class="historique-grid">


    <!-- VENTES -->

    <div class="historique-bloc">

        <div class="historique-header">

            <h2>
                🧾 Dernières ventes
            </h2>

            <a href="historique_ventes.php">
                Voir tout l'historique →
            </a>

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>

                </thead>


                <tbody>

                <?php if (
                    $dernieres_ventes &&
                    $dernieres_ventes->num_rows > 0
                ): ?>

                    <?php while (
                        $vente = $dernieres_ventes->fetch_assoc()
                    ): ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $vente['nom'],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </td>

                        <td>
                            <?php echo $vente['quantite']; ?>
                        </td>

                        <td>
                            <?php echo argent($vente['prix_unitaire']); ?>
                        </td>

                        <td class="total">
                            <?php echo argent($vente['total']); ?>
                        </td>

                        <td>
                            <?php echo afficherDate($vente['date_vente']); ?>
                        </td>

                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="empty">
                            Aucune vente enregistrée.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- DÉPENSES -->

    <div class="historique-bloc">

        <div class="historique-header">

            <h2>
                💳 Dernières dépenses
            </h2>

            <a href="historique_depenses.php">
                Voir tout l'historique →
            </a>

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>Date</th>
                    </tr>

                </thead>


                <tbody>

                <?php if (
                    $dernieres_depenses &&
                    $dernieres_depenses->num_rows > 0
                ): ?>

                    <?php while (
                        $depense = $dernieres_depenses->fetch_assoc()
                    ): ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $depense['description'],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </td>

                        <td class="total">
                            <?php echo argent($depense['montant']); ?>
                        </td>

                        <td>
                            <?php echo afficherDate($depense['date_depense']); ?>
                        </td>

                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="3" class="empty">
                            📄 Aucune dépense enregistrée.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!-- =========================
     FOOTER
     ========================= -->

<div class="footer">

    🌿

    <strong>
        Gestion Stock
    </strong>

    <br><br>

    Gestion simple des produits,
    ventes et bénéfices.

</div>


</div>

</body>

</html>


<?php
$connexion->close();
?>