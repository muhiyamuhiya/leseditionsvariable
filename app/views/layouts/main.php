<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titre ?? 'Accueil') ?> — Les éditions Variable</title>
    <meta name="description" content="<?= e($description ?? 'Plateforme de lecture numérique pour auteurs africains francophones. Ebooks, abonnements et services aux auteurs.') ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($titre ?? 'Les éditions Variable') ?>">
    <meta property="og:description" content="<?= e($description ?? 'Plateforme de lecture numérique pour auteurs africains francophones.') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e(url()) ?>">
    <meta property="og:locale" content="fr_FR">

    <!-- Favicon -->
    <link rel="icon" href="<?= asset('images/logo.jpg') ?>" type="image/jpeg">

    <!-- Google Fonts : Inter (texte) + Lora (titres) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lora:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Lora', 'serif'],
                    },
                    colors: {
                        brand: {
                            dark: '#0F172A',
                            indigo: '#4F46E5',
                            amber: '#F59E0B',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Styles personnalisés -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="min-h-screen flex flex-col bg-white text-gray-800 font-sans">

    <?php require BASE_PATH . '/app/views/partials/header.php'; ?>

    <main class="flex-grow">
        <?= $content ?>
    </main>

    <?php require BASE_PATH . '/app/views/partials/footer.php'; ?>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
