<?php

$connexion = new mysqli(
    "localhost",
    "root",
    "",
    "gestion_stock_medicaments"
);

if ($connexion->connect_error) {
    die("Erreur de connexion à la base de données.");
}

$connexion->set_charset("utf8mb4");

$fournisseurs = $connexion->query("
    SELECT id, nom
    FROM fournisseurs
    ORDER BY nom ASC
");

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Ajouter une dette</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5fdf9;
            color: #17352a;
        }

        .page {
            width: 100%;
            max-width: 600px;
            margin: auto;
            padding: 20px;
        }

        .header {
            background: #16845b;
            color: white;
            padding: 22px;
            border-radius: 18px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0 0 7px;
            font-size: 25px;
        }

        .header p {
            margin: 0;
        }

        .formulaire {
            background: white;
            padding: 22px;
            border-radius: 18px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 17px;
            margin-bottom: 7px;
        }

        label:first-child {
            margin-top: 0;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid #cfded7;
            border-radius: 12px;
            font-size: 16px;
            font-family: Arial, sans-serif;
            background: white;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .info {
            margin-top: 18px;
            padding: 14px;
            background: #f5fdf9;
            border-radius: 12px;
            line-height: 1.5;
            font-size: 14px;
        }

        .boutons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 25px;
        }

        button,
        .retour {
            min-height: 52px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        button {
            border: none;
            background: #16845b;
            color: white;
        }

        .retour {
            border: 2px solid #16845b;
            color: #16845b;
            background: white;
        }

        @media (max-width: 500px) {

            .page {
                padding: 14px;
            }

            .boutons {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="page">

    <div class="header">

        <h1>💳 Ajouter une dette</h1>

        <p>
            Enregistre une marchandise reçue et son montant à payer.
        </p>

    </div>


    <div class="formulaire">

        <form action="enregistrer_dette.php" method="POST">


            <label for="fournisseur_id">
                👤 Fournisseur
            </label>

            <select
                id="fournisseur_id"
                name="fournisseur_id"
                required
            >

                <option value="">
                    -- Choisir un fournisseur --
                </option>

                <?php while ($fournisseur = $fournisseurs->fetch_assoc()): ?>

                    <option value="<?= $fournisseur['id'] ?>">

                        <?= htmlspecialchars($fournisseur['nom']) ?>

                    </option>

                <?php endwhile; ?>

            </select>


            <label for="description">
                📦 Marchandise / Description
            </label>

            <input
                type="text"
                id="description"
                name="description"
                placeholder="Ex : 10 sacs de moringa"
                required
            >


            <label for="montant_total">
                💰 Montant total
            </label>

            <input
                type="number"
                id="montant_total"
                name="montant_total"
                min="0"
                step="0.01"
                placeholder="Ex : 50000"
                required
            >


            <label for="date_dette">
                📅 Date de la dette
            </label>

            <input
                type="date"
                id="date_dette"
                name="date_dette"
                value="<?= date('Y-m-d') ?>"
                required
            >


            <label for="notes">
                📝 Notes
            </label>

            <textarea
                id="notes"
                name="notes"
                placeholder="Informations supplémentaires..."
            ></textarea>


            <div class="info">
                💡 Si elle a déjà payé une partie de cette dette,
                nous l'enregistrerons ensuite comme un paiement.
            </div>


            <div class="boutons">

                <a
                    href="fournisseurs.php"
                    class="retour"
                >
                    ← Retour
                </a>

                <button type="submit">
                    💾 Enregistrer
                </button>

            </div>

        </form>

    </div>

</div>

</body>

</html>

<?php
$connexion->close();
?>