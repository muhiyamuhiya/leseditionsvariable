<section style="text-align: center; padding: 80px 20px;">
    <h1 style="font-size: 2.5em; color: #1a1a2e; margin-bottom: 20px;">
        Bienvenue sur Les éditions Variable
    </h1>
    <p style="font-size: 1.2em; color: #555; max-width: 600px; margin: 0 auto;">
        Plateforme de lecture numérique pour auteurs africains francophones.
    </p>

    <div style="margin-top: 40px; padding: 20px; border-radius: 8px; max-width: 500px; margin-left: auto; margin-right: auto;
        background-color: <?= $dbOk ? '#d4edda' : '#f8d7da' ?>;
        color: <?= $dbOk ? '#155724' : '#721c24' ?>;
        border: 1px solid <?= $dbOk ? '#c3e6cb' : '#f5c6cb' ?>;">
        <?php if ($dbOk): ?>
            <p style="font-size: 1.1em;">&#x2705; Connexion base de données : <?= e($dbStatus) ?></p>
        <?php else: ?>
            <p style="font-size: 1.1em;">&#x274C; Erreur : <?= e($dbStatus) ?></p>
        <?php endif; ?>
    </div>
</section>
