<?php
/**
 * xml节点管理
 * Created by PhpStorm.
 * User: wenruns
 * Date: 2020/1/3
 * Time: 11:13
 */

namespace App\Services\Xml;


class XmlNode
{
    /**
     * 当前节点元素
     * @var \DOMElement|null
     */
    protected $nodeElement = null;

    /**
     * 管理工具
     * @var XmlTools|null
     */
    protected $tools = null;

    /**
     * 父级节点
     * @var array
     */
    protected $_parentNodes = [];

    /**
     * 子节点
     * @var array
     */
    protected $_subNodes = [];


    /**
     * 是否为根节点
     * @var bool
     */
    protected $_is_root = false;

    /**
     * 别名
     * @var string
     */
    protected $_alias_name = '';


    /**
     * 添加父节点
     * @param XmlNode $node
     * @return $this
     */
    protected function addParentNode(XmlNode $node)
    {
        $this->_parentNodes[] = $node;
        return $this;
    }

    /**
     * 添加子节点
     * @param XmlNode $node
     * @return $this
     */
    protected function addSubNode(XmlNode $node)
    {
        $this->_subNodes[] = $node;
        return $this;
    }


    public function __construct(\DOMElement $nodeElement, XmlTools $tools, $aliasName = '')
    {
        $this->nodeElement = $nodeElement;
        $this->tools = $tools;
        $this->_alias_name = $aliasName;
    }

    /**
     * 判断是否为根节点
     * @return bool
     */
    public function isRoot()
    {
        return $this->_is_root;
    }


    /**
     * 设置为根节点
     * @return $this
     * @throws \Exception
     */
    public function rootNode()
    {
        $this->_is_root = true;
        $nodeName = $this->_alias_name ? $this->_alias_name : $this->nodeElement->nodeName;
        $this->tools->addRootNode($nodeName)->removeAbnormalNode($nodeName)->getNode()->appendChild($this->nodeElement);
        return $this;
    }

    /**
     * 设置节点父节点
     * @param null $nodeName
     * @return XmlNode
     * @throws \Exception
     */
    public function parentNode($nodeName = null)
    {
        $this->tools->removeAbnormalNode($this->_alias_name ? $this->_alias_name : $this->nodeElement->nodeName)
            ->getNode($nodeName)
            ->appendChild($this->nodeElement);

        $parentNode = $this->tools->getXmlNode($nodeName);
        $subNode = $this->tools->getXmlNode($this->_alias_name ? $this->_alias_name : $this->nodeElement->nodeName);
        $parentNode->addSubNode($subNode);
        $subNode->addParentNode($parentNode);
        return $this->tools->getXmlNode($nodeName);
    }

    /**
     * 设置节点元素属性
     * @param array $attributes
     * @return $this
     * @throws \Exception
     */
    public function attributes(array $attributes)
    {
        foreach ($attributes as $attrName => $attrValue) {
            $attr = $this->tools->getNode()->createAttribute($attrName);
            $attrV = $this->tools->getNode()->createTextNode($attrValue);
            $attr->appendChild($attrV);
            $this->nodeElement->appendChild($attr);
        }
        return $this;
    }

    /**
     * 添加子节点
     * @param $childNode
     * @return $this
     * @throws \Exception
     */
    public function appendChild($childNode)
    {
        $childNodeElement = $childNode instanceof \DOMElement ? $childNode
            : ($childNode instanceof XmlNode ? $childNode->getElement()
                : $this->tools->getNode($childNode));

        $childNodeName = $childNode instanceof \DOMDocument ? $childNode->nodeName
            : ($childNode instanceof XmlNode ? $childNode->getIndex()
                : $childNode);

        $this->nodeElement->appendChild($childNodeElement);

        $parentNode = $this->tools->getXmlNode($this->_alias_name ? $this->_alias_name : $this->nodeElement->nodeName);
        $subNode = $this->tools->getXmlNode($childNodeName);
        $parentNode->addSubNode($subNode);
        $subNode->addParentNode($parentNode);
        return $this;
    }


    /**
     * 设置节点的值
     * @param $value
     * @return $this
     */
    public function nodeValue($value)
    {
        $this->nodeElement->nodeValue = $value;
        return $this;
    }

    /**
     * 创建子元素
     * @param $nodeName
     * @param string $value
     * @param string $aliasName
     * @return XmlNode
     * @throws \Exception
     */
    public function createChildNode($nodeName, $value = '', $aliasName = '')
    {
        return $this->tools->createNode($nodeName, $value, $aliasName)->parentNode($this->_alias_name ? $this->_alias_name : $this->nodeElement->nodeName);
    }

    /**
     * 创建元素
     * @param $nodeName
     * @param string $value
     * @param string $aliasName
     * @return XmlNode
     * @throws \Exception
     */
    public function createNode($nodeName, $value = '', $aliasName = '')
    {
        return $this->tools->createNode($nodeName, $value, $aliasName);
    }

    /**
     * 设置别名
     * @param $aliasName
     * @return $this
     */
    public function aliasName($aliasName)
    {
        $this->tools->aliasName($aliasName);
        return $this;
    }


    /**
     * 获取当前节点元素
     * @return \DOMElement|null
     */
    public function getElement()
    {
        return $this->nodeElement;
    }


    /**
     * 获取别名
     * @return string
     */
    public function getAliasName()
    {
        return $this->_alias_name;
    }

    /**
     * 获取索引值
     * @return string
     */
    public function getIndex()
    {
        return $this->_alias_name ? $this->_alias_name : $this->nodeElement->nodeName;
    }
}