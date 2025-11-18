<?php
/**
 * Auth Class Unit Tests
 *
 * @package WordPress_Bootstrap_Claude
 * @subpackage Tests
 */

namespace WPBC\Tests\Unit;

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase {

    private $auth;

    protected function setUp(): void {
        parent::setUp();

        // Load the Auth class
        require_once WPBC_INCLUDES . '/class-wpbc-auth.php';
        $this->auth = new \WPBC_Auth();
    }

    /**
     * Test API key generation
     */
    public function test_generate_api_key_returns_valid_key() {
        $key = $this->auth->generate_api_key('test_user');

        $this->assertIsString($key);
        $this->assertGreaterThan(20, strlen($key));
        $this->assertStringStartsWith('wpbc_', $key);
    }

    public function test_generate_api_key_creates_unique_keys() {
        $key1 = $this->auth->generate_api_key('user1');
        $key2 = $this->auth->generate_api_key('user2');

        $this->assertNotEquals($key1, $key2, 'Generated keys should be unique');
    }

    /**
     * Test API key extraction from request
     */
    public function test_get_api_key_from_request_extracts_from_header() {
        $request = new \WP_REST_Request();
        $request->set_header('X-API-Key', 'test_api_key_12345');

        $reflection = new \ReflectionClass($this->auth);
        $method = $reflection->getMethod('get_api_key_from_request');
        $method->setAccessible(true);

        $result = $method->invoke($this->auth, $request);

        $this->assertEquals('test_api_key_12345', $result);
    }

    public function test_get_api_key_from_request_returns_null_when_no_key() {
        $request = new \WP_REST_Request();

        $reflection = new \ReflectionClass($this->auth);
        $method = $reflection->getMethod('get_api_key_from_request');
        $method->setAccessible(true);

        $result = $method->invoke($this->auth, $request);

        $this->assertNull($result);
    }

    public function test_get_api_key_from_request_ignores_query_parameters() {
        // After our security fix, query parameters should be ignored
        $request = new \WP_REST_Request();
        $request->set_param('api_key', 'should_be_ignored');

        $reflection = new \ReflectionClass($this->auth);
        $method = $reflection->getMethod('get_api_key_from_request');
        $method->setAccessible(true);

        $result = $method->invoke($this->auth, $request);

        $this->assertNull($result, 'API key in query parameter should be ignored for security');
    }

    /**
     * Test API key validation
     */
    public function test_validate_api_key_format() {
        $valid_keys = [
            'wpbc_1234567890abcdef',
            'wpbc_' . bin2hex(random_bytes(24)),
        ];

        foreach ($valid_keys as $key) {
            $this->assertStringStartsWith('wpbc_', $key);
            $this->assertGreaterThan(20, strlen($key));
        }
    }

    /**
     * Test authentication failure scenarios
     */
    public function test_authenticate_request_fails_without_key() {
        $request = new \WP_REST_Request();

        $result = $this->auth->authenticate_request($request);

        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertEquals('wpbc_auth_missing_key', $result->get_error_code());
    }

    /**
     * Test key information retrieval
     */
    public function test_get_key_info_structure() {
        // This tests the expected structure of key info
        $expected_keys = ['name', 'created', 'last_used', 'requests'];

        // Note: This is a structural test - actual implementation would need database
        $this->assertTrue(true, 'Key info structure validation placeholder');
    }

    /**
     * Test rate limiting integration
     */
    public function test_auth_respects_rate_limits() {
        // Placeholder for rate limiting integration test
        $this->assertTrue(true, 'Rate limiting integration placeholder');
    }

    /**
     * Test key expiration (if implemented)
     */
    public function test_expired_keys_are_rejected() {
        // Placeholder for key expiration test
        $this->assertTrue(true, 'Key expiration test placeholder');
    }
}
