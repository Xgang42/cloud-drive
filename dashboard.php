<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];

// Recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? AND original_name LIKE ? ORDER BY uploaded_at DESC");
    $stmt->execute([$user_id, "%$search%"]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$user_id]);
}
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul du stockage total
$totalStmt = $pdo->prepare("SELECT SUM(file_size) as total_size FROM files WHERE user_id = ?");
$totalStmt->execute([$user_id]);
$totalData = $totalStmt->fetch(PDO::FETCH_ASSOC);
$totalSize = $totalData['total_size'] ?? 0;

// Fonction pour afficher taille lisible
function formatSize($size) {
    if ($size >= 1073741824) return round($size / 1073741824, 2) . " GB";
    if ($size >= 1048576) return round($size / 1048576, 2) . " MB";
    if ($size >= 1024) return round($size / 1024, 2) . " KB";
    return $size . " B";
}

// Fonction pour icône fichier
function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $imageTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $pdfTypes = ['pdf'];
    $docTypes = ['doc', 'docx'];
    $zipTypes = ['zip'];
    $txtTypes = ['txt'];

    if (in_array($ext, $imageTypes)) return "🖼️";
    if (in_array($ext, $pdfTypes)) return "📕";
    if (in_array($ext, $docTypes)) return "📄";
    if (in_array($ext, $zipTypes)) return "🗜️";
    if (in_array($ext, $txtTypes)) return "📝";

    return "📁";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Drive</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">

    <div class="dashboard-container">
        <div class="top-bar">
            <h1>☁️ Cloud Drive</h1>
            <div class="user-info">
                <span>Bienvenue, <strong><?php echo htmlspecialchars($username); ?></strong></span>
                <a href="logout.php" class="logout-btn">Déconnexion</a>
            </div>
        </div>

        <div class="stats-section">
            <div class="stat-card">
                <h3>📦 Stockage utilisé</h3>
                <p><?php echo formatSize($totalSize); ?></p>
            </div>
            <div class="stat-card">
                <h3>📁 Nombre de fichiers</h3>
                <p><?php echo count($files); ?></p>
            </div>
        </div>

        <div class="upload-section">
            <h2>Uploader un fichier</h2>

            <?php
            if (isset($_GET["success"])) {
                echo "<p class='success'>Fichier uploadé avec succès.</p>";
            }

            if (isset($_GET["error"])) {
                $error = $_GET["error"];

                if ($error == "size") echo "<p class='error'>Le fichier dépasse 5 MB.</p>";
                elseif ($error == "type") echo "<p class='error'>Type de fichier non autorisé.</p>";
                else echo "<p class='error'>Erreur lors de l'upload.</p>";
            }
            ?>

            <form action="upload.php" method="POST" enctype="multipart/form-data" class="upload-form">
                <input type="file" name="file" required>
                <button type="submit">Uploader</button>
            </form>
            <small>Formats autorisés : jpg, png, gif, pdf, doc, docx, txt, zip (max 5MB)</small>
        </div>

        <div class="files-section">
            <div class="files-header">
                <h2>Mes fichiers</h2>
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Rechercher un fichier..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Rechercher</button>
                </form>
            </div>

            <?php if (count($files) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Nom du fichier</th>
                            <th>Taille</th>
                            <th>Date</th>
                            <th>Aperçu</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                            <tr>
                                <td><?php echo getFileIcon($file["original_name"]); ?></td>
                                <td><?php echo htmlspecialchars($file["original_name"]); ?></td>
                                <td><?php echo formatSize($file["file_size"]); ?></td>
                                <td><?php echo $file["uploaded_at"]; ?></td>
                                <td>
                                    <?php
                                    $ext = strtolower(pathinfo($file["original_name"], PATHINFO_EXTENSION));
                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        echo "<img src='uploads/" . htmlspecialchars($file["stored_name"]) . "' class='preview-img'>";
                                    } else {
                                        echo "—";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="download.php?id=<?php echo $file['id']; ?>" class="action-btn download">Télécharger</a>
                                    <a href="delete.php?id=<?php echo $file['id']; ?>" class="action-btn delete" onclick="return confirm('Supprimer ce fichier ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aucun fichier trouvé.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>