<?php

namespace Gliph\Graph;

/**
 * @coversDefaultClass \Gliph\Graph\DirectedAdjacencyList
 */
class DirectedAdjacencyListTest extends AdjacencyListBase {

    /**
     * @var DirectedAdjacencyList
     */
    protected $g;

    public function setUp() {
        parent::setUp();
        $this->g = new DirectedAdjacencyList();
    }

    /**
     * Implicitly depends on AdjacencyList::addVertex.
     *
     * @covers ::addDirectedEdge
     */
    public function testAddDirectedEdge() {
        extract($this->v);
        $this->g->addDirectedEdge($a, $b);

        $this->assertAttributeContains($a, 'vertices', $this->g);
        $this->assertAttributeContains($b, 'vertices', $this->g);
        $this->assertVertexCount(2, $this->g);
    }

    /**
     * @depends testAddDirectedEdge
     * @covers ::eachAdjacent
     */
    public function testEachAdjacent() {
        extract($this->v);
        $this->g->addDirectedEdge($a, $b);
        $this->g->addDirectedEdge($a, $c);

        $found = array();
        $this->g->eachAdjacent($a, function($to) use (&$found) {
            $found[] = $to;
        });
        $this->assertEquals(array($b, $c), $found);

        $found = array();
        $this->g->eachAdjacent($b, function($to) use (&$found) {
            $found[] = $to;
        });
        $this->assertEmpty($found);

        $this->g->eachAdjacent($c, function($to) use (&$found) {
            $found[] = $to;
        });
        $this->assertEmpty($found);
    }

    /**
     * @depends testAddDirectedEdge
     * @depends testEachAdjacent
     * @covers ::removeVertex
     */
    public function testRemoveVertex() {
        extract($this->v);
        $this->g->addDirectedEdge($a, $b);
        $this->assertVertexCount(2, $this->g);

        $this->g->removeVertex($b);
        $this->assertVertexCount(1, $this->g);

        // Ensure that b was correctly removed from a's outgoing edges
        $found = array();
        $this->g->eachAdjacent($a, function($to) use (&$found) {
            $found[] = $to;
        });

        $this->assertEquals(array(), $found);
    }

    /**
     * @depends testAddDirectedEdge
     * @covers ::removeEdge
     */
    public function testRemoveEdge() {
        extract($this->v);
        $this->g->addDirectedEdge($a, $b);
        $this->g->removeEdge($a, $b);

        $this->assertVertexCount(2, $this->g);
    }

    /**
     * @depends testAddDirectedEdge
     * @depends testEachAdjacent
     * @covers ::eachEdge
     */
    public function testEachEdge() {
        extract($this->v);
        $this->g->addDirectedEdge($a, $b);
        $this->g->addDirectedEdge($a, $c);

        $found = array();
        $this->g->eachEdge(function($edge) use (&$found) {
            $found[] = $edge;
        });

        $this->assertCount(2, $found);
        $this->assertEquals(array($a, $b), $found[0]);
        $this->assertEquals(array($a, $c), $found[1]);
    }

    /**
     * @depends testAddDirectedEdge
     * @depends testEachEdge
     * @covers ::transpose
     */
    public function testTranspose() {
        extract($this->v);
        $this->g->addDirectedEdge($a, $b);
        $this->g->addDirectedEdge($a, $c);

        $transpose = $this->g->transpose();

        $this->assertVertexCount(3, $transpose);

        $found = array();
        $transpose->eachEdge(function($edge) use (&$found) {
            $found[] = $edge;
        });

        $this->assertCount(2, $found);
        $this->assertContains(array($b, $a), $found);
        $this->assertContains(array($c, $a), $found);
    }

    /**
     * @expectedException \Gliph\Exception\NonexistentVertexException
     * @covers ::removeVertex
     */
    public function testRemoveNonexistentVertex() {
        $this->g->removeVertex($this->v['a']);
    }

    /**
     * @covers ::isAcyclic()
     */
    public function testIsAcyclic() {
        $this->g->addDirectedEdge($this->v['a'], $this->v['b']);
        $this->g->addDirectedEdge($this->v['b'], $this->v['c']);
        $this->assertTrue($this->g->isAcyclic());

        $this->g->addDirectedEdge($this->v['c'], $this->v['a']);
        $this->assertFalse($this->g->isAcyclic());
    }

    /**
     * This is primarily a test of the Tarjan SCC algo, but the coverage scoping
     * ensures that we are only focused on the graph's method for returning
     * correct outputs.
     *
     * @covers ::getCycles()
     */
    public function testGetCycles() {
        extract($this->v);
        $this->g->addDirectedEdge($a, $b);
        $this->g->addDirectedEdge($b, $c);

        $this->assertEmpty($this->g->getCycles());

        $this->g->addDirectedEdge($c, $a);
        $this->assertEquals(array(array($this->v['c'], $this->v['b'], $this->v['a'])), $this->g->getCycles());
    }
}
