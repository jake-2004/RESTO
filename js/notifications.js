// Notifications handling
function loadNotifications() {
    console.log('Loading notifications...');
    $.ajax({
        url: 'get_notifications.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log('Notifications data:', data);
            if (data.notifications && data.notifications.length > 0) {
                let notificationHtml = '';
                let unreadCount = 0;
                
                data.notifications.forEach(function(notification) {
                    console.log('Processing notification:', notification);
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
            console.log('Response:', xhr.responseText);
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

// Function to show toast notification
function showToastNotification(message) {
    // Create toast element if it doesn't exist
    if ($('#notification-toast').length === 0) {
        $('body').append(`
            <div id="notification-toast" style="position: fixed; top: 20px; right: 20px; 
                background-color: #ffbe33; color: #fff; padding: 15px; border-radius: 5px; 
                box-shadow: 0 4px 8px rgba(0,0,0,0.1); z-index: 9999; display: none;">
                <div id="toast-message"></div>
            </div>
        `);
    }
    
    // Set message and show toast
    $('#toast-message').text(message);
    $('#notification-toast').fadeIn().delay(5000).fadeOut();
}

// Check for new table booking notifications specifically
function checkTableBookingNotifications() {
    $.ajax({
        url: 'check_booking_notifications.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.hasNewNotifications) {
                // Reload all notifications if there are new booking notifications
                loadNotifications();
                
                // Optionally show a toast notification
                if (data.message) {
                    showToastNotification(data.message);
                }
            }
        }
    });
}

// Initialize notifications
$(document).ready(function() {
    console.log('Loading notifications...');
    
    function loadNotifications() {
        $.ajax({
            url: 'get_notifications.php',
            method: 'GET',
            success: function(data) {
                console.log('Notifications data:', data);
                if (data.notifications && Array.isArray(data.notifications)) {
                    data.notifications.forEach(function(notification) {
                        console.log('Processing notification:', notification);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading notifications:', error);
            }
        });
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

    // Load notifications when page loads
    loadNotifications();
    
    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
}); 