<?php 
/**
 * Variables available in this view:
 * @var array $friendList
 * @var array $receivedInvitations
 * @var array $newFriends
 * @var array $allUsers
 * @var string $searchTerm
 * @var array $user
 * @var object $friendService
 */

if(isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchTerm = trim($_GET['search']);
    list($allUsers, $friendList, $newFriends, $receivedInvitations) = $friendService->searchUsersAndCategorize($user['username'], $searchTerm);
}
?>

<?php if(count($friendList) > 0): ?>
    <div class="notification-banner">
        Keep your public wish list page updated!
        <a style="display: block;" href="/<?php echo htmlspecialchars($user['username']); ?>/wishlists" class="banner-link">View Your Page</a>
    </div>
<?php endif; ?>

<h1 class="center">Add Friends</h1>

<?php if(count($friendList) === 0 && count($receivedInvitations) === 0 && !isset($searchTerm)): ?>
    <div class="center">
        <p>You have no friends or friend requests yet. <a href="/friends/find">Find friends to get started!</a></p>
    </div>
<?php else: ?>

    <div class="center">
        <?php
        $options = [
            'placeholder' => 'Search for friends by name or username...',
            'input_id' => 'friends-search',
            'input_class' => 'friends-search-input',
            'container_class' => 'center',
            'search_term' => $searchTerm ?? ''
        ];
        include __DIR__ . '/../components/admin-table-search.php';
        ?>
    </div>

    <div class="friends-section friends-search-results-container add-friends-results-container">
        <?php require __DIR__ . '/../components/friends-results.php'; ?>
    </div>
<?php endif; ?>

<script src="/public/js/admin-table-search.js?v=2.9"></script>
<script src="/public/js/button-loading.js?v=2.9"></script>
<script src="/public/js/friend-search.js?v=2.9"></script>