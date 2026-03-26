<?php
session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Cloud Drive</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Connexion</h2>

        <?php
        if (isset($_GET["error"])) {
            echo "<p class='error'>Email ou mot de passe incorrect.</p>";
        }
        ?>

        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Adresse email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>

        <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
    </div>
</body>
</html>