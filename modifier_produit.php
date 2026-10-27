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

/* Vérifier qu'un ID est présent */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Produit introuvable.");
}

$id = (int) $_GET['id'];

/* Récupérer le produit */
$requete = $connexion->prepare(
    "SELECT
        id,
        nom,
        categorie,
        prix_achat,
        frais_transport,
        prix_vente,
        quantite_stock,
        seuil_alerte
     FROM produits
     WHERE id = ?"
);

$requete->bind_param("i", $id);
$requete->execute();

$resultat = $requete->get_result();

if ($resultat->num_rows === 0) {
    die("Produit introuvable.");
}

$produit = $resultat->fetch_assoc();

$requete->close();

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Modifier un produit</title>

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
    margin-bottom: 25px;
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

.exemple {
    margin-top: -12px;
    margin-bottom: 20px;
    color: #666;
    font-size: 13px;
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

<h1>✏️ Modifier le produit</h1>


<form
    class="formulaire"
    action="enregistrer_modification_produit.php"
    method="POST"
>


<input
    type="hidden"
    name="id"
    value="<?php echo $produit['id']; ?>"
>


<label for="nom">
Nom du produit
</label>

<input
    type="text"
    id="nom"
    name="nom"
    value="<?php echo htmlspecialchars($produit['nom']); ?>"
    required
>


<label for="categorie">
Catégorie
</label>

<input
    type="text"
    id="categorie"
    name="categorie"
    value="<?php echo htmlspecialchars($produit['categorie']); ?>"
    required
>


<label for="prix_achat">
Prix d'achat en gros
</label>

<input
    type="number"
    id="prix_achat"
    name="prix_achat"
    min="0"
    value="<?php echo $produit['prix_achat']; ?>"
    required
>

<div class="exemple">
Prix payé pour le grand sac ou le lot.
</div>


<label for="frais_transport">
Frais de transport
</label>

<input
    type="number"
    id="frais_transport"
    name="frais_transport"
    min="0"
    value="<?php echo $produit['frais_transport']; ?>"
    required
>

<div class="exemple">
Transport payé pour faire venir le produit.
</div>


<label for="prix_vente">
Prix de vente unitaire
</label>

<input
    type="number"
    id="prix_vente"
    name="prix_vente"
    min="0"
    value="<?php echo $produit['prix_vente']; ?>"
    required
>

<div class="exemple">
Prix d'un petit sac préparé pour la vente.
</div>


<label for="quantite_stock">
Quantité en stock
</label>

<input
    type="number"
    id="quantite_stock"
    name="quantite_stock"
    min="0"
    value="<?php echo $produit['quantite_stock']; ?>"
    required
>

<div class="exemple">
Nombre de petits sacs actuellement disponibles.
</div>


<label for="seuil_alerte">
Seuil d'alerte
</label>

<input
    type="number"
    id="seuil_alerte"
    name="seuil_alerte"
    min="0"
    value="<?php echo $produit['seuil_alerte']; ?>"
    required
>

<div class="exemple">
Alerte lorsque le stock atteint ce nombre.
</div>


<button
    type="submit"
    class="valider"
>
💾 Enregistrer les modifications
</button>


</form>


<a
    href="stock.php"
    class="retour"
>
← Retour au stock
</a>

</div>

</body>

</html>

<?php

$connexion->close();

?>