<?php
$connexion = new mysqli("localhost", "root", "", "gestion_stock_medicaments");

if ($connexion->connect_error) {
    die("Erreur de connexion : " . $connexion->connect_error);
}

$requete = "SELECT id, nom, stock_gros, stock_unitaire, prix_achat, frais_transport
            FROM produits
            ORDER BY nom";

$resultat = $connexion->query($requete);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transformer un gros sac</title>

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
            background: #fff;
        }

        .info {
            margin-top: 15px;
            padding: 14px;
            background: #eef8f3;
            border-radius: 12px;
            font-size: 14px;
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
            cursor: pointer;
        }

        button:hover {
            background: #116c4a;
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

    <h1>🔄 Transformation</h1>

    <p class="intro">
        Transformer un gros sac en petits sacs prêts à vendre
    </p>

    <div class="carte">

        <form action="enregistrer_transformation.php" method="POST">

            <label for="produit">Produit</label>

            <select name="produit_id" id="produit" required>
                <option value="">Choisir un produit</option>

                <?php while ($produit = $resultat->fetch_assoc()) { ?>

                    <option value="<?php echo $produit['id']; ?>">
                        <?php echo htmlspecialchars($produit['nom']); ?>
                        — Gros : <?php echo $produit['stock_gros']; ?>
                    </option>

                <?php } ?>

            </select>


            <label for="quantite_gros">
                Nombre de gros sacs utilisés
            </label>

            <input
                type="number"
                name="quantite_gros"
                id="quantite_gros"
                min="1"
                value="1"
                required
            >


            <label for="quantite_unitaire">
                Nombre de petits sacs produits
            </label>

            <input
                type="number"
                name="quantite_unitaire"
                id="quantite_unitaire"
                min="1"
                placeholder="Ex : 100"
                required
            >


            <div class="info">
                💡 Exemple :<br><br>
                1 gros sac → 100 petits sacs<br>
                Le système calculera automatiquement le coût d'un petit sac.
            </div>


            <button type="submit">
                🔄 Transformer
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