<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.01.15 - 20:36
 */

namespace FileConnectorTest\Service\FileNameRenderer\Mixin;

use FileConnector\Service\FileNameRenderer\Mixin\NowMixin;
use FileConnectorTest\TestCase;
use Phly\Mustache\Mustache;

final class NowMixinTest extends TestCase
{
    /**
     * @var Mustache
     */
    private $mustache;

    /**
     * @var NowMixin
     */
    private $mixin;

    protected function setUp()
    {
        $this->mustache = new Mustache();
        $this->mixin    = new NowMixin();
    }

    /**
     * @test
     */
    public function it_uses_given_format_to_render_current_date()
    {
        $this->assertEquals(date('d.m.Y'), $this->mustache->render('{{#now}}d.m.Y{{/now}}', ['now' => $this->mixin]));
    }

    /**
     * @test
     */
    public function it_uses_default_format_if_no_format_is_given()
    {
        $this->assertEquals(date('Y-m-d'), $this->mustache->render('{{#now}}{{/now}}', ['now' => $this->mixin]));
    }

    /**
     * @test
     */
    public function it_resolves_data_before_rendering_current_date()
    {
        $this->assertEquals(
            date('d.m.Y'),
            $this->mustache->render(
                '{{#now}}{{data.format}}{{/now}}',
                [
                    'now' => $this->mixin,
                    'data' => ['format' => 'd.m.Y']
                ]
            )
        );
    }
}
 