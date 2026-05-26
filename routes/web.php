<?php
/**
 * Defines all application routes mapping URLs to controller actions.
 */

// Auth routes
$routes = [
    // Auth
    'GET'  => [
        '/login'               => ['AuthController', 'showLogin'],
        '/logout'              => ['AuthController', 'logout'],
        '/dashboard'           => ['DashboardController', 'index'],
        '/'                    => ['DashboardController', 'index'],
        // Books
        '/books'               => ['BookController', 'index'],
        '/books/create'        => ['BookController', 'create'],
        '/books/show'          => ['BookController', 'show'],
        '/books/edit'          => ['BookController', 'edit'],
        // Members
        '/members'             => ['MemberController', 'index'],
        '/members/create'      => ['MemberController', 'create'],
        '/members/edit'        => ['MemberController', 'edit'],
        // Transactions
        '/transactions'        => ['BorrowController', 'index'],
        '/transactions/borrow' => ['BorrowController', 'borrowForm'],
        '/transactions/return' => ['BorrowController', 'returnForm'],
        '/transactions/show'   => ['BorrowController', 'show'],
        // Admin: Users
        '/users'               => ['UserController', 'index'],
        '/users/create'        => ['UserController', 'create'],
        '/users/edit'          => ['UserController', 'edit'],
        // Admin: Categories
        '/categories'          => ['CategoryController', 'index'],
        '/categories/create'   => ['CategoryController', 'create'],
        '/categories/edit'     => ['CategoryController', 'edit'],
        // Admin: Settings & Reports
        '/settings'            => ['SettingsController', 'index'],
        '/reports'             => ['ReportController', 'index'],
        '/reports/fines'       => ['ReportController', 'fines'],
    ],
    'POST' => [
        '/login'               => ['AuthController', 'login'],
        '/books/store'         => ['BookController', 'store'],
        '/books/update'        => ['BookController', 'update'],
        '/books/delete'        => ['BookController', 'delete'],
        '/members/store'       => ['MemberController', 'store'],
        '/members/update'      => ['MemberController', 'update'],
        '/members/delete'      => ['MemberController', 'delete'],
        '/transactions/borrow' => ['BorrowController', 'borrow'],
        '/transactions/return' => ['BorrowController', 'processReturn'],
        // Admin: Users
        '/users/store'         => ['UserController', 'store'],
        '/users/update'        => ['UserController', 'update'],
        '/users/delete'        => ['UserController', 'delete'],
        // Admin: Categories
        '/categories/store'    => ['CategoryController', 'store'],
        '/categories/update'   => ['CategoryController', 'update'],
        '/categories/delete'   => ['CategoryController', 'delete'],
        // Admin: Settings
        '/settings/update'     => ['SettingsController', 'update'],
    ],
];

return $routes;
