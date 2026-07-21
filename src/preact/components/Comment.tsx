import { useState, useEffect, useCallback } from "preact/hooks";
import type { Comment, Reply, CommentListResponse, ReplyListResponse } from "../../types/comment";

interface CommentProps {
  subjectKind: string;
  subjectName: string;
}

interface CommentFormData {
  displayName: string;
  email: string;
  website: string;
  content: string;
}

const API_BASE = "/apis/api.content.halo.run/v1alpha1";

/* ── Utilities ────────────────────────────────── */

function formatDate(dateStr?: string): string {
  if (!dateStr) return "";
  try {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    if (minutes < 1) return "刚刚";
    if (minutes < 60) return `${minutes} 分钟前`;
    if (hours < 24) return `${hours} 小时前`;
    if (days < 30) return `${days} 天前`;
    return date.toLocaleDateString("zh-CN", { year: "numeric", month: "long", day: "numeric" });
  } catch {
    return dateStr;
  }
}

function getAvatarUrl(owner: { avatar?: string; emailHash?: string }): string {
  if (owner.avatar) return owner.avatar;
  if (owner.emailHash) return `https://gravatar.com/avatar/${owner.emailHash}?d=mp&s=96`;
  return `https://gravatar.com/avatar/00000000000000000000000000000000?d=mp&s=96`;
}

function showToast(message: string, type: "success" | "error") {
  const existing = document.querySelector(".comment-toast");
  if (existing) existing.remove();
  const toast = document.createElement("div");
  toast.className = `comment-toast ${type}`;
  toast.textContent = message;
  document.body.appendChild(toast);
  requestAnimationFrame(() => toast.classList.add("show"));
  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => toast.remove(), 300);
  }, 2500);
}

/* ── Reply Item ────────────────────────────────── */

function ReplyItem({ reply }: { reply: Reply }) {
  const ts = reply.spec.creationTime || reply.metadata.creationTimestamp;
  return (
    <div class="comment-reply-item" id={`reply-${reply.metadata.name}`}>
      <div class="comment-header">
        <img class="comment-avatar" src={getAvatarUrl(reply.spec.owner)} alt={reply.spec.owner.displayName} loading="lazy" />
        <div class="comment-meta">
          {reply.spec.owner.website ? (
            <a class="comment-author" href={reply.spec.owner.website} target="_blank" rel="noopener noreferrer">
              {reply.spec.owner.displayName}
            </a>
          ) : (
            <span class="comment-author">{reply.spec.owner.displayName}</span>
          )}
          <time class="comment-time" datetime={ts}>{formatDate(ts)}</time>
        </div>
      </div>
      <div class="comment-body">{reply.spec.content}</div>
    </div>
  );
}

/* ── Comment Item ──────────────────────────────── */

function CommentItem({ comment, onReply }: { comment: Comment; onReply: (name: string) => void }) {
  const [replies, setReplies] = useState<Reply[]>([]);
  const [loaded, setLoaded] = useState(false);
  const [showReplies, setShowReplies] = useState(false);

  const loadReplies = useCallback(async () => {
    if (loaded) return;
    try {
      const res = await fetch(`${API_BASE}/comments/${comment.metadata.name}/reply?size=100`);
      if (res.ok) {
        const data: ReplyListResponse = await res.json();
        setReplies(data.items || []);
        setLoaded(true);
      }
    } catch (e) {
      console.error("加载回复失败:", e);
    }
  }, [comment.metadata.name, loaded]);

  useEffect(() => {
    if (comment.status.replyCount > 0) {
      loadReplies();
      setShowReplies(true);
    }
  }, [comment.status.replyCount, loadReplies]);

  const ts = comment.spec.creationTime || comment.metadata.creationTimestamp;

  return (
    <div class="comment-item" id={`comment-${comment.metadata.name}`}>
      <div class="comment-header">
        <img class="comment-avatar" src={getAvatarUrl(comment.spec.owner)} alt={comment.spec.owner.displayName} loading="lazy" />
        <div class="comment-meta">
          {comment.spec.owner.website ? (
            <a class="comment-author" href={comment.spec.owner.website} target="_blank" rel="noopener noreferrer">
              {comment.spec.owner.displayName}
            </a>
          ) : (
            <span class="comment-author">{comment.spec.owner.displayName}</span>
          )}
          <time class="comment-time" datetime={ts}>{formatDate(ts)}</time>
        </div>
      </div>
      <div class="comment-body">{comment.spec.content}</div>
      <div class="comment-actions">
        <button class="comment-reply-btn" onClick={() => onReply(comment.metadata.name)} title="回复">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 17 4 12 9 7" /><path d="M20 18v-2a4 4 0 0 0-4-4H4" />
          </svg>
          <span>回复</span>
        </button>
        {comment.status.replyCount > 0 && (
          <button class="comment-toggle-replies" onClick={() => setShowReplies(!showReplies)}>
            {showReplies ? "收起" : "展开"} {comment.status.replyCount} 条回复
          </button>
        )}
      </div>
      {showReplies && replies.length > 0 && (
        <div class="comment-replies">
          {replies.map((reply) => <ReplyItem key={reply.metadata.name} reply={reply} />)}
        </div>
      )}
    </div>
  );
}

/* ── Comment Form ──────────────────────────────── */

