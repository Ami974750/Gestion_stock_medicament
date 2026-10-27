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


$dettes = $connexion->query("
    SELECT
        d.id,
        d.description,
        d.montant_total,
        f.nom AS fournisseur_nom,
        COALESCE(SUM(p.montant), 0) AS deja_paye
    FROM dettes_fournisseurs d
    INNER JOIN fournisseurs f
        ON f.id = d.fournisseur_id
    LEFT JOIN paiements_dettes p
        ON p.dette_id = d.id
    GROUP BY
        d.id,
        d.description,
        d.montant_total,
        f.nom
    HAVING d.montant_total - deja_paye > 0
    ORDER BY d.id DESC
");

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Enregistrer un paiement</title>

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
    margin: 0 0 8px;
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
    margin-top: 18px;
    margin-bottom: 7px;
}

label:first-child {
    margin-top: 0;
}

select,
input,
textarea {
    width: 100%;
    padding: 14px;
    border: 1px solid #cfded7;
    border-radius: 12px;
    font-size: 16px;
    background: white;
}

textarea {
    min-height: 90px;
    resize: vertical;
}

.info {
    margin-top: 18px;
    padding: 14px;
    background: #f5fdf9;
    border-radius: 12px;
    line-height: 1.5;
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
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
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

        <h1>💳 Enregistrer un paiement</h1>

        <p>
            Indique combien a été payé au fournisseur.
        </p>

    </div>


    <div class="formulaire">

        <form action="enregistrer_paiement.php" method="POST">

            <label for="dette_id">
                👤 Dette / Fournisseur
            </label>

            <select
                id="dette_id"
                name="dette_id"
                required
            >

                <option value="">
                    -- Choisir une dette --
                </option>

                <?php while ($dette = $dettes->fetch_assoc()): ?>

                    <?php
                    $reste = $dette['montant_total'] - $dette['deja_paye'];
                    ?>

                    <option value="<?= $dette['id'] ?>">

                        <?= htmlspecialchars($dette['fournisseur_nom']) ?>
                        -
                        <?= htmlspecialchars($dette['description']) ?>
                        -
                        Reste :
                        <?= number_format($reste, 0, ',', ' ') ?>
                        FCFA

                    </option>

                <?php endwhile; ?>

            </select>


            <label for="montant">
                💰 Montant payé
            </label>

            <input
                type="number"
                id="montant"
                name="montant"
                min="1"
                step="1"
                placeholder="Ex : 20 000"
                required
            >


            <label for="notes">
                📝 Notes
            </label>

            <textarea
                id="notes"
                name="notes"
                placeholder="Ex : paiement en espèces"
            ></textarea>


            <div class="info">

                💡 Le montant sera automatiquement ajouté
                à ce qui a déjà été payé.

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