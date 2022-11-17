<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel;

use Drupal\Core\Url;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Provides test coverage for the ExternalLinks service.
 *
 * @group batch1
 */
class ExternalLinksTest extends AbstractKernelTestBase {

  /**
   * Covers isExternalLink method.
   */
  public function testIsExternalLink(): void {
    $external_links = $this->container->get('oe_theme_helper.external_links');
    // Assert internal URL.
    $this->assertFalse($external_links->isExternalLink(Url::fromUserInput('/user')));
    // Assert external string path.
    $this->assertTrue($external_links->isExternalLink('https://example.com'));
    $this->assertTrue($external_links->isExternalLink('https://example'));
    // Assert external string path under EU domain.
    $this->assertFalse($external_links->isExternalLink('https://example.europa.eu'));
    $this->assertFalse($external_links->isExternalLink('www.ec.europa.eu/info'));
    // Assert null and empty value.
    $this->assertFalse($external_links->isExternalLink());
    $this->assertFalse($external_links->isExternalLink(''));
    // Assert incorrect paths.
    $this->assertFalse($external_links->isExternalLink('www. incorrect . com'));
    $this->assertFalse($external_links->isExternalLink('www. ec . europa . eu'));
    $this->assertFalse($external_links->isExternalLink('https://'));
    $this->assertFalse($external_links->isExternalLink('internal:/'));
    $this->assertFalse($external_links->isExternalLink('route:<front>'));
    $this->assertFalse($external_links->isExternalLink('entity:node/1'));
  }

}
