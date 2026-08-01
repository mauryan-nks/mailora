<?php

namespace App\Libraries;

final class BrandContext
{
    private array $brand = [
        'reseller_id' => null,
        'name' => 'Mailora',
        'logo_path' => null,
        'favicon_path' => null,
        'primary_color' => '#44B89D',
        'secondary_color' => '#288DA5',
        'domain' => null,
    ];

    public function set(array $brand): void { $this->brand = $brand + $this->brand; }
    public function all(): array { return $this->brand; }
    public function get(string $key, mixed $default = null): mixed { return $this->brand[$key] ?? $default; }
    public function isReseller(): bool { return $this->brand['reseller_id'] !== null; }
}
