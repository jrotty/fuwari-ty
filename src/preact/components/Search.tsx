import { useState, useEffect, useRef } from "preact/hooks";

interface Hit {
  title: string;
  permalink: string;
  description: string;
}
interface SearchResult {
  hits: Hit[];
  keyword: string;
}

function getSiteUrl(): string {
  const el = document.querySelector<HTMLScriptElement>("#theme-config");
  if (!el?.textContent) return "";
  try {
    return JSON.parse(el.textContent).siteUrl || "";
  } catch {
    return "";
  }
}

function highlight(text: string, terms: string[]): string {
  let out = text;
  for (const term of terms) {
    out = out.replace(new RegExp(term.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"), "gi"),
      m => `<mark>${m}</mark>`);
  }
  return out;
}

export function Search() {
  const [keyword, setKeyword] = useState({ mobile: "", desktop: "" });
  const [result, setResult] = useState<SearchResult | undefined>();
  const [panelVisible, setPanelVisible] = useState(false);
  const timer = useRef<number>();
  const containerRef = useRef<HTMLDivElement>(null);

  const search = async (q: string) => {
    if (!q.trim()) { setResult(undefined); return; }
    const siteUrl = getSiteUrl();
    try {
      // 走 Typecho 的 /search/ 路由（filterSearchQuery 会剔除中文，原样路径即可）
      const res = await fetch(siteUrl + "/search/" + encodeURIComponent(q.trim()) + "/?ajax=1");
      if (!res.ok) throw new Error(String(res.status));
      const data: SearchResult = await res.json();
      setResult(data);
      if (data.hits?.length) setPanelVisible(true);
    } catch { setResult(undefined); }
  };

  const debouncedSearch = (q: string) => {
    clearTimeout(timer.current);
    timer.current = window.setTimeout(() => search(q), 300);
  };

  const onInput = (v: string, isDesktop: boolean) => {
    setKeyword(k => ({ ...k, [isDesktop ? "desktop" : "mobile"]: v }));
    debouncedSearch(v);
  };

  const onKeyDown = (e: KeyboardEvent) => {
    if (e.key === "Escape") { setPanelVisible(false); setResult(undefined); }
  };

  useEffect(() => {
    const handler = (e: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(e.target as Node))
        setPanelVisible(false);
    };
    if (panelVisible) document.addEventListener("mousedown", handler);
    return () => document.removeEventListener("mousedown", handler);
  }, [panelVisible]);

  useEffect(() => () => clearTimeout(timer.current), []);

  const terms = (result?.keyword || "").trim().split(/\s+/).filter(Boolean);

  return (
    <div ref={containerRef} className="flex items-center">
      {/* 桌面搜索栏 */}
      <div id="search-bar"
        className="mr-2 hidden h-11 items-center rounded-lg bg-black/[0.04] transition-all focus-within:bg-black/[0.06] hover:bg-black/[0.06] lg:flex dark:bg-white/5 dark:focus-within:bg-white/10 dark:hover:bg-white/10">
        <span className="icon-[tabler--search] pointer-events-none absolute my-auto ml-3 text-[1.25rem] text-black/30 transition dark:text-white/30"></span>
        <input placeholder="搜索文章..." value={keyword.desktop}
          onInput={e => onInput((e.target as HTMLInputElement).value, true)}
          onKeyDown={onKeyDown}
          onFocus={() => { if (result?.hits?.length) setPanelVisible(true); }}
          className="h-full w-40 bg-transparent pl-10 text-sm text-black/50 outline-0 transition-all focus:w-60 active:w-60 dark:text-white/50" />
        {/* 桌面结果面板（仿原版 float-panel，仅桌面渲染） */}
        <div id="search-panel" className={`search-panel float-panel absolute top-11 right-0 hidden w-[24rem] px-2 py-2 lg:block ${panelVisible && result?.hits?.length ? "" : "float-panel-closed"}`}>          {result?.hits?.map((item, i) => (
            <a key={i} href={item.permalink}
              className="group block rounded-xl px-3 py-2 text-lg transition hover:bg-[var(--btn-plain-bg-hover)] active:bg-[var(--btn-plain-bg-active)]">
              <div className="inline-flex text-90 font-bold transition group-hover:text-[var(--primary)]">
                {item.title}
                <span className="icon-[material-symbols--chevron-right-rounded] my-auto ml-1 translate-x-1 text-[0.75rem] text-[var(--primary)] transition"></span>
              </div>
              <div className="text-sm text-black/50 dark:text-white/50" dangerouslySetInnerHTML={{ __html: highlight(item.description, terms) }} />
            </a>
          ))}
        </div>
      </div>

      {/* 移动端搜索开关 */}
      <button onClick={() => setPanelVisible(v => !v)} aria-label="Search Panel"
        id="search-switch"
        className="btn-plain scale-animation h-11 w-11 rounded-lg active:scale-90 lg:!hidden">
        <span className="icon-[tabler--search] text-[1.25rem]"></span>
      </button>

      {/* 移动端全屏面板（仅移动端渲染） */}
      {panelVisible && (
        <div className="fixed inset-0 z-50 flex items-start justify-center bg-black/30 pt-32 md:pt-48 lg:hidden"
          onClick={() => setPanelVisible(false)}>
          <div className="w-[90%] max-w-md rounded-2xl bg-white p-4 shadow-2xl dark:bg-[var(--card-bg)]"
            onClick={e => e.stopPropagation()}>
            <div className="relative flex h-11 items-center rounded-xl bg-black/[0.04] dark:bg-white/5 dark:focus-within:bg-white/10">
              <span className="icon-[tabler--search] pointer-events-none absolute my-auto ml-3 text-[1.25rem] text-black/30 dark:text-white/30"></span>
              <input placeholder="搜索文章..." value={keyword.mobile}
                onInput={e => onInput((e.target as HTMLInputElement).value, false)}
                onKeyDown={onKeyDown} autoFocus
                className="h-full w-full bg-transparent pl-10 text-sm text-black/50 outline-0 dark:text-white/50" />
            </div>
            {result && result.hits?.length > 0 && (
              <div className="mt-2 max-h-60 overflow-y-auto">
                {result.hits.map((item, i) => (
                  <a key={i} href={item.permalink}
                    className="block rounded-lg px-3 py-2 transition hover:bg-black/[0.04] dark:hover:bg-white/5">
                    <div className="font-bold text-black/80 dark:text-white/80">{item.title}</div>
                    <div className="text-xs text-black/50 dark:text-white/50 truncate">{item.description}</div>
                  </a>
                ))}
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
