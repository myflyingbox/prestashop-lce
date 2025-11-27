<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@myflyingbox.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade your module to newer
 * versions in the future.
 *
 * @author    MyFlyingBox <contact@myflyingbox.com>
 * @copyright 2016 MyFlyingBox
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/JWTHelper.php';

/**
 * Abstract base controller for API endpoints
 * Handles JWT authentication and common response methods
 */
abstract class ApiController
{
    protected $module;
    protected $context;
    protected $payload;

    /**
     * Constructor
     *
     * @param Module $module The module instance
     * @param Context $context The PrestaShop context
     */
    public function __construct(Module $module, Context $context)
    {
        $this->module = $module;
        $this->context = $context;
    }

    /**
     * Authenticate the request using JWT
     * Checks:
     * - Dashboard sync behavior is not 'never'
     * - JWT secret is configured
     * - Authorization header is present
     * - JWT token is valid
     * - JWT audience matches shop UUID
     *
     * @return bool True if authenticated, sends error response and returns false otherwise
     */
    public function authenticate()
    {
        // Check if API is enabled
        $sync_behavior = Configuration::get('MOD_LCE_DASHBOARD_SYNC_BEHAVIOR');
        if ($sync_behavior === 'never') {
            $this->jsonResponse([
                'error' => 'API_DISABLED',
                'message' => 'API is disabled. Enable it in module configuration.',
            ], 403);
            return false;
        }

        // Check if JWT secret is configured
        $jwt_secret = Configuration::get('MOD_LCE_API_JWT_SHARED_SECRET');
        if (empty($jwt_secret)) {
            $this->jsonResponse([
                'error' => 'API_NOT_CONFIGURED',
                'message' => 'API authentication key is not configured.',
            ], 503);
            return false;
        }

        // Get Authorization header
        $auth_header = $this->getAuthorizationHeader();
        if (empty($auth_header)) {
            $this->jsonResponse([
                'error' => 'MISSING_AUTHORIZATION',
                'message' => 'Authorization header is required.',
            ], 401);
            return false;
        }

        // Extract token from "Bearer <token>" format
        if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            $jwt = $matches[1];
        } else {
            $this->jsonResponse([
                'error' => 'INVALID_AUTHORIZATION_FORMAT',
                'message' => 'Authorization header must use Bearer token format.',
            ], 401);
            return false;
        }

        // Decode and verify JWT
        $this->payload = JWTHelper::decode($jwt, $jwt_secret);
        if (!$this->payload) {
            $this->jsonResponse([
                'error' => 'INVALID_TOKEN',
                'message' => 'Invalid or expired JWT token.',
            ], 401);
            return false;
        }

        // Verify audience matches shop UUID
        $shop_uuid = Configuration::get('MOD_LCE_SHOP_UUID');
        if (!isset($this->payload->aud) || $this->payload->aud !== $shop_uuid) {
            $this->jsonResponse([
                'error' => 'INVALID_AUDIENCE',
                'message' => 'Token audience does not match shop identifier.',
            ], 403);
            return false;
        }

        return true;
    }

    /**
     * Get Authorization header from request
     * Supports multiple methods of retrieving the header
     *
     * @return string|null The authorization header value
     */
    protected function getAuthorizationHeader()
    {
        // Try standard HTTP_AUTHORIZATION
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }

        // Try REDIRECT_HTTP_AUTHORIZATION (some servers)
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        // Try Apache getallheaders()
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
            if (isset($headers['authorization'])) {
                return $headers['authorization'];
            }
        }

        return null;
    }

    /**
     * Send JSON response and exit
     *
     * @param array $data The data to encode as JSON
     * @param int $status_code HTTP status code (default 200)
     */
    protected function jsonResponse($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Handle the API request
     * Must be implemented by child classes
     *
     * @param string $method HTTP method (GET, POST, etc.)
     */
    abstract public function handle($method);
}
