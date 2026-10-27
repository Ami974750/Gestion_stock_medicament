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
?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Ajouter un fournisseur</title>

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
            margin-bottom: 7px;
            margin-top: 17px;
        }

        label:first-child {
            margin-top: 0;
        }

        input,
        textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid #cfded7;
            border-radius: 12px;
            font-size: 16px;
            font-family: Arial, sans-serif;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .obligatoire {
            color: #c0392b;
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

        <h1>➕ Ajouter un fournisseur</h1>

        <p>
            Enregistre les informations de ton fournisseur.
        </p>

    </div>


    <div class="formulaire">

        <form action="enregistrer_fournisseur.php"
              method="POST">


            <label for="nom">

                Nom du fournisseur
                <span class="obligatoire">*</span>

            </label>

            <input
                type="text"
                id="nom"
                name="nom"
                placeholder="Ex : Monsieur Kouassi"
                required
            >


            <label for="telephone">

                📞 Téléphone

            </label>

            <input
                type="text"
                id="telephone"
                name="telephone"
                placeholder="Ex : 07 00 00 00 00"
            >


            <label for="adresse">

                📍 Adresse

            </label>

            <input
                type="text"
                id="adresse"
                name="adresse"
                placeholder="Ex : Abidjan"
            >


            <label for="notes">

                📝 Notes

            </label>

            <textarea
                id="notes"
                name="notes"
                placeholder="Informations supplémentaires..."
            ></textarea>


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