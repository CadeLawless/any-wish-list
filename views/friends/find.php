<?php 
/**
 * Variables available in this view:
 * @var array $friendList
 * @var \App\Services\FriendService $friendService
 * @var array $receivedInvitations
 * @var array $newFriends
 * @var array $allUsers
 * @var string $searchTerm
 * @var array $user
 */

use App\Services\FriendService;

if(isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchTerm = trim($_GET['search']);
    list($allUsers, $friendList, $newFriends, $receivedInvitations) = $friendService->searchUsersAndCategorize($user['username'], $searchTerm);
}
?>

<div class="center">
    <h1 style="max-width: 500px; margin: 0 auto 0.67em;">Add friends to see what others are wishing for.</h1>
    <?php
    $options = [
        'placeholder' => 'Search for people by name or username...',
        'input_id' => 'friends-search',
        'input_class' => 'friends-search-input',
        'container_class' => 'center',
        'search_term' => $searchTerm ?? ''
    ];
    include __DIR__ . '/../components/admin-table-search.php';
    ?>
</div>

<div class="friends-search-results-container">
    <?php if (isset($searchTerm) && $searchTerm !== ''): ?>
        <?php require __DIR__ . '/../components/friends-results.php'; ?>
    <?php endif; ?>
</div>

<script src="/public/js/admin-table-search.js?v=2.9"></script>
<script src="/public/js/button-loading.js?v=2.9"></script>
<script src="/public/js/friend-search.js?v=2.9"></script>