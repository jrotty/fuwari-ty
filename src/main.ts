import "./styles/variables.styl";
import "./styles/tailwind.css";

import "./styles/main.css";

import "./styles/markdown-extend.styl";
import "./styles/back-to-top.styl";
import "./styles/scrollbar.css";
import "./styles/transition.css";
import "./styles/markdown.css";
import "./styles/photoswipe.css";
import "./styles/comment.css";

import "overlayscrollbars/overlayscrollbars.css";
import "photoswipe/style.css";

import hljs from "highlight.js";
import "highlight.js/styles/github.css";

import Alpine from "alpinejs";
import Swup from "swup";
import SwupHeadPlugin from "@swup/head-plugin";
import SwupPreloadPlugin from "@swup/preload-plugin";
import SwupScrollPlugin from "@swup/scroll-plugin";
import SwupScriptsPlugin from "@swup/scripts-plugin";

import { mountSearch, mountDisplaySettings, mountToc, clearToc } from "./preact";
import { mountComments } from "./comments";
import {
  OverlayScrollbars,
  // ScrollbarsHidingPlugin,
  // SizeObserverPlugin,
  // ClickScrollPlugin
} from "overlayscrollbars";
import PhotoSwipeLightbox from "photoswipe/lightbox";

import { getHue, setHue } from "./utils/setting-utils";
import { loadButtonScript } from "./widgets/navbar";
import { setClickOutsideToClose } from "./utils/base-utils";
import dropdown from "./alpine-data/dropdown";
import colorSchemeSwitcher from "./alpine-data/color-scheme-switcher";
import share from "./alpine-data/share";
import articleStats from "./alpine-data/article-stats";


import type { ThemeConfig, LIGHT_DARK_MODE } from "./types/config";
import {
  BANNER_HEIGHT,
  BANNER_HEIGHT_EXTEND,
  BANNER_HEIGHT_HOME,
  MAIN_PANEL_OVERLAPS_BANNER_HEIGHT,
} from "./constants/constants";

import { mountCounter } from "./preact";

window.Alpine = Alpine;

// 将主要函数暴露到全局 window.fuwari 对象中以便模板调用
window.fuwari = {
  setColorScheme,
  getCurrentColorScheme,
};
const swup = new Swup({
  animationSelector: '[class*="transition-swup-"]',
  containers: ["main"],
  plugins: [
    new SwupHeadPlugin({ persistAssets: true }),
    new SwupPreloadPlugin(),
    new SwupScrollPlugin(),
    new SwupScriptsPlugin({
      head: false,
      body: true,
    }),
  ],
});
Alpine.data("dropdown", dropdown);
Alpine.data("colorSchemeSwitcher", colorSchemeSwitcher);
Alpine.data("share", share);
Alpine.data("articleStats", articleStats);
Alpine.start();

function getThemeConfig(): ThemeConfig | undefined {
  const el = document.querySelector<HTMLScriptElement>("#theme-config");
  if (!el?.textContent) return undefined;

  try {
    return JSON.parse(el.textContent) as ThemeConfig;
  } catch (e) {
    console.error("解析 theme-config 失败:", e);
    return undefined;
  }
}

// 使用
const themeConfig = getThemeConfig();
console.log("主题配置：", themeConfig);

// 按当前页是否含文章正文（#content）同步侧栏目录：
// - 有 #content（文章）：显示 #toc-wrapper 并重挂一次目录（清空旧 headings，杜绝"窜台"）。
// - 无 #content（首页/分类/标签/归档）：卸载目录并隐藏 wrapper。
// #toc-wrapper 在 swup 的 main 容器之外，切页不会自动销毁，必须在这里显式管理生命周期。
function updateToc() {
  const wrapper = document.getElementById("toc-wrapper");
  const tocContainer = document.querySelector(".toc");
  if (!wrapper) return;
  const hasContent = !!document.getElementById("content");
  wrapper.style.display = hasContent ? "" : "none";
  if (tocContainer) {
    if (hasContent) {
      mountToc(tocContainer as HTMLElement);
    } else {
      clearToc(tocContainer as HTMLElement);
    }
  }
}

function mountWidgets() {
  console.log("Mounting widgets...");
  const counterContainer = document.querySelector("#counter");
  if (counterContainer) {
    mountCounter(counterContainer as HTMLElement);
  }
  //   挂载搜索框
  const searchContainer = document.querySelector("#search");
  if (searchContainer) {
    mountSearch(searchContainer as HTMLElement);
  }
  //   挂载主题色设置
  const displaySettingsContainer = document.querySelector("#display-setting");
  if (displaySettingsContainer) {
    mountDisplaySettings(displaySettingsContainer as HTMLElement);
  }
  //   挂载目录（结构上把挂载交给 updateToc，避免与初始逻辑分叉）
  updateToc();
  //   挂载评论区
  mountComments();
}

