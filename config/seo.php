<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Registered SEO pages (route names)
    |--------------------------------------------------------------------------
    | Run: php artisan db:seed --class=SeoPageSeeder (or migrate:fresh --seed)
    | to sync rows. Admin can edit meta per page_key.
    */
    'pages' => [
        '_default' => [
            'label' => 'Default (unnamed routes)',
            'path' => '*',
        ],
        'home' => [
            'label' => 'Home',
            'path' => '/',
        ],
        'services' => [
            'label' => 'Services',
            'path' => '/services',
        ],
        'about' => [
            'label' => 'About',
            'path' => '/about',
        ],
        'contact' => [
            'label' => 'Contact',
            'path' => '/contact',
        ],
        'templates' => [
            'label' => 'Public templates gallery',
            'path' => '/templates',
        ],
        'docs.index' => [
            'label' => 'Documentation index',
            'path' => '/docs',
        ],
        'docs.show' => [
            'label' => 'Documentation article',
            'path' => '/docs/{slug}',
        ],
        'flipbooks.public' => [
            'label' => 'Public flipbook view',
            'path' => '/flipbook/{slug}',
        ],
        'login' => [
            'label' => 'Login',
            'path' => '/login',
        ],
        'register' => [
            'label' => 'Register',
            'path' => '/register',
        ],
        'password.request' => [
            'label' => 'Forgot password',
            'path' => '/forgot-password',
        ],
        'designer-application.index' => [
            'label' => 'Become a designer',
            'path' => '/become-a-designer',
        ],
        'dashboard' => [
            'label' => 'User dashboard',
            'path' => '/dashboard',
        ],
        'design.index' => [
            'label' => 'Design hub',
            'path' => '/design',
        ],
        'design.templates.explore' => [
            'label' => 'Explore templates',
            'path' => '/design/templates/explore',
        ],
        'design.templates.index' => [
            'label' => 'My templates',
            'path' => '/design/templates',
        ],
        'enterprise' => [
            'label' => 'Enterprise',
            'path' => '/enterprise',
        ],
        'enterprise.mailbox' => [
            'label' => 'Enterprise mailbox',
            'path' => '/enterprise/mailbox',
        ],
        'orders.index' => [
            'label' => 'My orders',
            'path' => '/orders',
        ],
        'credits.index' => [
            'label' => 'Credits',
            'path' => '/credits',
        ],
    ],
];
