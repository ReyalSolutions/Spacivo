<?php
declare(strict_types=1);

final class ProbeController extends BaseController
{
    public function __construct() {}
    public function index(): void { echo 'probe-ok'; }
    public function bookings_history(): void { echo 'history-ok'; }
    protected function secret(): void { echo 'must-not-run'; }
    public function needsArgument(string $value): void { echo 'must-not-run'; }
}
