<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Feane</title>
<!-- Bootstrap core CSS -->
<link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!-- Custom styles -->
<link href="css/style.css" rel="stylesheet" />
<!-- responsive style -->
<link href="css/responsive.css" rel="stylesheet" />

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Manual implementation of dropdown functionality
    const dropdownToggleElements = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggleElements.forEach(function(dropdown) {
      dropdown.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Find the dropdown menu that's a sibling of the current toggle
        const dropdownMenu = this.nextElementSibling;
        
        // Close all other dropdowns first
        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
          if (menu !== dropdownMenu) {
            menu.classList.remove('show');
          }
        });
        
        // Toggle current dropdown
        dropdownMenu.classList.toggle('show');
      });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
          menu.classList.remove('show');
        });
      }
    });
  });
</script>

<style>
  /* Fix dropdown styling */
  .dropdown-menu {
    display: none;
    position: absolute;
    z-index: 1000;
    background-color: #212121;
    border: 1px solid rgba(255, 190, 51, 0.2);
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    padding: 8px 0;
    min-width: 10rem;
    top: 100%;
    left: 0;
    margin-top: 0.125rem;
  }
  
  .dropdown-menu.show {
    display: block !important;
  }
  
  .dropdown-item {
    display: block;
    width: 100%;
    padding: 8px 16px;
    clear: both;
    color: #fff;
    text-decoration: none;
    background-color: transparent;
    white-space: nowrap;
    transition: all 0.3s ease;
  }
  
  .dropdown-item:hover {
    background-color: rgba(255, 190, 51, 0.1);
    color: #ffbe33;
  }
  
  /* Ensure dropdown is above other elements */
  .dropdown {
    position: relative;
  }
  
  /* Make dropdown toggle show a pointer cursor */
  .dropdown-toggle {
    cursor: pointer;
  }
  
  /* Add a small arrow to dropdown toggles */
  .dropdown-toggle::after {
    display: inline-block;
    margin-left: 0.255em;
    vertical-align: 0.255em;
    content: "";
    border-top: 0.3em solid;
    border-right: 0.3em solid transparent;
    border-bottom: 0;
    border-left: 0.3em solid transparent;
  }
</style>
