<?php

namespace App\Service;

class FrontendLinkService
{
    private const MODERATION_TEST_PATH = '/moderation/tests/{test_id}/';
    private const ACCOUNT_TEST_PATH = '/account/tests/{test_id}/';
    private const AUTH_OPERATION_PATH = '/{operation_type}/{user_id}/{code}/';

    private string $frontUrl;

    public function __construct(string $frontUrl)
    {
        $this->frontUrl = $frontUrl;
    }

    public function getAuthOperationUrl(string $operationType, int $userId, string $code): string
    {
        $url = $this->frontUrl . self::AUTH_OPERATION_PATH;

        return str_replace(['{operation_type}', '{user_id}', '{code}'], [$operationType, $userId, $code], $url);
    }

    public function getModerationTestUrl(int $testId): string
    {
        $url = $this->frontUrl . self::MODERATION_TEST_PATH;

        return str_replace('{test_id}', $testId, $url);
    }

    public function getAccountTestUrl(int $testId): string
    {
        $url = $this->frontUrl . self::ACCOUNT_TEST_PATH;

        return str_replace('{test_id}', $testId, $url);
    }
}
