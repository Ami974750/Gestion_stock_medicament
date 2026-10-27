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

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ajouter une dépense</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5fdf9;
        }

        .container {
            max-width: 430px;
            margin: auto;
            padding: 25px 20px;
        }

        h1 {
            text-align: center;
            color: #16845b;
            margin-bottom: 30px;
        }

        .formulaire {
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 14px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
        }

        .valider {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            background-color: #16845b;
            color: white;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
        }

        .valider:hover {
            background-color: #116b49;
        }

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

    <h1>💸 Ajouter une dépense</h1>

    <form
        class="formulaire"
        action="enregistrer_depense.php"
        method="POST"
    >

        <label for="description">
            Description de la dépense
        </label>

        <input
            type="text"
            id="description"
            name="description"
            placeholder="Ex : Transport"
            required
        >


        <label for="montant">
            Montant
        </label>

        <input
            type="number"
            id="montant"
            name="montant"
            min="1"
            placeholder="Ex : 1000"
            required
        >


        <button
            type="submit"
            class="valider"
        >
            Enregistrer la dépense
        </button>

    </form>


    <a
        href="index.php"
        class="retour"
    >
        ← Retour à l'accueil
    </a>

</div>

</body>

</html>