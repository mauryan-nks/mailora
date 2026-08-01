<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class EnforceTenantDomain implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! app_auth()->loggedIn()) return null;
        $brandReseller = service('brandContext')->get('reseller_id');
        $user = current_user();
        $userReseller = $user['account_type'] === 'reseller' ? (int) $user['id'] : ($user['reseller_id'] ? (int) $user['reseller_id'] : null);

        if ($brandReseller !== null && ! in_array($user['account_type'], ['platform_admin', 'platform_team'], true) && $userReseller !== (int) $brandReseller) {
            app_auth()->logout();
            return redirect()->to('/login')->with('error', 'This account does not belong to this branded portal.');
        }
        if ($brandReseller === null && $userReseller !== null && ! in_array($user['account_type'], ['platform_admin', 'platform_team'], true)) {
            $portal = db_connect()->table('reseller_domains')->where('reseller_id', $userReseller)->where('status', 'verified')->orderBy('id', 'ASC')->get()->getRowArray();
            if ($portal !== null) {
                $path = ltrim($request->getUri()->getPath(), '/');
                $query = $request->getUri()->getQuery();
                return redirect()->to('https://' . $portal['domain'] . '/' . $path . ($query ? '?' . $query : ''));
            }
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
