<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * ساخت sitemap.xml خودکار
     * GET /sitemap.xml
     */
    public function index(): Response
    {
        $posts = Post::published()
            ->indexable()
            ->select(['slug', 'type', 'updated_at'])
            ->orderByDesc('updated_at')
            ->get();

        $categories = Category::where('is_active', true)
            ->where('is_indexable', true)
            ->select(['slug', 'updated_at'])
            ->get();

        $tags = Tag::select(['slug', 'updated_at'])
            ->get();

        $content = $this->buildXml($posts, $categories, $tags);

        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    /**
     * ساخت XML
     */
    private function buildXml($posts, $categories, $tags): string
    {
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', config('app.url'))), '/');

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . PHP_EOL;
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;

        // ==================== صفحه اصلی ====================
        $xml .= $this->urlTag($frontendUrl, now(), 'daily', '1.0');

        // ==================== مقالات ====================
        foreach ($posts as $post) {
            $path = match ($post->type) {
                'page'    => $post->slug,
                'product' => "products/{$post->slug}",
                default   => "blog/{$post->slug}",
            };

            $xml .= $this->urlTag(
                "{$frontendUrl}/{$path}",
                $post->updated_at,
                'weekly',
                '0.8'
            );
        }

        // ==================== دسته‌بندی‌ها ====================
        foreach ($categories as $category) {
            $xml .= $this->urlTag(
                "{$frontendUrl}/category/{$category->slug}",
                $category->updated_at,
                'weekly',
                '0.6'
            );
        }

        // ==================== برچسب‌ها ====================
        foreach ($tags as $tag) {
            $xml .= $this->urlTag(
                "{$frontendUrl}/tag/{$tag->slug}",
                $tag->updated_at,
                'monthly',
                '0.4'
            );
        }

        // ==================== صفحات ثابت ====================
        $staticPages = ['about', 'contact', 'privacy', 'terms'];
        foreach ($staticPages as $page) {
            $xml .= $this->urlTag(
                "{$frontendUrl}/{$page}",
                now(),
                'monthly',
                '0.5'
            );
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * ساخت یک تگ <url>
     */
    private function urlTag(string $loc, $lastmod, string $changefreq, string $priority): string
    {
        $lastmod = is_object($lastmod) ? $lastmod->toAtomString() : $lastmod;

        return '  <url>' . PHP_EOL
             . '    <loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>' . PHP_EOL
             . '    <lastmod>' . $lastmod . '</lastmod>' . PHP_EOL
             . '    <changefreq>' . $changefreq . '</changefreq>' . PHP_EOL
             . '    <priority>' . $priority . '</priority>' . PHP_EOL
             . '  </url>' . PHP_EOL;
    }
}