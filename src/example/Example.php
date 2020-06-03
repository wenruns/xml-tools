<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/2
 * Time: 15:32
 */

namespace Wenruns\Xml\example;


use Wenruns\XmlTools\XmlNode;
use Wenruns\XmlTools\XmlTools;

class Example
{

    public function index()
    {
        $xmlTools = new XmlTools();
        // 创建DOMDocument对象，命名为example
        $xmlTools->createDOMDocument('example', '1.0', 'utf-8');
        // 切换当前DOMDocument对象，创建时会默认切换为穿件的DOMDocument对象
        $xmlTools->switchDOM('example');
        // 创建节点rootNode 并设置为根节点
        $xmlTools->createNode('rootNode', function (XmlNode $node) {
            // 创建子节点
            $node->createChildNode('subNode1', '1');
            $node->createChildNode('subNode2', function (XmlNode $subNode) {
                $subNode->createChildNode('sSubNode1', 'A001')->attributes([]);
            });
        })->rootNode();
        // DOMDocument对象集合处理
        $res = $xmlTools->dealWithXml(function (\DOMDocument $dom, $domName, XmlTools $tools) {
            if ($tools->existAbnormalNodes($domName)) {
                // 存在异常节点
                return [
                    'status' => false,
                    'errMsg' => $domName . '存在异常节点',
                ];
            } elseif (empty($tools->getRootNodes($domName))) {
                // 没有设置根节点
                return [
                    'status' => false,
                    'errMsg' => $domName . '不存在根节点',
                ];
            } else {
                // 保存xml文件
                return [
                    'status' => true,
                    'errMsg' => '',
                    'result' => $dom->save(__DIR__ . '/example.xml')
                ];
            }
        });
        return $res;
    }
}