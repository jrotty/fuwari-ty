<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need("functions.php"); ?>
<?php
$_rc = getReactionsConfig($this->options);
$_rcCounts = getReactionCounts($this);
$_rcDisabled = @$this->fields->reactionsDisable;
if (empty($_rc) || !empty($_rcDisabled)) return;
$_rcPerma = $this->permalink;
$_rcAdd = __t('reactions.add');
?>
<style>
  .gh-reactions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }
  .gh-add,
  .gh-badge {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    height: 24px;
    box-sizing: border-box;
    padding: 2px 8px;
    font-size: 12px;
    line-height: 20px;
    color: var(--btn-content);
    background: transparent;
    border: 1px solid var(--line-color);
    border-radius: 999px;
    cursor: pointer;
    transition: border-color .15s, color .15s, background-color .15s, box-shadow .15s;
  }
  .gh-badge[hidden] {
    display: none;
  }
  .gh-add:hover,
  .gh-badge:hover {
    border-color: var(--primary);
    color: var(--primary);
  }
  .gh-add:active,
  .gh-badge:active {
    background: rgba(208, 215, 222, 0.24);
  }
  .gh-badge.selected {
    border-color: var(--primary);
  }
  .gh-emoji {
    font-style: normal;
    font-size: 14px;
    line-height: 1;
  }
  .gh-count {
    font-size: 12px;
    line-height: 20px;
  }
  .gh-panel {
    position: absolute;
    bottom: 100%;
    left: 0;
    margin-bottom: 6px;
    padding: 6px 8px;
    background: var(--float-panel-bg);
    border: 1px solid var(--line-color);
    border-radius: 6px;
    box-shadow: 0 8px 24px rgba(140, 149, 159, 0.2);
    opacity: 0;
    transform: translateY(4px);
    transition: opacity .15s, transform .15s;
    z-index: 50;
  }
  .gh-panel.open {
    opacity: 1;
    transform: translateY(0);
  }
  .gh-items {
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
  }
  .gh-item {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 3px 5px;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color .1s, border-color .1s;
  }
  .gh-item:hover {
    background: rgba(234, 238, 242, 0.5);
  }
  .dark .gh-item:hover {
    background: rgba(177, 186, 196, 0.12);
  }
  .gh-item.selected {
    border-color: var(--primary);
  }
</style>
<div class="gh-reactions mt-6" data-cid="<?php echo $this->cid; ?>" data-url="<?php echo $_rcPerma; ?>">
  <div class="relative">
    <button type="button" class="gh-add" aria-expanded="false" aria-label="<?php echo $_rcAdd; ?>" title="<?php echo $_rcAdd; ?>">
      <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor" aria-hidden="true">
        <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0Zm0 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13ZM8 4.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm-3.75 5.25a4.75 4.75 0 0 0 7.5 0 .75.75 0 0 0-1.203-.897 3.25 3.25 0 0 1-5.094 0 .75.75 0 0 0-1.203.897Z"></path>
      </svg>
    </button>
    <div class="gh-panel" hidden>
      <div class="gh-items">
        <?php foreach ($_rc as $_key => $_item): ?>
        <button type="button" class="gh-item" data-key="<?php echo $_key; ?>" aria-label="React <?php echo $_key; ?>">
          <?php if ($_item['type'] === 'svg'): ?>
            <?php echo $_item['value']; ?>
          <?php elseif ($_item['type'] === 'img'): ?>
            <img src="<?php echo $_item['value']; ?>" alt="" class="gh-emoji">
          <?php else: ?>
            <span class="gh-emoji"><?php echo $_item['value']; ?></span>
          <?php endif; ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php foreach ($_rc as $_key => $_item): ?>
  <?php if (empty($_rcCounts[$_key])) continue; ?>
  <button type="button" class="gh-badge" data-key="<?php echo $_key; ?>" aria-label="React <?php echo $_key; ?>">
    <?php if ($_item['type'] === 'svg'): ?>
      <?php echo $_item['value']; ?>
    <?php elseif ($_item['type'] === 'img'): ?>
      <img src="<?php echo $_item['value']; ?>" alt="" class="gh-emoji">
    <?php else: ?>
      <span class="gh-emoji"><?php echo $_item['value']; ?></span>
    <?php endif; ?>
    <span class="gh-count"><?php echo (int)($_rcCounts[$_key] ?? 0); ?></span>
  </button>
  <?php endforeach; ?>
