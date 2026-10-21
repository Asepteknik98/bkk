<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/middleware/auth.php';
require_post_csrf();
require_auth();
destroy_app_session();
redirect('auth/login.php', 303);