// 初始化 admonition 笔记块（输出结构匹配原版 rehype-component-admonition）
function initNoteBlocks() {
  const blockquotes = document.querySelectorAll("blockquote");
  const typeMap: Record<string, { className: string; title: string }> = {
    "[!NOTE]": { className: "bdm-note", title: "NOTE" },
    "[!TIP]": { className: "bdm-tip", title: "TIP" },
    "[!IMPORTANT]": { className: "bdm-important", title: "IMPORTANT" },
    "[!WARNING]": { className: "bdm-warning", title: "WARNING" },
    "[!CAUTION]": { className: "bdm-caution", title: "CAUTION" },
  };

  blockquotes.forEach(function (blockquote) {
    const paragraphs = blockquote.querySelectorAll("p");
    if (paragraphs.length === 0) return;

    const firstP = paragraphs[0] as HTMLElement;
    const firstText = firstP.textContent?.trim() || "";

    for (const [prefix, config] of Object.entries(typeMap)) {
      if (!firstText.startsWith(prefix)) continue;

      blockquote.classList.add("admonition", config.className);

      // 从第一个段落移除前缀
      firstP.innerHTML = firstP.innerHTML.replace(
        new RegExp(prefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + "\\s*"), ""
      );

      // 创建 <span class="bdm-title"> 插入到第一个段落之前
      const titleSpan = document.createElement("span");
      titleSpan.className = "bdm-title";
      titleSpan.textContent = config.title;
      blockquote.insertBefore(titleSpan, firstP);

      break;
    }
  });
}

// 单个图标的方形按钮
function makeIconButton(className: string, ariaLabel: string, path: string) {
  const btn = document.createElement("button");
  btn.className = className;
  btn.setAttribute("aria-label", ariaLabel);
  btn.innerHTML =
    '<svg class="ctrl-icon" viewBox="0 -960 960 960" xmlns="http://www.w3.org/2000/svg"><path d="' +
    path +
    '"/></svg>';
  return btn;
}

const ICON_COPY = "M368.37-237.37q-34.48 0-58.74-24.26-24.26-24.26-24.26-58.74v-474.26q0-34.48 24.26-58.74 24.26-24.26 58.74-24.26h378.26q34.48 0 58.74 24.26 24.26 24.26 24.26 58.74v474.26q0 34.48-24.26 58.74-24.26 24.26-58.74 24.26H368.37Zm0-83h378.26v-474.26H368.37v474.26Zm-155 238q-34.48 0-58.74-24.26-24.26-24.26-24.26-58.74v-515.76q0-17.45 11.96-29.48 11.97-12.02 29.33-12.02t29.54 12.02q12.17 12.03 12.17 29.48v515.76h419.76q17.45 0 29.48 11.96 12.02 11.97 12.02 29.33t-12.02 29.54q-12.03 12.17-29.48 12.17H213.37Zm155-238v-474.26 474.26Z";
const ICON_CHECK =
  "m389-377.13 294.7-294.7q12.58-12.67 29.52-12.67 16.93 0 29.61 12.67 12.67 12.68 12.67 29.53 0 16.86-12.28 29.14L419.07-288.41q-12.59 12.67-29.52 12.67-16.94 0-29.62-12.67L217.41-430.93q-12.67-12.68-12.79-29.45-.12-16.77 12.55-29.45 12.68-12.67 29.62-12.67 16.93 0 29.28 12.67L389-377.13Z";
const ICON_CHEVRON_UP = "m480-360 160-160H320l160 160Z";
const ICON_CHEVRON_DOWN = "m480-600 160-160H320l160 160Z";

