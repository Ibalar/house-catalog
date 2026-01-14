<?php

declare(strict_types=1);

namespace App\MoonShine\Field;

use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Textarea;

class TinyMCEField extends Textarea
{
    protected string $view = 'moonshine::fields.textarea';

    public function __construct(
        Closure|string|null $label = null,
        ?string $column = null,
        ?Closure $formatted = null,
        ?Closure $preview = null,
        ?string $hint = null,
        ?string $type = 'textarea',
        ?bool $isGroup = true,
    ) {
        parent::__construct($label, $column, $formatted, $preview, $hint, $type, $isGroup);
    }

    protected function prepareSound(): void
    {
        parent::prepareSound();

        $this->customAttributes([
            'data-tinymce' => 'true',
        ]);
    }
}