<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titre ?? 'Accueil') ?> — Les éditions Variable</title>
    <meta name="description" content="<?= e($description ?? 'Les éditions Variable — La plateforme de lecture pour l\'Afrique francophone.') ?>">

    <meta property="og:title" content="<?= e($titre ?? 'Les éditions Variable') ?>">
    <meta property="og:description" content="<?= e($description ?? 'La plateforme de lecture pour l\'Afrique francophone.') ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">

    <link rel="icon" type="image/png" href="/assets/images/logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        bg: '#0B0B0F',
                        surface: '#141419',
                        'surface-2': '#1C1C24',
                        border: '#2A2A35',
                        'text-main': '#FFFFFF',
                        'text-muted': '#A0A0B0',
                        'text-dim': '#6B6B7D',
                        accent: '#F59E0B',
                        'accent-hover': '#FBBF24',
                    }
                }
            }
        }
    </script>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="min-h-screen flex flex-col bg-bg text-white font-sans antialiased">

    <?php require BASE_PATH . '/app/views/partials/header.php'; ?>

    <main class="flex-grow">
        <?= $content ?>
    </main>

    <?php require BASE_PATH . '/app/views/partials/footer.php'; ?>

</body>
</html>
