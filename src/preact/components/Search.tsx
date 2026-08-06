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
      const res = await fetch(siteUrl + "/?s=" + encodeURIComponent(q.trim()) + "&ajax=1");
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

  return (
    <div ref={containerRef} className="flex items-center">
      <div id="search-bar"
        className="mr-2 hidden h-11 items-center rounded-lg bg-black/[0.04] transition-all focus-within:bg-black/[0.06] hover:bg-black/[0.06] lg:flex dark:bg-white/5 dark:focus-within:bg-white/10 dark:hover:bg-white/10">
        <span className="icon-[tabler--search] pointer-events-none absolute my-auto ml-3 text-[1.25rem] text-black/30 transition dark:text-white/30"></span>
        <input placeholder="搜索文章..." value={keyword.desktop}
          onInput={e => onInput((e.target as HTMLInputElement).value, true)}
          onKeyDown={onKeyDown}
          onFocus={() => { if (result?.hits?.length) setPanelVisible(true); }}
          className="h-full w-40 bg-transparent pl-10 text-sm text-black/50 outline-0 transition-all focus:w-60 active:w-60 dark:text-white/50" />
        {result && result.hits?.length > 0 && (
          <div className="absolute top-11 left-0 right-0 z-50 overflow-hidden rounded-xl bg-white shadow-xl dark:bg-[var(--card-bg)]">
            {result.hits.map((item, i) => (
              <a key={i} href={item.permalink}
                className="block px-4 py-2 text-sm transition hover:bg-black/[0.04] dark:hover:bg-white/5">
                <div className="font-bold text-black/80 dark:text-white/80">{item.title}</div>
                <div className="text-xs text-black/50 dark:text-white/50 truncate">{item.description}</div>
              </a>
            ))}
          </div>
        )}
      </div>

      <button onClick={() => setPanelVisible(v => !v)} aria-label="Search Panel"
        id="search-switch"
        className="btn-plain scale-animation h-11 w-11 rounded-lg active:scale-90 lg:!hidden">
        <span className="icon-[tabler--search] text-[1.25rem]"></span>
      </button>

      {panelVisible && (
        <div className="fixed inset-0 z-50 flex items-start justify-center bg-black/30 pt-32 md:pt-48"
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
            {result?.hits?.length === 0 && keyword.mobile && (
              <p className="mt-4 text-center text-sm text-black/50 dark:text-white/50">未找到相关文章</p>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
