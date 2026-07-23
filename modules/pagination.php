<?php
use Typecho\Router;
$_totalItems = $this->getTotal();
$_pageSize = $this->parameter->pageSize;
$_totalPages = max(1, (int)ceil($_totalItems / $_pageSize));
$_current = $this->currentPage;
$_hasPrev = $_current > 1;
$_hasNext = $_current < $_totalPages;
if ($_totalPages <= 1) return;
$_urlTemplate = Router::url(
    $this->parameter->type . (strpos($this->parameter->type, '_page') === false ? '_page' : ''),
    $this->pageRow,
    $this->options->index
);
$_pageUrl = function($page) use ($_urlTemplate) {
    return str_replace('{page}', $page, $_urlTemplate);
};
?>
<div class="onload-animation mx-auto flex flex-row justify-center gap-3" style="animation-delay: calc(var(--content-delay) + 0ms)">
    <?php /* Prev */ ?>
    <a href="<?php echo $_hasPrev ? $_pageUrl($_current - 1) : '#'; ?>"<?php if (!$_hasPrev): ?> class="btn-card h-11 w-11 overflow-hidden rounded-lg disabled"<?php else: ?> class="btn-card h-11 w-11 overflow-hidden rounded-lg text-[var(--primary)]" aria-label="Previous Page"<?php endif; ?>>
        <span class="icon-[material-symbols--chevron-left-rounded] text-[1.75rem]"></span>
    </a>

    <?php /* Page numbers in card-bg container */ ?>
    <div class="flex flex-row items-center rounded-lg bg-[var(--card-bg)]">
        <?php if ($_totalPages <= 7): ?>
            <?php for ($_i = 1; $_i <= $_totalPages; $_i++): ?>
                <?php if ($_i == $_current): ?>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-[var(--primary)] font-bold text-white"><?php echo $_i; ?></div>
                <?php else: ?>
                <a href="<?php echo $_pageUrl($_i); ?>" aria-label="Page <?php echo $_i; ?>" class="btn-card h-11 w-11 overflow-hidden rounded-lg active:scale-[0.85] text-[var(--btn-content)]"><?php echo $_i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        <?php else: ?>
            <?php /* First page */ ?>
            <?php if ($_current == 1): ?>
            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-[var(--primary)] font-bold text-white">1</div>
            <?php else: ?>
            <a href="<?php echo $_pageUrl(1); ?>" aria-label="Page 1" class="btn-card h-11 w-11 overflow-hidden rounded-lg active:scale-[0.85] text-[var(--btn-content)]">1</a>
            <?php endif; ?>

            <?php if ($_current <= 3): ?>
                <?php /* Near start: 1 2 3 4 5 ... total */ ?>
                <?php for ($_i = 2; $_i <= 5; $_i++): ?>
                    <?php if ($_i == $_current): ?>
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-[var(--primary)] font-bold text-white"><?php echo $_i; ?></div>
                    <?php else: ?>
                    <a href="<?php echo $_pageUrl($_i); ?>" aria-label="Page <?php echo $_i; ?>" class="btn-card h-11 w-11 overflow-hidden rounded-lg active:scale-[0.85] text-[var(--btn-content)]"><?php echo $_i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <span class="icon-[material-symbols--more-horiz] mx-1 my-auto text-lg"></span>
            <?php elseif ($_current >= $_totalPages - 2): ?>
                <?php /* Near end: 1 ... total-4 total-3 total-2 total-1 total */ ?>
                <span class="icon-[material-symbols--more-horiz] mx-1 my-auto text-lg"></span>
                <?php for ($_i = $_totalPages - 4; $_i <= $_totalPages - 1; $_i++): ?>
                    <?php if ($_i == $_current): ?>
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-[var(--primary)] font-bold text-white"><?php echo $_i; ?></div>
                    <?php else: ?>
                    <a href="<?php echo $_pageUrl($_i); ?>" aria-label="Page <?php echo $_i; ?>" class="btn-card h-11 w-11 overflow-hidden rounded-lg active:scale-[0.85] text-[var(--btn-content)]"><?php echo $_i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php else: ?>
                <?php /* Middle: 1 ... curr-2 curr-1 curr curr+1 curr+2 ... total */ ?>
                <span class="icon-[material-symbols--more-horiz] mx-1 my-auto text-lg"></span>
                <?php for ($_i = $_current - 2; $_i <= $_current + 2; $_i++): ?>
                    <?php if ($_i == $_current): ?>
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-[var(--primary)] font-bold text-white"><?php echo $_i; ?></div>
                    <?php else: ?>
                    <a href="<?php echo $_pageUrl($_i); ?>" aria-label="Page <?php echo $_i; ?>" class="btn-card h-11 w-11 overflow-hidden rounded-lg active:scale-[0.85] text-[var(--btn-content)]"><?php echo $_i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <span class="icon-[material-symbols--more-horiz] mx-1 my-auto text-lg"></span>
            <?php endif; ?>

            <?php /* Last page */ ?>
            <?php if ($_totalPages == $_current): ?>
            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-[var(--primary)] font-bold text-white"><?php echo $_totalPages; ?></div>
            <?php else: ?>
            <a href="<?php echo $_pageUrl($_totalPages); ?>" aria-label="Page <?php echo $_totalPages; ?>" class="btn-card h-11 w-11 overflow-hidden rounded-lg active:scale-[0.85] text-[var(--btn-content)]"><?php echo $_totalPages; ?></a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php /* Next */ ?>
    <a href="<?php echo $_hasNext ? $_pageUrl($_current + 1) : '#'; ?>"<?php if (!$_hasNext): ?> class="btn-card h-11 w-11 overflow-hidden rounded-lg disabled"<?php else: ?> class="btn-card h-11 w-11 overflow-hidden rounded-lg text-[var(--primary)]" aria-label="Next Page"<?php endif; ?>>
        <span class="icon-[material-symbols--chevron-right-rounded] text-[1.75rem]"></span>
    </a>
</div>
