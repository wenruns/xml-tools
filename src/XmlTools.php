<?php
/**
 * xml生成工具
 * Created by PhpStorm.
 * User: wenruns
 * Date: 2020/1/3
 * Time: 10:40
 */

namespace Wenruns\XmlTools;

class XmlTools
{
    /**
     * DOMDocument对象集合
     * @var array
     */
    protected $_dom = [];

    /**
     * 所有节点集合
     * @var array
     */
    protected $_nodes = [];

    /**
     * 别名
     * @var string
     */
    protected $_aliasName = '';

    /**
     * DOMDocument版本号
     * @var string
     */
    protected $_version = '1.0';

    /**
     * DOMDocument字符集
     * @var string
     */
    protected $_encoding = 'utf-8';

    /**
     * DOM自增索引
     * @var int
     */
    protected $_dom_index = 1;

    /**
     * 活动的DOM
     * @var string
     */
    protected $_dom_active = '';


    /**
     * 根节点
     * @var array
     */
    protected $_root_nodes = [];

    /**
     * 异常节点
     * @var array
     */
    protected $_abnormal_nodes = [];


    /**
     * 输出xml文件
     * @param $file
     * @param string $domIndex
     * @return mixed
     * @throws \Exception
     */
    public function output($file, $domIndex = '')
    {
        $domIndex = $this->isEmptyDomIndex($domIndex);
        return $this->_dom[$domIndex]->save($file);
    }


    public function __construct($version = '1.0', $encoding = 'utf-8')
    {
        $this->_version = $version;
        $this->_encoding = $encoding;
    }

    /**
     * 生成DOMDocument对象
     * @param string $domName
     * @param string $version
     * @param string $encoding
     * @return $this
     * @throws \Exception
     */
    public function createDOMDocument($domName = '', $version = '', $encoding = '')
    {
        if ($domName && isset($this->_dom[$domName])) {
            throw new \Exception('不可创建重复的DOMDocument对象，' . $domName . '已存在！');
        }

        $this->createDOM($domName, $version, $encoding);
        return $this;
    }

    /**
     * 创建DOMDocument对象
     * @param $index
     * @param $version
     * @param $encoding
     */
    protected function createDOM($index, $version, $encoding)
    {
        empty($index) && $index = $this->_dom_index;
        if (isset($this->_dom[$index])) {
            $this->_dom_index++;
            $this->createDOM('', $version, $encoding);
            return;
        }
        empty($version) && $version = $this->_version;
        empty($encoding) && $encoding = $this->_encoding;
        $this->_dom_active = $index;
        $this->_dom[$index] = new \DOMDocument($version, $encoding);
        $index == $this->_dom_index && $this->_dom_index++;
    }

    /**
     * 判断是否存在指定的DOMDocument对象
     * @param $domIndex
     * @return bool
     */
    public function isExistDOM($domIndex)
    {
        return isset($this->_dom[$domIndex]);
    }


    /**
     * 获取活动的DOMDocument对象
     * @return \DOMDocument
     * @throws \Exception
     */
    public function getActiveDOM()
    {
        if (empty($this->_dom_active)) {
            $keys = array_keys($this->_dom);
            if (empty($keys)) {
                $this->createDOMDocument();
            } else {
                $this->_dom_active = $keys[0];
            }
        }
        return $this->_dom[$this->_dom_active];
    }

    /**
     * 获取指定的DOMDocument对象
     * @param $index
     * @return mixed
     */
    public function getDOMDocument($index)
    {
        return $this->_dom[$index];
    }

    /**
     * 获取全部的DOMDocument对象
     * @return array
     */
    public function getDOMDocuments()
    {
        return $this->_dom;
    }

    /**
     * 处理DOMDocument对象，生成xml文件等等
     * @param \Closure $callback
     * @return array
     */
    public function dealWithXml(\Closure $callback)
    {
        $res = [];
        foreach ($this->getDOMDocuments() as $key => $dom) {
            $res[$key] = call_user_func($callback, $dom, $key, $this);
        }
        return $res;
    }


    /**
     * 切换DOMDocument对象
     * @param $domName
     * @return $this
     * @throws \Exception
     */
    public function switchDOM($domName)
    {
        if (isset($this->_dom[$domName])) {
            $this->_dom_active = $domName;
            return $this;
        }
        throw new \Exception('Can not found the DOMDocument object by given name:“' . $domName . '”');
    }

    /**
     * 删除DOMDocument对象
     * @param string $index
     * @return $this
     * @throws \Exception
     */
    public function deleteDOM($index = '')
    {
        if (empty($index)) {
            $this->_dom = [];
            $this->_dom_active = '';
            return $this;
        }
        if (!isset($this->_dom[$index])) {
            throw new \Exception('Not allowed to delete non-existing objects : ' . $index);
        }
        unset($this->_nodes[$index]);
        unset($this->_dom[$index]);

        if ($this->_dom_active == $index) {
            $keys = array_keys($this->_dom);
            empty($keys) ? $this->_dom_active = '' : $this->_dom_active = $keys[0];
        }
        return $this;
    }


    /**
     * 保存根节点
     * @param $index
     * @return $this
     * @throws \Exception
     */
    public function addRootNode($index)
    {
        if (isset($this->_root_nodes[$this->_dom_active]) && $this->_root_nodes[$this->_dom_active]) {
            throw new \Exception('同一个xml文档不应该有两个根节点');
        }
        $this->_root_nodes[$this->_dom_active] = $index;
        return $this;
    }

