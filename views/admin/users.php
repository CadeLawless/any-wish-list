<?php
/**
 * Variables available in this view:
 * @var array $users
 * @var int $currentPage
 * @var int $totalPages
 * @var array $all_users
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
        <h2 style="margin: 0;" class="items-list-title">All Users</h2>
        
        <?php 
        $searchOptions = [
            'placeholder' => 'Search users by name, username, or email...',
            'input_id' => 'users-search',
            'container_class' => 'center',
            'search_term' => $searchTerm ?? ''
        ];
        include __DIR__ . '/../components/admin-table-search.php'; 
        ?>
        
        <div class="admin-center-table-container">
            <table class="admin-center-table">
                <thead>
                    <tr>
                        <th class="th_border">Name</th>
                        <th class="th_border">Username</th>
                        <th class="th_border">Email</th>
                        <th class="th_border">Role</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    <?php echo \App\Services\AdminRenderService::generateUsersTableHtml($users); ?>
                </tbody>
            </table>
        </div>
        
        <?php if(isset($all_users) && count($all_users) > 0): ?>
            <!-- Bottom Pagination controls -->
            <?php 
            $pageno = $currentPage;
            $total_pages = $totalPages;
            $type = 'admin';
            $position = 'bottom';
            $total_count = count($all_users);
            $item_label = 'users';
            include __DIR__ . '/../components/pagination-controls.php'; 
            ?>
        <?php endif; ?>
    </div>
</div>

<script src="/public/js/admin-table-search.js?v=2.9"></script>
<?php if(isset($all_users) && count($all_users) > 0 && isset($totalPages) && $totalPages > 1): ?>
<script src="/public/js/pagination.js?v=2.9"></script>
<?php endif; ?>

