import mermaid from "mermaid";

let initialized = false;
let isRendering = false;
let currentTheme: "dark" | "default" | null = null;

function getTheme(): "dark" | "default" {
  return document.documentElement.classList.contains("dark") ? "dark" : "default";
}

function hasThemeChanged(): boolean {
  const newTheme = getTheme();
  if (currentTheme !== newTheme) {
    currentTheme = newTheme;
    return true;
  }
  return false;
}

function isMermaidCodeBlock(code: Element): boolean {
  const classes = code.classList;
  return classes.contains("language-mermaid") || classes.contains("lang-mermaid");
}

/**
 * 将 .custom-md 中的 mermaid 代码块转换为图表容器。
 * 只在 init 或 Swup 切页后调用，已转换过的容器不会被重复处理。
 */
function transformCodeBlocks(): void {
  document.querySelectorAll(".custom-md pre code").forEach((code) => {
    if (!isMermaidCodeBlock(code)) return;

    const pre = code.parentElement;
    if (!pre || !(pre instanceof HTMLElement)) return;
    if (pre.closest(".mermaid-diagram-container")) return;

    const source = code.textContent || "";

    const container = document.createElement("div");
    container.className = "mermaid-diagram-container";
    container.innerHTML = `
      <div class="mermaid-wrapper">
        <div class="mermaid" data-mermaid-code=""></div>
      </div>
    `;
    const mermaidEl = container.querySelector<HTMLElement>(".mermaid");
    if (mermaidEl) {
      mermaidEl.dataset.mermaidCode = source;
    }

    pre.parentNode?.replaceChild(container, pre);
  });
}

function configureMermaid(theme: "dark" | "default"): void {
  const isDark = theme === "dark";
  mermaid.initialize({
    startOnLoad: false,
    theme,
    themeVariables: {
      fontFamily: "inherit",
      fontSize: "16px",
      primaryColor: isDark ? "#ffffff" : "#000000",
      primaryTextColor: isDark ? "#ffffff" : "#000000",
      primaryBorderColor: isDark ? "#ffffff" : "#000000",
      lineColor: isDark ? "#ffffff" : "#000000",
      secondaryColor: isDark ? "#333333" : "#f0f0f0",
      tertiaryColor: isDark ? "#555555" : "#e0e0e0",
    },
    securityLevel: "loose",
  });
}

function postProcessSvg(element: HTMLElement, isDark: boolean): void {
  const svg = element.querySelector<SVGSVGElement>("svg");
  if (!svg) return;

  // 保留 Mermaid 自身的响应式尺寸（useMaxWidth 会按图表自然宽度设置 max-width），
  // 不强制 width=100%，否则小图会被拉伸到整个内容区宽度。
  svg.style.height = "auto";
  svg.style.filter = isDark ? "brightness(0.9) contrast(1.1)" : "none";
}

async function renderMermaidDiagrams(): Promise<void> {
  if (isRendering) return;
  isRendering = true;

  const theme = getTheme();
  configureMermaid(theme);

  const elements = document.querySelectorAll<HTMLElement>(".mermaid[data-mermaid-code]");

  await Promise.all(
    Array.from(elements).map(async (element, index) => {
      const code = element.dataset.mermaidCode;
      if (!code) return;

      try {
        const id = `mermaid-${index}-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
        const { svg, bindFunctions } = await mermaid.render(id, code);
        element.innerHTML = svg;
        postProcessSvg(element, theme === "dark");
        bindFunctions?.(element);
      } catch (error) {
        console.error("Mermaid rendering error:", error);
        element.innerHTML = `<div class="mermaid-error">Failed to render diagram. Please check the syntax.</div>`;
      }
    }),
  );

  isRendering = false;
}

function setupThemeObserver(): void {
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (
        mutation.type !== "attributes" ||
        mutation.attributeName !== "class" ||
        !(mutation.target instanceof HTMLElement)
      ) {
        return;
      }

      const wasDark = mutation.oldValue?.includes("dark") ?? false;
      const isDark = mutation.target.classList.contains("dark");
      if (wasDark !== isDark && hasThemeChanged()) {
        renderMermaidDiagrams();
      }
    });
  });

  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ["class"],
    attributeOldValue: true,
  });
}

export function initMermaid(): void {
  if (!initialized) {
    initialized = true;
    currentTheme = getTheme();
    setupThemeObserver();
  }

  transformCodeBlocks();
  void renderMermaidDiagrams();
}
