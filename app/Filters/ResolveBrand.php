<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ResolveBrand implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $host = strtolower(preg_replace('/:\d+$/', '', $request->getServer('HTTP_HOST') ?: ''));
        if ($host === '' || in_array($host, ['localhost', '127.0.0.1'], true)) return null;

        $row = db_connect()->table('reseller_domains d')
            ->select('d.domain, d.reseller_id, p.brand_name AS name, p.logo_path, p.favicon_path, p.primary_color, p.secondary_color')
            ->join('reseller_profiles p', 'p.user_id = d.reseller_id')
            ->where('d.domain', $host)->where('d.status', 'verified')->get()->getRowArray();

        if ($row !== null) service('brandContext')->set($row);
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
