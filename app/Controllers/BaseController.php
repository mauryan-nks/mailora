<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    protected int $workspaceId = 0;

    protected function page(string $view, array $data = []): string
    {
        return view('layouts/app', $data + ['content' => view($view, $data), 'active' => $data['active'] ?? 'dashboard']);
    }

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        if (function_exists('app_auth') && app_auth()->loggedIn()) {
            $membership = db_connect()->table('workspace_members')
                ->where('user_id', app_auth()->id())
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if ($membership !== null) {
                $this->workspaceId = (int) $membership['workspace_id'];
            }
            $user=current_user();if($user&&$user['workspace_id'])$this->workspaceId=(int)$user['workspace_id'];
            $selected=(int)session('active_workspace_id');if($selected&&$user&&(new \App\Libraries\WorkspaceAccessService())->canAccess($user,$selected))$this->workspaceId=$selected;
        }

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
    }
}
