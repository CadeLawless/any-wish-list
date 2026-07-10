<?php
// Display flash messages
if (isset($flash['success'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../../public/images/site-images/menu-close.php');
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
                require(__DIR__ . '/../../../public/images/site-images/menu-close.php');
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
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>
    <div class="content">
        <p style="margin: 0 0 20px;"><a class="button accent" href="/admin/gift-wraps<?php echo isset($pageno) ? '?pageno=' . (int)$pageno : ''; ?>">Back to Gift Wraps</a></p>
        <?php
        $add = true;
        include __DIR__ . '/_form.php';
        ?>
    </div>
</div>

<style>
    .form-container {
        margin: 20px auto 30px;
        background-color: var(--background-darker);
        max-width: 500px;
        padding: 20px;
    }
</style>

<script src="/public/js/form-validation.js?v=2.9"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="/public/js/gift-wrap-form.js?v=2.9"></script>