// 为代码块包一层外框：右上角默认显示语言标签，悬停改显复制 + 收起/展开；≥20 行默认折叠。
function decorateCodeBlocks() {
  document.querySelectorAll(".custom-md pre").forEach(function (pre) {
    if (pre.closest(".md-code-frame")) return;

    const code = pre.querySelector<HTMLElement>("code");
    if (!code) return;

    // 语言标签：hljs 加的 language-X / lang-X
    const lang = (code.className.match(/(?:lang|language)-([^\s]+)/) || [])[1] || "";

    // 行数（wrapCodeLines 已生成 .line）：≥20 行视为长块，默认折叠
    const lineCount = code.querySelectorAll("span.line").length;
    const isLong = lineCount >= 20;

    // 外框
    const frame = document.createElement("div");
    frame.className = "md-code-frame";
    if (isLong) frame.classList.add("collapsible", "collapsed");

    // 语言标签（默认显示）
    const langLabel = document.createElement("span");
    langLabel.className = "md-code-lang";
    langLabel.textContent = lang || "text";

    // 复制按钮（单图标；成功时换对勾）
    const copyBtn = makeIconButton("copy-btn", "Copy", ICON_COPY);
    copyBtn.addEventListener("click", function () {
      const lines = code.querySelectorAll(".line");
      const text = lines.length
        ? Array.from(lines)
            .map((l) => (l as HTMLElement).innerText || (l as HTMLElement).textContent || "")
            .join("\n")
            .replace(/\n+$/, "")
        : code.textContent || "";
      navigator.clipboard.writeText(text).then(function () {
        copyBtn.classList.add("success");
        copyBtn.querySelector(".ctrl-icon path")?.setAttribute("d", ICON_CHECK);
        setTimeout(function () {
          copyBtn.classList.remove("success");
          copyBtn.querySelector(".ctrl-icon path")?.setAttribute("d", ICON_COPY);
        }, 2000);
      });
    });

    frame.appendChild(langLabel);
    frame.appendChild(copyBtn);

    // 收起/展开按钮（长块才有）
    if (isLong) {
      const collapseBtn = makeIconButton("collapse-toggle", "Collapse", ICON_CHEVRON_UP);
      collapseBtn.addEventListener("click", function () {
        const collapsed = frame.classList.toggle("collapsed");
        collapseBtn.querySelector(".ctrl-icon path")?.setAttribute(
          "d",
          collapsed ? ICON_CHEVRON_UP : ICON_CHEVRON_DOWN,
        );
        collapseBtn.setAttribute("aria-label", collapsed ? "Expand" : "Collapse");
      });
      frame.appendChild(collapseBtn);
    }

    // 把 pre 移进外框，控件在最前
    pre.parentNode?.insertBefore(frame, pre);
    frame.appendChild(pre);
  });
}

// 将 hljs 已高亮的 <code> 按行包成 <span class="line">（保留 token span，跨行 token 按行复制）
function wrapCodeLines(code: HTMLElement) {
  if (code.querySelector("span.line")) return;

  const lineArrays: (Node[])[] = [];
  let lineIdx = 0;

  // 元素：返回其内容按行拆分后的分片数组（每行一个浅克隆，跨行 token 被复制到多行）
  function splitElement(el: Element): Node[][] {
    if (el.childNodes.length === 0) return [[]];
    let base = 0;
    const chunks: Node[][] = [];
    for (const child of Array.from(el.childNodes)) {
      let cc: Node[][];
      if (child.nodeType === Node.TEXT_NODE) {
        const parts = (child.textContent || "").split("\n");
        cc = parts.map((t) => [document.createTextNode(t)] as Node[]);
      } else {
        cc = splitElement(child as Element);
      }
      cc.forEach((chunk, i) => {
        const g = base + i;
        (chunks[g] = chunks[g] || []).push(...chunk);
      });
      // 只按实际换行数推进（无换行的文本/子元素仍停留在当前行）
      base += Math.max(cc.length - 1, 0);
    }
    return chunks.map((chunk): Node[] => {
      const clone = el.cloneNode(false) as HTMLElement;
      if (chunk.length) clone.append(...chunk);
      return [clone];
    });
  }

  function put(g: number, nodes: Node[]) {
    (lineArrays[g] = lineArrays[g] || []);
    lineArrays[g].push(...nodes);
  }

  function walk(node: Node) {
    if (node.nodeType === Node.TEXT_NODE) {
      const parts = (node.textContent || "").split("\n");
      parts.forEach((t, i) => {
        if (t) put(lineIdx + i, [document.createTextNode(t)]);
      });
      lineIdx += Math.max(parts.length - 1, 0);
    } else if (node.nodeType === Node.ELEMENT_NODE) {
      const chunks = splitElement(node as Element);
      chunks.forEach((chunk, i) => put(lineIdx + i, chunk));
      lineIdx += Math.max(chunks.length - 1, 0);
    }
  }

  Array.from(code.childNodes).forEach(walk);

  code.innerHTML = "";
  const frag = document.createDocumentFragment();
  lineArrays.forEach((nodes) => {
    const span = document.createElement("span");
    span.className = "line";
    if (nodes.length) span.append(...nodes);
    frag.appendChild(span);
  });
  code.appendChild(frag);
}