function CommentForm({
  parentCommentName,
  onSubmit,
  onCancel,
}: {
  parentCommentName?: string;
  onSubmit: (data: CommentFormData) => Promise<void>;
  onCancel?: () => void;
}) {
  const [form, setForm] = useState<CommentFormData>(() => {
    const saved = localStorage.getItem("comment-author-info");
    if (saved) {
      try { return JSON.parse(saved); } catch { /* ignore */ }
    }
    return { displayName: "", email: "", website: "", content: "" };
  });
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (e: Event) => {
    e.preventDefault();
    if (!form.displayName.trim()) { setError("请输入昵称"); return; }
    if (!form.content.trim()) { setError("请输入评论内容"); return; }

    setSubmitting(true);
    setError(null);

    try {
      localStorage.setItem("comment-author-info", JSON.stringify({
        displayName: form.displayName,
        email: form.email,
        website: form.website,
      }));
      await onSubmit(form);
      setForm((prev) => ({ ...prev, content: "" }));
      showToast(parentCommentName ? "回复已提交，等待审核" : "评论已提交，等待审核", "success");
    } catch (err) {
      setError(err instanceof Error ? err.message : "提交失败，请稍后重试");
      showToast(err instanceof Error ? err.message : "提交失败", "error");
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <form class="comment-form" onSubmit={handleSubmit}>
      {parentCommentName && (
        <div class="comment-form-reply-hint">
          回复评论
          <button type="button" class="comment-form-cancel" onClick={onCancel}>取消</button>
        </div>
      )}
      <div class="comment-form-row">
        <input type="text" placeholder="昵称 *" value={form.displayName}
          onInput={(e) => setForm({ ...form, displayName: (e.target as HTMLInputElement).value })}
          required class="comment-input" />
        <input type="email" placeholder="邮箱（不会公开）" value={form.email}
          onInput={(e) => setForm({ ...form, email: (e.target as HTMLInputElement).value })}
          class="comment-input" />
        <input type="url" placeholder="网站" value={form.website}
          onInput={(e) => setForm({ ...form, website: (e.target as HTMLInputElement).value })}
          class="comment-input" />
      </div>
      <textarea placeholder="写下你的评论... *" value={form.content}
        onInput={(e) => setForm({ ...form, content: (e.target as HTMLTextAreaElement).value })}
        required class="comment-textarea" rows={4} />
      {error && <div class="comment-error">{error}</div>}
      <div class="comment-submit-row">
        <button type="submit" class="comment-submit" disabled={submitting}>
          {submitting ? "提交中..." : parentCommentName ? "提交回复" : "发表评论"}
        </button>
      </div>
    </form>
  );
}

/* ── Main Comment Component ────────────────────── */

export function Comment({ subjectKind, subjectName }: CommentProps) {
  const [comments, setComments] = useState<Comment[]>([]);
  const [loading, setLoading] = useState(true);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [replyingTo, setReplyingTo] = useState<string | null>(null);
  const pageSize = 20;

  const loadComments = useCallback(async (pageNum: number) => {
    setLoading(true);
    try {
      const res = await fetch(
        `${API_BASE}/comments?subjectRef.kind=${subjectKind}&subjectRef.name=${subjectName}&page=${pageNum}&size=${pageSize}`
      );
      if (!res.ok) throw new Error("加载评论失败");
      const data: CommentListResponse = await res.json();
      setComments(data.items || []);
      setTotal(data.total || 0);
    } catch (e) {
      console.error("加载评论失败:", e);
    } finally {
      setLoading(false);
    }
  }, [subjectKind, subjectName]);

  useEffect(() => { loadComments(page); }, [page, loadComments]);

  const handleCommentSubmit = async (data: CommentFormData) => {
    const res = await fetch(`${API_BASE}/comments`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        spec: {
          raw: data.content,
          content: data.content,
          owner: { kind: "Email", displayName: data.displayName, email: data.email, website: data.website },
          subjectRef: { group: "content.halo.run", kind: subjectKind, name: subjectName },
          allowNotification: true,
        },
      }),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.error || "提交失败");
    }
    loadComments(page);
  };

  const handleReplySubmit = async (data: CommentFormData) => {
    if (!replyingTo) return;
    const res = await fetch(`${API_BASE}/comments/${replyingTo}/reply`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        spec: {
          raw: data.content,
          content: data.content,
          owner: { kind: "Email", displayName: data.displayName, email: data.email, website: data.website },
          allowNotification: true,
        },
      }),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.error || "提交回复失败");
    }
    setReplyingTo(null);
    loadComments(page);
  };

  const totalPages = Math.ceil(total / pageSize);

  return (
    <div class="comment-section">
      <h3 class="comment-title">
        评论
        {total > 0 && <span class="comment-count">({total})</span>}
      </h3>

      <CommentForm onSubmit={handleCommentSubmit} />

      {loading ? (
        <div class="comment-loading">加载中...</div>
      ) : comments.length === 0 ? (
        <div class="comment-empty">
          <div class="comment-empty-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
          </div>
          <span>暂无评论，来抢沙发吧~</span>
        </div>
      ) : (
        <div class="comment-list">
          {comments.map((comment) => (
            <div key={comment.metadata.name}>
              <CommentItem comment={comment} onReply={(name) => setReplyingTo(name)} />
              {replyingTo === comment.metadata.name && (
                <div class="comment-reply-form">
                  <CommentForm
                    parentCommentName={comment.metadata.name}
                    onSubmit={handleReplySubmit}
                    onCancel={() => setReplyingTo(null)}
                  />
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {totalPages > 1 && (
        <div class="comment-pagination">
          <button class="comment-page-btn" disabled={page <= 1} onClick={() => setPage(page - 1)}>上一页</button>
          <span class="comment-page-info">{page} / {totalPages}</span>
          <button class="comment-page-btn" disabled={page >= totalPages} onClick={() => setPage(page + 1)}>下一页</button>
        </div>
      )}
    </div>
  );
}
