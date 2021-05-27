<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'https://hainaservice.com/notif',
        'https://hainaservice.com/hook',
        'http://testgit.hainaservice.com/notif',
        'http://testgit.hainaservice.com/hook',
    ];
}
