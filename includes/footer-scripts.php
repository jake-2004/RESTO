<!-- jQery -->
<script src="js/jquery-3.4.1.min.js"></script>
<!-- popper js -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<!-- bootstrap js -->
<script src="js/bootstrap.js"></script>
<!-- owl slider -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<!-- isotope js -->
<script src="https://unpkg.com/isotope-layout@3.0.4/dist/isotope.pkgd.min.js"></script>
<!-- nice select -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js"></script>
<!-- custom js -->
<script src="js/custom.js"></script>

<!-- Notification scripts -->
<script>
  $(document).ready(function() {
    // Function to load notifications
    function loadNotifications() {
      $.ajax({
        url: 'get_notifications.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          if (data.notifications && data.notifications.length > 0) {
            let notificationHtml = '';
            let unreadCount = 0;
            
            data.notifications.forEach(function(notification) {
              if (notification.is_read == 0) {
                unreadCount++;
              }
              
              notificationHtml += `
                <div class="notification-item ${notification.is_read == 0 ? 'unread' : ''}" 
                     data-id="${notification.id}">
                  <div>${notification.message}</div>
                  <div class="time">${timeAgo(new Date(notification.created_at))}</div>
                </div>
              `;
            });
            
            $('#notification-list').html(notificationHtml);
            
            if (unreadCount > 0) {
              $('#notification-count').text(unreadCount).show();
            } else {
              $('#notification-count').hide();
            }
          } else {
            $('#notification-list').html('<div class="notification-empty">No notifications</div>');
            $('#notification-count').hide();
          }
        },
        error: function(xhr, status, error) {
          console.error('Error loading notifications:', error);
          $('#notification-list').html('<div class="notification-empty">Error loading notifications</div>');
        }
      });
    }
    
    // Function to format time ago
    function timeAgo(date) {
      const seconds = Math.floor((new Date() - date) / 1000);
      
      let interval = Math.floor(seconds / 31536000);
      if (interval > 1) return interval + ' years ago';
      if (interval === 1) return '1 year ago';
      
      interval = Math.floor(seconds / 2592000);
      if (interval > 1) return interval + ' months ago';
      if (interval === 1) return '1 month ago';
      
      interval = Math.floor(seconds / 86400);
      if (interval > 1) return interval + ' days ago';
      if (interval === 1) return '1 day ago';
      
      interval = Math.floor(seconds / 3600);
      if (interval > 1) return interval + ' hours ago';
      if (interval === 1) return '1 hour ago';
      
      interval = Math.floor(seconds / 60);
      if (interval > 1) return interval + ' minutes ago';
      if (interval === 1) return '1 minute ago';
      
      if (seconds < 10) return 'just now';
      
      return Math.floor(seconds) + ' seconds ago';
    }
    
    // Click handler for marking notifications as read
    $(document).on('click', '.notification-item', function() {
      const id = $(this).data('id');
      $.ajax({
        url: 'mark_notification_read.php',
        type: 'POST',
        data: { notification_id: id },
        dataType: 'json',
        success: function() {
          loadNotifications();
        }
      });
    });
    
    // Click handler for marking all notifications as read
    $('#mark-all-read').click(function(e) {
      e.preventDefault();
      $.ajax({
        url: 'mark_all_notifications_read.php',
        type: 'POST',
        dataType: 'json',
        success: function() {
          loadNotifications();
        }
      });
    });
    
    // Load notifications when page loads
    loadNotifications();
    
    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
    
    // Initialize dropdown
    $('.dropdown-toggle').dropdown();
  });
</script> 