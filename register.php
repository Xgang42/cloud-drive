<?php
session_start();
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "Tous les champs sont obligatoires.";
    } elseif ($password !== $confirm_password) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $message = "Cet email existe déjà.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashedPassword])) {
                header("Location: index.php");
                exit();
            } else {
                $message = "Erreur lors de l'inscription.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Cloud Drive</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Créer un compte</h2>
        <?php if (!empty($message)) echo "<p class='error'>$message</p>"; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Nom d'utilisateur">
            <input type="email" name="email" placeholder="Adresse email">
            <input type="password" name="password" placeholder="Mot de passe">
            <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe">
            <button type="submit">S'inscrire</button>
        </form>

        <p>Déjà un compte ? <a href="index.php">Se connecter</a></p>
    </div>
</body>
</html>