<?php
namespace Vifeed\TagBundle\Tests\Unit;

use Vifeed\SystemBundle\Tests\TestCase;

class TagManagerTest extends TestCase {

    /**
     * проверка е и ё
     */
    public function testYeYo()
    {
        $tagManager = $this->getContainer()->get('vifeed.tag.manager');

        try {
            $tags = $tagManager->loadOrCreateTags(['мед', 'мёд']);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());

            return;
        }

        $this->assertCount(2, $tags);
        $this->assertInstanceOf('\Vifeed\TagBundle\Entity\Tag', $tags[0]);
        $this->assertInstanceOf('\Vifeed\TagBundle\Entity\Tag', $tags[1]);

        $this->assertNotEquals($tags[0]->getId(), $tags[1]->getId());
    }
}
 