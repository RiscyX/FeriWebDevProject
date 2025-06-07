<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Bootstrap Test</title>
    <link rel="stylesheet" href="./bootstrap/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="bg-light">

<?php require_once "../src/View/partials/nav.php"; ?>

<div class="container mt-5">
    <h1 class="text-center">Hello, Bootstrap!</h1>
    <button class="btn btn-primary">Klikk ide</button>
    <?php
    require_once __DIR__ . '/../vendor/autoload.php';

    use WebDevProject\config\Config;

    try {
        $connection = new PDO(
            "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=utf8mb4",
            Config::DB_USER,
            Config::DB_PASSWORD
        );
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $connection->query('SELECT NOW() AS currentTime');
        $row = $stmt->fetch();

        echo "<h1>Database connection successful!</h1>";
        echo "<p>Current DB time: " . htmlspecialchars($row['currentTime']) . "</p>";

    } catch (PDOException $e) {
        echo "<h1>Database connection error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        exit;
    }

    ?>
</div>

<!-- Bootstrap JS és Popper (Bootstrap 5 esetén a bundle tartalmazza) -->
<script src="./bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
