<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.01.15 - 21:40
 */

namespace FileConnectorTest\Service;

use FileConnector\Service\FileNameRenderer;
use FileConnectorTest\Bootstrap;
use FileConnectorTest\TestCase;

/**
 * Class FileNameRendererTest
 *
 * @package FileConnectorTest\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FileNameRendererTest extends TestCase
{
    /**
     * @var FileNameRenderer
     */
    private $fileNameRenderer;

    protected function setUp()
    {
        $this->fileNameRenderer = Bootstrap::getServiceManager()->get('fileconnector.filename_renderer');
    }

    /**
     * @test
     */
    public function it_simply_returns_filename_if_it_does_not_contain_a_placeholder()
    {
        $this->assertEquals("data.json", $this->fileNameRenderer->render('data.json'));
    }

    /**
     * @test
     */
    public function it_uses_provided_data_to_resolve_placeholder()
    {
        $this->assertEquals("data.json", $this->fileNameRenderer->render('data.{{type}}', ['type' => 'json']));
    }

    /**
     * @test
     */
    public function it_uses_configured_mixins_to_resolve_sections()
    {
        $this->assertEquals(
            sprintf("data_%s.json", date('d.m.Y')),
            $this->fileNameRenderer->render(
                'data_{{#now}}d.m.Y{{/now}}.{{type}}',
                ['type' => 'json']
            )
        );
    }
}
 