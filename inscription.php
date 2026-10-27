<?php
session_start();

$erreur = "";
$succes = "";

$connexion = new mysqli("localhost", "root", "", "gestion_stock_medicaments");

if ($connexion->connect_error) {
    die("Erreur de connexion à la base de données.");
}

$connexion->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom_utilisateur = trim($_POST["nom_utilisateur"] ?? "");
    $mot_de_passe = $_POST["mot_de_passe"] ?? "";
    $confirmation = $_POST["confirmation"] ?? "";

    if ($nom_utilisateur === "" || $mot_de_passe === "" || $confirmation === "") {
        $erreur = "Remplis tous les champs.";

    } elseif (strlen($nom_utilisateur) < 3) {
        $erreur = "Le nom d'utilisateur doit contenir au moins 3 caractères.";

    } elseif (strlen($mot_de_passe) < 8) {
        $erreur = "Le mot de passe doit contenir au moins 8 caractères.";

    } elseif ($mot_de_passe !== $confirmation) {
        $erreur = "Les deux mots de passe ne correspondent pas.";

    } else {
        $verification = $connexion->prepare(
            "SELECT id FROM utilisateurs WHERE nom_utilisateur = ?"
        );

        $verification->bind_param("s", $nom_utilisateur);
        $verification->execute();
        $verification->store_result();

        if ($verification->num_rows > 0) {
            $erreur = "Ce nom d'utilisateur est déjà utilisé.";
        } else {
            $mot_de_passe_hache = password_hash(
                $mot_de_passe,
                PASSWORD_DEFAULT
            );

            $insertion = $connexion->prepare(
                "INSERT INTO utilisateurs (nom_utilisateur, mot_de_passe)
                 VALUES (?, ?)"
            );

            $insertion->bind_param(
                "ss",
                $nom_utilisateur,
                $mot_de_passe_hache
            );

            if ($insertion->execute()) {
                $succes = "Ton compte a été créé ! Tu peux maintenant te connecter.";
            } else {
                $erreur = "Impossible de créer le compte. Réessaie.";
            }

            $insertion->close();
        }

        $verification->close();
    }
}

$connexion->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte - Gestion Stock</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f0f8f2;
        }

        .conteneur {
            width: 100%;
            max-width: 430px;
            padding: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            color: #16845b;
            text-align: center;
        }

        .description {
            text-align: center;
            color: #555;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin: 16px 0 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ccc;
            border-radius: 9px;
            font-size: 16px;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 14px;
            border: none;
            border-radius: 9px;
            background: #16845b;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #106b49;
        }

        .message {
            margin-top: 15px;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
        }

        .erreur {
            color: #a32020;
            background: #ffe8e8;
        }

        .succes {
            color: #12633f;
            background: #e3f7eb;
        }

        .lien {
            margin-top: 22px;
            text-align: center;
        }

        a {
            color: #16845b;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="conteneur">
        <h1>🌿 Gestion Stock</h1>

        <p class="description">Créer ton compte personnel</p>

        <form method="POST" action="">
            <label for="nom_utilisateur">Nom d'utilisateur</label>
            <input
                type="text"
                id="nom_utilisateur"
                name="nom_utilisateur"
                minlength="3"
                maxlength="100"
                autocomplete="username"
                required
            >

            <label for="mot_de_passe">Mot de passe</label>
            <input
                type="password"
                id="mot_de_passe"
                name="mot_de_passe"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <label for="confirmation">Confirmer le mot de passe</label>
            <input
                type="password"
                id="confirmation"
                name="confirmation"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <button type="submit">Créer mon compte</button>
        </form>

        <?php if ($erreur !== ""): ?>
            <div class="message erreur">
                <?= htmlspecialchars($erreur, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <?php if ($succes !== ""): ?>
            <div class="message succes">
                <?= htmlspecialchars($succes, ENT_QUOTES, "UTF-8") ?>
                <br><br>
                <a href="connexion.php">Se connecter</a>
            </div>
        <?php endif; ?>

        <div class="lien">
            Tu as déjà un compte ?
            <a href="connexion.php">Se connecter</a>
        </div>
    </div>
</body>
</html>