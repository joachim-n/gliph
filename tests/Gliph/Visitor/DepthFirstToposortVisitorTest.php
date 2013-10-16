<?php

namespace Gliph\Visitor;

use Gliph\TestVertex;

class DepthFirstToposortVisitorTest extends SimpleStatefulDepthFirstVisitorTestBase {

    /**
     * Creates a DepthFirstToposortVisitor in IN_PROGRESS state.
     *
     * @return DepthFirstToposortVisitor
     */
    public function createInProgressVisitor() {
        return new DepthFirstToposortVisitor();
    }

    /**
     * Creates a DepthFirstToposortVisitor in COMPLETED state.
     *
     * @return DepthFirstToposortVisitor
     */
    public function createCompletedVisitor() {
        $stub = new DepthFirstToposortVisitor();

        $prop = new \ReflectionProperty($stub, 'state');
        $prop->setAccessible(TRUE);
        $prop->setValue($stub, StatefulVisitorInterface::COMPLETE);

        return $stub;
    }

    public function completionRequiredMethods() {
        return array(
            array('getTsl', array()),
        );
    }

    /**
     * @expectedException \Gliph\Exception\RuntimeException
     */
    public function testOnBackEdge() {
        $this->createInProgressVisitor()->onBackEdge(new \stdClass(), function() {});
    }

    public function testGetTsl() {
        $a = new TestVertex('a');
        $b = new TestVertex('b');
        $c = new TestVertex('c');

        $vis = $this->createInProgressVisitor();

        $vis->onFinishVertex($a, function() {});
        $vis->onFinishVertex($b, function() {});
        $vis->onFinishVertex($c, function() {});
        $vis->endTraversal();

        $this->assertEquals(array($a, $b, $c), $vis->getTsl());
    }
}
