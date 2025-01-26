<?php

/**
 * Quantum PHP Framework
 *
 * An open source software development framework for PHP
 *
 * @package Quantum
 * @author Arman Ag. <arman.ag@softberg.org>
 * @copyright Copyright (c) 2018 Softberg LLC (https://softberg.org)
 * @link http://quantum.softberg.org/
 * @since 2.9.5
 */

namespace Quantum\Libraries\Storage\Adapters\Dropbox;

use Quantum\Libraries\Storage\Contracts\TokenServiceInterface;
use Quantum\Libraries\Encryption\Exceptions\CryptorException;
use Quantum\Libraries\Database\Exceptions\DatabaseException;
use Quantum\Libraries\Storage\Contracts\CloudAppInterface;
use Quantum\Libraries\Lang\Exceptions\LangException;
use Quantum\Libraries\Storage\Traits\CloudAppTrait;
use Quantum\Libraries\HttpClient\HttpClient;
use Quantum\Http\Exceptions\HttpException;
use Quantum\Exceptions\BaseException;
use Exception;

/**
 * Class DropboxApp
 * @package Quantum\Libraries\Storage
 */
class DropboxApp implements CloudAppInterface
{

    use CloudAppTrait;

    /**
     * Authorization URL
     */
    const AUTH_URL = 'https://dropbox.com/oauth2/authorize';

    /**
     * Token URL
     */
    const AUTH_TOKEN_URL = 'https://api.dropboxapi.com/oauth2/token';

    /**
     * URL for remote procedure call endpoints
     */
    const RPC_API_URL = 'https://api.dropboxapi.com/2';

    /**
     * URL for content endpoints
     */
    const CONTENT_API_URL = 'https://content.dropboxapi.com/2';

    /**
     * Create folder endpoint
     */
    const ENDPOINT_CREATE_FOLDER = 'files/create_folder_v2';

    /**
     * Delete file endpoint
     */
    const ENDPOINT_DELETE_FILE = 'files/delete_v2';

    /**
     * Download file endpoint
     */
    const ENDPOINT_DOWNLOAD_FILE = 'files/download';

    /**
     * Upload file endpoint
     */
    const ENDPOINT_UPLOAD_FILE = 'files/upload';

    /**
     * Move file endpoint
     */
    const ENDPOINT_MOVE_FILE = 'files/move_v2';

    /**
     * Copy file endpoint
     */
    const ENDPOINT_COPY_FILE = 'files/copy_v2';

    /**
     * Get metadata for file endpoint
     */
    const ENDPOINT_FILE_METADATA = 'files/get_metadata';

    /**
     * List folder endpoint
     */
    const ENDPOINT_LIST_FOLDER = 'files/list_folder';

    /**
     * Access token status indicating it needs refresh
     */
    const ACCESS_TOKEN_STATUS = ['invalid_access_token', 'expired_access_token'];

    /**
     * Error code for invalid token
     */
    const INVALID_TOKEN_ERROR_CODE = 401;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var string
     */
    private $appKey;

    /**
     * @var string
     */
    private $appSecret;

    /**
     * @var TokenServiceInterface
     */
    private $tokenService;

    /**
     * DropboxApp constructor
     * @param string $appKey
     * @param string $appSecret
     * @param TokenServiceInterface $tokenService
     * @param HttpClient $httpClient
     */
    public function __construct(string $appKey, string $appSecret, TokenServiceInterface $tokenService, HttpClient $httpClient)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->tokenService = $tokenService;
        $this->httpClient = $httpClient;
    }

    /**
     * Gets the auth URL
     * @param string $redirectUrl
     * @param string $tokenAccessType
     * @return string
     * @throws BaseException
     * @throws CryptorException
     * @throws DatabaseException
     */
    public function getAuthUrl(string $redirectUrl, string $tokenAccessType = 'offline'): string
    {
        $params = [
            'client_id' => $this->appKey,
            'response_type' => 'code',
            'state' => csrf_token(),
            'redirect_uri' => $redirectUrl,
            'token_access_type' => $tokenAccessType,
        ];

        return self::AUTH_URL . '?' . http_build_query($params, '', '&');
    }

    /**
     * Fetch tokens
     * @param string $code
     * @param string $redirectUrl
     * @return object|null
     * @throws BaseException
     * @throws HttpException
     * @throws LangException
     */
    public function fetchTokens(string $code, string $redirectUrl): ?object
    {
        $params = [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'client_id' => $this->appKey,
            'client_secret' => $this->appSecret,
            'redirect_uri' => $redirectUrl,
        ];

        $tokenUrl = self::AUTH_TOKEN_URL . '?' . http_build_query($params, '', '&');

        $response = $this->sendRequest($tokenUrl);

        $this->tokenService->saveTokens($response->access_token, $response->refresh_token);

        return $response;
    }

    /**
     * Fetches the access token by refresh token
     * @param string $refreshToken
     * @return string
     * @throws BaseException
     * @throws HttpException
     * @throws LangException
     */
    private function fetchAccessTokenByRefreshToken(string $refreshToken): string
    {
        $params = [
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
            'client_id' => $this->appKey,
            'client_secret' => $this->appSecret
        ];

        $tokenUrl = self::AUTH_TOKEN_URL . '?' . http_build_query($params, '', '&');

        $response = $this->sendRequest($tokenUrl);

        $this->tokenService->saveTokens($response->access_token);

        return $response->access_token;
    }

    /**
     * Sends rpc request
     * @param string $endpoint
     * @param array|null $params
     * @return mixed|null
     * @throws Exception
     */
    public function rpcRequest(string $endpoint, ?array $params = [])
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->tokenService->getAccessToken(),
            'Content-Type' => 'application/json'
        ];

        return $this->sendRequest(self::RPC_API_URL . '/' . $endpoint, $params, $headers);
    }

    /**
     * Sends content request
     * @param string $endpoint
     * @param array $params
     * @param string $content
     * @return mixed|null
     * @throws Exception
     */
    public function contentRequest(string $endpoint, array $params, string $content = '')
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->tokenService->getAccessToken(),
            'Dropbox-API-Arg' => json_encode($params),
            'Content-Type' => 'application/octet-stream'
        ];

        return $this->sendRequest(self::CONTENT_API_URL . '/' . $endpoint, $content, $headers);
    }

    /**
     * Gets the normalized path
     * @param string $name
     * @return array
     */
    public function path(string $name): array
    {
        return ['path' => '/' . trim($name, '/')];
    }

    /**
     * Checks if the access token need refresh
     * @param int $code
     * @param object|null $message
     * @return bool
     */
    private function accessTokenNeedsRefresh(int $code, ?object $message = null): bool
    {
        if ($code != self::INVALID_TOKEN_ERROR_CODE) {
            return false;
        }

        if(isset($message->error)) {
            $error = (array)$message->error;

            if (!isset($error['.tag']) && !in_array($error['.tag'], self::ACCESS_TOKEN_STATUS)) {
                return false;
            }
        }

        return true;
    }
}