<?php

$connexion = new mysqli(
    "localhost",
    "root",
    "",
    "gestion_stock_medicaments"
);

if ($connexion->connect_error) {
    die("Erreur de connexion : " . $connexion->connect_error);
}


$nom = $_POST['nom'] ?? '';
$categorie = $_POST['categorie'] ?? '';

$prix_achat = $_POST['prix_achat'] ?? '';
$frais_transport = $_POST['frais_transport'] ?? '';
$prix_vente = $_POST['prix_vente'] ?? '';

$stock_gros = $_POST['stock_gros'] ?? '';
$stock_unitaire = $_POST['stock_unitaire'] ?? '';

$seuil_alerte = $_POST['seuil_alerte'] ?? '';


if (
    empty($nom) ||
    empty($categorie) ||
    $prix_achat === '' ||
    $frais_transport === '' ||
    $prix_vente === '' ||
    $stock_gros === '' ||
    $stock_unitaire === '' ||
    $seuil_alerte === ''
) {
    die("Veuillez remplir tous les champs.");
}


$requete = $connexion->prepare(
    "INSERT INTO produits
    (
        nom,
        categorie,
        prix_achat,
        frais_transport,
        prix_vente,
        quantite_stock,
        stock_gros,
        stock_unitaire,
        seuil_alerte
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);


/*
    quantite_stock est conservé temporairement
    pour ne pas casser les anciennes pages.
    Il correspond ici au stock unitaire.
*/

$quantite_stock = $stock_unitaire;


$requete->bind_param(
    "ssdddiiii",
    $nom,
    $categorie,
    $prix_achat,
    $frais_transport,
    $prix_vente,
    $quantite_stock,
    $stock_gros,
    $stock_unitaire,
    $seuil_alerte
);


if ($requete->execute()) {

    echo "
    <!DOCTYPE html>
    <html lang='fr'>

    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>

        <title>Produit ajouté</title>

        <style>

            body {
                margin: 0;
                font-family: Arial, sans-serif;
                background: #f2f8f5;
                color: #173b2b;
            }

            .container {
                max-width: 430px;
                margin: auto;
                padding: 30px 20px;
            }

            .carte {
                background: white;
                padding: 25px;
                border-radius: 20px;
                text-align: center;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            }

            .succes {
                font-size: 50px;
            }

            h1 {
                color: #16845b;
            }

            .retour {
                display: block;
                margin-top: 20px;
                padding: 14px;
                background: #16845b;
                color: white;
                text-decoration: none;
                border-radius: 12px;
                font-weight: bold;
            }

        </style>
    </head>

    <body>

        <div class='container'>

            <div class='carte'>

                <div class='succes'>✅</div>

                <h1>Produit ajouté !</h1>

                <p>
                    Le produit <strong>" . htmlspecialchars($nom) . "</strong>
                    a été enregistré avec succès.
                </p>

                <a href='ajouter_produit.html' class='retour'>
                    ➕ Ajouter un autre produit
                </a>

                <a href='index.php' class='retour'>
                    🏠 Retour à l'accueil
                </a>

            </div>

        </div>

    </body>

    </html>
    ";

} else {

    echo "Erreur lors de l'enregistrement : " . $requete->error;
}


$requete->close();
$connexion->close();

?>