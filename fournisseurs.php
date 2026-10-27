<?php

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

$message = "";
$type_message = "";

/* =========================================================
   ENREGISTREMENT D'UNE OPÉRATION
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    /* ---------- NOUVEAU FOURNISSEUR ---------- */

    if ($action === "nouveau_fournisseur") {

        $nom = trim($_POST["nom"] ?? "");
        $telephone = trim($_POST["telephone"] ?? "");
        $adresse = trim($_POST["adresse"] ?? "");
        $notes = trim($_POST["notes"] ?? "");

        if ($nom === "") {

            $message = "Le nom du fournisseur est obligatoire.";
            $type_message = "erreur";

        } else {

            $requete = $connexion->prepare("
                INSERT INTO fournisseurs
                (nom, telephone, adresse, notes)
                VALUES (?, ?, ?, ?)
            ");

            $requete->bind_param(
                "ssss",
                $nom,
                $telephone,
                $adresse,
                $notes
            );

            if ($requete->execute()) {

                $message = "Fournisseur ajouté avec succès.";
                $type_message = "succes";

            } else {

                $message = "Erreur lors de l'ajout du fournisseur.";
                $type_message = "erreur";
            }

            $requete->close();
        }
    }

    /* ---------- NOUVELLE DETTE + PAIEMENT ---------- */

    if ($action === "nouvelle_operation") {

        $fournisseur_id = intval($_POST["fournisseur_id"] ?? 0);
        $description = trim($_POST["description"] ?? "");
        $montant_dette = floatval($_POST["montant_dette"] ?? 0);
        $montant_paye = floatval($_POST["montant_paye"] ?? 0);
        $date_dette = $_POST["date_dette"] ?? "";
        $note = trim($_POST["note"] ?? "");

        if ($fournisseur_id <= 0) {

            $message = "Choisis un fournisseur.";
            $type_message = "erreur";

        } elseif ($description === "") {

            $message = "Indique ce qui a été acheté.";
            $type_message = "erreur";

        } elseif ($montant_dette <= 0) {

            $message = "Le montant de la dette doit être supérieur à 0.";
            $type_message = "erreur";

        } elseif ($montant_paye < 0) {

            $message = "Le paiement ne peut pas être négatif.";
            $type_message = "erreur";

        } elseif ($montant_paye > $montant_dette) {

            $message = "Le paiement ne peut pas être supérieur à la dette.";
            $type_message = "erreur";

        } elseif ($date_dette === "") {

            $message = "Choisis une date.";
            $type_message = "erreur";

        } else {

            $connexion->begin_transaction();

            try {

                /* Vérifier que le fournisseur existe */

                $verification = $connexion->prepare("
                    SELECT id
                    FROM fournisseurs
                    WHERE id = ?
                ");

                $verification->bind_param(
                    "i",
                    $fournisseur_id
                );

                $verification->execute();

                $resultat_verification = $verification->get_result();

                if ($resultat_verification->num_rows === 0) {
                    throw new Exception("Fournisseur introuvable.");
                }

                $verification->close();

                /* Enregistrer la dette */

                $requete_dette = $connexion->prepare("
                    INSERT INTO dettes_fournisseurs
                    (
                        fournisseur_id,
                        description,
                        montant_total,
                        date_dette,
                        notes
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                $requete_dette->bind_param(
                    "isdss",
                    $fournisseur_id,
                    $description,
                    $montant_dette,
                    $date_dette,
                    $note
                );

                if (!$requete_dette->execute()) {
                    throw new Exception("Impossible d'enregistrer la dette.");
                }

                $dette_id = $connexion->insert_id;

                $requete_dette->close();

                /* Enregistrer le paiement s'il y en a un */

                if ($montant_paye > 0) {

                    $requete_paiement = $connexion->prepare("
                        INSERT INTO paiements_dettes
                        (
                            dette_id,
                            montant,
                            note
                        )
                        VALUES (?, ?, ?)
                    ");

                    $requete_paiement->bind_param(
                        "ids",
                        $dette_id,
                        $montant_paye,
                        $note
                    );

                    if (!$requete_paiement->execute()) {
                        throw new Exception("Impossible d'enregistrer le paiement.");
                    }

                    $requete_paiement->close();
                }

                $connexion->commit();

                $message = "Dette enregistrée avec succès.";
                $type_message = "succes";

            } catch (Exception $e) {

                $connexion->rollback();

                $message = $e->getMessage();
                $type_message = "erreur";
            }
        }
    }
}


/* =========================================================
   RÉCUPÉRER LES FOURNISSEURS
   ========================================================= */

$fournisseurs = [];

$resultat_fournisseurs = $connexion->query("
    SELECT id, nom
    FROM fournisseurs
    ORDER BY nom ASC
");

while ($ligne = $resultat_fournisseurs->fetch_assoc()) {
    $fournisseurs[] = $ligne;
}


/* =========================================================
   RÉCUPÉRER LES RÉSUMÉS
   ========================================================= */

$requete_resumes = "
    SELECT
        f.id,
        f.nom,

        COALESCE(SUM(d.montant_total), 0) AS total_dettes,

        COALESCE(
            (
                SELECT SUM(p.montant)
                FROM paiements_dettes p
                INNER JOIN dettes_fournisseurs d2
                    ON d2.id = p.dette_id
                WHERE d2.fournisseur_id = f.id
            ),
            0
        ) AS total_paye

    FROM fournisseurs f

    LEFT JOIN dettes_fournisseurs d
        ON d.fournisseur_id = f.id

    GROUP BY f.id, f.nom

    ORDER BY f.nom ASC
";

$resultat_resumes = $connexion->query($requete_resumes);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Fournisseurs et dettes</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 25px;
    font-family: Arial, sans-serif;
    background: #f5fdf9;
    color: #17352a;
}

.conteneur {
    max-width: 950px;
    margin: auto;
}

h1 {
    text-align: center;
    color: #16845b;
    margin-bottom: 10px;
}

.sous-titre {
    text-align: center;
    color: #66756e;
    margin-bottom: 25px;
}

.carte {
    background: white;
    border-radius: 18px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.07);
}

.carte h2 {
    margin-top: 0;
    color: #16845b;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 7px;
}

input,
select,
textarea {
    width: 100%;
    padding: 13px;
    border: 1px solid #cfdcd6;
    border-radius: 10px;
    font-size: 16px;
    margin-bottom: 18px;
}

textarea {
    min-height: 90px;
    resize: vertical;
}

.grille {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.calcul {
    background: #e9f8f0;
    border-radius: 14px;
    padding: 18px;
    margin: 5px 0 20px;
    text-align: center;
}

.calcul span {
    display: block;
    color: #567066;
    margin-bottom: 5px;
}

.reste {
    font-size: 28px;
    font-weight: bold;
    color: #16845b;
}

.bouton {
    display: block;
    width: 100%;
    border: none;
    border-radius: 12px;
    padding: 14px;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
}

.bouton-principal {
    background: #16845b;
    color: white;
}

.bouton-principal:hover {
    background: #116d4a;
}

.message {
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: bold;
}

.succes {
    background: #dff5e9;
    color: #12613f;
}

.erreur {
    background: #ffe4e4;
    color: #a12525;
}

.resume {
    border: 1px solid #dce9e3;
    border-radius: 15px;
    padding: 18px;
    margin-bottom: 15px;
}

.resume h3 {
    margin-top: 0;
    color: #16845b;
}

.ligne {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 7px 0;
}

.reste-positif {
    color: #d07b00;
    font-weight: bold;
}

.reste-zero {
    color: #16845b;
    font-weight: bold;
}

.bouton-accueil {
    margin-top: 15px;
    background: #e8f1ed;
    color: #16845b;
}

@media (max-width: 650px) {

    body {
        padding: 15px;
    }

    .carte {
        padding: 18px;
    }

    .grille {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .ligne {
        flex-direction: column;
        gap: 3px;
    }
}

</style>

</head>

<body>

<div class="conteneur">

<h1>🤝 Fournisseurs</h1>

<p class="sous-titre">
Gère les dettes, paiements et montants restants au même endroit.
</p>


<?php if ($message !== ""): ?>

<div class="message <?php echo $type_message; ?>">
    <?php echo htmlspecialchars($message); ?>
</div>

<?php endif; ?>


<!-- =====================================================
     AJOUTER UN FOURNISSEUR
     ===================================================== -->

<div class="carte">

<h2>➕ Nouveau fournisseur</h2>

<form method="POST">

<input type="hidden" name="action" value="nouveau_fournisseur">

<label>Nom du fournisseur *</label>

<input
    type="text"
    name="nom"
    placeholder="Ex : El_HADJ"
    required
>

<label>Téléphone</label>

<input
    type="text"
    name="telephone"
    placeholder="Numéro de téléphone"
>

<label>Adresse</label>

<input
    type="text"
    name="adresse"
    placeholder="Adresse du fournisseur"
>

<label>Notes</label>

<textarea
    name="notes"
    placeholder="Informations supplémentaires"
></textarea>

<button
    type="submit"
    class="bouton bouton-principal"
>
    Ajouter le fournisseur
</button>

</form>

</div>


<!-- =====================================================
     DETTE + PAIEMENT
     ===================================================== -->

<div class="carte">

<h2>📦 Nouvelle dette</h2>

<form method="POST">

<input
    type="hidden"
    name="action"
    value="nouvelle_operation"
>

<label>Fournisseur *</label>

<select name="fournisseur_id" required>

<option value="">
    -- Choisir un fournisseur --
</option>

<?php foreach ($fournisseurs as $fournisseur): ?>

<option value="<?php echo $fournisseur["id"]; ?>">

<?php echo htmlspecialchars($fournisseur["nom"]); ?>

</option>

<?php endforeach; ?>

</select>


<label>Ce qui a été acheté *</label>

<input
    type="text"
    name="description"
    placeholder="Ex : 5 sacs de feuilles de moringa"
    required
>


<div class="grille">

<div>

<label>Montant de la dette *</label>

<input
    type="number"
    id="montant_dette"
    name="montant_dette"
    min="0"
    step="0.01"
    placeholder="0"
    required
>

</div>

<div>

<label>Paiement effectué</label>

<input
    type="number"
    id="montant_paye"
    name="montant_paye"
    min="0"
    step="0.01"
    value="0"
>

</div>

</div>


<div class="calcul">

<span>Montant restant</span>

<div class="reste" id="montant_restant">
    0 FCFA
</div>

</div>


<div class="grille">

<div>

<label>Date *</label>

<input
    type="date"
    name="date_dette"
    value="<?php echo date("Y-m-d"); ?>"
    required
>

</div>

<div>

<label>Note</label>

<input
    type="text"
    name="note"
    placeholder="Ex : paiement en espèces"
>

</div>

</div>


<button
    type="submit"
    class="bouton bouton-principal"
>
    💾 Enregistrer
</button>

</form>

</div>


<!-- =====================================================
     RÉSUMÉS
     ===================================================== -->

<div class="carte">

<h2>📊 Situation des fournisseurs</h2>

<?php if ($resultat_resumes && $resultat_resumes->num_rows > 0): ?>

<?php while ($fournisseur = $resultat_resumes->fetch_assoc()): ?>

<?php

$total_dettes = floatval($fournisseur["total_dettes"]);
$total_paye = floatval($fournisseur["total_paye"]);

$reste = $total_dettes - $total_paye;

if ($reste < 0) {
    $reste = 0;
}

?>

<div class="resume">

<h3>
🤝 <?php echo htmlspecialchars($fournisseur["nom"]); ?>
</h3>

<div class="ligne">
<span>Total des dettes</span>
<strong>
<?php echo number_format($total_dettes, 0, ",", " "); ?> FCFA
</strong>
</div>

<div class="ligne">
<span>Total payé</span>
<strong>
<?php echo number_format($total_paye, 0, ",", " "); ?> FCFA
</strong>
</div>

<div class="ligne">

<span>Reste à payer</span>

<strong class="<?php echo $reste > 0 ? "reste-positif" : "reste-zero"; ?>">

<?php

if ($reste > 0) {

    echo number_format($reste, 0, ",", " ") . " FCFA";

} else {

    echo "Réglé";

}

?>

</strong>

</div>

</div>

<?php endwhile; ?>

<?php else: ?>

<p>Aucun fournisseur enregistré pour le moment.</p>

<?php endif; ?>

</div>


<a
    href="index.php"
    class="bouton bouton-accueil"
>
    🏠 Retour à l'accueil
</a>

</div>


<script>

/* =====================================================
   CALCUL AUTOMATIQUE DU RESTE
   ===================================================== */

const dette = document.getElementById("montant_dette");
const paye = document.getElementById("montant_paye");
const restant = document.getElementById("montant_restant");

function calculerReste() {

    let montantDette = parseFloat(dette.value) || 0;
    let montantPaye = parseFloat(paye.value) || 0;

    let reste = montantDette - montantPaye;

    if (reste < 0) {
        reste = 0;
    }

    restant.textContent =
        reste.toLocaleString("fr-FR") + " FCFA";
}

dette.addEventListener("input", calculerReste);
paye.addEventListener("input", calculerReste);

calculerReste();

</script>

</body>
</html>