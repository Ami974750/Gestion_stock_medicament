<?php
require_once "securite.php";

$connexion = new mysqli("localhost", "root", "", "gestion_stock_medicaments");

if ($connexion->connect_error) {
    die("Erreur de connexion : " . $connexion->connect_error);
}

$requete = "
    SELECT id, nom, stock_unitaire, prix_vente
    FROM produits
    WHERE stock_unitaire > 0
    ORDER BY nom
";

$resultat = $connexion->query($requete);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nouvelle vente</title>

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
        }

        .intro {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        .carte {
            background: white;
            padding: 20px;
            border-radius: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 7px;
        }

        select,
        input {
            width: 100%;
            padding: 14px;
            border: 1px solid #d5e3dc;
            border-radius: 12px;
            font-size: 16px;
            background: white;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 15px;
            border: none;
            border-radius: 12px;
            background: #16845b;
            color: white;
            font-size: 17px;
            font-weight: bold;
        }

        .info {
            margin-top: 15px;
            padding: 14px;
            background: #eef8f3;
            border-radius: 12px;
            font-size: 14px;
        }

        .retour {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #16845b;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>🛍️ Nouvelle vente</h1>

    <p class="intro">
        Vendre un petit sac prêt à la vente
    </p>

    <div class="carte">

        <form action="enregistrer_vente.php" method="POST">

            <label for="produit">
                Produit
            </label>

            <select name="produit_id" id="produit" required>

                <option value="">
                    Choisir un produit
                </option>

                <?php while ($produit = $resultat->fetch_assoc()) { ?>

                    <option value="<?php echo $produit['id']; ?>">

                        <?php echo htmlspecialchars($produit['nom']); ?>

                        — Stock :
                        <?php echo $produit['stock_unitaire']; ?>

                        — <?php echo number_format($produit['prix_vente'], 0, ',', ' '); ?> FCFA

                    </option>

                <?php } ?>

            </select>


            <label for="quantite">
                Quantité vendue
            </label>

            <input
                type="number"
                id="quantite"
                name="quantite"
                min="1"
                placeholder="Ex : 2"
                required
            >


            <div class="info">
                💡 La vente diminuera uniquement le
                <strong>stock unitaire</strong>.
            </div>


            <button type="submit">
                ✅ Enregistrer la vente
            </button>

        </form>

    </div>

    <a href="index.php" class="retour">
        ← Retour à l'accueil
    </a>

</div>

</body>
</html>

<?php
$connexion->close();
?>