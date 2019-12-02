<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Webtools;

use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Test share this block rendering.
 */
class SocialShareTest extends AbstractKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'oe_webtools_social_share',
  ];

  /**
   * Test Social share block rendering.
   */
  public function testSocialShareBlockRendering(): void {
    // Setup and render social share block.
    $config = [
      'id' => 'social_share',
      'label' => 'OpenEuropa Social Share block',
      'provider' => 'oe_webtools_social_share',
      'label_display' => '0',
    ];

    $render = $this->buildBlock('social_share', $config);

    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);

    // Make sure that social media share block is correctly rendered.
    $custom_footer = $crawler->filter('p.ecl-social-media-share__description');
    $this->assertCount(1, $custom_footer);
  }

}
