# AISummary
用于 MediaWiki 的一个插件，可以在页面历史、最近更改、用户贡献列表等地方，显示由 AI 生成的摘要信息

## css示例
这是一个简单的 CSS 示例，可以直接复制到 MediaWiki 的 `MediaWiki:Common.css` 页面中使用
```css
/* AI摘要显示 */

.mw-ai-summary {
    display: inline-block;
    margin: 2px 4px;
    padding: 2px 8px;
    font-size: 0.85em;
    line-height: 1.3;
    color: #ffffffff;
    background: #5b9ad5a2;
    border-radius: 8px;
    box-shadow: 0 1px 1.5px rgba(0,0,0,.14);
    transition: box-shadow .3s ease, background .3s ease;
    /* 提示 */
    position: relative;
    cursor: help;   /* 问号指针 */
}
.mw-ai-summary:hover {
    background: #4473c4c4;
    box-shadow: 0 3px 4px rgba(0,0,0,.2);
}
.mw-ai-summary:empty {
    display: none !important;
}

/* 提示显示 */
.mw-ai-summary::after {
    content: "此摘要为 AI 生成";
    position: absolute;
    left: 0;
    top: 100%;                   /* 紧跟在元素下方 */
    margin-top: 4px;
    white-space: nowrap;         /* 不换行 */
    padding: 4px 8px;
    font-size: 0.8em;
    color: #ffffffff;
    box-shadow: 0 1px 1.5px rgba(0,0,0,.14);
    background: #4473c460;
    border-radius: 4px;
    opacity: 0;
    pointer-events: none;
    transition: opacity .2s ease;
    z-index: 9999;
}

/* 悬停时显出来 */
.mw-ai-summary:hover::after {
    opacity: 1;
}
```