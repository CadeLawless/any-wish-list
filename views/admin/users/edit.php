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
        <p style="margin: 0 0 20px;"><a class="button accent" href="/admin/users<?php echo isset($pageno) ? '?pageno=' . (int)$pageno : ''; ?>">Back to Users</a></p>
        
        <div class="form-container">
            <h2>Edit User</h2>
            
            <!-- User Info Form -->
            <form method="POST" action="/admin/users/update">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($editUser['username'] ?? ''); ?>">
                <input type="hidden" name="pageno" value="<?php echo isset($pageno) ? (int)$pageno : 1; ?>">
                
                <?php if(isset($error_msg)) echo $error_msg; ?>
                
                <h3>User Information</h3>
                <div class="flex form-flex">
                    <div class="large-input">
                        <label for="username_display">Username:<br></label>
                        <input type="text" id="username_display" value="<?php echo htmlspecialchars($editUser['username'] ?? ''); ?>" disabled style="background-color: var(--background-darker); cursor: not-allowed;" />
                    </div>
                    
                    <div class="large-input">
                        <label for="name">Name:<br></label>
                        <input required type="text" name="name" id="name" value="<?php echo htmlspecialchars($name ?? $editUser['name'] ?? ''); ?>" maxlength="50" />
                    </div>
                    
                    <div class="large-input">
                        <label for="email">Email:<br></label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email ?? $editUser['email'] ?? ''); ?>" />
                    </div>
                    
                    <div class="large-input">
                        <label for="role">Role:<br></label>
                        <select required name="role" id="role">
                            <option value="User" <?php echo (($role ?? $editUser['role'] ?? '') == 'User') ? 'selected' : ''; ?>>User</option>
                            <option value="Admin" <?php echo (($role ?? $editUser['role'] ?? '') == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div class="large-input center">
                        <input type="submit" class="button text" value="Update User" />
                    </div>
                </div>
            </form>
            
            <br />
            
            <!-- Password Reset Form -->
            <h3>Password Reset</h3>
            <form method="POST" action="/admin/users/send-password-reset">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($editUser['username'] ?? ''); ?>">
                <input type="hidden" name="pageno" value="<?php echo isset($pageno) ? (int)$pageno : 1; ?>">
                <div class="flex form-flex">
                    <?php if (empty($editUser['email'])): ?>
                        <p class='large-input no-margin-top'>User does not have an email set up. Please set an email above before sending a password reset link.</p>
                    <?php else: ?>
                        <p class='large-input no-margin-top'>Send a password reset email to <strong><?php echo htmlspecialchars($editUser['email']); ?></strong></p>
                        <div class="large-input">
                            <input type="submit" class="button text" value="Send Password Reset Email" />
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
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
<script>
$(document).ready(function() {
    FormValidator.init('form', {
        name: {
            required: true,
            minLength: 2,
            maxLength: 50
        },
        email: {
            email: true
        },
        role: {
            required: true
        }
    });
});
</script>

