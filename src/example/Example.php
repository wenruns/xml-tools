<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/2
 * Time: 15:32
 */

namespace Wenruns\Xml\example;

use Wenruns\Xml\XmlNode;
use Wenruns\Xml\XmlTools;

class Example
{
    public function index()
    {
        $xmlTools = new XmlTools();
        $xmlTools->createDOMDocument('wenruns', '1.0', 'utf-8');
        $xmlTools->switchDOM('wenruns');
        $xmlTools->createNode('rootNode', function (XmlNode $node) {
            $node->createChildNode('subNode1', '1');
            $node->createChildNode('subNode2', function (XmlNode $subNode) {
                $subNode->createChildNode('sSubNode1', 'A001')->attributes([]);
            });
        })->rootNode();
    }
}