// 为标题注入 id 和 anchor 链接（匹配 rehypeSlug + rehypeAutolinkHeadings 效果）
function initHeadingAnchors() {
  document.querySelectorAll(".custom-md h1, .custom-md h2, .custom-md h3, .custom-md h4, .custom-md h5, .custom-md h6").forEach(function (h) {
    const heading = h as HTMLElement;
    if (!heading.id) {
      heading.id = heading.textContent
        ?.toLowerCase()
        .replace(/[^a-z0-9\u4e00-\u9fa5]+/g, "-")
        .replace(/(^-|-$)/g, "") || "";
    }
    if (!heading.querySelector(".anchor")) {
      const a = document.createElement("a");
      a.className = "anchor";
      a.href = "#" + heading.id;
      a.innerHTML = '<span class="anchor-icon">#</span>';
      heading.appendChild(a);
    }
  });
}

let currentColorScheme: LIGHT_DARK_MODE = "auto";

export function initColorScheme(defaultColorScheme: LIGHT_DARK_MODE, enableChangeColorScheme: boolean) {
  let colorScheme = defaultColorScheme;

  if (enableChangeColorScheme) {
    colorScheme = (localStorage.getItem("color-scheme-fuwari") as LIGHT_DARK_MODE) || defaultColorScheme;
  }

  currentColorScheme = colorScheme;

  setColorScheme(colorScheme, true);
}

export function setColorScheme(colorScheme: LIGHT_DARK_MODE, store: boolean) {
  if (colorScheme === "auto") {
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
    document.documentElement.classList.add(prefersDark ? "dark" : "light");
    document.documentElement.classList.remove(prefersDark ? "light" : "dark");
  } else {
    document.documentElement.classList.add(colorScheme);
    document.documentElement.classList.remove(colorScheme === "dark" ? "light" : "dark");
  }
  currentColorScheme = colorScheme;
  if (store) {
    localStorage.setItem("color-scheme-fuwari", colorScheme);
  }
}

export function getCurrentColorScheme(): LIGHT_DARK_MODE {
  return currentColorScheme;
}

window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", function () {
  if (currentColorScheme === "auto") {
    setColorScheme("auto", false);
  }
});

let bannerEnabled = !!document.getElementById("banner-wrapper");

function loadHue() {
  setHue(getHue());
}

function initCustomScrollbar() {
  const bodyElement = document.querySelector("body");
  if (!bodyElement) return;
  OverlayScrollbars(
    // docs say that an initialization to the body element would affect native functionality like window.scrollTo
    // but just leave it here for now
    {
      target: bodyElement,
      cancel: {
        nativeScrollbarsOverlaid: true, // don't initialize the overlay scrollbar if there is a native one
      },
    },
    {
      scrollbars: {
        theme: "scrollbar-base scrollbar-auto py-1",
        autoHide: "move",
        autoHideDelay: 500,
        autoHideSuspend: false,
      },
    },
  );

  const katexElements = document.querySelectorAll(".katex-display") as NodeListOf<HTMLElement>;

  const katexObserverOptions = {
    root: null,
    rootMargin: "100px",
    threshold: 0.1,
  };

  const processKatexElement = (element: HTMLElement) => {
    if (!element.parentNode) return;
    if (element.hasAttribute("data-scrollbar-initialized")) return;

    const container = document.createElement("div");
    container.className = "katex-display-container";
    container.setAttribute("aria-label", "scrollable container for formulas");

    element.parentNode.insertBefore(container, element);
    container.appendChild(element);

    OverlayScrollbars(container, {
      scrollbars: {
        theme: "scrollbar-base scrollbar-auto",
        autoHide: "leave",
        autoHideDelay: 500,
        autoHideSuspend: false,
      },
    });

    element.setAttribute("data-scrollbar-initialized", "true");
  };

  const katexObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        processKatexElement(entry.target as HTMLElement);
        observer.unobserve(entry.target);
      }
    });
  }, katexObserverOptions);

  katexElements.forEach((element) => {
    katexObserver.observe(element);
  });
}
function showBanner() {
  if (!themeConfig?.base.banner.enable) return;
  // 全屏模式：壁纸常显，无需入场动画
  if (document.body.classList.contains("banner-fullscreen")) return;

  const banner = document.getElementById("banner");
  if (!banner) {
    console.error("Banner element not found");
    return;
  }

  banner.classList.remove("opacity-0", "scale-105");
}

