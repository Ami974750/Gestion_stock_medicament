<?php
require_once "securite.php";
$connexion = new mysqli(
    "localhost",
    "root",
    "",
    "gestion_stock_medicaments"
);

if ($connexion->connect_error) {
    die("Erreur de connexion : " . $connexion->connect_error);
}

$requete = "
    SELECT
        id,
        nom,
        categorie,
        prix_achat,
        frais_transport,
        prix_vente,
        stock_gros,
        stock_unitaire,
        seuil_alerte,
        cout_unitaire
    FROM produits
    ORDER BY nom
";

$resultat = $connexion->query($requete);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mon Stock</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f2f8f5;
            color: #173b2b;
        }

        .container {
            max-width: 430px;
            margin: auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #16845b;
            margin-bottom: 8px;
        }

        .intro {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        .produit {
            background: white;
            padding: 20px;
            margin-bottom: 18px;
            border-radius: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .produit h2 {
            margin-top: 0;
            color: #16845b;
        }

        .ligne {
            padding: 8px 0;
            border-bottom: 1px solid #edf2ef;
        }

        .ligne:last-child {
            border-bottom: none;
        }

        .gros {
            color: #765b00;
            font-weight: bold;
        }

        .unitaire {
            color: #16845b;
            font-weight: bold;
        }

        .alerte {
            margin-top: 15px;
            padding: 12px;
            border-radius: 10px;
            background: #fff4d6;
            color: #8a6500;
            font-weight: bold;
        }

        .rupture {
            margin-top: 15px;
            padding: 12px;
            border-radius: 10px;
            background: #ffe3e3;
            color: #b00020;
            font-weight: bold;
        }

        .ok {
            margin-top: 15px;
            padding: 12px;
            border-radius: 10px;
            background: #eef8f3;
            color: #16845b;
            font-weight: bold;
        }

        .modifier {
            display: block;
            text-align: center;
            margin-top: 18px;
            padding: 13px;
            background: #16845b;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: bold;
        }

        .transformation {
            display: block;
            text-align: center;
            margin-top: 10px;
            padding: 13px;
            background: #e8f3ee;
            color: #16845b;
            text-decoration: none;
            border-radius: 12px;
            font-weight: bold;
        }

        .retour {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #16845b;
            text-decoration: none;
            font-weight: bold;
        }

        .aucun {
            background: white;
            padding: 25px;
            border-radius: 18px;
            text-align: center;
            color: #666;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>📦 Mon Stock</h1>

    <p class="intro">
        Gros sacs et petits sacs disponibles
    </p>


    <?php if ($resultat && $resultat->num_rows > 0) { ?>

        <?php while ($produit = $resultat->fetch_assoc()) { ?>

            <div class="produit">

                <h2>
                    <?php echo htmlspecialchars($produit['nom']); ?>
                </h2>


                <div class="ligne">
                    Catégorie :
                    <strong>
                        <?php echo htmlspecialchars($produit['categorie']); ?>
                    </strong>
                </div>


                <div class="ligne gros">
                    📦 Stock en gros :
                    <?php echo $produit['stock_gros']; ?>
                    sac(s)
                </div>


                <div class="ligne unitaire">
                    🛍️ Stock unitaire :
                    <?php echo $produit['stock_unitaire']; ?>
                    petit(s) sac(s)
                </div>


                <div class="ligne">
                    💰 Prix d'achat en gros :
                    <strong>
                        <?php
                        echo number_format(
                            $produit['prix_achat'],
                            0,
                            ',',
                            ' '
                        );
                        ?>
                        FCFA
                    </strong>
                </div>


                <div class="ligne">
                    🚚 Transport :
                    <strong>
                        <?php
                        echo number_format(
                            $produit['frais_transport'],
                            0,
                            ',',
                            ' '
                        );
                        ?>
                        FCFA
                    </strong>
                </div>


                <div class="ligne">
                    💵 Prix de vente d'un petit sac :
                    <strong>
                        <?php
                        echo number_format(
                            $produit['prix_vente'],
                            0,
                            ',',
                            ' '
                        );
                        ?>
                        FCFA
                    </strong>
                </div>


                <div class="ligne">
                    🧮 Coût réel d'un petit sac :

                    <strong>

                        <?php

                        if ($produit['cout_unitaire'] > 0) {

                            echo number_format(
                                $produit['cout_unitaire'],
                                0,
                                ',',
                                ' '
                            );

                            echo " FCFA";

                        } else {

                            echo "Pas encore calculé";

                        }

                        ?>

                    </strong>

                </div>


                <?php

                if ($produit['stock_unitaire'] == 0) {

                    echo "
                    <div class='rupture'>
                        🚨 Aucun petit sac disponible à la vente
                    </div>
                    ";

                } elseif (
                    $produit['stock_unitaire']
                    <=
                    $produit['seuil_alerte']
                ) {

                    echo "
                    <div class='alerte'>
                        ⚠️ Stock unitaire faible
                    </div>
                    ";

                } else {

                    echo "
                    <div class='ok'>
                        ✅ Stock disponible
                    </div>
                    ";

                }

                ?>


                <a
                    href="transformation.php"
                    class="transformation"
                >
                    🔄 Transformer un gros sac
                </a>


                <a
                    href="modifier_produit.php?id=<?php echo $produit['id']; ?>"
                    class="modifier"
                >
                    ✏️ Modifier le produit
                </a>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="aucun">
            Aucun produit enregistré.
        </div>

    <?php } ?>


    <a href="index.php" class="retour">
        ← Retour à l'accueil
    </a>

</div>

</body>

</html>

<?php

$connexion->close();

?>