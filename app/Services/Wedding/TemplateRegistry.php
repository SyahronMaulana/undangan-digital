<?php

namespace App\Services\Wedding;

use App\Models\Template;

class TemplateRegistry
{
    /** @return \Illuminate\Support\Collection<int, Template> */
    public function active()
    {
        $templates = Template::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($templates->isNotEmpty()) {
            return $templates;
        }

        return collect(config('wedding.templates', []))
            ->map(function (array $meta, string $slug) {
                $template = new Template([
                    'slug' => $slug,
                    'name' => $meta['name'],
                    'style_label' => $meta['tagline'],
                    'description' => $meta['description'],
                    'palette' => $meta['palette'],
                    'is_active' => true,
                    'sort_order' => array_search($slug, array_keys(config('wedding.templates')), true),
                ]);
                $template->exists = false;

                return $template;
            })->values();
    }

    public function allMeta(): array
    {
        return config('wedding.templates', []);
    }

    public function meta(string $slug): array
    {
        return config('wedding.templates.'.$slug, []);
    }

    public function isKnown(string $slug): bool
    {
        return array_key_exists($slug, config('wedding.templates', []));
    }
}
