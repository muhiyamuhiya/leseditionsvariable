<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titre ?? 'Accueil') ?> — Les éditions Variable</title>
    <meta name="description" content="<?= e($description ?? 'Plateforme de lecture numérique pour auteurs africains francophones') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>

    <?php require BASE_PATH . '/app/views/partials/header.php'; ?>

    <main>
        <?= $content ?>
    </main>

    <?php require BASE_PATH . '/app/views/partials/footer.php'; ?>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
