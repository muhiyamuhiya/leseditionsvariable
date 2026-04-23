<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titre ?? 'Accueil') ?> — Les éditions Variable</title>
    <meta name="description" content="<?= e($description ?? 'Maison d\'édition numérique dédiée aux voix d\'Afrique francophone et de la diaspora.') ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($titre ?? 'Les éditions Variable') ?>">
    <meta property="og:description" content="<?= e($description ?? 'Maison d\'édition numérique dédiée aux voix d\'Afrique francophone et de la diaspora.') ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="/assets/images/logo.jpg">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600&family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Playfair Display', 'serif'],
                        accent: ['Cormorant Garamond', 'serif'],
                    },
                    colors: {
                        ink: '#0A0A0A',
                        paper: '#FAFAF7',
                        gold: '#B8935A',
                        'gold-light': '#D4A574',
                        muted: '#4A4A4A',
                        subtle: '#E5E5E0',
                        soft: '#F2F0EA',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="min-h-screen flex flex-col bg-paper text-ink font-sans antialiased">

    <?php require BASE_PATH . '/app/views/partials/header.php'; ?>

    <main class="flex-grow">
        <?= $content ?>
    </main>

    <?php require BASE_PATH . '/app/views/partials/footer.php'; ?>

</body>
</html>
