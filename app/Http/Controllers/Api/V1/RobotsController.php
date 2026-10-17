<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * ساخت robots.txt
     * GET /robots.txt
     */
    public function index(): Response
    {
        $baseUrl = rtrim(config('app.url'), '/');

        $content = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /api/',
            'Disallow: /livewire/',
            'Disallow: /storage/private/',
            '',
            'Allow: /',
            '',
            'User-agent: Googlebot',
            'Allow: /',
            '',
            'User-agent: Bingbot',
            'Allow: /',
            '',
            'User-agent: Twitterbot',
            'Allow: /',
            '',
            'User-agent: facebookexternalhit',
            'Allow: /',
            '',
            "Sitemap: {$baseUrl}/sitemap.xml",
        ];

        return response(implode("\n", $content), 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}