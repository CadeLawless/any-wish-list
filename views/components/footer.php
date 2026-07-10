<footer>
  <p class="center">&copy; <?php echo date("Y"); ?> Any Wish List.<br>
  Designed by Cade and Meleah Lawless. All rights reserved.</p>
</footer>
<script src="/public/js/popups.js?v=2.9"></script>
<script>
  $(document).ready(function(){
    let isToggling = false; // Prevent multiple simultaneous requests
    let lastClickTime = 0;
    
    // Remove any existing event listeners to prevent duplicates
    $(document).off("click", ".dark-mode-link, .light-mode-link");
    
    // Use event delegation to handle clicks on toggle links
    $(document).on("click", ".dark-mode-link, .light-mode-link", function(e){
      const now = Date.now();
      
      // Prevent rapid successive clicks (within 500ms)
      if (now - lastClickTime < 500) {
        e.preventDefault();
        e.stopPropagation();
        return;
      }
      
      if (isToggling) {
        e.preventDefault();
        e.stopPropagation();
        return;
      }
      
      lastClickTime = now;
      
      e.preventDefault();
      e.stopPropagation();
      isToggling = true;
      
      // Toggle the dark class immediately for visual feedback
      $(document.body).toggleClass("dark");

      $dark = $(document.body).hasClass("dark") ? "Yes" : "No";
      
      $.ajax({
            type: "POST",
            url: "/toggle-dark-mode",
            data: {
                dark: $dark,
            },
            success: function(response) {
                isToggling = false; // Reset flag on success
            },
            error: function(xhr, status, error) {
                // If request fails, revert the visual change
                $(document.body).toggleClass("dark");
                isToggling = false; // Reset flag on error
            }
        });
    });
  });
</script>
<script>
    // Header dropdown functionality
    $(document).ready(function() {
        // Hamburger menu functionality
        $(".hamburger-menu").on("click", function(){
            $(this).addClass("hidden");
            $(".close-menu").removeClass("hidden");
            $(".menu-links").css("display", "flex").removeClass("hidden");
        });
        
        $(".close-menu").on("click", function(){
            $(this).addClass("hidden");
            $(".hamburger-menu").removeClass("hidden");
            $(".menu-links").addClass("hidden");
        });
        
        // Dropdown functionality
        $(document).off('click', '.dropdown-link').on('click', '.dropdown-link', function(e) {
            // If clicking on a dropdown menu link, let it work normally
            if ($(e.target).hasClass('dropdown-menu-link')) {
                return; // Let the link work normally
            }
            
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            const $dropdownMenu = $(this).find('.dropdown-menu');
            
            if ($dropdownMenu.hasClass('hidden')) {
                $dropdownMenu.removeClass('hidden').show();
                $(this).addClass('active');
            } else {
                $dropdownMenu.addClass('hidden').hide();
                $(this).removeClass('active');
            }
        });
        
        // Close dropdown when clicking outside - separate handler to prevent recursion
        $(document).on("click", function(e){
            // Only handle if not clicking on dropdown or mobile menu elements
            if (!$(e.target).closest(".dropdown-link, .menu-links, .hamburger-menu, .close-menu").length) {
                // Close mobile menu if open
                if($(".menu-links").css("display") == "flex"){
                    $(".close-menu").addClass("hidden");
                    $(".hamburger-menu").removeClass("hidden");
                    $(".menu-links").addClass("hidden");
                }
                // Close dropdown if open
                if($(".dropdown-menu:not(.hidden)").length){
                    $(".dropdown-link.active").removeClass("active");
                    $(".dropdown-menu:not(.hidden)").addClass("hidden").hide();
                }
            }
        });
    });
</script>