function init() {
  // disableAnimation()()		// TODO
  initColorScheme(
    (themeConfig?.style.color_scheme as LIGHT_DARK_MODE) ?? 'auto',
    (themeConfig?.style.enable_change_color_scheme as boolean) ?? true,
  );
  loadHue();
  initCustomScrollbar();
  initNoteBlocks();
  initCodeHighlight();
  initHeadingAnchors();
  showBanner();
}

function initCodeHighlight() {
  document.querySelectorAll(".custom-md pre code").forEach((block) => {
    if (!(block instanceof HTMLElement)) return;
    if (block.classList.contains("hljs")) return; // already highlighted
    hljs.highlightElement(block);
  });
  document.querySelectorAll(".custom-md pre code").forEach((block) => {
    if (block instanceof HTMLElement) wrapCodeLines(block);
  });
  decorateCodeBlocks();
}
/* Load settings when entering the site */

// 初始化 Swup
const setup = () => {
  // TODO: temp solution to change the height of the banner
  /*
    window.swup.hooks.on('animation:out:start', () => {
      const path = window.location.pathname
      const body = document.querySelector('body')
      if (path[path.length - 1] === '/' && !body.classList.contains('is-home')) {
        body.classList.add('is-home')
      } else if (path[path.length - 1] !== '/' && body.classList.contains('is-home')) {
        body.classList.remove('is-home')
      }
    })
  */
  swup.hooks.on("link:click", () => {
    // Remove the delay for the first time page load
    document.documentElement.style.setProperty("--content-delay", "0ms");

    // prevent elements from overlapping the navbar
    if (!bannerEnabled) {
      return;
    }
    const threshold = window.innerHeight * (BANNER_HEIGHT / 100) - 72 - 16;
    const navbar = document.getElementById("navbar-wrapper");
    if (!navbar || !document.body.classList.contains("is-home")) {
      return;
    }
    if (document.body.scrollTop >= threshold || document.documentElement.scrollTop >= threshold) {
      navbar.classList.add("navbar-hidden");
    }
  });
  swup.hooks.on("content:replace", (_visit) => {
    // set the page type to the body element
    document.body?.setAttribute("data-page-type", _visit.to.document?.body?.getAttribute("data-page-type") || "");

    initCustomScrollbar();
    initNoteBlocks();
    initHeadingAnchors();
    initCodeHighlight();
    loadButtonScript();
    updateToc();
    mountComments();
  });
  swup.hooks.on("visit:start", (visit) => {
    // toggle is-home class based on target URL (matching original Fuwari pattern)
    const bodyElement = document.querySelector("body");
    if (bodyElement) {
      const toUrl = new URL(visit.to.url, window.location.origin);
      if (toUrl.pathname === "/") {
        bodyElement.classList.add("is-home");
      } else {
        bodyElement.classList.remove("is-home");
      }
    }

    // increase the page height during page transition to prevent the scrolling animation from jumping
    const heightExtend = document.getElementById("page-height-extend");
    if (heightExtend) {
      heightExtend.classList.remove("hidden");
    }

    // Hide the TOC while scrolling back to top
    const toc = document.getElementById("toc-wrapper");
    if (toc) {
      toc.classList.add("toc-not-ready");
    }
  });
  swup.hooks.on("page:view", () => {
    // hide the temp high element when the transition is done
    const heightExtend = document.getElementById("page-height-extend");
    if (heightExtend) {
      heightExtend.classList.add("hidden");
    }
  });
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  swup.hooks.on("visit:end", (_visit: { to: { url: string } }) => {
    setTimeout(() => {
      const heightExtend = document.getElementById("page-height-extend");
      if (heightExtend) {
        heightExtend.classList.add("hidden");
      }

      // Just make the transition looks better
      const toc = document.getElementById("toc-wrapper");
      if (toc) {
        toc.classList.remove("toc-not-ready");
      }
    }, 200);
  });
};
setup();

window.onresize = () => {
  // calculate the --banner-height-extend, which needs to be a multiple of 4 to avoid blurry text
  let offset = Math.floor(window.innerHeight * (BANNER_HEIGHT_EXTEND / 100));
  offset = offset - (offset % 4);
  document.documentElement.style.setProperty("--banner-height-extend", `${offset}px`);
};

