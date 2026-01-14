<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\Route;

class SeoHelper
{
    public static function pageTitle(string $title, string $suffix = ''): string
    {
        $siteName = config('app.name', 'Строительная компания');
        return $suffix ? "$title - $siteName" : $title;
    }

    public static function metaDescription(?string $description, int $maxLength = 160): string
    {
        if (empty($description)) {
            return '';
        }

        return mb_strlen($description) > $maxLength
            ? mb_substr($description, 0, $maxLength - 3) . '...'
            : $description;
    }

    public static function canonicalUrl(): string
    {
        return url()->current();
    }

    public static function ogTags(array $data): string
    {
        $tags = '';

        if (!empty($data['title'])) {
            $tags .= '<meta property="og:title" content="' . e($data['title']) . '">' . "\n";
        }

        if (!empty($data['description'])) {
            $tags .= '<meta property="og:description" content="' . e(self::metaDescription($data['description'])) . '">' . "\n";
        }

        if (!empty($data['url'])) {
            $tags .= '<meta property="og:url" content="' . e($data['url']) . '">' . "\n";
        }

        if (!empty($data['type'])) {
            $tags .= '<meta property="og:type" content="' . e($data['type']) . '">' . "\n";
        }

        if (!empty($data['image'])) {
            $imageUrl = str_starts_with($data['image'], 'http')
                ? $data['image']
                : url($data['image']);
            $tags .= '<meta property="og:image" content="' . e($imageUrl) . '">' . "\n";
        }

        return $tags;
    }

    public static function breadcrumbList(array $items): string
    {
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];

        foreach ($items as $index => $item) {
            $jsonLd['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null
            ];
        }

        return '<script type="application/ld+json">' . json_encode($jsonLd, JSON_UNESCAPED_UNICODE) . '</script>';
    }

    public static function organizationSchema(): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name', 'Строительная компания'),
            'url' => config('app.url'),
        ];

        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE) . '</script>';
    }

    public static function projectSchema(object $project): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Property',
            'name' => $project->title ?? '',
            'description' => $project->description ?? '',
            'url' => route('projects.show', $project->slug),
        ];

        if ($project->main_image) {
            $schema['image'] = url($project->main_image);
        }

        if ($project->area) {
            $schema['floorSize'] = [
                '@type' => 'QuantitativeValue',
                'value' => $project->area,
                'unitCode' => 'MTK'
            ];
        }

        if ($project->bedrooms) {
            $schema['numberOfRooms'] = $project->bedrooms;
        }

        if ($project->bathrooms) {
            $schema['numberOfBathroomsTotal'] = $project->bathrooms;
        }

        if ($project->price_from && $project->price_to) {
            $schema['priceRange'] = number_format($project->price_from, 0, '', ' ') . ' - ' . number_format($project->price_to, 0, '', ' ') . ' RUB';
        } elseif ($project->price_from) {
            $schema['priceRange'] = 'от ' . number_format($project->price_from, 0, '', ' ') . ' RUB';
        }

        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE) . '</script>';
    }

    public static function localBusinessSchema(): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => config('app.name', 'Строительная компания'),
            'url' => config('app.url'),
        ];

        // Try to get phone from settings
        $phone = \get_setting('phone');
        if ($phone) {
            $schema['telephone'] = $phone;
        }

        $email = \get_setting('email');
        if ($email) {
            $schema['email'] = $email;
        }

        $address = \get_setting('address');
        if ($address) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $address,
            ];
        }

        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE) . '</script>';
    }
}
