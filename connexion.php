<?php
session_start();

if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] === true) {
    header("Location: index.php");
    exit;
}

$host = "localhost";
$user = "root";
$password = "";
$dbname = "gestion_stock_medicaments";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion à la base de données.");
}

$conn->set_charset("utf8mb4");

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom_utilisateur = trim($_POST["nom_utilisateur"] ?? "");
    $mot_de_passe = $_POST["mot_de_passe"] ?? "";

    if ($nom_utilisateur === "" || $mot_de_passe === "") {

        $erreur = "Veuillez remplir tous les champs.";

    } else {

        $sql = "SELECT id, nom_utilisateur, mot_de_passe
                FROM utilisateurs
                WHERE nom_utilisateur = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nom_utilisateur);
        $stmt->execute();

        $resultat = $stmt->get_result();

        if ($resultat->num_rows === 1) {

            $utilisateur = $resultat->fetch_assoc();

            if (password_verify(
                $mot_de_passe,
                $utilisateur["mot_de_passe"]
            )) {

                session_regenerate_id(true);

                $_SESSION["connecte"] = true;
                $_SESSION["utilisateur_id"] = $utilisateur["id"];
                $_SESSION["nom_utilisateur"] = $utilisateur["nom_utilisateur"];

                header("Location: index.php");
                exit;

            } else {
                $erreur = "Nom d'utilisateur ou mot de passe incorrect.";
            }

        } else {
            $erreur = "Nom d'utilisateur ou mot de passe incorrect.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Connexion - Gestion Stock</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f5fdf9, #e8f6ef);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .connexion-box {
            width: 100%;
            max-width: 390px;
            background: white;
            border: 1px solid #dceee4;
            border-radius: 24px;
            padding: 32px 24px;
            box-shadow: 0 16px 45px rgba(22, 132, 91, 0.12);
        }

        .logo {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            background: #16845b;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 18px;
        }

        h1 {
            text-align: center;
            color: #17352a;
            font-size: 25px;
            margin: 0;
        }

        .sous-titre {
            text-align: center;
            color: #718078;
            margin: 8px 0 26px;
            font-size: 14px;
        }

        label {
            display: block;
            color: #28453a;
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 7px;
        }

        input {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid #d4e5dc;
            border-radius: 12px;
            font-size: 16px;
            outline: none;
            background: #fbfefc;
        }

        input:focus {
            border-color: #16845b;
            box-shadow: 0 0 0 3px rgba(22, 132, 91, 0.10);
        }

        button {
            width: 100%;
            margin-top: 24px;
            padding: 15px;
            border: none;
            border-radius: 12px;
            background: #16845b;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #126f4c;
        }

        .erreur {
            background: #fff1f1;
            color: #b42318;
            border: 1px solid #f3c7c7;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .inscription {
            text-align: center;
            margin-top: 22px;
            color: #53665c;
            font-size: 14px;
            line-height: 1.6;
        }

        .inscription a {
            color: #16845b;
            font-weight: bold;
            text-decoration: none;
        }

        .inscription a:hover {
            text-decoration: underline;
        }

        .securite {
            text-align: center;
            color: #7a8a83;
            font-size: 12px;
            margin-top: 18px;
        }
    </style>
</head>

<body>

<div class="connexion-box">

    <div class="logo">🔐</div>

    <h1>Gestion Stock</h1>

    <p class="sous-titre">
        Connectez-vous pour accéder à votre espace
    </p>

    <?php if ($erreur !== ""): ?>
        <div class="erreur">
            <?= htmlspecialchars($erreur, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="connexion.php">

        <label for="nom_utilisateur">Nom d'utilisateur</label>

        <input
            type="text"
            id="nom_utilisateur"
            name="nom_utilisateur"
            placeholder="Votre nom d'utilisateur"
            autocomplete="username"
            required
        >

        <label for="mot_de_passe">Mot de passe</label>

        <input
            type="password"
            id="mot_de_passe"
            name="mot_de_passe"
            placeholder="Votre mot de passe"
            autocomplete="current-password"
            required
        >

        <button type="submit">Se connecter</button>

    </form>

    <div class="inscription">
        Vous n'avez pas encore de compte ?<br>
        <a href="inscription.php">Créer un compte</a>
    </div>

    <div class="securite">
        🔒 Accès sécurisé
    </div>

</div>

</body>
</html>