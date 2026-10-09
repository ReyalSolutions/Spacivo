<?php require __DIR__ . '/../layouts/header.php'; ?>
<main class="container py-5" style="max-width: 560px">
    <h1><?= $mode === 'reset' ? 'Choose a new password' : 'Recover your account' ?></h1>
    <?php if ($error): ?><script>document.addEventListener('DOMContentLoaded',function(){ToastStack.error(<?= json_encode($error, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);});</script><?php endif; ?>
    <?php if ($message): ?>
        <script>document.addEventListener('DOMContentLoaded',function(){ToastStack.success(<?= json_encode($message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);});</script>
    <?php else: ?>
        <form method="POST" action="/tenant/?url=auth/<?= $mode === 'reset' ? 'reset_password' : 'forgot_password' ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($mode === 'reset'): ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                <label for="recovery-password" class="form-label">New password</label>
                <input id="recovery-password" class="form-control mb-3" type="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                <label for="recovery-confirm" class="form-label">Confirm password</label>
                <input id="recovery-confirm" class="form-control mb-3" type="password" name="confirm_password" minlength="8" maxlength="72" autocomplete="new-password" required>
            <?php else: ?>
                <label for="recovery-email" class="form-label">Email address</label>
                <input id="recovery-email" class="form-control mb-3" type="email" name="email" autocomplete="email" required>
            <?php endif; ?>
            <button class="btn btn-primary" type="submit"><?= $mode === 'reset' ? 'Change password' : 'Request recovery' ?></button>
        </form>
    <?php endif; ?>
    <p class="mt-3"><a href="/tenant/?url=auth/login">Back to sign in</a></p>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
