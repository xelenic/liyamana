<?php

namespace Database\Seeders;

use App\Models\Documentation;
use App\Models\DocumentationCategory;
use Illuminate\Database\Seeder;

class DocumentationSeeder extends Seeder
{
    /**
     * Seed documentation categories and pages from API-USER-PANEL.md structure.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'API Reference',
                'slug' => 'api-reference',
                'description' => 'General API reference: base URL, headers, response format, token auth.',
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Authentication',
                'slug' => 'authentication',
                'description' => 'Login, register, logout, and current user endpoints.',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'User Panel',
                'slug' => 'user-panel',
                'description' => 'Profile, address book, orders, credits, and notifications.',
                'sort_order' => 20,
                'is_active' => true,
            ],
        ];

        $categoryIds = [];
        foreach ($categories as $cat) {
            $model = DocumentationCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
            $categoryIds[$cat['slug']] = $model->id;
        }

        $docs = [
            [
                'title' => 'User Panel API',
                'slug' => 'user-panel-api',
                'category_slugs' => ['api-reference'],
                'sort_order' => 0,
                'content' => <<<'MD'
<h2>User Panel API</h2>
<p>JSON APIs that mirror the authenticated user panel. Use token auth (Sanctum) or session cookie (same-origin).</p>
<p><strong>Base URL:</strong> <code>/api</code></p>
<p><strong>Headers:</strong> <code>Accept: application/json</code> for JSON responses.</p>
<p><strong>Authentication (protected routes):</strong> Send <code>Authorization: Bearer {token}</code> (from login/register) or session cookie when using the app from the same origin.</p>
MD,
            ],
            [
                'title' => 'Auth (public)',
                'slug' => 'auth-public',
                'category_slugs' => ['authentication'],
                'sort_order' => 10,
                'content' => <<<'MD'
<h2>Auth (public)</h2>
<table class="table table-bordered">
<thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
<tbody>
<tr><td>POST</td><td><code>/api/login</code></td><td>Login. Body: <code>email</code>, <code>password</code>; optional: <code>device_name</code>. Returns <code>token</code> and <code>user</code>.</td></tr>
<tr><td>POST</td><td><code>/api/register</code></td><td>Register. Body: <code>name</code>, <code>email</code>, <code>password</code>, <code>password_confirmation</code>; optional: <code>device_name</code>. Returns <code>token</code> and <code>user</code>. Respects admin "allow registration" setting.</td></tr>
</tbody>
</table>
<p><strong>Auth response shape:</strong> <code>{ "success": true, "message": "...", "data": { "user": { "id", "name", "email", "balance" }, "token": "...", "token_type": "Bearer" } }</code></p>
MD,
            ],
            [
                'title' => 'Auth (protected)',
                'slug' => 'auth-protected',
                'category_slugs' => ['authentication'],
                'sort_order' => 20,
                'content' => <<<'MD'
<h2>Auth (protected)</h2>
<table class="table table-bordered">
<thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
<tbody>
<tr><td>POST</td><td><code>/api/logout</code></td><td>Revoke current token (or end session).</td></tr>
<tr><td>GET</td><td><code>/api/me</code></td><td>Current user (id, name, email, balance, avatar).</td></tr>
</tbody>
</table>
<p>Use the returned <code>token</code> in subsequent requests: <code>Authorization: Bearer {token}</code>.</p>
MD,
            ],
            [
                'title' => 'Profile',
                'slug' => 'api-profile',
                'category_slugs' => ['user-panel'],
                'sort_order' => 30,
                'content' => <<<'MD'
<h2>Profile</h2>
<table class="table table-bordered">
<thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
<tbody>
<tr><td>GET</td><td><code>/api/user/profile</code></td><td>Get current user profile (id, name, email, balance, avatar, etc.)</td></tr>
<tr><td>PUT</td><td><code>/api/user/profile</code></td><td>Update name and email</td></tr>
<tr><td>PUT</td><td><code>/api/user/password</code></td><td>Update password (body: <code>current_password</code>, <code>password</code>, <code>password_confirmation</code>)</td></tr>
</tbody>
</table>
MD,
            ],
            [
                'title' => 'Address Book',
                'slug' => 'api-address-book',
                'category_slugs' => ['user-panel'],
                'sort_order' => 40,
                'content' => <<<'MD'
<h2>Address Book</h2>
<table class="table table-bordered">
<thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
<tbody>
<tr><td>GET</td><td><code>/api/user/address-book</code></td><td>List all address book entries</td></tr>
<tr><td>POST</td><td><code>/api/user/address-book</code></td><td>Create entry (label, contact_name, email, phone, address_line1, address_line2, city, state, postal_code, country)</td></tr>
<tr><td>PUT</td><td><code>/api/user/address-book/{id}</code></td><td>Update entry</td></tr>
<tr><td>DELETE</td><td><code>/api/user/address-book/{id}</code></td><td>Delete entry</td></tr>
</tbody>
</table>
MD,
            ],
            [
                'title' => 'Orders',
                'slug' => 'api-orders',
                'category_slugs' => ['user-panel'],
                'sort_order' => 50,
                'content' => <<<'MD'
<h2>Orders</h2>
<table class="table table-bordered">
<thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
<tbody>
<tr><td>GET</td><td><code>/api/orders</code></td><td>List orders (paginated). Query: <code>per_page</code> (default 15, max 50)</td></tr>
<tr><td>GET</td><td><code>/api/orders/{id}</code></td><td>Get single order with checkout_data and invoice_url</td></tr>
</tbody>
</table>
MD,
            ],
            [
                'title' => 'Credits',
                'slug' => 'api-credits',
                'category_slugs' => ['user-panel'],
                'sort_order' => 60,
                'content' => <<<'MD'
<h2>Credits</h2>
<table class="table table-bordered">
<thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
<tbody>
<tr><td>GET</td><td><code>/api/credits</code></td><td>Balance, top-up config (min/max amount, payment methods), currency symbol</td></tr>
<tr><td>GET</td><td><code>/api/credits/transactions</code></td><td>List credit transactions (paginated). Query: <code>per_page</code></td></tr>
</tbody>
</table>
MD,
            ],
            [
                'title' => 'Notifications',
                'slug' => 'api-notifications',
                'category_slugs' => ['user-panel'],
                'sort_order' => 70,
                'content' => <<<'MD'
<h2>Notifications</h2>
<table class="table table-bordered">
<thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
<tbody>
<tr><td>GET</td><td><code>/api/user/notifications</code></td><td>List notifications. Query: <code>limit</code> (default 20, max 50)</td></tr>
<tr><td>POST</td><td><code>/api/user/notifications/read-all</code></td><td>Mark all as read</td></tr>
<tr><td>POST</td><td><code>/api/user/notifications/{id}/read</code></td><td>Mark one as read</td></tr>
</tbody>
</table>
MD,
            ],
            [
                'title' => 'Response format',
                'slug' => 'api-response-format',
                'category_slugs' => ['api-reference'],
                'sort_order' => 80,
                'content' => <<<'MD'
<h2>Response format</h2>
<ul>
<li><strong>Success:</strong> <code>{ "success": true, "data": ... }</code> or <code>{ "success": true, "message": "..." }</code></li>
<li><strong>Error (4xx/5xx):</strong> <code>{ "success": false, "message": "..." }</code> or validation <code>{ "message": "...", "errors": { "field": ["..."] } }</code></li>
</ul>
MD,
            ],
            [
                'title' => 'Token auth',
                'slug' => 'api-token-auth',
                'category_slugs' => ['api-reference'],
                'sort_order' => 90,
                'content' => <<<'MD'
<h2>Token auth</h2>
<ol>
<li><strong>Get a token:</strong> <code>POST /api/login</code> with <code>email</code> and <code>password</code> (or <code>POST /api/register</code>). Response includes <code>data.token</code>.</li>
<li><strong>Call protected endpoints:</strong> Send header <code>Authorization: Bearer {token}</code> on every request.</li>
<li><strong>Logout:</strong> <code>POST /api/logout</code> with the same Bearer token to revoke it.</li>
</ol>
MD,
            ],
        ];

        foreach ($docs as $doc) {
            $categorySlugs = $doc['category_slugs'];
            unset($doc['category_slugs']);

            $model = Documentation::updateOrCreate(
                ['slug' => $doc['slug']],
                [
                    'title' => $doc['title'],
                    'content' => $doc['content'],
                    'sort_order' => $doc['sort_order'],
                    'is_published' => true,
                ]
            );

            $ids = array_map(fn ($slug) => $categoryIds[$slug], $categorySlugs);
            $model->categories()->sync($ids);
        }
    }
}
