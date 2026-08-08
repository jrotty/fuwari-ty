// 评论系统前端：事件委托 + fetch 端点 + 服务端 HTML 渲染
interface CommaState {
  cid: number;
  sort: string;
  page: number;
  total: number;
  hasMore: boolean;
  login: boolean;
  busy: boolean;
}

interface CommaInitial {
  cid: number;
  sort: string;
  login: boolean;
  token?: string;
}

const I18N: Record<string, string> = {
  replying: "回复 @{0}",
  cancelReply: "取消回复",
  deleteConfirm: "确定删除这条评论吗？",
  errTextRequired: "评论内容不能为空",
  errTooLong: "评论不能超过 3000 字",
  errGeneric: "提交失败，请稍后再试",
  waiting: "评论已提交，等待审核通过后显示",
  empty: "还没有评论，来抢沙发吧",
  loading: "加载中…",
};

export function mountComments(): void {
  const root = document.querySelector<HTMLElement>(".fuwari-comments");
  if (!root || root.dataset.mounted) return;
  root.dataset.mounted = "1";

  const jsonEl = document.getElementById("comma-json");
  let initial: CommaInitial | null = null;
  if (jsonEl?.textContent) {
    try {
      initial = JSON.parse(jsonEl.textContent);
    } catch {
      initial = null;
    }
  }
  const state: CommaState = {
    cid: Number(root.dataset.cid || initial?.cid || 0),
    sort: initial?.sort || "recommend",
    page: 0,
    total: 0,
    hasMore: true,
    login: root.dataset.login === "1" || !!initial?.login,
    busy: false,
  };

  const listEl = root.querySelector<HTMLElement>(".comma-list");
  const moreBtn = root.querySelector<HTMLButtonElement>(".js-comma-more");
  const form = root.querySelector<HTMLFormElement>("#comma-form");
  const textarea = form?.querySelector<HTMLTextAreaElement>("#comma-text");
  const countEl = root.querySelector<HTMLElement>(".comma-count");
  const totalEl = root.querySelector<HTMLElement>("[data-comma-total]");
  const replyingEl = root.querySelector<HTMLElement>(".comma-replying");
  const refreshBtn = root.querySelector<HTMLElement>(".js-comma-refresh");
  const sortBtns = Array.from(root.querySelectorAll<HTMLButtonElement>(".comma-sort"));

  function i18n(key: string, args?: string[]): string {
    let s = I18N[key] || key;
    if (args) {
      args.forEach((a, i) => {
        s = s.replace(`{${i}}`, a);
      });
    }
    return s;
  }

  function url(action: string, extra: Record<string, string | number> = {}): string {
    const q = new URLSearchParams({
      comma: "1",
      action,
      cid: String(state.cid),
      ...Object.fromEntries(Object.entries(extra).map(([k, v]) => [k, String(v)])),
    });
    return "?" + q.toString();
  }

  function showSkeleton(): void {
    if (!listEl) return;
    const items = Array.from({ length: 3 }, () => `
      <li class="comma-skeleton">
        <div class="comma-skeleton-avatar"></div>
        <div class="comma-skeleton-lines">
          <div class="comma-skeleton-line"></div>
          <div class="comma-skeleton-line"></div>
          <div class="comma-skeleton-line"></div>
        </div>
      </li>`).join("");
    listEl.innerHTML = items;
  }

  async function load(page: number, sort?: string): Promise<void> {
    if (state.busy) return;
    state.busy = true;
    refreshBtn?.classList.add("spinning");
    showSkeleton();
    const useSort = sort || state.sort;
    try {
      const r = await fetch(url("list", { sort: useSort, page }));
      const j = await r.json();
      if (!j.ok) return;
      state.page = page;
      state.sort = j.sort;
      state.total = j.total;
      state.hasMore = j.hasMore;
      if (listEl) listEl.innerHTML = j.html || `<li class="comma-empty">${i18n("empty")}</li>`;
      if (totalEl) totalEl.textContent = String(j.total);
      if (moreBtn) moreBtn.hidden = !j.hasMore;
      sortBtns.forEach((b) => {
        b.classList.toggle("comma-sort-on", b.getAttribute("data-sort") === j.sort);
      });
    } catch {
      if (listEl) listEl.innerHTML = `<li class="comma-empty">${i18n("errGeneric")}</li>`;
    } finally {
      state.busy = false;
      refreshBtn?.classList.remove("spinning");
    }
  }

  function toast(msg: string, isError = false): void {
    const t = document.createElement("div");
    t.className = "comma-toast" + (isError ? " error" : "");
    t.textContent = msg;
    root!.appendChild(t);
    requestAnimationFrame(() => t.classList.add("show"));
    setTimeout(() => {
      t.classList.remove("show");
      setTimeout(() => t.remove(), 300);
    }, 2500);
  }

  function setReplying(coid: number | null, author?: string): void {
    if (!replyingEl || !form) return;
    const parentInput = form.querySelector<HTMLInputElement>('input[name="parent"]');
    if (coid === null) {
      replyingEl.hidden = true;
      replyingEl.innerHTML = "";
      if (parentInput) parentInput.value = "0";
      return;
    }
    replyingEl.hidden = false;
    replyingEl.innerHTML =
      `<span>${i18n("replying", [author || ""])}</span>` +
      `<button type="button" class="comma-reply-cancel js-reply-cancel">${i18n("cancelReply")}</button>`;
    if (parentInput) parentInput.value = String(coid);
    replyingEl.querySelector(".js-reply-cancel")?.addEventListener("click", () => setReplying(null));
  }

  // 排序 tab
  sortBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const s = btn.getAttribute("data-sort");
      if (s && s !== state.sort) load(1, s);
    });
  });

  // 手动刷新
  refreshBtn?.addEventListener("click", () => {
    load(state.page || 1);
  });

  // 查看更多
  moreBtn?.addEventListener("click", () => {
    load(state.page + 1);
  });

  // 事件委托：投票 / 回复 / 删除
  root.addEventListener("click", async (e) => {
    const t = e.target as HTMLElement;
    const voteBtn = t.closest<HTMLElement>(".js-vote");
    if (voteBtn && root.contains(voteBtn)) {
      if (state.busy) return;
      state.busy = true;
      try {
        const r = await fetch(
          url("vote", { coid: voteBtn.dataset.coid || 0, vote: voteBtn.dataset.vote || "like" }),
        );
        const j = await r.json();
        if (j.ok) {
          root.querySelectorAll(`.js-vote[data-coid="${voteBtn.dataset.coid}"]`).forEach((b) => {
            b.classList.remove("voted");
          });
          if (j.my) voteBtn.classList.add("voted");
          const likeBtn = root.querySelector<HTMLElement>(
            `.js-vote[data-coid="${voteBtn.dataset.coid}"][data-vote="like"]`,
          );
          const count = likeBtn?.querySelector(".comma-count");
          if (count) count.textContent = j.likes;
        }
      } finally {
        state.busy = false;
      }
      return;
    }
    const replyBtn = t.closest<HTMLElement>(".js-reply");
    if (replyBtn && root.contains(replyBtn)) {
      setReplying(Number(replyBtn.dataset.coid), replyBtn.dataset.author);
      textarea?.focus();
      form?.scrollIntoView({ behavior: "smooth", block: "center" });
      return;
    }
    const delBtn = t.closest<HTMLElement>(".js-delete");
    if (delBtn && root.contains(delBtn)) {
      if (!window.confirm(i18n("deleteConfirm"))) return;
      const r = await fetch(url("delete", { coid: delBtn.dataset.coid || 0 }));
      const j = await r.json();
      if (j.ok) {
        if (totalEl) totalEl.textContent = String(j.total);
        load(state.page || 1);
      }
      return;
    }
  });

  // 字数统计
  textarea?.addEventListener("input", () => {
    const n = textarea.value.length;
    if (countEl) {
      countEl.textContent = `${n}/3000`;
      countEl.classList.toggle("over", n > 3000);
    }
  });

  // 访客身份实时显示：昵称/邮箱填了 → 显示头像+昵称
  const guestIdentity = root.querySelector<HTMLElement>(".comma-identity-box[data-guest]");
  const authorInput = form?.querySelector<HTMLInputElement>("#comma-author");
  const mailInput = form?.querySelector<HTMLInputElement>("#comma-mail");
  if (guestIdentity && authorInput && mailInput) {
    const gId = guestIdentity;
    const aInput = authorInput;
    const mInput = mailInput;
    function updateGuestIdentity(): void {
      const author = aInput.value.trim();
      const mail = mInput.value.trim();
      if (!author || !mail || !/^\S+@\S+\.\S+$/.test(mail)) {
        gId.hidden = true;
        return;
      }
      gId.hidden = false;
      const avatar = gId.querySelector<HTMLImageElement>(".comma-identity-avatar");
      if (avatar) {
        // QQ 邮箱显示 QQ 头像，否则主题色首字母
        const qqMatch = mail.toLowerCase().match(/^(\d+)@qq\.com$/);
        if (qqMatch) {
          avatar.src = `https://q1.qlogo.cn/g?b=qq&nk=${qqMatch[1]}&s=100`;
        } else {
          const letter = author.charAt(0).toUpperCase() || "?";
          avatar.src =
            "data:image/svg+xml;charset=utf-8," +
            encodeURIComponent(
              `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><rect width="64" height="64" fill="oklch(0.55 0.12 250)"/><text x="32" y="43" font-family="sans-serif" font-size="28" font-weight="700" fill="#fff" text-anchor="middle">${letter}</text></svg>`,
            );
        }
      }
      const name = gId.querySelector<HTMLElement>(".comma-identity-name");
      if (name) name.textContent = author;
    }
    aInput.addEventListener("input", updateGuestIdentity);
    mInput.addEventListener("input", updateGuestIdentity);
    updateGuestIdentity();
  }

  // 提交
  form?.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (state.busy) return;
    const text = (textarea?.value || "").trim();
    if (!text) {
      toast(i18n("errTextRequired"), true);
      return;
    }
    if (text.length > 3000) {
      toast(i18n("errTooLong"), true);
      return;
    }
    const data = new URLSearchParams({
      comma: "1",
      action: "submit",
      cid: String(state.cid),
      text,
      parent: form.querySelector<HTMLInputElement>('input[name="parent"]')?.value || "0",
    });
    if (initial?.token) data.set("_", initial.token);
    const author = form.querySelector<HTMLInputElement>("#comma-author");
    const mail = form.querySelector<HTMLInputElement>("#comma-mail");
    const site = form.querySelector<HTMLInputElement>("#comma-url");
    if (author) data.set("author", author.value);
    if (mail) data.set("mail", mail.value);
    if (site) data.set("url", site.value);

    state.busy = true;
    try {
      const r = await fetch("?comma=1", { method: "POST", body: data });
      const j = await r.json();
      if (!j.ok) {
        toast(j.msg || i18n("errGeneric"), true);
        return;
      }
      if (j.waiting) toast(i18n("waiting"));
      if (textarea) textarea.value = "";
      if (countEl) countEl.textContent = "0/3000";
      setReplying(null);
      load(1);
    } catch {
      toast(i18n("errGeneric"), true);
    } finally {
      state.busy = false;
    }
  });

  // 首屏加载
  load(1);
}
