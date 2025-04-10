<?php
require_once __DIR__ . '/auth/config.php';

$google_auth_url = $google_client->createAuthUrl();
?>

<a href="<?= $google_auth_url ?>" class="btn btn-danger">
    <i class="fab fa-google"></i> Sign in with Google
</a>
