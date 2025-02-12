<?php

class Links_Directory_Test extends PLL_UnitTestCase {
	protected $structure = '/%postname%/';
	protected $host = 'http://example.org';

	/**
	 * @param WP_UnitTest_Factory $factory
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		self::create_language( 'en_US' );
		self::create_language( 'fr_FR' );
		self::create_language( 'de_DE_formal' );
	}

	public function set_up() {
		parent::set_up();

		global $wp_rewrite;

		self::$model->options['hide_default'] = 1;
		self::$model->options['rewrite'] = 1;

		// switch to pretty permalinks
		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( $this->structure );
		$this->links_model = self::$model->get_links_model();
		$this->links_model->init();
	}

	protected function _test_add_language_to_link() {
		$url = $this->root . '/test/';

		self::$model->options['rewrite'] = 1;
		$this->assertEquals( $this->root . '/test/', $this->links_model->add_language_to_link( $url, self::$model->get_language( 'en' ) ) );
		$this->assertEquals( $this->root . '/fr/test/', $this->links_model->add_language_to_link( $url, self::$model->get_language( 'fr' ) ) );

		self::$model->options['rewrite'] = 0;
		$this->assertEquals( $this->root . '/test/', $this->links_model->add_language_to_link( $url, self::$model->get_language( 'en' ) ) );
		$this->assertEquals( $this->root . '/language/fr/test/', $this->links_model->add_language_to_link( $url, self::$model->get_language( 'fr' ) ) );
	}

	protected function _test_double_add_language_to_link() {
		self::$model->options['rewrite'] = 1;
		$this->assertEquals( $this->root . '/fr/test/', $this->links_model->add_language_to_link( $this->root . '/fr/test/', self::$model->get_language( 'fr' ) ) );

		self::$model->options['rewrite'] = 0;
		$this->assertEquals( $this->root . '/language/fr/test/', $this->links_model->add_language_to_link( $this->root . '/language/fr/test/', self::$model->get_language( 'fr' ) ) );
	}

	protected function _test_remove_language_from_link() {
		self::$model->options['rewrite'] = 1;
		$this->assertEquals( $this->root . '/en/test/', $this->links_model->remove_language_from_link( $this->root . '/en/test/' ) );
		$this->assertEquals( $this->root . '/test/', $this->links_model->remove_language_from_link( $this->root . '/fr/test/' ) );

		self::$model->options['rewrite'] = 0;
		$this->assertEquals( $this->root . '/language/en/test/', $this->links_model->remove_language_from_link( $this->root . '/language/en/test/' ) );
		$this->assertEquals( $this->root . '/test/', $this->links_model->remove_language_from_link( $this->root . '/language/fr/test/' ) );
	}

	protected function _test_switch_language_in_link() {
		self::$model->options['rewrite'] = 1;
		$this->assertEquals( $this->root . '/test/', $this->links_model->switch_language_in_link( $this->root . '/fr/test/', self::$model->get_language( 'en' ) ) );
		$this->assertEquals( $this->root . '/de/test/', $this->links_model->switch_language_in_link( $this->root . '/fr/test/', self::$model->get_language( 'de' ) ) );
		$this->assertEquals( $this->root . '/fr/test/', $this->links_model->switch_language_in_link( $this->root . '/test/', self::$model->get_language( 'fr' ) ) );
	}

	protected function _test_add_paged_to_link() {
		self::$model->options['rewrite'] = 1;
		$this->assertEquals( $this->root . '/test/page/2/', $this->links_model->add_paged_to_link( $this->root . '/test/', 2 ) );
		$this->assertEquals( $this->root . '/fr/test/page/2/', $this->links_model->add_paged_to_link( $this->root . '/fr/test/', 2 ) );
	}

	protected function _test_remove_paged_from_link() {
		self::$model->options['rewrite'] = 1;
		$this->assertEquals( $this->root . '/test/', $this->links_model->remove_paged_from_link( $this->root . '/test/page/2/' ) );
		$this->assertEquals( $this->root . '/fr/test/', $this->links_model->remove_paged_from_link( $this->root . '/fr/test/page/2/' ) );
	}

	public function test_link_filters_with_absolute_links() {
		$this->root = $this->host;
		$this->_test_add_language_to_link();
		$this->_test_double_add_language_to_link();
		$this->_test_remove_language_from_link();
		$this->_test_switch_language_in_link();
		$this->_test_add_paged_to_link();
		$this->_test_remove_paged_from_link();
	}

	public function test_link_filters_with_relative_links() {
		$this->root = '';
		$this->_test_add_language_to_link();
		$this->_test_double_add_language_to_link();
		$this->_test_remove_language_from_link();
		$this->_test_switch_language_in_link();
		$this->_test_add_paged_to_link();
		$this->_test_remove_paged_from_link();
	}

	/**
	 * Bug fixed in 2.6.
	 */
	public function test_link_filters_mixing_ssl() {
		$this->root = 'https://example.org'; // $this->links_model->home uses http
		$this->_test_add_language_to_link();
		$this->_test_double_add_language_to_link();
		$this->_test_remove_language_from_link();
		$this->_test_switch_language_in_link();
		$this->_test_add_paged_to_link();
		$this->_test_remove_paged_from_link();
	}

	public function test_link_filters_with_home_in_subdirectory() {
		$this->root = 'http://example.org/polylang-pro';
		$this->links_model->home = $this->root;
		$this->_test_add_language_to_link();
		$this->_test_double_add_language_to_link();
		$this->_test_remove_language_from_link();
		$this->_test_switch_language_in_link();
		$this->_test_add_paged_to_link();
		$this->_test_remove_paged_from_link();
	}

	public function test_get_language_from_url() {
		$server = $_SERVER;

		$this->assertEquals( 'fr', $this->links_model->get_language_from_url( home_url( '/fr' ) ) );

		$_SERVER['REQUEST_URI'] = '/test/';
		$this->assertEmpty( $this->links_model->get_language_from_url() );

		$_SERVER['REQUEST_URI'] = '/fr/test/';
		$this->assertEquals( 'fr', $this->links_model->get_language_from_url() );

		// Bug fixed in 2.6.10.
		$_SERVER['REQUEST_URI'] = '/test/fr/';
		$this->assertEmpty( $this->links_model->get_language_from_url() );

		self::$model->options['rewrite'] = 0;
		$_SERVER['REQUEST_URI'] = '/language/fr/test/';
		$this->assertEquals( 'fr', $this->links_model->get_language_from_url() );

		$_SERVER = $server;
	}

	public function test_home_url() {
		$this->assertEquals( $this->host . '/', $this->links_model->home_url( self::$model->get_language( 'en' ) ) );
		$this->assertEquals( $this->host . '/fr/', $this->links_model->home_url( self::$model->get_language( 'fr' ) ) );

		self::$model->options['rewrite'] = 0;
		$this->assertEquals( $this->host . '/', $this->links_model->home_url( self::$model->get_language( 'en' ) ) );
		$this->assertEquals( $this->host . '/language/fr/', $this->links_model->home_url( self::$model->get_language( 'fr' ) ) );
	}

	/**
	 * Issue fixed in version 2.1.2.
	 */
	public function test_get_language_from_url_with_wrong_ssl() {
		$server = $_SERVER;

		$_SERVER['REQUEST_URI'] = '/fr/test/';
		$_SERVER['SERVER_PORT'] = 80;
		$this->assertEquals( 'fr', $this->links_model->get_language_from_url() );

		$_SERVER['SERVER_PORT'] = 443;
		$this->assertEquals( 'fr', $this->links_model->get_language_from_url() );

		$_SERVER = $server;
	}
}