</div>
<script>
(function () {
  var block = document.querySelector(".gh-reactions");
  if (!block) return;
  var addBtn = block.querySelector(".gh-add");
  var panel = block.querySelector(".gh-panel");
  var cid = block.dataset.cid, url = block.dataset.url;
  var storeKey = "fuwari_react_" + cid;
  var sel = new Set((localStorage.getItem(storeKey) || "").split(",").filter(Boolean));
  var badges = {};

  block.querySelectorAll(".gh-badge").forEach(function (b) {
    badges[b.dataset.key] = b;
  });
  block.querySelectorAll(".gh-item").forEach(function (item) {
    if (sel.has(item.dataset.key)) item.classList.add("selected");
  });

  function ensureBadge(key) {
    var badge = badges[key];
    if (badge) return badge;
    var ref = block.querySelector('.gh-item[data-key="' + key + '"]');
    if (!ref) return null;
    badge = ref.cloneNode(true);
    badge.classList.remove("gh-item");
    badge.classList.add("gh-badge");
    badge.appendChild(document.createElement("span")).className = "gh-count";
    badges[key] = badge;
    block.insertBefore(badge, block.querySelector(".relative").nextSibling);
    badge.addEventListener("click", function () { react(key); });
    return badge;
  }
  if (sel.size) {
    sel.forEach(function (k) {
      var b = ensureBadge(k);
      if (b) b.classList.add("selected");
    });
  }

  function closePanel() {
    panel.classList.remove("open");
    panel.hidden = true;
    addBtn.setAttribute("aria-expanded", "false");
  }

  addBtn.addEventListener("click", function (e) {
    e.stopPropagation();
    if (panel.hidden) {
      panel.hidden = false;
      requestAnimationFrame(function () { panel.classList.add("open"); });
      addBtn.setAttribute("aria-expanded", "true");
    } else {
      closePanel();
    }
  });
  document.addEventListener("click", function (e) {
    if (!block.contains(e.target)) closePanel();
  });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closePanel();
  });

  function post(key, action) {
    return fetch(url + "?reaction=1&cid=" + cid + "&emoji=" + key + "&action=" + action)
      .then(function (r) { return r.json(); })
      .then(function (data) { return data && data.ok ? data : null; });
  }

  function setBadge(key, count) {
    var badge = ensureBadge(key);
    if (!badge) return;
    var span = badge.querySelector(".gh-count");
    span.textContent = count;
    badge.hidden = count <= 0;
  }

  function persist() {
    if (sel.size) localStorage.setItem(storeKey, Array.from(sel).join(","));
    else localStorage.removeItem(storeKey);
  }

  function setSelected(key, on) {
    if (on) sel.add(key);
    else sel.delete(key);
    block.querySelectorAll(".gh-item").forEach(function (item) {
      item.classList.toggle("selected", sel.has(item.dataset.key));
    });
    Object.keys(badges).forEach(function (k) {
      badges[k].classList.toggle("selected", sel.has(k));
    });
    persist();
  }

  function react(key) {
    if (block.dataset.busy) return;
    var on = !sel.has(key);
    block.dataset.busy = "1";
    post(key, on ? "add" : "remove").then(function (data) {
      if (data) {
        setBadge(key, data.count);
        setSelected(key, on);
      }
    }).finally(function () { delete block.dataset.busy; });
  }

  block.querySelectorAll(".gh-item").forEach(function (btn) {
    btn.addEventListener("click", function () { react(this.dataset.key); });
  });
  block.querySelectorAll(".gh-badge").forEach(function (btn) {
    btn.addEventListener("click", function () { react(btn.dataset.key); });
  });
})();
</script>
