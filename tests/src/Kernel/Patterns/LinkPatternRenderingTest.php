<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\Core\Url;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Test link pattern rendering.
 *
 * @group batch2
 */
class LinkPatternRenderingTest extends AbstractKernelTestBase {

  /**
   * Test that link patterns are correctly rendered when passing a URL object.
   *
   * @throws \Exception
   */
  public function testLinkPatternRendering() {
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'link',
      '#fields' => [
        'text' => 'Link text',
        'url' => Url::fromUserInput('/node/add', [
          'attributes' => [
            'class' => ['foo'],
            'foo' => 'bar',
          ],
        ]),
      ],
    ];

    $html = $this->renderRoot($pattern);
    $crawler = new Crawler($html);
    $this->assertEquals('Link text', $crawler->filter('a.ecl-link.ecl-link--standalone.foo')->text());
    $this->assertEquals('/node/add', $crawler->filter('a.ecl-link.ecl-link--standalone.foo')->attr('href'));
    $this->assertEquals('bar', $crawler->filter('a.ecl-link.ecl-link--standalone.foo')->attr('foo'));
    $this->assertCount(0, $crawler->filter('span.ecl-link__label'));
    $this->assertCount(0, $crawler->filter('svg.ecl-icon.ecl-icon--s.ecl-link__icon'));

    $pattern = [
      '#type' => 'pattern',
      '#id' => 'link',
      '#fields' => [
        'text' => 'Link text',
        'url' => Url::fromUri('https://example.com'),
      ],
    ];

    $html = $this->renderRoot($pattern);
    $crawler = new Crawler($html);
    $this->assertEquals('Link text', $crawler->filter('a.ecl-link.ecl-link--standalone.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text());
    $this->assertEquals('https://example.com', $crawler->filter('a.ecl-link.ecl-link--standalone.ecl-link--icon.ecl-link--icon-after')->attr('href'));
    $this->assertEquals('<use xlink:href="/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#external"></use>', $crawler->filter('svg.ecl-icon.ecl-icon--2xs.ecl-link__icon')->html());
  }

}
