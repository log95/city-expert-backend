<?php

namespace App\Service;

class FrontendLinkService
{
    private const MODERATION_TEST_PATH = '/moderation/tests/{test}/';
    private const ACCOUNT_TEST_PATH = '/account/tests/{test}/';

    private string $frontUrl;

    public function __construct(string $frontUrl)
    {
        $this->frontUrl = $frontUrl;
    }

    public function getModerationTestUrl(int $testId): string
    {
        $url = $this->frontUrl . self::MODERATION_TEST_PATH;

        return str_replace('{test}', $testId, $url);
    }

    public function getAccountTestUrl(int $testId): string
    {
        $url = $this->frontUrl . self::ACCOUNT_TEST_PATH;

        return str_replace('{test}', $testId, $url);
    }
}
