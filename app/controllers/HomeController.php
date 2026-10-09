<?php
declare(strict_types=1);

final class HomeController extends BaseController
{
    public function index(): void
    {
        header('Location: /tenant/?url=boarding/index');
        exit;
    }
}

