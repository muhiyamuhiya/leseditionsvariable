<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titre ?? 'Accueil') ?> — Les éditions Variable</title>
    <meta name="description" content="<?= e($description ?? 'Plateforme de lecture numérique pour auteurs africains francophones') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-gray-800">

    <?php require BASE_PATH . '/app/views/partials/header.php'; ?>

    <main class="flex-grow">
        <?= $content ?>
    </main>

    <?php require BASE_PATH . '/app/views/partials/footer.php'; ?>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
