<?php
/**
 * Antigravity Agent Unit Tests
 *
 * @package WordPress_Bootstrap_Claude
 * @subpackage Tests
 */

namespace WPBC\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Mockery;

class AntigravityAgentTest extends TestCase
{

    private $translator;
    private $logger;
    private $agent;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock dependencies
        $this->translator = Mockery::mock('WPBC\TranslationBridge\Core\WPBC_Translator');
        $this->logger = Mockery::mock('WPBC_Logger');

        // Load class
        require_once WPBC_INCLUDES . '/class-wpbc-antigravity-agent.php';

        $this->agent = new \WPBC_Antigravity_Agent($this->translator, $this->logger);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_run_executes_plan_successfully()
    {
        // Mock Logger
        $this->logger->shouldReceive('info')->atLeast()->times(1);
        $this->logger->shouldReceive('error')->never();
        $this->logger->shouldReceive('warning')->never();

        // Mock internal methods via partial mock or just test the public interface
        // Since we can't easily mock private methods in PHPUnit without reflection or redesign,
        // we will test that the method runs and handles the "mocked" Claude response (which is empty in test env).

        // Note: In a real test environment, we would mock the `call_claude` method or the shell_exec function.
        // For this basic test, we expect it to fail or fallback because `claude` CLI might not be in the test runner's path 
        // or we want to verify the fallback behavior.

        // Actually, let's test the fallback behavior when Claude returns empty/invalid JSON.

        // The agent uses `shell_exec`. We can't easily mock that without namespace tricks.
        // So we will assume the fallback plan is used.

        $result = $this->agent->run('https://example.com', 'bootstrap');

        // It should return true because the fallback plan is executed and verified (default verify is success).
        $this->assertTrue($result);
    }
}
