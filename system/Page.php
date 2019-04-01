<?php
/**
 * 分页
 * @author Colin <15070091894@163.com>
 */

namespace system;
class Page {
    private $total;                    //总记录
    private $pagesize;                //每页显示多少条
    private $limit;                    //LIMIT
    private $page;                    //当前页码
    private $pagenum;                //总页数
    private $url;                    //页面地址
    private $bothnum;                //两边数字保留的量
    private $page_url;                //整体URL
    private $url_model;                //URL模式

    /**
     * 构造方法
     * @author Colin <15070091894@163.com>
     */
    public function __construct($total, $pagesize) {
        $this->total     = $total ? $total : 1;
        $this->pagesize  = $pagesize;
        $this->pagenum   = ceil($this->total / $this->pagesize);
        $this->url_model = Config('URL_MODEL');
        //处理url
        $this->page_url = '?page=%d';
        $this->page     = $this->setPage();
        $this->limit    = "LIMIT " . ($this->page - 1) * $this->pagesize . ",$this->pagesize";
        $this->url      = $this->setUrl();
        $this->bothnum  = 2;
    }

    /**
     * 拦截器
     * @author Colin <15070091894@163.com>
     */
    public function __get($key) {
        return $this->$key;
    }

    /**
     * 获取当前页面 setPage()
     * @author Colin <15070091894@163.com>
     */
    private function setPage() {
        //排除page=0 或page为空
        $page = values('get', 'page');
        if (!empty($page)) {
            //排除负数 和 非法字符
            if ($page > 0) {
                //排除比总页数大的
                if ($page > $this->pagenum) {
                    return $this->pagenum;
                } else {
                    return $page;
                }
            } else {
                return 1;
            }
        } else {
            return 1;
        }
    }

    /**
     * 智能获取地址
     * @author Colin <15070091894@163.com>
     */
    private function setUrl() {
        return Url::parseUrl();
    }

    /**
     * 数字分页
     * @author Colin <15070091894@163.com>
     */
    private function pageList() {
        $_pagelist = '';
        //这一块还需好好理解
        for ($i = $this->bothnum; $i >= 1; $i--) {
            $_page = $this->page - $i;
            if ($_page < 1) continue;
            $_pagelist .= '<li><a href="' . $this->url . sprintf($this->page_url, $_page) . '">' . $_page . '</a></li>';
        }
        $_pagelist .= '<li class="active"><a href="javascript:void(0);">' . $this->page . '</a></li>';
        for ($i = 1; $i <= $this->bothnum; $i++) {
            $_page = $this->page + $i;
            if ($_page > $this->pagenum) break;
            $_pagelist .= '<li><a href="' . $this->url . sprintf($this->page_url, $_page) . '">' . $_page . '</a></li>';
        }

        return $_pagelist;
    }

    /**
     * 首页
     * @author Colin <15070091894@163.com>
     */
    private function first() {
        //如果当前页码 > 两边分页保留量+1
        if ($this->page > $this->bothnum + 1) {
            return ' <a href="' . $this->url . '">1</a> ...';
        }
    }

    /**
     * 上一页
     * @author Colin <15070091894@163.com>
     */
    private function prev() {
        if ($this->page == 1) {
            return '<span class="disabled">上一页</span>';
        }

        return ' <a href="' . $this->url . sprintf($this->page_url, ($this->page - 1)) . '">上一页</a> ';
    }

    /**
     * 下一页
     * @author Colin <15070091894@163.com>
     */
    private function next() {
        if ($this->page == $this->pagenum) {
            return '<span class="disabled">下一页</span>';
        }

        return ' <a href="' . $this->url . sprintf($this->page_url, ($this->page + 1)) . '">下一页</a> ';
    }

    /**
     * 尾页
     * @author Colin <15070091894@163.com>
     */
    private function last() {
        //如果总页码-当前页码 > 两边保持的两边分页保留量
        if ($this->pagenum - $this->page > $this->bothnum) {
            return ' ...<a href="' . $this->url . sprintf($this->page_url, $this->pagenum) . '">' . $this->pagenum . '</a> ';
        }
    }

    /**
     * 对外公开的方法。分页信息
     * @author Colin <15070091894@163.com>
     */
    public function showpage() {
        $_page = '<ul class="pagination">';
        // $_page .= $this->first();
        $_page .= $this->pageList();
        // $_page .= $this->last();
        // $_page .= $this->prev();
        // $_page .= $this->next();
        $_page .= '</ul>';

        return $_page;
    }
}