import "./styles/variables.styl";
import "./styles/tailwind.css";

import "./styles/main.css";

import "./styles/markdown-extend.styl";
import "./styles/back-to-top.styl";
import "./styles/scrollbar.css";
import "./styles/transition.css";
import "./styles/markdown.css";
import "./styles/photoswipe.css";

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

import { mountSearch, mountDisplaySettings, mountToc } from "./preact";
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
  //   挂载目录
  const tocContainer = document.querySelector(".toc");
  if (tocContainer) {
    mountToc(tocContainer as HTMLElement);
  }
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

// 为 <pre><code> 注入复制按钮
function initCopyButtons() {
  document.querySelectorAll(".custom-md pre").forEach(function (pre) {
    if (pre.querySelector(".copy-btn")) return;

    const code = pre.querySelector("code");
    if (!code) return;

    const btn = document.createElement("button");
    btn.className = "copy-btn";
    btn.innerHTML =
      '<svg class="copy-btn-icon copy-icon" viewBox="0 0 24 24"><path d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z"/></svg>' +
      '<svg class="copy-btn-icon success-icon" viewBox="0 0 24 24"><path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"/></svg>';

    btn.addEventListener("click", function () {
      const text = code.textContent || "";
      navigator.clipboard.writeText(text).then(function () {
        btn.classList.add("success");
        setTimeout(function () { btn.classList.remove("success"); }, 2000);
      });
    });

    // pre 容器设为 relative 使按钮可以 absolute 定位
    (pre as HTMLElement).style.position = "relative";
    pre.appendChild(btn);
  });
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
  initCopyButtons();
  initHeadingAnchors();
  initCodeHighlight();
  showBanner();
}

function initCodeHighlight() {
  document.querySelectorAll(".custom-md pre code").forEach((block) => {
    if (!(block instanceof HTMLElement)) return;
    if (block.classList.contains("hljs")) return; // already highlighted
    hljs.highlightElement(block);
  });
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
    initCopyButtons();
    initHeadingAnchors();
    initCodeHighlight();
    loadButtonScript();
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

// 页面初始加载
document.addEventListener("DOMContentLoaded", () => {
  mountWidgets();
  initNoteBlocks(); // 确保初始加载生效
});
