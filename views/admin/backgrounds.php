<?php
/**
 * Variables available in this view:
 * @var array $backgrounds
 * @var int $currentPage
 * @var int $totalPages
 * @var array $all_backgrounds
 * @var string $searchTerm
 */

// Display flash messages
if (isset($flash['success'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../public/images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['success']) . "</label></p>
            </div>
        </div>
    </div>";
}

if (isset($flash['error'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../public/images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['error']) . "</label></p>
            </div>
        </div>
    </div>";
}
?>

<h1 class="center">Admin Center</h1>
<div class="sidebar-main">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    <div class="content">
        <h2 style="margin: 0;" class="items-list-title">All Backgrounds<a href="/admin/backgrounds/add?pageno=<?php echo $currentPage; ?>" class="icon-container add-item">
            <?php require(__DIR__ . "/../../public/images/site-images/icons/plus.php"); ?>
            <div class='inline-label'>Add</div></a></h2>
        
        <?php 
        $searchOptions = [
            'placeholder' => 'Search backgrounds by name, tag, or image...',
            'input_id' => 'backgrounds-search',
            'container_class' => 'center',
            'search_term' => $searchTerm ?? ''
        ];
        include __DIR__ . '/../components/admin-table-search.php'; 
        ?>
        
        <div class="admin-center-table-container">
            <table class="admin-center-table">
                <thead>
                    <tr>
                        <th class="th_border">ID</th>
                        <th class="th_border">Tag</th>
                        <th class="th_border">Name</th>
                        <th class="th_border">Thumbnail</th>
                        <th class="th_border">Image</th>
                        <th class="th_border">Default Gift Wrap</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    <?php echo \App\Services\AdminRenderService::generateBackgroundsTableHtml($backgrounds, $currentPage); ?>
                </tbody>
            </table>
        </div>
        
        <?php if(isset($all_backgrounds) && count($all_backgrounds) > 0): ?>
            <!-- Bottom Pagination controls -->
            <?php 
            $pageno = $currentPage;
            $total_pages = $totalPages;
            $type = 'admin';
            $position = 'bottom';
            $total_count = count($all_backgrounds);
            $item_label = 'backgrounds';
            include __DIR__ . '/../components/pagination-controls.php'; 
            ?>
        <?php endif; ?>
    </div>
</div>

<script src="/public/js/admin-table-search.js?v=2.9"></script>
<?php if(isset($all_backgrounds) && count($all_backgrounds) > 0 && isset($totalPages) && $totalPages > 1): ?>
<script src="/public/js/pagination.js?v=2.9"></script>
<?php endif; ?>