let lightbox: PhotoSwipeLightbox;
const pswp = import("photoswipe");
function createPhotoSwipe() {
  lightbox = new PhotoSwipeLightbox({
    gallery: ".custom-md img, #post-cover img",
    pswpModule: () => pswp,
    closeSVG:
      '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#ffffff"><path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z"/></svg>',
    zoomSVG:
      '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#ffffff"><path d="M340-540h-40q-17 0-28.5-11.5T260-580q0-17 11.5-28.5T300-620h40v-40q0-17 11.5-28.5T380-700q17 0 28.5 11.5T420-660v40h40q17 0 28.5 11.5T500-580q0 17-11.5 28.5T460-540h-40v40q0 17-11.5 28.5T380-460q-17 0-28.5-11.5T340-500v-40Zm40 220q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l224 224q11 11 11 28t-11 28q-11 11-28 11t-28-11L532-372q-30 24-69 38t-83 14Zm0-80q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>',
    padding: { top: 20, bottom: 20, left: 20, right: 20 },
    wheelToZoom: true,
    arrowPrev: false,
    arrowNext: false,
    imageClickAction: "close",
    tapAction: "close",
    doubleTapAction: "zoom",
  });

  lightbox.addFilter("domItemData", (itemData, element) => {
    if (element instanceof HTMLImageElement) {
      itemData.src = element.src;

      itemData.w = Number(element.naturalWidth || window.innerWidth);
      itemData.h = Number(element.naturalHeight || window.innerHeight);

      itemData.msrc = element.src;
    }

    return itemData;
  });

  lightbox.init();
}
const setupLightbox = () => {
  if (!lightbox) {
    createPhotoSwipe();
  }
  swup.hooks.on("page:view", () => {
    createPhotoSwipe();
  });

  swup.hooks.on(
    "content:replace",
    () => {
      lightbox?.destroy?.();
    },
    { before: true },
  );
};
setupLightbox();

// 导航点击

// 页面初始加载
document.addEventListener("DOMContentLoaded", () => {
  init();
  mountWidgets();
  loadButtonScript();
  setClickOutsideToClose("display-setting", ["display-setting", "display-settings-switch"]);
  setClickOutsideToClose("nav-menu-panel", ["nav-menu-panel", "nav-menu-switch"]);
  // setClickOutsideToClose("search-panel", ["search-panel", "search-bar", "search-switch"])

  const backToTopBtn = document.getElementById("back-to-top-btn");
  const toc = document.getElementById("toc-wrapper");
  const navbar = document.getElementById("navbar-wrapper");
  bannerEnabled = !!document.getElementById("banner-wrapper");
  const bannerFullscreen = document.body.classList.contains("banner-fullscreen");
  function scrollFunction() {
    const bannerHeight = window.innerHeight * (BANNER_HEIGHT / 100);

    if (backToTopBtn) {
      if (document.body.scrollTop > bannerHeight || document.documentElement.scrollTop > bannerHeight) {
        backToTopBtn.classList.remove("hide");
      } else {
        backToTopBtn.classList.add("hide");
      }
    }

    if (bannerEnabled && toc) {
      if (document.body.scrollTop > bannerHeight || document.documentElement.scrollTop > bannerHeight) {
        toc.classList.remove("toc-hide");
      } else {
        toc.classList.add("toc-hide");
      }
    }

    // 全屏模式：背景固定，导航始终悬浮，无需隐藏逻辑
    if (bannerFullscreen) return;
    if (!bannerEnabled) return;
    if (navbar) {
      const NAVBAR_HEIGHT = 72;
      const MAIN_PANEL_EXCESS_HEIGHT = MAIN_PANEL_OVERLAPS_BANNER_HEIGHT * 16; // The height the main panel overlaps the banner

      let bannerHeight = BANNER_HEIGHT;
      if (document.body.classList.contains("is-home") && window.innerWidth >= 1024) {
        bannerHeight = BANNER_HEIGHT_HOME;
      }
      const threshold = window.innerHeight * (bannerHeight / 100) - NAVBAR_HEIGHT - MAIN_PANEL_EXCESS_HEIGHT - 16;
      if (document.body.scrollTop >= threshold || document.documentElement.scrollTop >= threshold) {
        navbar.classList.add("navbar-hidden");
      } else {
        navbar.classList.remove("navbar-hidden");
      }
    }
  }
  window.onscroll = scrollFunction;

  const scrollToTopButton = document.getElementById("back-to-top-btn");

  if (!scrollToTopButton) {
    return;
  }

  scrollToTopButton.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
});