    /**
     * 获取根节点
     * @param string $dom
     * @return array|mixed
     */
    public function getRootNodes($dom = '')
    {
        return $dom ? $this->_root_nodes[$dom] : $this->_root_nodes;
    }

    /**
     * 异常节点，指没有挂载在DOMDocument对象的节点
     * @param $index
     * @return $this
     */
    public function removeAbnormalNode($index)
    {
        $key = array_search($index, $this->_abnormal_nodes[$this->_dom_active]);
        unset($this->_abnormal_nodes[$this->_dom_active][$key]);
        return $this;
    }

    /**
     * 获取异常节点名称
     * @param string $dom
     * @return mixed
     */
    public function getAbnormalNodes($dom = '')
    {
        return $dom ? $this->_abnormal_nodes[$dom] : $this->_abnormal_nodes;
    }

    /**
     * 判断是否存在异常节点
     * @param string $domName
     * @return bool
     */
    public function existAbnormalNodes($domName = '')
    {
        if ($domName) {
            return !empty($this->_abnormal_nodes[$domName]);
        } else {
            foreach ($this->_abnormal_nodes as $key => $item) {
                if (!empty($item)) {
                    return true;
                }
            }
            return false;
        }
    }


    /**
     * 创建Node节点
     * @param $nodeName
     * @param string $value
     * @param string $aliasName
     * @return XmlNode
     * @throws \Exception
     */
    public function createNode($nodeName, $value = '', $aliasName = ''): XmlNode
    {
        empty($aliasName) && $aliasName = $this->_aliasName;

        $this->_aliasName = '';
        $index = $aliasName ? $aliasName : $nodeName;

        // 默认为异常节点
        $this->_abnormal_nodes[$this->_dom_active][] = $index;

        $nodeObject = $this->getActiveDOM()->createElement($nodeName, is_callable($value) ? '' : $value);
        $node = new XmlNode($nodeObject, $this, $aliasName);
        $this->_nodes[$this->_dom_active][$index] = $node;
        if (is_callable($value)) {
            call_user_func($value, $node);
        }
        return $node;
    }


    /**
     * 为节点设置别名，区分两个相同名称的节点使用
     * 必须在createNode方法之前使用
     * @param $aliasName
     * @return $this
     */
    public function aliasName($aliasName)
    {
        $this->_aliasName = $aliasName;
        return $this;
    }


    /**
     * 获取节点
     * @param null $nodeName
     * @param string $domIndex
     * @param bool $isRoot
     * @return \DOMElement|mixed|null
     * @throws \Exception
     */
    public function getNode($nodeName = null, $domIndex = '', $isRoot = false)
    {
        $domIndex = $this->isEmptyDomIndex($domIndex);
        if ($nodeName == null) {
            return $this->_dom[$domIndex];
        }

        if (isset($this->_nodes[$domIndex][$nodeName])) {
            return $this->_nodes[$domIndex][$nodeName]->getElement();
        }

        return $this->createNode($nodeName)->getElement();
    }

    /**
     * 获取Xml节点
     * @param null $nodeName
     * @param string $domIndex
     * @return XmlNode
     * @throws \Exception
     */
    public function getXmlNode($nodeName = null, $domIndex = '')
    {
        $domIndex = $this->isEmptyDomIndex($domIndex);
        if ($nodeName == null) {
            return $this->_dom[$domIndex];
        }
        if (isset($this->_nodes[$domIndex][$nodeName])) {
            return $this->_nodes[$domIndex][$nodeName];
        }
        throw  new \Exception('Can not found the nodeElement : "' . $nodeName . '".');
    }


    /**
     * 获取所有节点
     * @return array
     */
    public function getNodes()
    {
        return $this->_nodes;
    }


    /**
     * 删除节点
     * @param $nodeName
     * @param string $domIndex
     * @return $this
     * @throws \Exception
     */
    public function deleteNode($nodeName, $domIndex = '')
    {
        $domIndex = $this->isEmptyDomIndex($domIndex);

        $this->_dom[$domIndex]->removeChild($this->getNode($nodeName, $domIndex));
        unset($this->_nodes[$domIndex][$nodeName]);
        return $this;
    }

    /**
     * 判断某个节点是否存在
     * @param $nodeName
     * @param string $domIndex
     * @return bool
     * @throws \Exception
     */
    public function nodeIsExist($nodeName, $domIndex = '')
    {
        $domIndex = $this->isEmptyDomIndex($domIndex);

        return $this->_dom[$domIndex]->getElementsByTagName($nodeName)->count() > 0;
    }


    /**
     * 判断是否创建过某节点
     * @param $aliasName
     * @param string $domIndex
     * @return bool
     * @throws \Exception
     */
    public function aliasIsExist($aliasName, $domIndex = '')
    {
        $domIndex = $this->isEmptyDomIndex($domIndex);

        return isset($this->_nodes[$domIndex][$aliasName]);
    }


    /**
     * 检测是否指定DOMDocument对象 或者 是否存在活动的DOMDocument对象
     * @param $domIndex
     * @return string
     * @throws \Exception
     */
    protected function isEmptyDomIndex($domIndex)
    {
        empty($domIndex) && $domIndex = $this->_dom_active;
        if (empty($domIndex)) {
            throw new \Exception('There is no active DOMDocument object.');
        }
        if (!isset($this->_dom[$domIndex])) {
            throw new \Exception('The specified DOMDocument object does not exist : ' . $domIndex);
        }
        return $domIndex;
    }

}