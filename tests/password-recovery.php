<?php
declare(strict_types=1);

$delivery = new class implements App\Modules\Identity\Services\PasswordResetDelivery {
    public array $tokens = [];
    public function queue(string $email, string $url): void
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->tokens[] = $query['token'];
    }
};
$now = time();
$clock = static function () use (&$now): int { return $now; };
$repository = new App\Modules\Identity\Repositories\PasswordResetRepository($server);
$recovery = new App\Modules\Identity\Services\PasswordResetService($repository, $delivery, 'http://localhost/tenant', $clock);
$recovery->request('missing@example.test');
$check($delivery->tokens === [], 'Unknown account creates no recovery token');
$recovery->request('fixture_owner@example.test');
$firstToken = $delivery->tokens[0];
$check($server->query("SELECT 1 FROM password_resets WHERE token_hash = '" . hash('sha256', $firstToken) . "'")->num_rows === 1, 'Recovery token stored hashed');
$recovery->request('fixture_owner@example.test');
$secondToken = $delivery->tokens[1];
$check(!$recovery->reset($firstToken, 'NewPassword123'), 'Issuing another link invalidates previous link');
$check(!$recovery->reset($secondToken, str_repeat('a', 73)), 'Recovery rejects passwords over bcrypt byte limit');
$check($recovery->reset($secondToken, 'NewPassword123'), 'Current recovery token consumed');
$check(!$recovery->reset($secondToken, 'DifferentPassword123'), 'Recovery token cannot be replayed');
$check($repository->credentialVersion($owner) === 1, 'Successful reset increments session version');
$recovery->request('fixture_owner@example.test');
$thirdToken = $delivery->tokens[2];
$recovery->request('fixture_owner@example.test');
$check(count($delivery->tokens) === 3, 'Recovery requests limited to three per hour');
$now += 1800;
$check(!$recovery->reset($thirdToken, 'NewPassword123'), 'Recovery token expires at the boundary');
$now += 1801;
$recovery->request('fixture_owner@example.test');
$fourthToken = $delivery->tokens[3];
$server->query('UPDATE users SET status = 0 WHERE id = ' . $owner);
$check(!$recovery->reset($fourthToken, 'NewPassword123'), 'Deactivated account cannot consume recovery token');
$server->query('UPDATE users SET status = 1 WHERE id = ' . $owner);
$check(!$recovery->reset('invalid', 'NewPassword123'), 'Malformed recovery token rejected');
